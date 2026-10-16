<?php

namespace App\Providers;

use Anthropic\Client;
use App\Models\Comment;
use App\Models\Course;
use App\Models\User;
use App\Services\AIValidationService;
use App\Services\BoldService;
use App\Services\BunnyStreamService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BunnyStreamService::class, function () {
            return new BunnyStreamService(
                libraryId: (string) config('services.bunny_stream.library_id'),
                hostname: (string) config('services.bunny_stream.hostname'),
                tokenKey: config('services.bunny_stream.token_key') ?: null,
            );
        });

        $this->app->singleton(BoldService::class, function () {
            return new BoldService(
                apiKey: (string) config('services.bold.key'),
                baseUrl: (string) config('services.bold.base_url'),
                webhookSecret: (string) config('services.bold.webhook_secret'),
                sandbox: (bool) config('services.bold.sandbox'),
            );
        });

        $this->app->singleton(AIValidationService::class, function () {
            $apiKey = config('services.anthropic.api_key');

            return new AIValidationService(
                client: $apiKey ? new Client(apiKey: $apiKey) : null,
                model: (string) config('services.anthropic.validation_model'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', function (User $user) {
            return (bool) ($user->is_admin ?? false);
        });

        // Caps that a real visitor never reaches but that stop scripted abuse:
        // mass sign-ups, reset-email flooding, upload or comment spam, payment-link churn
        RateLimiter::for('registrations', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinutes(15, 5)->by($request->ip()));
        RateLimiter::for('document-uploads', fn (Request $request) => Limit::perHour(5)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('comments', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));

        // Password recovery email in the visitor's language, with the brand's wording
        ResetPassword::toMailUsing(function (User $user, string $token) {
            $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject(__('app.reset_email_subject'))
                ->greeting(__('app.reset_email_greeting', ['name' => $user->name]))
                ->line(__('app.reset_email_intro'))
                ->action(__('app.reset_email_action'), route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]))
                ->line(__('app.reset_email_expire', ['minutes' => $expireMinutes]))
                ->line(__('app.reset_email_ignore'))
                ->salutation(__('app.reset_email_salutation'));
        });

        // Share a small courses list for the header navigation
        try {
            $courses = Course::orderBy('title')->limit(20)->get(['id', 'title', 'slug']);
        } catch (\Throwable $e) {
            $courses = collect();
        }

        View::share('navCourses', $courses);

        // Pending-comments badge for the admin nav (top-level comments with no admin reply yet)
        View::composer('layouts.admin', function ($view) {
            try {
                $count = Comment::whereNull('parent_id')
                    ->whereDoesntHave('replies', fn ($q) => $q->whereHas('user', fn ($q2) => $q2->where('is_admin', true)))
                    ->count();
            } catch (\Throwable $e) {
                $count = 0;
            }

            $view->with('pendingCommentsCount', $count);
        });
    }
}

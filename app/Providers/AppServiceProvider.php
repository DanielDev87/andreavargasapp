<?php

namespace App\Providers;

use Anthropic\Client;
use App\Models\Comment;
use App\Models\Course;
use App\Models\User;
use App\Services\AIValidationService;
use App\Services\BunnyStreamService;
use Illuminate\Support\Facades\Gate;
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

<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  @include('partials.theme-init')
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <title>{{ __('app.profile_title') }} — {{ __('app.brand_name') }}</title>
</head>
<body class="bg-white dark:bg-ink-950 min-h-screen text-ink-800 dark:text-ink-100 font-sans antialiased transition-colors duration-300">

  <header class="bg-white dark:bg-ink-900 shadow-sm py-4 border-b border-gold-500/10">
    <div class="container mx-auto px-4 md:px-8 flex justify-between items-center max-w-5xl">
      <a href="/" class="font-serif text-shine font-bold text-2xl">{{ __('app.brand_name') }}</a>
      <div class="flex items-center gap-4">
        @include('partials.theme-toggle')
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="text-sm font-semibold text-ink-600 dark:text-ink-300 hover:text-gold-500 transition">{{ __('app.nav_logout') }}</button>
        </form>
      </div>
    </div>
  </header>

  <main class="container mx-auto px-4 md:px-8 py-12 max-w-5xl">
    @if(session('status'))
      <div class="mb-6 p-4 rounded-lg bg-gold-50 dark:bg-ink-900 border border-gold-500/20 text-ink-700 dark:text-ink-200">{{ session('status') }}</div>
    @endif
    @if(session('warning'))
      <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-300">{{ session('warning') }}</div>
    @endif

    <h1 class="text-2xl font-bold">{{ __('app.profile_welcome') }}, {{ $user->name }}.</h1>

    {{-- Validation status --}}
    <section class="mt-8 bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-6 shadow-md">
      <h2 class="text-lg font-bold text-gold-600 dark:text-gold-400">{{ __('app.profile_validation_title') }}</h2>

      @if(! $latestValidation)
        <p class="mt-2 text-ink-600 dark:text-ink-300">{{ __('app.profile_validation_none') }}</p>
        <a href="{{ route('validation.create') }}" class="inline-block mt-4 shine-sweep bg-gold-500 text-ink-950 font-semibold px-5 py-2 rounded-lg hover:bg-gold-400 transition">{{ __('app.profile_validation_cta') }}</a>
      @elseif($latestValidation->status === 'approved')
        <p class="mt-2 text-emerald-600 dark:text-emerald-400 font-medium">{{ __('app.profile_validation_approved') }}</p>
      @elseif($latestValidation->status === 'rejected')
        <p class="mt-2 text-red-600 dark:text-red-400 font-medium">{{ __('app.profile_validation_rejected') }}</p>
        <a href="{{ route('validation.create') }}" class="inline-block mt-4 shine-sweep bg-gold-500 text-ink-950 font-semibold px-5 py-2 rounded-lg hover:bg-gold-400 transition">{{ __('app.profile_validation_cta') }}</a>
      @else
        <p class="mt-2 text-ink-600 dark:text-ink-300">{{ __('app.profile_validation_pending') }}</p>
      @endif
    </section>

    {{-- Purchased courses --}}
    <section class="mt-8">
      <h2 class="text-lg font-bold text-gold-600 dark:text-gold-400">{{ __('app.profile_courses_title') }}</h2>

      @if($purchases->isEmpty())
        <p class="mt-2 text-ink-600 dark:text-ink-300">{{ __('app.profile_courses_empty') }}</p>
        <a href="/#cursos" class="inline-block mt-3 text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.profile_courses_browse') }} &rarr;</a>
      @else
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          @foreach($purchases as $purchase)
            <div class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-5 shadow-md">
              <h3 class="font-semibold">{{ $purchase->course->title }}</h3>
              <span class="inline-block mt-1 text-xs font-semibold uppercase tracking-wide text-ink-500 dark:text-ink-400">{{ __('app.profile_status_'.$purchase->status) }}</span>

              @if(in_array($purchase->status, ['paid', 'completed']))
                <a href="{{ route('courses.show', $purchase->course) }}" class="block mt-3 text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.profile_continue') }} &rarr;</a>
              @endif
            </div>
          @endforeach
        </div>
      @endif
    </section>

    {{-- Certificates --}}
    <section class="mt-8">
      <h2 class="text-lg font-bold text-gold-600 dark:text-gold-400">{{ __('app.profile_certificates_title') }}</h2>

      @if($certificates->isEmpty())
        <p class="mt-2 text-ink-600 dark:text-ink-300">{{ __('app.profile_certificates_empty') }}</p>
      @else
        <ul class="mt-4 space-y-2">
          @foreach($certificates as $certificate)
            <li class="flex items-center justify-between bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-4 shadow-md">
              <span>{{ $certificate->course->title }}</span>
              <a href="{{ Storage::disk('public')->url($certificate->certificate_path) }}" target="_blank" class="text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.profile_certificate_download') }} &rarr;</a>
            </li>
          @endforeach
        </ul>
      @endif
    </section>

    {{-- Badges --}}
    <section class="mt-8">
      <h2 class="text-lg font-bold text-gold-600 dark:text-gold-400">{{ __('app.profile_badges_title') }}</h2>

      @if($badges->isEmpty())
        <p class="mt-2 text-ink-600 dark:text-ink-300">{{ __('app.profile_badges_empty') }}</p>
      @else
        <div class="mt-4 grid gap-4 sm:grid-cols-3">
          @foreach($badges as $badge)
            <a href="{{ route('badges.show', $badge) }}" class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-5 shadow-md text-center hover:border-gold-500/40 transition">
              <span class="block text-3xl">🏅</span>
              <span class="block mt-2 text-sm font-semibold">{{ data_get($badge->badge_data, 'title') }}</span>
            </a>
          @endforeach
        </div>
      @endif
    </section>

    <a href="/" class="inline-block mt-10 text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">&larr; {{ __('app.admin_view_site') }}</a>
  </main>
</body>
</html>

<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  @include('partials.theme-init')
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <title>{{ __('app.login_title') }} — {{ __('app.brand_name') }}</title>
</head>
<body class="bg-white dark:bg-ink-950 min-h-screen text-ink-800 dark:text-ink-100 font-sans antialiased transition-colors duration-300">

  <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
    <div class="absolute top-6 right-6">
      @include('partials.theme-toggle')
    </div>

    <a href="/" class="font-serif text-shine font-bold text-3xl mb-8">{{ __('app.brand_name') }}</a>

    <div class="w-full max-w-sm bg-white dark:bg-ink-900 border border-gold-500/15 rounded-2xl shadow-xl p-8">
      <h1 class="text-xl font-bold text-center">{{ __('app.login_title') }}</h1>
      <p class="text-sm text-ink-500 dark:text-ink-400 text-center mt-1 mb-6">{{ __('app.login_subtitle') }}</p>

      @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-3 text-sm text-emerald-700 dark:text-emerald-300" role="status">
          {{ session('status') }}
        </div>
      @endif

      @if (session('warning'))
        <div class="mb-4 rounded-lg bg-gold-50 dark:bg-gold-500/10 border border-gold-500/30 p-3 text-sm text-gold-800 dark:text-gold-200">
          {{ session('warning') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-300">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
          <label for="email" class="block text-xs font-semibold uppercase tracking-wide text-ink-500 dark:text-ink-400 mb-1">{{ __('app.login_email') }}</label>
          <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
            class="w-full rounded-lg border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 px-4 py-3 text-ink-900 dark:text-ink-100 focus:outline-none focus:border-gold-500 focus:ring-1 focus:ring-gold-500">
        </div>
        <div>
          <div class="flex items-baseline justify-between gap-3 mb-1">
            <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-ink-500 dark:text-ink-400">{{ __('app.login_password') }}</label>
            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-gold-600 dark:text-gold-400 hover:text-gold-500 transition">{{ __('app.login_forgot_password') }}</a>
          </div>
          <input id="password" type="password" name="password" required
            class="w-full rounded-lg border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 px-4 py-3 text-ink-900 dark:text-ink-100 focus:outline-none focus:border-gold-500 focus:ring-1 focus:ring-gold-500">
        </div>
        <label class="flex items-center gap-2 text-sm text-ink-600 dark:text-ink-300">
          <input type="checkbox" name="remember" class="rounded border-ink-300 text-gold-600 focus:ring-gold-500">
          {{ __('app.login_remember') }}
        </label>
        <button type="submit" class="shine-sweep w-full bg-gradient-to-r from-gold-600 via-gold-400 to-gold-600 text-ink-950 font-bold py-3 rounded-lg shadow-gold transition">
          {{ __('app.login_submit') }}
        </button>
      </form>
    </div>

    <a href="/" class="mt-6 text-sm text-ink-500 dark:text-ink-400 hover:text-gold-500 transition">&larr; {{ __('app.login_back') }}</a>
  </div>
</body>
</html>

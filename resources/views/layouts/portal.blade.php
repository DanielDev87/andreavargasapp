<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  @include('partials.theme-init')
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <title>@yield('title', __('app.brand_name')) — {{ __('app.brand_name') }}</title>
  @stack('head')
</head>
<body class="bg-white dark:bg-ink-950 min-h-screen text-ink-800 dark:text-ink-100 font-sans antialiased transition-colors duration-300">

  <header class="bg-white dark:bg-ink-900 shadow-sm py-4 border-b border-gold-500/10">
    <div class="container mx-auto px-4 md:px-8 flex justify-between items-center max-w-5xl">
      <a href="/" class="font-serif text-shine font-bold text-2xl">{{ __('app.brand_name') }}</a>
      <div class="flex items-center gap-4 text-sm font-semibold">
        @include('partials.theme-toggle')
        @auth
          <a href="{{ route('profile') }}" class="text-ink-600 dark:text-ink-300 hover:text-gold-500 transition">{{ __('app.profile_title') }}</a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-ink-600 dark:text-ink-300 hover:text-gold-500 transition">{{ __('app.nav_logout') }}</button>
          </form>
        @else
          <a href="{{ route('login') }}" class="text-ink-600 dark:text-ink-300 hover:text-gold-500 transition">{{ __('app.login_title') }}</a>
        @endauth
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
    @if($errors->any())
      <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-300">
        <ul class="list-disc pl-5">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @yield('content')
  </main>

  @stack('scripts')
</body>
</html>

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
  <title>@yield('title', __('app.admin_dashboard_title')) — {{ __('app.brand_name') }}</title>
</head>
<body class="bg-white dark:bg-ink-950 min-h-screen text-ink-800 dark:text-ink-100 font-sans antialiased transition-colors duration-300">

  <header class="bg-white dark:bg-ink-900 shadow-sm border-b border-gold-500/10">
    <div class="container mx-auto px-4 md:px-8 py-4 flex flex-wrap justify-between items-center gap-4 max-w-6xl">
      <a href="{{ route('admin.dashboard') }}" class="font-serif text-shine font-bold text-2xl">{{ __('app.brand_name') }}</a>

      <nav class="flex flex-wrap items-center gap-1 text-sm font-semibold">
        @php
          $adminNav = [
            'admin.dashboard' => __('app.admin_nav_dashboard'),
            'admin.courses.index' => __('app.admin_nav_courses'),
            'admin.validations.index' => __('app.admin_nav_validations'),
            'admin.purchases.index' => __('app.admin_nav_purchases'),
          ];
        @endphp
        @foreach($adminNav as $routeName => $label)
          <a href="{{ route($routeName) }}"
             class="px-3 py-2 rounded-lg transition {{ request()->routeIs($routeName) || (str_starts_with($routeName, 'admin.courses') && request()->routeIs('admin.courses.*')) ? 'bg-gold-500 text-ink-950' : 'text-ink-600 dark:text-ink-300 hover:text-gold-500' }}">
            {{ $label }}
          </a>
        @endforeach
      </nav>

      <div class="flex items-center gap-4">
        @include('partials.theme-toggle')
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="text-sm font-semibold text-ink-600 dark:text-ink-300 hover:text-gold-500 transition">{{ __('app.nav_logout') }}</button>
        </form>
      </div>
    </div>
  </header>

  <main class="container mx-auto px-4 md:px-8 py-10 max-w-6xl">
    @if(session('status'))
      <div class="mb-6 p-4 rounded-lg bg-gold-50 dark:bg-ink-900 border border-gold-500/20 text-ink-700 dark:text-ink-200">{{ session('status') }}</div>
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

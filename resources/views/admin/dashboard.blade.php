@extends('layouts.admin')

@section('content')
  <h1 class="text-2xl font-bold">{{ __('app.admin_welcome') }}, {{ auth()->user()->name }}.</h1>

  <div class="mt-8 grid gap-6 sm:grid-cols-3">
    <a href="{{ route('admin.courses.index') }}" class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-6 shadow-md hover:border-gold-500/40 transition">
      <span class="block text-sm text-ink-500 dark:text-ink-400 font-semibold uppercase tracking-wide">{{ __('app.admin_courses_count') }}</span>
      <span class="block text-4xl font-black text-gold-600 dark:text-gold-400 mt-2">{{ $coursesCount }}</span>
    </a>

    <a href="{{ route('admin.validations.index') }}" class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-6 shadow-md hover:border-gold-500/40 transition flex flex-col justify-center">
      <span class="block text-sm text-ink-500 dark:text-ink-400 font-semibold uppercase tracking-wide">{{ __('app.admin_nav_validations') }}</span>
      <span class="block mt-2 text-gold-600 dark:text-gold-400 font-semibold">&rarr;</span>
    </a>

    <a href="{{ route('admin.purchases.index') }}" class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-6 shadow-md hover:border-gold-500/40 transition flex flex-col justify-center">
      <span class="block text-sm text-ink-500 dark:text-ink-400 font-semibold uppercase tracking-wide">{{ __('app.admin_nav_purchases') }}</span>
      <span class="block mt-2 text-gold-600 dark:text-gold-400 font-semibold">&rarr;</span>
    </a>
  </div>

  <a href="/" class="inline-block mt-8 text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">&larr; {{ __('app.admin_view_site') }}</a>
@endsection

@extends('layouts.portal')

@section('title', __('app.data_policy_title'))

@section('content')
  <article class="max-w-2xl">
    <h1 class="text-2xl font-serif font-bold text-shine">{{ __('app.data_policy_title') }}</h1>
    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">{{ __('app.data_policy_version', ['version' => config('app.data_policy_version')]) }}</p>

    @foreach(__('app.data_policy_sections') as $section)
      <section class="mt-6">
        <h2 class="font-bold text-gold-600 dark:text-gold-400">{{ $section['title'] }}</h2>
        <p class="mt-1 leading-relaxed text-ink-700 dark:text-ink-200">{{ str_replace(':email', config('app.data_protection_email'), $section['body']) }}</p>
      </section>
    @endforeach
  </article>
@endsection

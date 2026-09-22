@extends('layouts.portal')

@section('title', data_get($badge->badge_data, 'title'))

@section('content')
  <div class="max-w-md mx-auto text-center bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl shadow-lg p-10">
    <span class="text-6xl">🏅</span>
    <h1 class="mt-4 text-xl font-serif font-bold text-shine">{{ data_get($badge->badge_data, 'title') }}</h1>
    <p class="mt-2 text-sm text-ink-500 dark:text-ink-400">{{ __('app.badge_issued_on') }} {{ optional($badge->issued_at)->translatedFormat('d M Y') }}</p>

    <a href="{{ route('badges.share', $badge) }}" class="inline-block mt-6 shine-sweep bg-gold-500 text-ink-950 font-semibold px-5 py-2 rounded-lg hover:bg-gold-400 transition">
      {{ __('app.badge_share_title') }}
    </a>
  </div>

  <a href="{{ route('profile') }}" class="block text-center mt-8 text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">&larr; {{ __('app.badge_back_to_profile') }}</a>
@endsection

@extends('layouts.portal')

@section('title', __('app.badge_share_title'))

@section('content')
  <div class="max-w-md mx-auto text-center bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl shadow-lg p-10">
    <span class="text-6xl">🏅</span>
    <h1 class="mt-4 text-xl font-serif font-bold">{{ data_get($badge->badge_data, 'title') }}</h1>
    <p class="mt-2 text-sm text-ink-600 dark:text-ink-300">{{ __('app.badge_share_intro') }}</p>

    <div class="mt-6 flex flex-col gap-3">
      <a target="_blank" rel="noopener"
         href="https://twitter.com/intent/tweet?text={{ urlencode('He obtenido una insignia: '.data_get($badge->badge_data, 'title')) }}&url={{ urlencode($badge->share_url) }}"
         class="shine-sweep bg-gold-500 text-ink-950 font-semibold px-5 py-2 rounded-lg hover:bg-gold-400 transition">
        Twitter / X
      </a>
      <a target="_blank" rel="noopener"
         href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($badge->share_url) }}"
         class="border border-gold-500/30 font-semibold px-5 py-2 rounded-lg hover:bg-gold-50 dark:hover:bg-ink-950 transition">
        Facebook
      </a>
      <a target="_blank" rel="noopener"
         href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($badge->share_url) }}"
         class="border border-gold-500/30 font-semibold px-5 py-2 rounded-lg hover:bg-gold-50 dark:hover:bg-ink-950 transition">
        LinkedIn
      </a>
      <a href="mailto:?subject={{ urlencode('Mi insignia') }}&body={{ urlencode($badge->share_url) }}"
         class="border border-gold-500/30 font-semibold px-5 py-2 rounded-lg hover:bg-gold-50 dark:hover:bg-ink-950 transition">
        Email
      </a>
    </div>
  </div>

  <a href="{{ route('badges.show', $badge) }}" class="block text-center mt-8 text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">&larr; {{ __('app.badge_back_to_profile') }}</a>
@endsection

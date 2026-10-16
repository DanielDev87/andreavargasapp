@extends('layouts.portal')

@section('title', __('app.validation_page_title'))

@section('content')
  <div class="max-w-xl">
    <h1 class="text-2xl font-serif font-bold text-shine">{{ __('app.validation_page_title') }}</h1>
    <p class="mt-3 text-ink-600 dark:text-ink-300">{{ __('app.validation_intro') }}</p>

    <form action="{{ route('validation.store') }}" method="POST" enctype="multipart/form-data"
          class="mt-8 bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-6 shadow-md space-y-5">
      @csrf
      <div>
        <label for="diploma" class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.validation_diploma_label') }}</label>
        <input id="diploma" type="file" name="diploma" accept=".pdf,.jpg,.jpeg,.png" required
               class="w-full text-sm rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 file:mr-3 file:rounded-md file:border-0 file:bg-gold-500 file:text-ink-950 file:font-semibold file:px-3 file:py-1.5">
      </div>
      <div>
        <label for="id_document" class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.validation_id_label') }}</label>
        <input id="id_document" type="file" name="id_document" accept=".pdf,.jpg,.jpeg,.png" required
               class="w-full text-sm rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 file:mr-3 file:rounded-md file:border-0 file:bg-gold-500 file:text-ink-950 file:font-semibold file:px-3 file:py-1.5">
        <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">{{ __('app.validation_files_hint') }}</p>
      </div>

      <fieldset>
        <legend class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-2">{{ __('app.validation_method_label') }}</legend>

        @unless($aiAvailable)
          <p class="mb-3 rounded-lg border border-gold-500/30 bg-gold-50 dark:bg-gold-500/10 p-3 text-sm text-gold-800 dark:text-gold-200">
            {{ __('app.validation_ai_unavailable_notice') }}
          </p>
        @endunless

        @php $selectedMethod = old('review_method', $aiAvailable ? 'ai' : 'manual'); @endphp
        <div class="grid gap-3 sm:grid-cols-2">
          @foreach(['ai', 'manual'] as $method)
            @php $disabled = $method === 'ai' && ! $aiAvailable; @endphp
            <label class="relative flex flex-col gap-1 rounded-lg border p-4 transition
                          {{ $disabled ? 'opacity-50 cursor-not-allowed border-ink-200 dark:border-ink-700' : 'cursor-pointer border-gold-500/20 hover:border-gold-500/50 has-[:checked]:border-gold-500 has-[:checked]:bg-gold-50 dark:has-[:checked]:bg-gold-500/10' }}">
              <span class="flex items-center gap-2 font-semibold text-sm">
                <input type="radio" name="review_method" value="{{ $method }}" class="accent-gold-500"
                       @checked($selectedMethod === $method && ! $disabled) @disabled($disabled) required>
                {{ __('app.validation_method_'.$method) }}
              </span>
              <span class="text-xs text-ink-500 dark:text-ink-400">{{ __('app.validation_method_'.$method.'_hint') }}</span>
            </label>
          @endforeach
        </div>
      </fieldset>

      <label class="flex items-start gap-3 text-sm text-ink-600 dark:text-ink-300">
        <input type="checkbox" name="data_consent" value="1" required @checked(old('data_consent')) class="mt-1 accent-gold-500">
        <span>
          {{ __('app.validation_consent_text') }}
          <a href="{{ route('data-policy') }}" target="_blank" class="font-semibold text-gold-600 dark:text-gold-400 underline hover:text-gold-500">{{ __('app.validation_consent_link') }}</a>.
        </span>
      </label>

      <button type="submit" class="shine-sweep bg-gold-500 text-ink-950 font-semibold px-5 py-2 rounded-lg hover:bg-gold-400 transition">{{ __('app.validation_submit') }}</button>
    </form>
  </div>
@endsection

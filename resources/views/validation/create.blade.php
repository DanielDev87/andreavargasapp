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
        <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.validation_diploma_label') }}</label>
        <input type="file" name="diploma" accept=".pdf,.jpg,.jpeg,.png" required
               class="w-full text-sm rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 file:mr-3 file:rounded-md file:border-0 file:bg-gold-500 file:text-ink-950 file:font-semibold file:px-3 file:py-1.5">
      </div>
      <div>
        <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.validation_id_label') }}</label>
        <input type="file" name="id_document" accept=".pdf,.jpg,.jpeg,.png" required
               class="w-full text-sm rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 file:mr-3 file:rounded-md file:border-0 file:bg-gold-500 file:text-ink-950 file:font-semibold file:px-3 file:py-1.5">
      </div>
      <button type="submit" class="shine-sweep bg-gold-500 text-ink-950 font-semibold px-5 py-2 rounded-lg hover:bg-gold-400 transition">{{ __('app.validation_submit') }}</button>
    </form>
  </div>
@endsection

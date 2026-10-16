@extends('layouts.portal')

@section('title', __('app.payment_result_title'))

@if($nextCheck)
  @push('head')
    <meta http-equiv="refresh" content="5;url={{ route('purchases.result', ['purchase' => $purchase, 'check' => $nextCheck]) }}">
  @endpush
@endif

@section('content')
  <div class="max-w-xl mx-auto text-center bg-white dark:bg-ink-900 border border-gold-500/15 rounded-2xl shadow-md p-8 md:p-10">
    @if($purchase->status === 'completed')
      <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400" aria-hidden="true">
        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      </span>
      <h1 class="mt-5 font-serif text-2xl md:text-3xl font-bold">{{ __('app.payment_result_paid_title') }}</h1>
      <p class="mt-3 text-ink-600 dark:text-ink-300">{{ __('app.payment_result_paid_body', ['course' => $purchase->course->title]) }}</p>
      @if($purchase->attendance_code)
        <p class="mt-4 text-sm text-ink-500 dark:text-ink-400">{{ __('app.payment_result_attendance_code') }}</p>
        <p class="mt-1 font-mono text-2xl font-bold tracking-widest text-gold-600 dark:text-gold-400">{{ $purchase->attendance_code }}</p>
      @endif
      <a href="{{ route('courses.show', $purchase->course) }}" class="shine-sweep inline-block mt-8 bg-gold-500 text-ink-950 font-bold px-6 py-3 rounded-lg hover:bg-gold-400 transition">{{ __('app.payment_result_go_to_course') }}</a>

    @elseif($purchase->status === 'cancelled')
      <h1 class="font-serif text-2xl md:text-3xl font-bold">{{ __('app.payment_result_cancelled_title') }}</h1>
      <p class="mt-3 text-ink-600 dark:text-ink-300">{{ __('app.payment_result_cancelled_body') }}</p>

    @elseif($rejected)
      <h1 class="font-serif text-2xl md:text-3xl font-bold">{{ __('app.payment_result_rejected_title') }}</h1>
      <p class="mt-3 text-ink-600 dark:text-ink-300">{{ __('app.payment_result_rejected_body') }}</p>
      <form method="POST" action="{{ route('purchases.initiate', $purchase->course) }}" class="mt-8">
        @csrf
        <input type="hidden" name="payment_method" value="bold">
        <button type="submit" class="shine-sweep bg-gold-500 text-ink-950 font-bold px-6 py-3 rounded-lg hover:bg-gold-400 transition">{{ __('app.payment_result_retry') }}</button>
      </form>

    @else
      @if($nextCheck)
        <span class="mx-auto block h-12 w-12 rounded-full border-4 border-gold-500/25 border-t-gold-500 animate-spin motion-reduce:animate-none" aria-hidden="true"></span>
      @endif
      <h1 class="mt-5 font-serif text-2xl md:text-3xl font-bold">{{ __('app.payment_result_pending_title') }}</h1>
      <p class="mt-3 text-ink-600 dark:text-ink-300" role="status">
        {{ $nextCheck ? __('app.payment_result_pending_body') : __('app.payment_result_pending_slow') }}
      </p>
      <a href="{{ route('profile') }}" class="inline-block mt-8 text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.payment_result_go_to_profile') }} &rarr;</a>
    @endif
  </div>
@endsection

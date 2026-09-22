@extends('layouts.portal')

@section('title', __('app.checkout_title'))

@section('content')
  <div class="max-w-md mx-auto bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl shadow-lg p-8 text-center">
    <h1 class="text-xl font-serif font-bold">{{ $course->title }}</h1>
    <p class="mt-2 text-3xl font-black text-gold-600 dark:text-gold-400">${{ number_format($purchase->amount, 0, ',', '.') }}</p>

    <form method="POST" action="{{ route('webhooks.bold') }}" class="mt-6">
      @csrf
      <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
      <input type="hidden" name="status" value="paid">
      <button type="submit" class="w-full shine-sweep bg-gold-500 text-ink-950 font-bold py-3 rounded-lg hover:bg-gold-400 transition">
        {{ __('app.checkout_pay_bold') }}
      </button>
    </form>
  </div>
@endsection

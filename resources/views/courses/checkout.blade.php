@extends('layouts.portal')

@section('title', __('app.checkout_title'))

@section('content')
  <div class="flex flex-col md:flex-row gap-8">

    {{-- Course summary --}}
    <div class="w-full md:w-5/12">
      <div class="bg-white dark:bg-ink-900 rounded-xl shadow-md overflow-hidden border border-gold-500/10">
        <div class="bg-gold-50 dark:bg-ink-800 p-6 flex justify-center border-b border-gold-500/10">
          @if($course->image_url)
            <img src="{{ asset($course->image_url) }}" alt="{{ $course->title }}" class="h-40 object-contain drop-shadow-md">
          @else
            <div class="h-40 flex items-center text-gold-400 font-bold text-2xl">{{ __('app.brand_name') }}</div>
          @endif
        </div>
        <div class="p-6">
          <h1 class="text-2xl font-bold leading-tight mb-2">{{ $course->title }}</h1>
          <p class="text-ink-600 dark:text-ink-300 mb-6 text-sm">{{ $course->description }}</p>

          <div class="border-t border-b border-gold-500/10 py-4 mb-6">
            <div class="flex justify-between items-center">
              <span class="font-semibold text-ink-700 dark:text-ink-200">{{ __('app.checkout_total') }}</span>
              <span class="text-3xl font-black text-gold-600 dark:text-gold-400">${{ number_format($course->price, 0, ',', '.') }}</span>
            </div>
          </div>

          @if($course->features)
            <div class="mb-6">
              <h4 class="font-semibold mb-3 text-sm uppercase tracking-wider">{{ __('app.checkout_includes') }}</h4>
              <ul class="space-y-2">
                @foreach($course->features as $feature)
                  <li class="flex items-start">
                    <svg class="w-5 h-5 text-gold-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="text-ink-700 dark:text-ink-200 text-sm">{{ $feature }}</span>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 flex items-center">
            <svg class="w-10 h-10 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <div>
              <p class="font-bold text-green-800 dark:text-green-300 text-sm">{{ __('app.checkout_guarantee_title') }}</p>
              <p class="text-green-700 dark:text-green-400 text-xs">{{ __('app.checkout_guarantee_body') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Payment method --}}
    <div class="w-full md:w-7/12">
      <div class="bg-white dark:bg-ink-900 rounded-xl shadow-md p-6 md:p-8 border border-gold-500/10">
        <h2 class="text-2xl font-bold mb-6">{{ __('app.checkout_title') }}</h2>

        <div class="flex gap-4 mb-8">
          <button type="button" data-tab-btn="bold"
                  class="flex-1 py-3 px-4 rounded-lg border-2 border-gold-500 bg-gold-50 dark:bg-gold-500/10 text-gold-700 dark:text-gold-300 font-semibold transition flex flex-col items-center justify-center gap-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            {{ __('app.checkout_pay_bold') }}
          </button>
          <button type="button" data-tab-btn="transfer"
                  class="flex-1 py-3 px-4 rounded-lg border border-ink-200 dark:border-ink-700 bg-ink-50 dark:bg-ink-800 text-ink-600 dark:text-ink-400 font-semibold transition hover:bg-ink-100 dark:hover:bg-ink-700 flex flex-col items-center justify-center gap-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
            {{ __('app.checkout_pay_transfer') }}
          </button>
        </div>

        {{-- Bold --}}
        <div data-tab-content="bold" class="block">
          <div class="bg-ink-50 dark:bg-ink-800 rounded-lg p-6 text-center border border-gold-500/10">
            <div class="inline-flex items-center gap-2 mb-4 text-ink-400 dark:text-ink-500">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
              <span class="text-sm font-semibold tracking-wide uppercase">Bold Payments</span>
            </div>
            <h3 class="text-lg font-bold mb-4">{{ __('app.checkout_bold_desc') }}</h3>

            <form method="POST" action="{{ route('purchases.initiate', $course) }}">
              @csrf
              <input type="hidden" name="payment_method" value="bold">
              <button type="submit" class="shine-sweep block w-full bg-gradient-to-r from-gold-600 via-gold-400 to-gold-600 text-ink-950 font-bold py-4 rounded-lg text-lg shadow-lg hover:shadow-gold transition transform hover:-translate-y-0.5 text-center">
                {{ __('app.checkout_pay_now') }}
              </button>
            </form>

            <div class="flex justify-center items-center mt-4 text-xs text-ink-500 dark:text-ink-400">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              {{ __('app.checkout_secure_tx') }}
            </div>
          </div>
        </div>

        {{-- Transfer --}}
        <div data-tab-content="transfer" class="hidden">
          <div class="bg-gold-50 dark:bg-ink-800 rounded-lg p-6 border border-gold-500/20">
            <h3 class="text-lg font-bold mb-4">{{ __('app.checkout_transfer_title') }}</h3>

            <div class="space-y-4 mb-6">
              <div class="bg-white dark:bg-ink-900 p-4 rounded shadow-sm border border-gold-500/10">
                <p class="text-xs text-ink-500 dark:text-ink-400 uppercase tracking-wide font-bold mb-1">Bancolombia Ahorros</p>
                <p class="text-lg font-mono">46688552753</p>
                <p class="text-sm text-ink-600 dark:text-ink-300 mt-1">A nombre de: Andrea Vargas</p>
              </div>
            </div>

            <div class="border-t border-gold-500/20 pt-4">
              <p class="font-bold mb-2">{{ __('app.checkout_transfer_instr') }}</p>
              <ol class="list-decimal list-inside text-sm text-ink-700 dark:text-ink-200 space-y-2 mb-6">
                <li>{{ __('app.checkout_transfer_step1') }} <strong>${{ number_format($course->price, 0, ',', '.') }}</strong>.</li>
                <li>{{ __('app.checkout_transfer_step2') }}</li>
                <li>{{ __('app.checkout_transfer_step3') }}</li>
              </ol>

              <form method="POST" action="{{ route('purchases.initiate', $course) }}">
                @csrf
                <input type="hidden" name="payment_method" value="transfer">
                <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-lg text-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 text-center flex items-center justify-center">
                  <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                  {{ __('app.checkout_send_receipt') }}
                </button>
              </form>
              <p class="text-xs text-ink-500 dark:text-ink-400 mt-3 text-center">{{ __('app.checkout_transfer_review_notice') }}</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      const activeClasses = 'flex-1 py-3 px-4 rounded-lg border-2 border-gold-500 bg-gold-50 dark:bg-gold-500/10 text-gold-700 dark:text-gold-300 font-semibold transition flex flex-col items-center justify-center gap-1';
      const inactiveClasses = 'flex-1 py-3 px-4 rounded-lg border border-ink-200 dark:border-ink-700 bg-ink-50 dark:bg-ink-800 text-ink-600 dark:text-ink-400 font-semibold transition hover:bg-ink-100 dark:hover:bg-ink-700 flex flex-col items-center justify-center gap-1';

      document.querySelectorAll('[data-tab-btn]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const tab = btn.dataset.tabBtn;

          document.querySelectorAll('[data-tab-btn]').forEach(function (b) {
            b.className = b.dataset.tabBtn === tab ? activeClasses : inactiveClasses;
          });

          document.querySelectorAll('[data-tab-content]').forEach(function (c) {
            c.classList.toggle('hidden', c.dataset.tabContent !== tab);
          });
        });
      });
    </script>
  @endpush
@endsection

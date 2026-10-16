<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  @include('partials.theme-init')
  <script>
    /* Hide reveal targets before first paint; unhide if the JS bundle never starts */
    (function () {
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
      var root = document.documentElement;
      root.classList.add('motion-ready');
      setTimeout(function () {
        if (root.dataset.motion !== 'started') root.classList.remove('motion-ready');
      }, 3000);
    })();
  </script>
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <title>{{ __('app.meta_title') }}</title>

  <style>
    details > summary {
      list-style: none;
    }
    details > summary::-webkit-details-marker {
      display: none;
    }
  </style>
</head>

@inject('bunny', 'App\Services\BunnyStreamService')
@php
  $introVideoId = config('services.bunny_stream.intro_video_id');
  $introVideoUrl = $bunny->isConfigured() && $bunny->isValidVideoId($introVideoId)
      ? $bunny->embedUrl($introVideoId, autoplay: true)
      : null;
@endphp
<body class="landing antialiased bg-white dark:bg-ink-950 font-sans text-ink-900 dark:text-ink-100 transition-colors duration-300">

  <div data-scroll-progress aria-hidden="true" class="fixed top-0 inset-x-0 h-0.5 z-[60] origin-left bg-gradient-to-r from-gold-600 via-gold-300 to-gold-600" style="transform: scaleX(0)"></div>

  <main class="w-full">

    <!-- start header -->
    <header data-site-header class="group fixed top-0 left-0 w-full z-50 px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 transition-[background-color,box-shadow] duration-500 data-[scrolled]:bg-ink-950/85 data-[scrolled]:backdrop-blur-md data-[scrolled]:shadow-lg data-[scrolled]:shadow-black/30">
      <div class="hidden md:grid grid-rows-[1fr] transition-[grid-template-rows,opacity] duration-500 group-data-[scrolled]:grid-rows-[0fr] group-data-[scrolled]:opacity-0">
      <div class="min-h-0 overflow-hidden">
      <div class="flex justify-between items-center py-3 border-b border-gold-500/25 text-sm">
        <a href="https://wa.me/573115228585?text=Hola,%20quisiera%20más%20información."
          target="_blank" rel="noopener" class="flex items-center text-white hover:text-gold-400 transition">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
          {{ __('app.top_bar_whatsapp') }}
        </a>

        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            @foreach(['en' => 'EN', 'es' => 'ES'] as $locale => $label)
              <a href="{{ route('lang.switch', $locale) }}" lang="{{ $locale }}"
                 class="text-xs font-bold tracking-wider px-2 py-1 rounded transition {{ app()->getLocale() === $locale ? 'bg-gold-500 text-ink-950' : 'text-white hover:text-gold-400' }}">
                {{ $label }}
              </a>
            @endforeach
          </div>
          @include('partials.theme-toggle')
          @auth
            @if(auth()->user()->is_admin)
              <a href="{{ route('admin.dashboard') }}" class="text-white hover:text-gold-400 font-semibold transition">{{ __('app.nav_admin') }}</a>
            @else
              <a href="{{ route('profile') }}" class="text-white hover:text-gold-400 font-semibold transition">{{ __('app.profile_title') }}</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="text-white hover:text-gold-400 font-semibold transition">{{ __('app.nav_logout') }}</button>
            </form>
          @else
            <a href="{{ route('login') }}" class="text-white hover:text-gold-400 font-semibold transition">{{ __('app.nav_login') }}</a>
            <a href="{{ route('register') }}" class="text-white hover:text-gold-400 font-semibold transition">{{ __('app.nav_register') }}</a>
          @endauth
        </div>
      </div>
      </div>
      </div>

      <div class="flex flex-wrap items-center justify-between py-6 transition-[padding] duration-500 group-data-[scrolled]:py-3">
        <div class="w-1/2 md:w-auto">
          <a href="/" class="font-serif text-shine font-bold text-2xl">
            {{ __('app.brand_name') }}
          </a>
        </div>

        <button type="button" data-menu-toggle aria-controls="menu" aria-expanded="false" aria-label="{{ __('app.nav_menu') }}"
          class="group/menu md:hidden inline-flex h-11 w-11 items-center justify-center rounded-full text-white border border-gold-500/30 hover:border-gold-400 transition">
          <svg class="h-5 w-5 group-aria-expanded/menu:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
          <svg class="h-5 w-5 hidden group-aria-expanded/menu:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="hidden md:block w-full md:w-auto" id="menu">
          <nav
            class="w-full bg-white dark:bg-ink-900 md:bg-transparent md:dark:bg-transparent rounded-2xl border border-gold-500/15 md:border-0 shadow-xl px-6 py-4 mt-4 text-center md:p-0 md:mt-0 md:shadow-none">
            <ul class="md:flex items-center md:gap-6">
              @foreach([
                'filosofia' => 'nav_philosophy',
                'doctora' => 'nav_doctor',
                'tratamientos' => 'nav_treatments',
                'cursos' => 'nav_courses',
                'resultados' => 'nav_results',
              ] as $anchor => $labelKey)
                <li>
                  <a class="relative py-3 md:py-2 inline-block md:text-white font-semibold hover:text-gold-500 md:hover:text-gold-300 transition-colors after:absolute after:left-0 after:bottom-1 after:h-px after:w-full after:origin-left after:scale-x-0 after:bg-gold-400 after:transition-transform after:duration-300 hover:after:scale-x-100"
                    href="#{{ $anchor }}">{{ __('app.'.$labelKey) }}</a>
                </li>
              @endforeach
              <li class="mt-3 md:mt-0">
                <a class="shine-sweep inline-block font-semibold px-5 py-2.5 text-ink-950 md:text-white bg-gold-500 md:bg-transparent border border-gold-500/60 rounded-full hover:bg-gold-500 hover:text-ink-950 hover:border-gold-500 transition"
                  href="#contacto">{{ __('app.nav_book') }}</a>
              </li>
            </ul>

            {{-- On mobile the top bar is hidden, so language, theme and account live here --}}
            <div class="md:hidden mt-4 pt-4 border-t border-gold-500/15 flex flex-wrap items-center justify-center gap-3 text-sm">
              @foreach(['en' => 'EN', 'es' => 'ES'] as $locale => $label)
                <a href="{{ route('lang.switch', $locale) }}" lang="{{ $locale }}"
                   class="text-xs font-bold tracking-wider px-3 py-2 rounded-full transition {{ app()->getLocale() === $locale ? 'bg-gold-500 text-ink-950' : 'border border-gold-500/30 hover:text-gold-500' }}">
                  {{ $label }}
                </a>
              @endforeach
              @include('partials.theme-toggle')
            </div>
            <div class="md:hidden mt-3 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm font-semibold">
              @auth
                @if(auth()->user()->is_admin)
                  <a href="{{ route('admin.dashboard') }}" class="py-2 hover:text-gold-500 transition">{{ __('app.nav_admin') }}</a>
                @else
                  <a href="{{ route('profile') }}" class="py-2 hover:text-gold-500 transition">{{ __('app.profile_title') }}</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="py-2 hover:text-gold-500 transition">{{ __('app.nav_logout') }}</button>
                </form>
              @else
                <a href="{{ route('login') }}" class="py-2 hover:text-gold-500 transition">{{ __('app.nav_login') }}</a>
                <a href="{{ route('register') }}" class="py-2 hover:text-gold-500 transition">{{ __('app.nav_register') }}</a>
              @endauth
            </div>
          </nav>
        </div>
      </div>
    </header>
    <!-- end header -->

    <!-- start hero -->
    <div class="bg-ink-950">
      <section class="cover bg-gradient-to-br from-ink-950 via-ink-900 to-black relative px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 overflow-hidden py-48 flex
      items-center min-h-screen">
        <div data-parallax class="absolute inset-x-0 -top-40 bottom-0 z-0 overflow-hidden will-change-transform">
          <img data-hero-image src="/images/IMG_0685.JPG.jpeg" alt="" fetchpriority="high" class="w-full h-full object-cover object-[center_62%] opacity-20 -scale-x-100">
        </div>
        <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_20%_20%,rgba(207,154,46,0.18),transparent_55%)]"></div>
        <div class="absolute inset-x-0 bottom-0 z-0 h-40 bg-gradient-to-t from-ink-950 to-transparent"></div>

        <div data-hero-content class="lg:w-3/4 xl:w-2/4 relative z-10 h-100 lg:mt-16">
          <div>
            <span data-hero class="inline-flex items-center gap-3 text-gold-400 tracking-[0.3em] text-xs font-semibold uppercase mb-5">
              <span class="h-px w-10 bg-gold-400" aria-hidden="true"></span>
              {{ __('app.hero_eyebrow') }}
            </span>
            <h1 class="text-white font-serif text-4xl md:text-5xl xl:text-6xl font-bold leading-tight text-balance">
              <span data-hero class="block">{{ __('app.hero_title_line1') }}</span>
              <span data-hero class="block italic text-shine pb-1">{{ __('app.hero_title_line2') }}</span>
            </h1>
            <p data-hero class="text-ink-200 text-xl md:text-2xl leading-snug mt-5 max-w-2xl text-pretty">{{ __('app.hero_body') }}</p>

            <div data-hero class="flex flex-wrap items-center gap-6 mt-10">
              <a href="https://wa.me/573115228585?text=Hola,%20quisiera%20agendar%20mi%20Valoraci%C3%B3n%20VIP."
                target="_blank" rel="noopener"
                class="shine-sweep group/cta inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-gold-600 via-gold-400 to-gold-600 text-ink-950 rounded-full font-bold shadow-gold transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_18px_40px_-12px_rgba(207,154,46,0.65)] active:translate-y-0">
                {{ __('app.hero_cta') }}
                <svg class="w-5 h-5 transition-transform duration-300 group-hover/cta:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/></svg>
              </a>

              <div class="flex items-center">
                <div class="flex -space-x-3">
                  @for ($i = 0; $i < 3; $i++)
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-gold-300 to-gold-600 border-2 border-ink-950 flex items-center justify-center">
                      <svg class="w-4 h-4 text-ink-950" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/></svg>
                    </div>
                  @endfor
                </div>
                <span data-count-up class="ml-3 text-white text-sm font-semibold tabular-nums">{{ __('app.hero_social_proof') }}</span>
              </div>
            </div>
          </div>
        </div>

        <a href="#filosofia" data-hero aria-label="{{ __('app.hero_scroll') }}"
          class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 hidden md:flex flex-col items-center p-3 text-gold-400/80 hover:text-gold-300 transition-colors">
          <span class="relative block h-12 w-px bg-gold-500/20 overflow-hidden" aria-hidden="true">
            <span class="scroll-cue absolute inset-0 bg-gold-400"></span>
          </span>
        </a>
      </section>
    </div>
    <!-- end hero -->

    <!-- start philosophy -->
    <section class="relative bg-white dark:bg-ink-950 px-4 py-16 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 lg:py-32 transition-colors duration-300 text-center" id="filosofia">
      <h2 data-reveal class="font-serif text-3xl md:text-4xl leading-tight font-bold max-w-3xl mx-auto text-balance">
        {{ __('app.philosophy_title') }}
      </h2>
      <p data-reveal data-reveal-delay="0.1" class="mt-4 max-w-2xl mx-auto leading-relaxed text-ink-600 dark:text-ink-300 text-pretty">{{ __('app.philosophy_body') }}</p>

      <div data-reveal-stagger class="grid gap-6 md:grid-cols-3 mt-16 text-left">
        @foreach([
          ['title' => 'feature1_title', 'body' => 'feature1_body', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 3h12l3 5-9 13L3 8l3-5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 8h18M9 3l3 5 3-5M12 8l-3 13M12 8l3 13"/>'],
          ['title' => 'feature2_title', 'body' => 'feature2_body', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v6c0 4.5-3 8.4-7 9.5-4-1.1-7-5-7-9.5V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>'],
          ['title' => 'feature3_title', 'body' => 'feature3_body', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 8l3 3 5-6 5 6 3-3-2 11H6L4 8z"/>'],
        ] as $feature)
          <div class="spotlight group/card bg-white dark:bg-ink-900 rounded-2xl border border-gold-500/15 p-8 shadow-md transition-[translate,box-shadow,border-color] duration-500 hover:-translate-y-1 hover:border-gold-500/40 hover:shadow-xl hover:shadow-gold-500/10">
            <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-gold-50 dark:bg-gold-500/10 text-gold-600 dark:text-gold-400 mb-5 transition duration-500 group-hover/card:bg-gold-500 group-hover/card:text-ink-950 group-hover/card:rotate-6">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">{!! $feature['icon'] !!}</svg>
            </span>
            <h3 class="text-xl font-bold">{{ __('app.'.$feature['title']) }}</h3>
            <p class="mt-2 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.'.$feature['body']) }}</p>
          </div>
        @endforeach
      </div>
    </section>
    <!-- end philosophy -->

    <!-- start doctor bio -->
    <section class="relative bg-ink-950 text-white px-4 py-16 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 lg:py-32" id="doctora">
      <div class="flex flex-col lg:flex-row lg:items-center gap-12 lg:gap-16">
        <div data-reveal="left" class="w-full lg:w-2/5 relative">
          <div class="absolute -inset-3 rounded-3xl border border-gold-500/20 -rotate-2" aria-hidden="true"></div>
          <div class="group/photo relative aspect-[4/5] rounded-2xl overflow-hidden border border-gold-500/20 shadow-2xl shadow-black/40">
            <img src="/images/doctora.jpg" alt="{{ __('app.brand_name') }}" loading="lazy" decoding="async" class="w-full h-full object-cover transition duration-[1.2s] ease-out group-hover/photo:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-ink-950/50 via-transparent to-transparent" aria-hidden="true"></div>
          </div>
          <div data-reveal="scale" data-reveal-delay="0.4" class="absolute -bottom-6 -right-4 bg-gradient-to-br from-gold-400 to-gold-600 text-ink-950 rounded-xl px-6 py-4 shadow-gold">
            <span class="block text-2xl font-black leading-none">{{ __('app.doctor_years') }}</span>
          </div>
        </div>

        <div data-reveal="right" class="w-full lg:w-3/5">
          <span class="inline-flex items-center gap-3 text-gold-400 font-semibold uppercase tracking-wide text-sm">
            <span class="h-px w-8 bg-gold-400" aria-hidden="true"></span>
            {{ __('app.doctor_role') }}
          </span>
          <p class="font-serif text-3xl md:text-4xl leading-snug mt-3 text-balance">
            <span class="text-gold-400">&ldquo;</span>{{ __('app.doctor_quote') }}<span class="text-gold-400">&rdquo;</span>
          </p>
          <p class="mt-6 text-ink-200 leading-relaxed">{{ __('app.doctor_body1') }}</p>
          <p class="mt-4 text-ink-200 leading-relaxed">{{ __('app.doctor_body2') }}</p>

          <ul class="mt-6 space-y-2">
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-gold-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>{{ __('app.doctor_bullet1') }}</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-gold-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>{{ __('app.doctor_bullet2') }}</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-gold-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>{{ __('app.doctor_bullet3') }}</span>
            </li>
          </ul>

          @if($introVideoUrl)
            <button type="button" data-modal-open="intro-video-dialog" aria-haspopup="dialog"
              class="shine-sweep group/play inline-flex items-center gap-3 mt-8 px-6 py-3 border border-gold-500 text-gold-400 font-semibold rounded hover:bg-gold-500 hover:text-ink-950 transition">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gold-500/15 group-hover/play:bg-ink-950/15 transition" aria-hidden="true">
                <svg class="w-4 h-4 translate-x-px" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5.14v13.72a1 1 0 001.5.86l11-6.86a1 1 0 000-1.72l-11-6.86A1 1 0 008 5.14z"/></svg>
              </span>
              {{ __('app.doctor_cta') }}
            </button>
          @else
            <a href="#tratamientos" class="shine-sweep inline-block mt-8 px-6 py-3 border border-gold-500 text-gold-400 font-semibold rounded hover:bg-gold-500 hover:text-ink-950 transition">
              {{ __('app.doctor_cta') }}
            </a>
          @endif
        </div>
      </div>
    </section>
    <!-- end doctor bio -->

    <!-- start treatments -->
    <section class="relative bg-white dark:bg-ink-950 px-4 py-16 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 lg:py-32 transition-colors duration-300" id="tratamientos">
      <h2 data-reveal class="font-serif text-3xl md:text-4xl leading-tight font-bold text-center mb-16 text-balance">{{ __('app.treatments_title') }}</h2>

      <div data-reveal-stagger class="max-w-3xl mx-auto">
        <div class="spotlight bg-white dark:bg-ink-900 rounded-2xl border border-gold-500/20 p-8 md:p-10 shadow-md relative overflow-hidden transition-[translate,box-shadow,border-color] duration-500 hover:-translate-y-1 hover:border-gold-500/45 hover:shadow-2xl hover:shadow-gold-500/10">
          <span class="absolute top-6 right-6 text-xs font-bold uppercase tracking-wide bg-gold-500 text-ink-950 px-3 py-1 rounded-full">{{ __('app.treatment1_badge') }}</span>
          <span class="font-serif text-5xl text-gold-500/30 font-bold">01</span>
          <h3 class="text-2xl font-bold mt-2">{{ __('app.treatment1_title') }}</h3>
          <p class="mt-3 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.treatment1_body') }}</p>
          <div class="flex flex-wrap gap-2 mt-6">
            @foreach(['treatment1_tag1', 'treatment1_tag2', 'treatment1_tag3'] as $tag)
              <span class="text-xs font-semibold px-3 py-1 rounded-full bg-gold-50 dark:bg-gold-500/10 text-gold-700 dark:text-gold-300">{{ __('app.'.$tag) }}</span>
            @endforeach
          </div>
          <button type="button" data-modal-open="treatment1-dialog" aria-haspopup="dialog"
            class="shine-sweep inline-block mt-8 px-6 py-3 bg-ink-950 dark:bg-gold-500 text-gold-400 dark:text-ink-950 font-bold rounded-lg shadow-md">
            {{ __('app.treatment_cta') }}
          </button>
        </div>
      </div>
    </section>
    <!-- end treatments -->

    <!-- start pricing -->
    <section class="relative bg-white dark:bg-ink-950 px-4 py-16 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 lg:py-32 transition-colors duration-300 text-center">
      <h2 data-reveal class="font-serif text-3xl md:text-4xl leading-tight font-bold text-balance">{{ __('app.pricing_title') }}</h2>
      <p data-reveal data-reveal-delay="0.1" class="mt-2 max-w-xl mx-auto text-ink-600 dark:text-ink-300">{{ __('app.pricing_subtitle') }}</p>

      <div class="grid gap-8 lg:grid-cols-2 mt-16 text-left">
        <div data-reveal="left" class="spotlight bg-ink-950 text-white rounded-2xl p-8 shadow-xl ring-1 ring-gold-500/30 dark:ring-gold-500/40">
          <h3 class="text-2xl font-bold">{{ __('app.pricing_tier1_title') }}</h3>
          <p class="text-ink-300 mt-1">{{ __('app.pricing_tier1_subtitle') }}</p>

          <div class="mt-8 space-y-6 divide-y divide-white/10">
            <div>
              <div class="flex justify-between items-baseline">
                <span class="font-semibold">{{ __('app.pricing_tier1_item1_name') }}</span>
                <span class="text-gold-400 font-bold">{{ __('app.pricing_tier1_item1_price') }}</span>
              </div>
              <p class="text-ink-300 text-sm mt-1">{{ __('app.pricing_tier1_item1_body') }}</p>
            </div>
            <div class="pt-6">
              <div class="flex justify-between items-baseline">
                <span class="font-semibold">{{ __('app.pricing_tier1_item2_name') }}</span>
                <span class="text-gold-400 font-bold">{{ __('app.pricing_tier1_item2_price') }}</span>
              </div>
              <p class="text-ink-300 text-sm mt-1">{{ __('app.pricing_tier1_item2_body') }}</p>
            </div>
            <div class="pt-6">
              <div class="flex justify-between items-baseline">
                <span class="font-semibold">{{ __('app.pricing_tier1_item3_name') }}</span>
                <span class="text-gold-400 font-bold">{{ __('app.pricing_tier1_item3_price') }}</span>
              </div>
              <p class="text-ink-300 text-sm mt-1">{{ __('app.pricing_tier1_item3_body') }}</p>
            </div>
          </div>

          <a href="https://wa.me/573115228585?text=Hola,%20quiero%20mi%20Valoraci%C3%B3n%20para%20Transformaci%C3%B3n%20Completa."
            target="_blank" rel="noopener" class="shine-sweep block text-center mt-8 px-6 py-4 bg-gradient-to-r from-gold-600 via-gold-400 to-gold-600 text-ink-950 font-bold rounded-lg shadow-gold">
            {{ __('app.pricing_tier1_cta') }}
          </a>
        </div>

        <div data-reveal="right" class="spotlight bg-ink-50 dark:bg-ink-900 rounded-2xl p-8 border border-gold-500/15">
          <h3 class="text-2xl font-bold">{{ __('app.pricing_tier2_title') }}</h3>
          <p class="text-ink-600 dark:text-ink-300 mt-1">{{ __('app.pricing_tier2_subtitle') }}</p>

          <ul class="mt-8 space-y-4">
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-gold-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>{{ __('app.pricing_tier2_item1') }}</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-gold-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>{{ __('app.pricing_tier2_item2') }}</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-gold-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>{{ __('app.pricing_tier2_item3') }}</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-gold-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>{{ __('app.pricing_tier2_item4') }}</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-gold-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>{{ __('app.pricing_tier2_item5') }}</span>
            </li>
          </ul>

          <a href="https://wa.me/573115228585?text=Hola,%20quiero%20agendar%20una%20cita%20de%20mantenimiento."
            target="_blank" rel="noopener" class="block text-center mt-8 px-6 py-4 border border-gold-500 text-gold-600 dark:text-gold-400 font-bold rounded-lg hover:bg-gold-500 hover:text-ink-950 transition">
            {{ __('app.pricing_tier2_cta') }}
          </a>
        </div>
      </div>
    </section>
    <!-- end pricing -->

    <!-- start courses -->
    <section class="relative bg-ink-950 text-white px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 py-16 lg:py-32" id="cursos">
      <div class="text-center mb-12">
        <span data-reveal class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest bg-gold-500/10 text-gold-400 border border-gold-500/30 px-4 py-2 rounded-full">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.42A12.02 12.02 0 0112 21.02 12.02 12.02 0 015.84 10.58L12 14z"/></svg>
          {{ __('app.courses_kicker') }}
        </span>
        <h2 data-reveal data-reveal-delay="0.1" class="font-serif text-3xl md:text-4xl leading-tight font-bold mt-6 text-balance">{{ __('app.courses_teaser_title') }}</h2>
        <p data-reveal data-reveal-delay="0.2" class="text-ink-300 mt-2 max-w-2xl mx-auto">{{ __('app.courses_teaser_subtitle') }}</p>
      </div>

      <div data-reveal-stagger class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto text-center border border-gold-500/15 bg-gradient-to-b from-ink-900/60 to-transparent rounded-2xl p-8 mb-16">
        @foreach([1, 2, 3, 4] as $stat)
          <div>
            <span class="block font-serif text-shine text-4xl font-black tabular-nums"><span data-count-up>{{ __('app.courses_stat'.$stat.'_value') }}</span>@if($stat === 3)&#9733;@endif</span>
            <span class="block text-xs uppercase tracking-wide text-ink-300 mt-1">{{ __('app.courses_stat'.$stat.'_label') }}</span>
          </div>
        @endforeach
      </div>

      <h3 data-reveal class="text-center font-serif text-2xl font-bold mb-8">{{ __('app.courses_why_title') }}</h3>
      <div data-reveal-stagger class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-16">
        @foreach([1, 2, 3, 4] as $reason)
          <div class="spotlight bg-ink-900 border border-gold-500/15 rounded-xl p-6 text-center transition-[border-color,translate] duration-500 hover:-translate-y-1 hover:border-gold-500/40">
            <h4 class="font-bold">{{ __('app.courses_why'.$reason.'_title') }}</h4>
            <p class="text-ink-300 text-sm mt-2">{{ __('app.courses_why'.$reason.'_body') }}</p>
          </div>
        @endforeach
      </div>

      <!-- Carousel Container -->
      <div data-reveal-stagger class="flex overflow-x-auto gap-8 pb-8 pt-4 px-4 snap-x snap-mandatory [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] -mx-4 sm:mx-0 sm:[mask-image:linear-gradient(to_right,transparent,black_2rem,black_calc(100%-2rem),transparent)]">
        @foreach($courses as $course)
        @php $soldOut = $course->isSoldOut(); @endphp
        <div class="group/course bg-ink-900 text-white rounded-2xl shadow-xl hover:shadow-2xl hover:shadow-gold transition-[translate,box-shadow,border-color] duration-500 overflow-hidden flex flex-col min-w-[85vw] sm:min-w-[350px] snap-center border border-gold-500/15 hover:border-gold-500/40 hover:-translate-y-1.5">

          {{-- Header / image area --}}
          <div class="relative h-48 bg-gradient-to-br from-ink-800 to-ink-900 flex items-center justify-center p-6 overflow-hidden">
            @if($course->image_url)
              <img src="{{ asset($course->image_url) }}" alt="{{ $course->title }}" loading="lazy" decoding="async" class="h-full w-auto object-contain transition-transform duration-700 group-hover/course:scale-105 {{ $soldOut ? 'opacity-40' : '' }}">
            @else
              <div class="font-serif text-shine font-black text-3xl tracking-tighter {{ $soldOut ? 'opacity-40' : '' }}">{{ __('app.brand_name') }}</div>
            @endif

            @if($soldOut)
              <span class="absolute top-3 left-3 flex items-center gap-1 bg-red-600 text-white text-xs font-bold uppercase tracking-wide px-3 py-1.5 rounded">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                {{ __('app.courses_sold_out') }}
              </span>
              <span class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <span class="border-2 border-white/40 text-white/50 text-xl font-black uppercase tracking-widest px-6 py-2 -rotate-12">{{ __('app.courses_sold_out') }}</span>
              </span>
            @elseif($course->type === 'virtual')
              <span class="absolute top-3 left-3 flex items-center gap-1 bg-gold-500 text-ink-950 text-xs font-bold uppercase tracking-wide px-3 py-1.5 rounded">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.42A12.02 12.02 0 0112 21.02 12.02 12.02 0 015.84 10.58L12 14z"/></svg>
                {{ __('app.courses_virtual_badge') }}
              </span>
            @endif

            <span class="absolute top-3 right-3 flex items-center gap-1 bg-black/70 text-gold-400 text-xs font-bold px-2.5 py-1.5 rounded">
              <svg class="w-3.5 h-3.5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.062 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.958z"/></svg>
              {{ number_format($course->rating ?? 4.9, 1) }}
            </span>
          </div>

          {{-- Body --}}
          <div class="p-8 flex flex-col flex-grow">
            @if($course->category)
              <span class="inline-block self-start text-xs font-bold uppercase tracking-wide bg-gold-500/10 text-gold-400 border border-gold-500/20 px-3 py-1 rounded mb-4">{{ $course->category }}</span>
            @endif

            <h3 class="text-2xl font-bold mb-3 leading-tight">{{ $course->title }}</h3>
            <p class="text-ink-300 mb-6 flex-grow leading-relaxed">{{ Str::limit($course->description, 120) }}</p>

            <div class="space-y-2 text-sm text-ink-300 border-t border-gold-500/10 pt-5 mb-5">
              @if($course->isPresencial())
                <div class="flex items-center gap-4">
                  <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ optional($course->event_date)->translatedFormat('d \d\e F \d\e Y') }}
                  </span>
                  @if($course->location)
                    <span class="flex items-center gap-1.5">
                      <svg class="w-4 h-4 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                      {{ $course->location }}
                    </span>
                  @endif
                </div>
                <div class="flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4"/></svg>
                  {{ $course->seatsAvailable() }} {{ __('app.courses_seats_available') }}
                </div>
              @else
                <div class="flex items-center gap-4">
                  @if($course->duration_hours)
                    <span class="flex items-center gap-1.5">
                      <svg class="w-4 h-4 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                      {{ $course->duration_hours }}{{ __('app.courses_content_hours') }}
                    </span>
                  @endif
                  @if($course->level)
                    <span class="flex items-center gap-1.5">
                      <svg class="w-4 h-4 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20V10m6 10V4m6 16v-7"/></svg>
                      {{ $course->level }}
                    </span>
                  @endif
                </div>
                <div class="flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                  {{ __('app.courses_lifetime_access') }}
                </div>
              @endif
            </div>

            @if($course->isPresencial())
              <div class="flex items-end justify-between gap-3 border-t border-gold-500/10 pt-5">
                <div class="leading-snug">
                  <span class="block text-sm text-ink-400">{{ __('app.courses_investment') }}</span>
                  <span class="text-2xl font-black text-gold-400">COP ${{ number_format($course->price, 0, ',', '.') }}</span>
                </div>
                @if($soldOut)
                  <span class="shrink-0 bg-ink-800 text-ink-400 font-bold uppercase text-sm px-5 py-3 rounded-xl cursor-not-allowed">{{ __('app.courses_sold_out') }}</span>
                @else
                  <a href="{{ route('courses.show', $course->id) }}" class="shine-sweep shrink-0 bg-gold-500 hover:bg-gold-400 text-ink-950 font-bold uppercase text-sm px-5 py-3 rounded-xl transition duration-200 shadow-md">{{ __('app.courses_reserve_cta') }}</a>
                @endif
              </div>
            @else
              <div class="flex items-end justify-between mb-6">
                <div>
                  <span class="block text-sm text-ink-400 font-semibold mb-1">{{ __('app.courses_investment') }}</span>
                  <span class="text-3xl font-black text-gold-400">${{ number_format($course->price, 0, ',', '.') }}</span>
                </div>
              </div>
              <a href="{{ route('courses.show', $course->id) }}" class="shine-sweep block w-full text-center bg-gold-500 hover:bg-gold-400 text-ink-950 font-bold py-4 px-6 rounded-xl transition duration-200 shadow-md">
                {{ __('app.courses_cta') }}
              </a>
            @endif
          </div>
        </div>
        @endforeach
      </div>

      <div class="text-center mt-4">
        <a href="#cursos" class="inline-block text-gold-400 font-semibold hover:text-gold-300 transition">{{ __('app.courses_teaser_cta') }} &rarr;</a>
      </div>
    </section>
    <!-- end courses -->

    <!-- start results -->
    <section class="relative bg-white dark:bg-ink-950 px-4 py-16 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 lg:py-32 transition-colors duration-300 text-center" id="resultados">
      <span data-reveal class="inline-block text-xs font-bold uppercase tracking-widest bg-gold-50 dark:bg-gold-500/10 text-gold-600 dark:text-gold-400 border border-gold-500/30 px-4 py-2 rounded-full">{{ __('app.results_badge') }}</span>
      <h2 data-reveal data-reveal-delay="0.1" class="font-serif text-3xl md:text-4xl leading-tight font-bold mt-6 text-balance">{{ __('app.results_title') }}</h2>
      <p data-reveal data-reveal-delay="0.2" class="mt-2 max-w-xl mx-auto text-ink-600 dark:text-ink-300">{{ __('app.results_subtitle') }}</p>

      {{-- Results Carousel --}}
      <div data-reveal="scale" class="relative mt-12 w-full" id="results-carousel-wrapper">
        {{-- Track --}}
        <div class="overflow-hidden rounded-2xl" id="results-carousel-overflow">
          <div class="flex transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] motion-reduce:transition-none" id="results-carousel-track">
            @foreach(['resultado-1.jpg', 'resultado-2.jpg', 'resultado-3.jpg', 'resultado-4.jpg', 'resultado-alt.jpg'] as $resultImage)
              <div class="results-carousel-slide flex-shrink-0 w-full sm:w-1/2 lg:w-1/3 px-2">
                <div class="group/result relative overflow-hidden rounded-2xl border border-gold-500/20 shadow-lg aspect-[4/5]">
                  <img src="/images/{{ $resultImage }}" alt="{{ __('app.results_title') }} {{ $loop->iteration }}" loading="lazy" decoding="async" class="w-full h-full object-cover transition duration-700 group-hover/result:scale-105">
                  <div class="absolute inset-0 bg-gradient-to-t from-ink-950/40 to-transparent opacity-0 transition-opacity duration-500 group-hover/result:opacity-100" aria-hidden="true"></div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Prev button --}}
        <button id="results-prev" type="button" aria-label="{{ __('app.results_prev') }}" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 z-10 w-11 h-11 rounded-full bg-ink-950/80 backdrop-blur border border-gold-500/30 text-gold-400 flex items-center justify-center hover:bg-gold-500 hover:text-ink-950 active:scale-95 transition duration-200 shadow-lg">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        {{-- Next button --}}
        <button id="results-next" type="button" aria-label="{{ __('app.results_next') }}" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 z-10 w-11 h-11 rounded-full bg-ink-950/80 backdrop-blur border border-gold-500/30 text-gold-400 flex items-center justify-center hover:bg-gold-500 hover:text-ink-950 active:scale-95 transition duration-200 shadow-lg">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>

        {{-- Dot indicators --}}
        <div class="flex justify-center gap-2 mt-6" id="results-dots"></div>
      </div>

      <script>
        window.addEventListener('load', function () {
          var track      = document.getElementById('results-carousel-track');
          var overflow   = document.getElementById('results-carousel-overflow');
          var dotsEl     = document.getElementById('results-dots');
          var slides     = Array.from(track.querySelectorAll('.results-carousel-slide'));
          var total      = slides.length;
          var current    = 0;
          var timer      = null;
          var visible    = 1;
          var autoplay   = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
          var dotLabel   = @json(__('app.results_go_to'));

          function getVisible() {
            var w = overflow.offsetWidth;
            if (w >= 1024) return 3;
            if (w >= 640)  return 2;
            return 1;
          }

          function setWidths() {
            visible = getVisible();
            slides.forEach(function(s) {
              s.style.flex = '0 0 ' + (100 / visible) + '%';
              s.style.maxWidth = (100 / visible) + '%';
            });
          }

          function maxIdx() { return Math.max(0, total - visible); }

          function buildDots() {
            dotsEl.innerHTML = '';
            var n = maxIdx() + 1;
            for (var i = 0; i < n; i++) {
              (function(idx) {
                var d = document.createElement('button');
                d.type = 'button';
                d.setAttribute('aria-label', dotLabel + ' ' + (idx + 1));
                d.className = 'h-2.5 rounded-full cursor-pointer transition-all duration-500';
                d.addEventListener('click', function() { goTo(idx); resetTimer(); });
                dotsEl.appendChild(d);
              })(i);
            }
            syncDots();
          }

          function syncDots() {
            var dots = dotsEl.querySelectorAll('button');
            dots.forEach(function(d, i) {
              var active = i === current;
              d.style.width      = active ? '28px' : '10px';
              d.style.background = active ? 'var(--color-gold-500)' : 'rgba(150,150,150,.4)';
              d.setAttribute('aria-current', active ? 'true' : 'false');
            });
          }

          function goTo(idx) {
            current = Math.min(Math.max(idx, 0), maxIdx());
            var slideW = slides[0].getBoundingClientRect().width;
            track.style.transform = 'translateX(-' + (current * slideW) + 'px)';
            syncDots();
          }

          function next() { goTo(current >= maxIdx() ? 0 : current + 1); }
          function prev() { goTo(current <= 0 ? maxIdx() : current - 1); }

          function resetTimer() {
            if (timer) clearInterval(timer);
            if (autoplay) timer = setInterval(next, 4500);
          }

          document.getElementById('results-next').addEventListener('click', function() { next(); resetTimer(); });
          document.getElementById('results-prev').addEventListener('click', function() { prev(); resetTimer(); });

          var wrapEl = document.getElementById('results-carousel-wrapper');
          wrapEl.addEventListener('mouseenter', function() { if (timer) clearInterval(timer); });
          wrapEl.addEventListener('mouseleave', resetTimer);

          window.addEventListener('resize', function() {
            setWidths();
            buildDots();
            goTo(Math.min(current, maxIdx()));
          });

          setWidths();
          buildDots();
          goTo(0);
          resetTimer();
        });
      </script>
    </section>
    <!-- end results -->

    <!-- start testimonials -->
    <section class="relative bg-ink-50 dark:bg-ink-900 px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 py-16 lg:py-32 transition-colors duration-300">
      <h2 data-reveal class="font-serif text-3xl md:text-4xl leading-tight font-bold text-center mb-12 text-balance">{{ __('app.testimonials2_title') }}</h2>

      <div data-reveal-stagger data-autoplay-carousel="2500" class="flex overflow-x-auto gap-6 pb-8 pt-2 px-4 snap-x snap-mandatory [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] -mx-4 sm:mx-0 sm:[mask-image:linear-gradient(to_right,transparent,black_2rem,black_calc(100%-2rem),transparent)]">
        @foreach([
          ['name' => 'testimonial1_name', 'quote' => 'testimonial1_quote', 'initials' => 'CT'],
          ['name' => 'testimonial2_name', 'quote' => 'testimonial2_quote', 'initials' => 'JM'],
          ['name' => 'testimonial3_name', 'quote' => 'testimonial3_quote', 'initials' => 'VR'],
          ['name' => 'testimonial4_name', 'quote' => 'testimonial4_quote', 'initials' => 'DS'],
          ['name' => 'testimonial5_name', 'quote' => 'testimonial5_quote', 'initials' => 'AM'],
        ] as $t)
        <div class="spotlight bg-white dark:bg-ink-800 rounded-2xl border border-gold-500/15 p-6 min-w-[85vw] sm:min-w-[320px] snap-center flex flex-col shadow-sm transition-[translate,box-shadow,border-color] duration-500 hover:-translate-y-1 hover:border-gold-500/40 hover:shadow-xl">
          <div class="flex items-center justify-between mb-3">
            <svg class="w-6 h-6 text-gold-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h4v6l-3 5H5l2-5H5V7h2zm8 0h4v6l-3 5h-3l2-5h-2V7h2z"/></svg>
            <div class="flex gap-0.5 text-gold-500" role="img" aria-label="5/5">
              @for ($star = 0; $star < 5; $star++)
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.062 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.958z"/></svg>
              @endfor
            </div>
          </div>
          <p class="italic text-ink-700 dark:text-ink-200 flex-grow leading-relaxed">&ldquo;{{ __('app.'.$t['quote']) }}&rdquo;</p>
          <div class="flex items-center mt-4">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gold-400 to-gold-700 flex items-center justify-center text-ink-950 font-bold text-sm">{{ $t['initials'] }}</div>
            <span class="ml-3 font-semibold">{{ __('app.'.$t['name']) }}</span>
          </div>
        </div>
        @endforeach
      </div>
    </section>
    <!-- end testimonials -->

    <!-- start faq -->
    <section class="relative bg-white dark:bg-ink-950 px-4 py-16 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 lg:py-32 transition-colors duration-300">
      <div class="text-center mb-12">
        <h2 data-reveal class="font-serif text-3xl md:text-4xl leading-tight font-bold text-balance">{{ __('app.faq_title') }}</h2>
        <p data-reveal data-reveal-delay="0.1" class="mt-2 max-w-2xl mx-auto text-ink-600 dark:text-ink-300">{{ __('app.faq_subtitle') }}</p>
      </div>

      <div data-reveal-stagger class="max-w-3xl mx-auto space-y-4">
        @foreach([1, 2, 3, 4] as $n)
        <details data-faq name="faq" class="group bg-white dark:bg-ink-900 border border-gold-500/20 open:border-gold-500/50 open:shadow-lg open:shadow-gold-500/5 rounded-xl transition-[border-color,box-shadow] duration-300" @if($n === 1) open @endif>
          <summary class="flex items-center justify-between gap-4 cursor-pointer font-serif text-lg font-bold p-6 hover:text-gold-600 dark:hover:text-gold-400 transition-colors">
            {{ __('app.faq'.$n.'_q') }}
            <span class="flex-shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-full border border-gold-500/30 text-gold-500 transition duration-300 group-open:rotate-45 group-open:bg-gold-500 group-open:text-ink-950" aria-hidden="true">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            </span>
          </summary>
          <p data-faq-answer class="px-6 pb-6 -mt-2 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.faq'.$n.'_a') }}</p>
        </details>
        @endforeach
      </div>
    </section>
    <!-- end faq -->

    <!-- start final cta -->
    <section
      class="relative overflow-hidden bg-gradient-to-r from-ink-950 to-black px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 py-24 lg:py-32 text-center" id="contacto">
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(207,154,46,0.16),transparent_65%)]" aria-hidden="true"></div>
      <div class="relative">
        <h2 data-reveal class="font-serif text-3xl md:text-5xl font-bold text-white leading-tight text-balance">
          {{ __('app.final_cta_title_line1') }}<br>
          <span class="italic text-shine">{{ __('app.final_cta_title_line2') }}</span>
        </h2>
        <p data-reveal data-reveal-delay="0.1" class="text-ink-300 max-w-xl mx-auto mt-4">{{ __('app.final_cta_body') }}</p>
        <div data-reveal="scale" data-reveal-delay="0.2" class="mt-10">
          <a href="https://wa.me/573115228585?text=Hola,%20quiero%20agendar%20mi%20cita."
            target="_blank" rel="noopener"
            class="shine-sweep group/cta inline-flex items-center gap-3 px-10 py-4 bg-gradient-to-r from-gold-600 via-gold-400 to-gold-600 text-ink-950 rounded-full font-bold shadow-gold transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_18px_40px_-12px_rgba(207,154,46,0.65)] active:translate-y-0">
            {{ __('app.final_cta_button') }}
            <svg class="w-5 h-5 transition-transform duration-300 group-hover/cta:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/></svg>
          </a>
        </div>
      </div>
    </section>
    <!-- end final cta -->

    <!-- start footer -->
    <footer class="relative bg-ink-950 text-ink-300 px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 py-12 border-t border-gold-500/15 text-center">
      <h3 class="font-serif text-shine font-bold text-2xl">{{ __('app.brand_name') }}</h3>
      <p class="mt-2">{{ __('app.footer_tagline') }}</p>
      <p class="text-sm text-ink-500 mt-8">&copy; {{ now()->year }} {{ __('app.brand_name') }}. {{ __('app.footer_rights') }}</p>
      <a href="{{ route('data-policy') }}" class="inline-block mt-2 text-sm text-ink-400 hover:text-gold-400 transition">{{ __('app.data_policy_title') }}</a>
    </footer>
    <!-- end footer -->

  </main>

  {{-- Treatment 01 details: description, prices and a WhatsApp booking link --}}
  <dialog id="treatment1-dialog" data-modal aria-labelledby="treatment1-dialog-title"
    class="m-auto w-[min(92vw,640px)] max-w-none max-h-[90dvh] overflow-y-auto rounded-2xl border border-gold-500/30 bg-white dark:bg-ink-900 p-0 text-ink-900 dark:text-ink-100 shadow-2xl shadow-black/50 backdrop:bg-ink-950/80 backdrop:backdrop-blur-sm">
    <div class="p-6 md:p-8">
      <div class="flex items-start justify-between gap-4">
        <div>
          <span class="inline-block text-xs font-bold uppercase tracking-wide bg-gold-500 text-ink-950 px-3 py-1 rounded-full">{{ __('app.treatment1_badge') }}</span>
          <h2 id="treatment1-dialog-title" class="mt-3 font-serif text-2xl md:text-3xl font-bold leading-tight">{{ __('app.treatment1_title') }}</h2>
        </div>
        <button type="button" data-modal-close aria-label="{{ __('app.modal_close') }}"
          class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-gold-500/40 text-gold-600 dark:text-gold-400 hover:bg-gold-500 hover:text-ink-950 transition">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <p class="mt-4 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.treatment1_body') }}</p>
      <div class="flex flex-wrap gap-2 mt-4">
        @foreach(['treatment1_tag1', 'treatment1_tag2', 'treatment1_tag3'] as $tag)
          <span class="text-xs font-semibold px-3 py-1 rounded-full bg-gold-50 dark:bg-gold-500/10 text-gold-700 dark:text-gold-300">{{ __('app.'.$tag) }}</span>
        @endforeach
      </div>

      <h3 class="mt-8 text-sm font-bold uppercase tracking-wider text-gold-600 dark:text-gold-400">{{ __('app.treatment_prices_title') }}</h3>
      {{-- Prices come from the "Inversión Clara" section so both always show the same amounts --}}
      <dl class="mt-3 divide-y divide-gold-500/15 rounded-xl border border-gold-500/20">
        @foreach(['pricing_tier1_item2'] as $priceItem)
          <div class="p-4">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
              <dt class="font-semibold">{{ __('app.'.$priceItem.'_name') }}</dt>
              <dd class="font-bold text-lg text-gold-600 dark:text-gold-400">{{ __('app.'.$priceItem.'_price') }}</dd>
            </div>
            <dd class="mt-1 text-sm text-ink-500 dark:text-ink-400">{{ __('app.'.$priceItem.'_body') }}</dd>
          </div>
        @endforeach
      </dl>
      <p class="mt-3 text-xs text-ink-500 dark:text-ink-400">{{ __('app.treatment_prices_note') }}</p>

      <a href="https://wa.me/573115228585?text={{ rawurlencode(__('app.treatment1_whatsapp_message')) }}" target="_blank" rel="noopener"
        class="shine-sweep mt-8 flex w-full items-center justify-center gap-3 px-6 py-4 bg-gradient-to-r from-gold-600 via-gold-400 to-gold-600 text-ink-950 font-bold rounded-full shadow-gold transition duration-300 hover:-translate-y-0.5">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
        {{ __('app.treatment_book_cta') }}
      </a>
    </div>
  </dialog>

  @if($introVideoUrl)
    {{-- Doctor intro video: the player URL is only assigned when the dialog opens, so it costs nothing until clicked --}}
    <dialog id="intro-video-dialog" data-modal aria-labelledby="intro-video-title"
      class="m-auto w-[min(92vw,960px)] max-w-none bg-transparent p-0 text-white backdrop:bg-ink-950/85 backdrop:backdrop-blur-sm open:flex flex-col">
      <div class="flex items-center justify-between gap-4 pb-3">
        <h2 id="intro-video-title" class="font-serif text-xl md:text-2xl font-bold">{{ __('app.doctor_video_title') }}</h2>
        <button type="button" data-modal-close aria-label="{{ __('app.doctor_video_close') }}"
          class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-gold-500/40 text-gold-400 hover:bg-gold-500 hover:text-ink-950 transition">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="relative w-full aspect-video overflow-hidden rounded-2xl border border-gold-500/30 bg-ink-950 shadow-2xl shadow-black/60">
        <iframe data-modal-src="{{ $introVideoUrl }}" referrerpolicy="strict-origin-when-cross-origin" title="{{ __('app.doctor_video_title') }}" class="absolute inset-0 h-full w-full border-0"
          allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>
      </div>
    </dialog>
  @endif

  <!-- WhatsApp Floating Button -->
  <a href="https://wa.me/573115228585?text=Hola,%20quisiera%20más%20información." target="_blank" rel="noopener" aria-label="{{ __('app.top_bar_whatsapp') }}" class="fixed bottom-6 right-6 w-16 h-16 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-2xl transition duration-300 hover:-translate-y-1 hover:scale-110 active:scale-95 z-[9999] flex items-center justify-center aspect-square" style="position: fixed; z-index: 9999;">
    <span class="pulse-ring absolute inset-0 rounded-full bg-green-500" aria-hidden="true"></span>
    <svg class="relative w-8 h-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.347-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.876 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
  </a>
</body>

</html>

<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  @include('partials.theme-init')
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <title>{{ __('app.meta_title') }}</title>

  <style>
    #menu-toggle:checked+#menu {
      display: block;
    }

    details > summary {
      list-style: none;
    }
    details > summary::-webkit-details-marker {
      display: none;
    }
    details[open] .faq-icon-plus {
      display: none;
    }
    .faq-icon-x {
      display: none;
    }
    details[open] .faq-icon-x {
      display: block;
    }
  </style>
</head>

<body class="antialiased bg-white dark:bg-ink-950 font-sans text-ink-900 dark:text-ink-100 transition-colors duration-300">

  <main class="w-full">

    <!-- start header -->
    <header class="absolute top-0 left-0 w-full z-50 px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64">
      <div class="hidden md:flex justify-between items-center py-3 border-b text-sm"
        style="border-color: rgba(207,154,46,.25)">
        <a href="https://wa.me/573115228585?text=Hola,%20quisiera%20más%20información."
          target="_blank" class="flex items-center text-white hover:text-gold-400 transition">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
          {{ __('app.top_bar_whatsapp') }}
        </a>

        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            @foreach(['en' => '🇺🇸 EN', 'es' => '🇨🇴 ES'] as $locale => $label)
              <a href="{{ route('lang.switch', $locale) }}"
                 class="text-xs font-bold px-2 py-1 rounded transition {{ app()->getLocale() === $locale ? 'bg-gold-500 text-ink-950' : 'text-white hover:text-gold-400' }}">
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

      <div class="flex flex-wrap items-center justify-between py-6">
        <div class="w-1/2 md:w-auto">
          <a href="/" class="font-serif text-shine font-bold text-2xl">
            {{ __('app.brand_name') }}
          </a>
        </div>

        <label for="menu-toggle" class="pointer-cursor md:hidden block"><svg class="fill-current text-white"
            xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
            <title>menu</title>
            <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"></path>
          </svg></label>

        <input class="hidden" type="checkbox" id="menu-toggle">

        <div class="hidden md:block w-full md:w-auto" id="menu">
          <nav
            class="w-full bg-white dark:bg-ink-900 md:bg-transparent md:dark:bg-transparent rounded shadow-lg px-6 py-4 mt-4 text-center md:p-0 md:mt-0 md:shadow-none">
            <ul class="md:flex items-center">
              <li><a class="py-2 inline-block md:text-white font-semibold" href="#filosofia">{{ __('app.nav_philosophy') }}</a></li>
              <li class="md:ml-4"><a class="py-2 inline-block md:text-white md:px-2 font-semibold" href="#doctora">{{ __('app.nav_doctor') }}</a></li>
              <li class="md:ml-4"><a class="py-2 inline-block md:text-white md:px-2 font-semibold" href="#tratamientos">{{ __('app.nav_treatments') }}</a></li>
              <li class="md:ml-4"><a class="py-2 inline-block md:text-white md:px-2 font-semibold" href="#cursos">{{ __('app.nav_courses') }}</a></li>
              <li class="md:ml-4"><a class="py-2 inline-block md:text-white md:px-2 font-semibold" href="#resultados">{{ __('app.nav_results') }}</a></li>
              <li class="md:ml-6 mt-3 md:mt-0">
                <a class="inline-block font-semibold px-4 py-2 text-white bg-black md:bg-transparent md:text-white border border-gold-500/60 rounded hover:bg-gold-600 hover:border-gold-600 transition"
                  href="#contacto">{{ __('app.nav_book') }}</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </header>
    <!-- end header -->

    <!-- start hero -->
    <div class="bg-ink-950">
      <section class="cover bg-gradient-to-br from-ink-950 via-ink-900 to-black relative px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 overflow-hidden py-48 flex
      items-center min-h-screen">
        <div class="w-full h-full absolute top-0 left-0 z-0">
          <img src="/images/IMG_0685.JPG.jpeg" alt="" class="w-full h-full object-cover object-[center_62%] opacity-20 -scale-x-100">
        </div>
        <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_20%_20%,rgba(207,154,46,0.18),transparent_55%)]"></div>

        <div class="lg:w-3/4 xl:w-2/4 relative z-10 h-100 lg:mt-16">
          <div>
            <span class="inline-block text-gold-400 tracking-[0.3em] text-xs font-semibold uppercase mb-4">{{ __('app.hero_eyebrow') }}</span>
            <h1 class="text-white font-serif text-4xl md:text-5xl xl:text-6xl font-bold leading-tight">
              {{ __('app.hero_title_line1') }}<br>
              <span class="italic text-gold-400">{{ __('app.hero_title_line2') }}</span>
            </h1>
            <p class="text-ink-200 text-xl md:text-2xl leading-snug mt-4">{{ __('app.hero_body') }}</p>

            <div class="flex flex-wrap items-center gap-6 mt-8">
              <a href="https://wa.me/573115228585?text=Hola,%20quisiera%20agendar%20mi%20Valoraci%C3%B3n%20VIP."
                target="_blank"
                class="shine-sweep px-8 py-4 bg-gradient-to-r from-gold-600 via-gold-400 to-gold-600 text-ink-950 rounded inline-block font-bold shadow-gold">
                {{ __('app.hero_cta') }}
              </a>

              <div class="flex items-center">
                <div class="flex -space-x-3">
                  @for ($i = 0; $i < 3; $i++)
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-gold-300 to-gold-600 border-2 border-ink-950 flex items-center justify-center">
                      <svg class="w-4 h-4 text-ink-950" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/></svg>
                    </div>
                  @endfor
                </div>
                <span class="ml-3 text-white text-sm font-semibold">{{ __('app.hero_social_proof') }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <!-- end hero -->

    <!-- start philosophy -->
    <section class="relative bg-white dark:bg-ink-950 px-4 py-16 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 lg:py-32 transition-colors duration-300 text-center" id="filosofia">
      <h2 class="font-serif text-3xl md:text-4xl leading-tight font-bold max-w-3xl mx-auto">
        {{ __('app.philosophy_title') }}
      </h2>
      <p class="mt-4 max-w-2xl mx-auto leading-relaxed text-ink-600 dark:text-ink-300">{{ __('app.philosophy_body') }}</p>

      <div class="grid gap-6 md:grid-cols-3 mt-16 text-left">
        <div class="bg-white dark:bg-ink-900 rounded-xl border border-gold-500/15 p-8 shadow-md">
          <svg class="w-8 h-8 text-gold-500 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3h12l3 5-9 13L3 8l3-5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 8h18M9 3l3 5 3-5M12 8l-3 13M12 8l3 13"/></svg>
          <h3 class="text-xl font-bold">{{ __('app.feature1_title') }}</h3>
          <p class="mt-2 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.feature1_body') }}</p>
        </div>
        <div class="bg-white dark:bg-ink-900 rounded-xl border border-gold-500/15 p-8 shadow-md">
          <svg class="w-8 h-8 text-gold-500 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v6c0 4.5-3 8.4-7 9.5-4-1.1-7-5-7-9.5V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
          <h3 class="text-xl font-bold">{{ __('app.feature2_title') }}</h3>
          <p class="mt-2 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.feature2_body') }}</p>
        </div>
        <div class="bg-white dark:bg-ink-900 rounded-xl border border-gold-500/15 p-8 shadow-md">
          <svg class="w-8 h-8 text-gold-500 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8l3 3 5-6 5 6 3-3-2 11H6L4 8z"/></svg>
          <h3 class="text-xl font-bold">{{ __('app.feature3_title') }}</h3>
          <p class="mt-2 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.feature3_body') }}</p>
        </div>
      </div>
    </section>
    <!-- end philosophy -->

    <!-- start doctor bio -->
    <section class="relative bg-ink-950 text-white px-4 py-16 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 lg:py-32" id="doctora">
      <div class="flex flex-col lg:flex-row lg:items-center gap-12 lg:gap-16">
        <div class="w-full lg:w-2/5 relative">
          <div class="aspect-[4/5] rounded-2xl overflow-hidden border border-gold-500/20">
            <img src="/images/doctora.jpg" alt="{{ __('app.brand_name') }}" class="w-full h-full object-cover">
          </div>
          <div class="absolute -bottom-6 -right-4 bg-gold-500 text-ink-950 rounded-xl px-6 py-4 shadow-gold">
            <span class="block text-2xl font-black leading-none">{{ __('app.doctor_years') }}</span>
          </div>
        </div>

        <div class="w-full lg:w-3/5">
          <span class="text-gold-400 font-semibold uppercase tracking-wide text-sm">{{ __('app.doctor_role') }}</span>
          <p class="font-serif text-3xl md:text-4xl leading-snug mt-3">
            &ldquo;{{ __('app.doctor_quote') }}&rdquo;
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

          <a href="#tratamientos" class="shine-sweep inline-block mt-8 px-6 py-3 border border-gold-500 text-gold-400 font-semibold rounded hover:bg-gold-500 hover:text-ink-950 transition">
            {{ __('app.doctor_cta') }}
          </a>
        </div>
      </div>
    </section>
    <!-- end doctor bio -->

    <!-- start treatments -->
    <section class="relative bg-white dark:bg-ink-950 px-4 py-16 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 lg:py-32 transition-colors duration-300" id="tratamientos">
      <h2 class="font-serif text-3xl md:text-4xl leading-tight font-bold text-center mb-16">{{ __('app.treatments_title') }}</h2>

      <div class="grid gap-8 lg:grid-cols-2">
        <div class="bg-white dark:bg-ink-900 rounded-2xl border border-gold-500/20 p-8 shadow-md relative overflow-hidden">
          <span class="absolute top-6 right-6 text-xs font-bold uppercase tracking-wide bg-gold-500 text-ink-950 px-3 py-1 rounded-full">{{ __('app.treatment1_badge') }}</span>
          <span class="font-serif text-5xl text-gold-500/30 font-bold">01</span>
          <h3 class="text-2xl font-bold mt-2">{{ __('app.treatment1_title') }}</h3>
          <p class="mt-3 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.treatment1_body') }}</p>
          <div class="flex flex-wrap gap-2 mt-6">
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-gold-50 dark:bg-gold-500/10 text-gold-700 dark:text-gold-300">{{ __('app.treatment1_tag1') }}</span>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-gold-50 dark:bg-gold-500/10 text-gold-700 dark:text-gold-300">{{ __('app.treatment1_tag2') }}</span>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-gold-50 dark:bg-gold-500/10 text-gold-700 dark:text-gold-300">{{ __('app.treatment1_tag3') }}</span>
          </div>
          <a href="https://wa.me/573115228585?text=Hola,%20quiero%20consultar%20sobre%20Carillas%20en%20Resina."
            target="_blank" class="shine-sweep inline-block mt-8 px-6 py-3 bg-ink-950 dark:bg-gold-500 text-gold-400 dark:text-ink-950 font-bold rounded-lg shadow-md">
            {{ __('app.treatment_cta') }}
          </a>
        </div>

        <div class="bg-white dark:bg-ink-900 rounded-2xl border border-gold-500/20 p-8 shadow-md relative overflow-hidden">
          <span class="absolute top-6 right-6 text-xs font-bold uppercase tracking-wide bg-gold-500 text-ink-950 px-3 py-1 rounded-full">{{ __('app.treatment2_badge') }}</span>
          <span class="font-serif text-5xl text-gold-500/30 font-bold">02</span>
          <h3 class="text-2xl font-bold mt-2">{{ __('app.treatment2_title') }}</h3>
          <p class="mt-3 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.treatment2_body') }}</p>
          <div class="flex flex-wrap gap-2 mt-6">
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-gold-50 dark:bg-gold-500/10 text-gold-700 dark:text-gold-300">{{ __('app.treatment2_tag1') }}</span>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-gold-50 dark:bg-gold-500/10 text-gold-700 dark:text-gold-300">{{ __('app.treatment2_tag2') }}</span>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-gold-50 dark:bg-gold-500/10 text-gold-700 dark:text-gold-300">{{ __('app.treatment2_tag3') }}</span>
          </div>
          <a href="https://wa.me/573115228585?text=Hola,%20quiero%20consultar%20sobre%20Dise%C3%B1o%20Digital%20de%20Sonrisa%20(DSD)."
            target="_blank" class="shine-sweep inline-block mt-8 px-6 py-3 bg-ink-950 dark:bg-gold-500 text-gold-400 dark:text-ink-950 font-bold rounded-lg shadow-md">
            {{ __('app.treatment_cta') }}
          </a>
        </div>
      </div>
    </section>
    <!-- end treatments -->

    <!-- start process -->
    <section class="relative bg-ink-50 dark:bg-ink-900 px-4 py-16 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 lg:py-32 transition-colors duration-300 text-center">
      <h2 class="font-serif text-3xl md:text-4xl leading-tight font-bold">{{ __('app.process_title') }}</h2>
      <p class="mt-2 text-ink-600 dark:text-ink-300">{{ __('app.process_subtitle') }}</p>

      <div class="grid gap-8 md:grid-cols-3 mt-16 text-left">
        <div>
          <span class="flex items-center justify-center w-12 h-12 rounded-full bg-gold-500 text-ink-950 font-black text-lg mb-4">01</span>
          <h3 class="text-xl font-bold">{{ __('app.process1_title') }}</h3>
          <p class="mt-2 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.process1_body') }}</p>
        </div>
        <div>
          <span class="flex items-center justify-center w-12 h-12 rounded-full bg-gold-500 text-ink-950 font-black text-lg mb-4">02</span>
          <h3 class="text-xl font-bold">{{ __('app.process2_title') }}</h3>
          <p class="mt-2 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.process2_body') }}</p>
        </div>
        <div>
          <span class="flex items-center justify-center w-12 h-12 rounded-full bg-gold-500 text-ink-950 font-black text-lg mb-4">03</span>
          <h3 class="text-xl font-bold">{{ __('app.process3_title') }}</h3>
          <p class="mt-2 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.process3_body') }}</p>
        </div>
      </div>
    </section>
    <!-- end process -->

    <!-- start pricing -->
    <section class="relative bg-white dark:bg-ink-950 px-4 py-16 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 lg:py-32 transition-colors duration-300 text-center">
      <h2 class="font-serif text-3xl md:text-4xl leading-tight font-bold">{{ __('app.pricing_title') }}</h2>
      <p class="mt-2 max-w-xl mx-auto text-ink-600 dark:text-ink-300">{{ __('app.pricing_subtitle') }}</p>

      <div class="grid gap-8 lg:grid-cols-2 mt-16 text-left">
        <div class="bg-ink-950 text-white rounded-2xl p-8 shadow-xl">
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
            target="_blank" class="shine-sweep block text-center mt-8 px-6 py-4 bg-gradient-to-r from-gold-600 via-gold-400 to-gold-600 text-ink-950 font-bold rounded-lg shadow-gold">
            {{ __('app.pricing_tier1_cta') }}
          </a>
        </div>

        <div class="bg-ink-50 dark:bg-ink-900 rounded-2xl p-8 border border-gold-500/15">
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
            target="_blank" class="block text-center mt-8 px-6 py-4 border border-gold-500 text-gold-600 dark:text-gold-400 font-bold rounded-lg hover:bg-gold-500 hover:text-ink-950 transition">
            {{ __('app.pricing_tier2_cta') }}
          </a>
        </div>
      </div>
    </section>
    <!-- end pricing -->

    <!-- start courses -->
    <section class="relative bg-ink-950 text-white px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 py-16 lg:py-32" id="cursos">
      <div class="text-center mb-12">
        <span class="inline-block text-xs font-bold uppercase tracking-widest bg-gold-500/10 text-gold-400 border border-gold-500/30 px-4 py-2 rounded-full">🎓 {{ __('app.courses_kicker') }}</span>
        <h2 class="font-serif text-3xl md:text-4xl leading-tight font-bold mt-6">{{ __('app.courses_teaser_title') }}</h2>
        <p class="text-ink-300 mt-2 max-w-2xl mx-auto">{{ __('app.courses_teaser_subtitle') }}</p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto text-center border border-gold-500/15 rounded-2xl p-8 mb-16">
        <div>
          <span class="block font-serif text-shine text-4xl font-black">{{ __('app.courses_stat1_value') }}</span>
          <span class="block text-xs uppercase tracking-wide text-ink-300 mt-1">{{ __('app.courses_stat1_label') }}</span>
        </div>
        <div>
          <span class="block font-serif text-shine text-4xl font-black">{{ __('app.courses_stat2_value') }}</span>
          <span class="block text-xs uppercase tracking-wide text-ink-300 mt-1">{{ __('app.courses_stat2_label') }}</span>
        </div>
        <div>
          <span class="block font-serif text-shine text-4xl font-black">{{ __('app.courses_stat3_value') }}&#9733;</span>
          <span class="block text-xs uppercase tracking-wide text-ink-300 mt-1">{{ __('app.courses_stat3_label') }}</span>
        </div>
        <div>
          <span class="block font-serif text-shine text-4xl font-black">{{ __('app.courses_stat4_value') }}</span>
          <span class="block text-xs uppercase tracking-wide text-ink-300 mt-1">{{ __('app.courses_stat4_label') }}</span>
        </div>
      </div>

      <h3 class="text-center font-serif text-2xl font-bold mb-8">{{ __('app.courses_why_title') }}</h3>
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-16">
        <div class="bg-ink-900 border border-gold-500/15 rounded-xl p-6 text-center">
          <h4 class="font-bold">{{ __('app.courses_why1_title') }}</h4>
          <p class="text-ink-300 text-sm mt-2">{{ __('app.courses_why1_body') }}</p>
        </div>
        <div class="bg-ink-900 border border-gold-500/15 rounded-xl p-6 text-center">
          <h4 class="font-bold">{{ __('app.courses_why2_title') }}</h4>
          <p class="text-ink-300 text-sm mt-2">{{ __('app.courses_why2_body') }}</p>
        </div>
        <div class="bg-ink-900 border border-gold-500/15 rounded-xl p-6 text-center">
          <h4 class="font-bold">{{ __('app.courses_why3_title') }}</h4>
          <p class="text-ink-300 text-sm mt-2">{{ __('app.courses_why3_body') }}</p>
        </div>
        <div class="bg-ink-900 border border-gold-500/15 rounded-xl p-6 text-center">
          <h4 class="font-bold">{{ __('app.courses_why4_title') }}</h4>
          <p class="text-ink-300 text-sm mt-2">{{ __('app.courses_why4_body') }}</p>
        </div>
      </div>

      <!-- Carousel Container -->
      <div class="flex overflow-x-auto gap-8 pb-8 pt-4 px-4 snap-x snap-mandatory [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] -mx-4 sm:mx-0">
        @foreach($courses as $course)
        @php $soldOut = $course->isSoldOut(); @endphp
        <div class="bg-ink-900 text-white rounded-2xl shadow-xl hover:shadow-2xl hover:shadow-gold transition-all duration-300 overflow-hidden flex flex-col min-w-[85vw] sm:min-w-[350px] snap-center border border-gold-500/15 transform hover:-translate-y-1">

          {{-- Header / image area --}}
          <div class="relative h-48 bg-gradient-to-br from-ink-800 to-ink-900 flex items-center justify-center p-6 overflow-hidden">
            @if($course->image_url)
              <img src="{{ asset($course->image_url) }}" alt="{{ $course->title }}" class="h-full w-auto object-contain {{ $soldOut ? 'opacity-40' : '' }}">
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
                <div class="text-sm leading-snug">
                  <span class="block text-ink-400">{{ __('app.courses_total_label') }}: <span class="text-white font-semibold">COP ${{ number_format($course->price, 0, ',', '.') }}</span></span>
                  @if($course->deposit_amount)
                    <span class="block text-gold-400 font-bold">{{ __('app.courses_deposit_label') }}: COP ${{ number_format($course->deposit_amount, 0, ',', '.') }}</span>
                  @endif
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
      <span class="inline-block text-xs font-bold uppercase tracking-widest bg-gold-50 dark:bg-gold-500/10 text-gold-600 dark:text-gold-400 border border-gold-500/30 px-4 py-2 rounded-full">{{ __('app.results_badge') }}</span>
      <h2 class="font-serif text-3xl md:text-4xl leading-tight font-bold mt-6">{{ __('app.results_title') }}</h2>
      <p class="mt-2 max-w-xl mx-auto text-ink-600 dark:text-ink-300">{{ __('app.results_subtitle') }}</p>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-12 max-w-3xl mx-auto">
        @foreach(['resultado-1.jpg', 'resultado-2.jpg', 'resultado-3.jpg', 'resultado-4.jpg'] as $resultImage)
          <div class="aspect-square rounded-xl overflow-hidden border border-gold-500/15 shadow-md">
            <img src="/images/{{ $resultImage }}" alt="{{ __('app.results_title') }}" class="w-full h-full object-cover hover:scale-105 transition duration-300">
          </div>
        @endforeach
      </div>
    </section>
    <!-- end results -->

    <!-- start testimonials -->
    <section class="relative bg-ink-50 dark:bg-ink-900 px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 py-16 lg:py-32 transition-colors duration-300">
      <h2 class="font-serif text-3xl leading-tight font-bold text-center mb-12">{{ __('app.testimonials2_title') }}</h2>

      <div class="flex overflow-x-auto gap-6 pb-8 px-4 snap-x snap-mandatory [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] -mx-4 sm:mx-0">
        @foreach([
          ['name' => 'testimonial1_name', 'quote' => 'testimonial1_quote', 'initials' => 'CT'],
          ['name' => 'testimonial2_name', 'quote' => 'testimonial2_quote', 'initials' => 'JM'],
          ['name' => 'testimonial3_name', 'quote' => 'testimonial3_quote', 'initials' => 'VR'],
          ['name' => 'testimonial4_name', 'quote' => 'testimonial4_quote', 'initials' => 'DS'],
          ['name' => 'testimonial5_name', 'quote' => 'testimonial5_quote', 'initials' => 'AM'],
        ] as $t)
        <div class="bg-white dark:bg-ink-800 rounded-xl border border-gold-500/15 p-6 min-w-[85vw] sm:min-w-[320px] snap-center flex flex-col">
          <svg class="w-6 h-6 text-gold-400 mb-3" fill="currentColor" viewBox="0 0 24 24"><path d="M7 7h4v6l-3 5H5l2-5H5V7h2zm8 0h4v6l-3 5h-3l2-5h-2V7h2z"/></svg>
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
        <h2 class="font-serif text-3xl md:text-4xl leading-tight font-bold">{{ __('app.faq_title') }}</h2>
        <p class="mt-2 max-w-2xl mx-auto text-ink-600 dark:text-ink-300">{{ __('app.faq_subtitle') }}</p>
      </div>

      <div class="max-w-3xl mx-auto space-y-4">
        @foreach([1, 2, 3, 4] as $n)
        <details class="group bg-white dark:bg-ink-900 border border-gold-500/20 rounded-xl p-6" @if($n === 1) open @endif>
          <summary class="flex items-center justify-between gap-4 cursor-pointer font-serif text-lg font-bold">
            {{ __('app.faq'.$n.'_q') }}
            <svg class="faq-icon-plus w-5 h-5 text-gold-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            <svg class="faq-icon-x w-5 h-5 text-gold-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
          </summary>
          <p class="mt-4 text-ink-600 dark:text-ink-300 leading-relaxed">{{ __('app.faq'.$n.'_a') }}</p>
        </details>
        @endforeach
      </div>
    </section>
    <!-- end faq -->

    <!-- start final cta -->
    <section
      class="relative bg-gradient-to-r from-ink-950 to-black px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 py-20 text-center" id="contacto">
      <h2 class="font-serif text-3xl md:text-4xl font-bold text-white leading-tight">
        {{ __('app.final_cta_title_line1') }}<br>
        <span class="italic text-gold-400">{{ __('app.final_cta_title_line2') }}</span>
      </h2>
      <p class="text-ink-300 max-w-xl mx-auto mt-4">{{ __('app.final_cta_body') }}</p>
      <a href="https://wa.me/573115228585?text=Hola,%20quiero%20agendar%20mi%20cita."
        target="_blank"
        class="shine-sweep px-8 py-4 bg-gold-500 text-ink-950 rounded inline-block font-semibold mt-8 shadow-gold">{{ __('app.final_cta_button') }}</a>
    </section>
    <!-- end final cta -->

    <!-- start footer -->
    <footer class="relative bg-ink-950 text-ink-300 px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 py-12 border-t border-gold-500/15 text-center">
      <h3 class="font-serif text-shine font-bold text-2xl">{{ __('app.brand_name') }}</h3>
      <p class="mt-2">{{ __('app.footer_tagline') }}</p>
      <p class="text-sm text-ink-500 mt-8">&copy; {{ now()->year }} {{ __('app.brand_name') }}. {{ __('app.footer_rights') }}</p>
    </footer>
    <!-- end footer -->

  </main>

  <!-- WhatsApp Floating Button -->
  <a href="https://wa.me/573115228585?text=Hola,%20quisiera%20más%20información." target="_blank" class="fixed bottom-6 right-6 w-16 h-16 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-2xl transition-transform transform hover:-translate-y-1 hover:scale-110 z-[9999] flex items-center justify-center aspect-square" style="position: fixed; z-index: 9999;">
    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.347-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.876 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
  </a>
</body>

</html>

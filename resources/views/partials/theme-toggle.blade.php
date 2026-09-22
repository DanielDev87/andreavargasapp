<button
  type="button"
  data-theme-toggle
  aria-label="{{ __('app.theme_toggle') }}"
  class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gold-500/40 text-gold-400 transition hover:border-gold-400 hover:text-gold-300 hover:shadow-gold {{ $class ?? '' }}"
>
  <svg class="h-5 w-5 block dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5m0 15V21m9-9h-1.5M4.5 12H3m15.364 6.364l-1.06-1.06M6.696 6.696l-1.06-1.06m12.728 0l-1.06 1.06M6.696 17.304l-1.06 1.06M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
  </svg>
  <svg class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
  </svg>
</button>

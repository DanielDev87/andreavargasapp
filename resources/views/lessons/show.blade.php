@extends('layouts.portal')

@section('title', $lesson->title)

@section('content')
  <a href="{{ route('courses.show', $course) }}" class="text-sm text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">&larr; {{ __('app.lesson_back_to_course') }}</a>

  <h1 class="text-2xl font-serif font-bold mt-3">{{ $lesson->title }}</h1>
  @if($lesson->description)
    <p class="mt-2 text-ink-600 dark:text-ink-300">{{ $lesson->description }}</p>
  @endif

  {{--
    Bunny Stream player.
    The embed URL is fetched server-side (lessons.embed) which applies purchase +
    sequential-order checks AND optionally signs the URL with an expiring token,
    so the raw Bunny credentials are never exposed to the browser.
    Bunny's own player prevents right-click download and has DRM options.

    The student's email floats over the video as a watermark so any screen recording
    can be traced back to its account. Fullscreen is handled on #player-wrap (not inside
    the iframe) so the watermark stays visible in fullscreen too.
  --}}
  <div id="player-wrap" class="group/player relative mt-6 w-full max-w-3xl bg-ink-950 rounded-xl overflow-hidden shadow-lg [&:fullscreen]:max-w-none [&:fullscreen]:rounded-none [&:fullscreen]:flex [&:fullscreen]:items-center [&:fullscreen]:justify-center">
    <div id="player-frame" class="relative w-full aspect-video group-[:fullscreen]/player:w-[min(100vw,177.78vh)]">
      <p id="player-status" class="absolute inset-0 flex items-center justify-center text-ink-400 text-sm px-4 text-center">
        {{ __('app.lesson_loading') }}
      </p>

      <div id="player-watermark" aria-hidden="true"
           class="pointer-events-none select-none absolute z-10 left-[10%] top-[12%] max-w-[80%] truncate text-xs sm:text-sm font-semibold text-white/35 [text-shadow:0_1px_2px_rgba(0,0,0,0.6)] transition-[left,top] duration-[2500ms] ease-in-out">
        {{ auth()->user()->email }}
      </div>
    </div>
  </div>

  <div class="mt-4 flex flex-wrap items-center gap-3">
    <button id="fullscreen-btn" type="button" hidden
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gold-500/30 text-sm font-semibold hover:bg-gold-50 dark:hover:bg-ink-900 transition">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>
      {{ __('app.lesson_fullscreen') }}
    </button>
  </div>

  <div class="mt-4">
    <button id="complete-btn" type="button"
            class="shine-sweep bg-gold-500 text-ink-950 font-semibold px-5 py-2 rounded-lg hover:bg-gold-400 transition">
      {{ __('app.lesson_mark_watching') }}
    </button>
  </div>

  {{-- Completion modal --}}
  <div id="completion-modal" hidden class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
    <div class="bg-white dark:bg-ink-900 border border-gold-500/20 rounded-xl shadow-2xl max-w-md w-full p-8 text-center">
      <span class="text-4xl">🎉</span>
      <h2 id="modal-title" class="mt-4 text-xl font-serif font-bold"></h2>
      <p id="modal-body" class="mt-2 text-ink-600 dark:text-ink-300"></p>
      <div class="mt-6 flex justify-center gap-3">
        <a id="modal-cta" href="{{ route('profile') }}" hidden class="shine-sweep bg-gold-500 text-ink-950 font-semibold px-5 py-2 rounded-lg hover:bg-gold-400 transition">
          {{ __('app.lesson_course_done_cta') }}
        </a>
        <button id="modal-close" type="button" class="px-5 py-2 rounded-lg border border-gold-500/20 font-semibold hover:bg-gold-50 dark:hover:bg-ink-950 transition">
          {{ __('app.lesson_modal_close') }}
        </button>
      </div>
    </div>
  </div>

  @include('partials.course-comments', ['course' => $course, 'comments' => $comments ?? collect()])

  @push('scripts')
    <script>
      (async function () {
        const statusEl  = document.getElementById('player-status');
        const frameWrap = document.getElementById('player-frame');

        try {
          const resp = await fetch("{{ route('lessons.embed', $lesson) }}", { credentials: 'same-origin' });

          if (! resp.ok) {
            const data = await resp.json().catch(() => ({}));
            const messages = {
              locked: @json(__('app.course_locked_notice')),
              unavailable: @json(__('app.lesson_video_unavailable')),
              'no video': @json(__('app.lesson_video_unavailable')),
            };
            statusEl.textContent = messages[data.message] ?? @json(__('app.lesson_forbidden'));
            return;
          }

          const { embed_url } = await resp.json();

          // Remove loading text and inject the Bunny iframe
          statusEl.remove();

          const playerWrap    = document.getElementById('player-wrap');
          const fullscreenBtn = document.getElementById('fullscreen-btn');
          const canFullscreenWrapper = document.fullscreenEnabled === true;

          const iframe = document.createElement('iframe');
          iframe.src              = embed_url;
          iframe.allow            = 'accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;';
          // Bunny checks the embedding domain (Allowed domains) from the Referer header
          iframe.referrerPolicy = 'strict-origin-when-cross-origin';
          // Keep the player's own fullscreen off where we can fullscreen the wrapper instead,
          // otherwise the iframe would cover the watermark. iPhones can't fullscreen a
          // wrapper element, so there the native player fullscreen is kept.
          iframe.allowFullscreen  = ! canFullscreenWrapper;
          // Bunny Stream already blocks right-click save and external embeds at CDN level
          iframe.style.cssText    = 'position:absolute;top:0;left:0;width:100%;height:100%;border:none;';

          // Prevent context menu on the iframe wrapper to discourage right-click tricks
          frameWrap.addEventListener('contextmenu', function (e) { e.preventDefault(); });

          frameWrap.prepend(iframe);

          if (canFullscreenWrapper) {
            fullscreenBtn.hidden = false;
            fullscreenBtn.addEventListener('click', function () {
              document.fullscreenElement ? document.exitFullscreen() : playerWrap.requestFullscreen();
            });
          }

          moveWatermark();
          setInterval(moveWatermark, 8000);
        } catch (e) {
          statusEl.textContent = "{{ __('app.lesson_forbidden') }}";
        }
      })();

      // Drift the watermark across the video so it can't be cropped or blurred out of a recording
      function moveWatermark() {
        const watermark = document.getElementById('player-watermark');
        watermark.style.left = (5 + Math.random() * 55) + '%';
        watermark.style.top  = (5 + Math.random() * 80) + '%';
      }

      // If this account signs in on another device, stop playback here and go to the login page
      setInterval(async function () {
        const resp = await fetch("{{ route('session.ping') }}", {
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin',
        }).catch(() => null);

        if (resp && resp.status === 401) {
          document.getElementById('player-frame').querySelector('iframe')?.remove();
          window.location.href = "{{ route('login') }}";
        }
      }, 60000);

      document.getElementById('complete-btn').addEventListener('click', async function () {
        const btn = this;
        btn.disabled = true;

        const resp = await fetch("{{ route('lessons.complete', $lesson) }}", {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
          credentials: 'same-origin',
        });

        const data = await resp.json().catch(() => ({}));
        if (! data.ok) { btn.disabled = false; return; }

        const modal = document.getElementById('completion-modal');
        const title = document.getElementById('modal-title');
        const body  = document.getElementById('modal-body');
        const cta   = document.getElementById('modal-cta');

        if (data.course_completed) {
          title.textContent = "{{ __('app.lesson_course_done_title') }}";
          body.textContent  = "{{ __('app.lesson_course_done_body') }}";
          cta.hidden        = false;
        } else {
          title.textContent = "{{ __('app.lesson_completed_title') }}";
          body.textContent  = '';
          cta.hidden        = true;
        }

        modal.hidden = false;
      });

      document.getElementById('modal-close').addEventListener('click', function () {
        window.location.reload();
      });
    </script>
  @endpush
@endsection

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
  --}}
  <div id="player-wrap" class="relative mt-6 w-full max-w-3xl bg-ink-950 rounded-xl overflow-hidden shadow-lg">
    <div id="player-frame" class="relative w-full" style="padding-top: 56.25%;">
      <p id="player-status" class="absolute inset-0 flex items-center justify-center text-ink-400 text-sm px-4 text-center">
        {{ __('app.lesson_loading') }}
      </p>
    </div>
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

          const iframe = document.createElement('iframe');
          iframe.src              = embed_url;
          iframe.allow            = 'accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;';
          iframe.allowFullscreen  = true;
          // Bunny Stream already blocks right-click save and external embeds at CDN level
          iframe.style.cssText    = 'position:absolute;top:0;left:0;width:100%;height:100%;border:none;';

          // Prevent context menu on the iframe wrapper to discourage right-click tricks
          frameWrap.addEventListener('contextmenu', function (e) { e.preventDefault(); });

          frameWrap.appendChild(iframe);
        } catch (e) {
          statusEl.textContent = "{{ __('app.lesson_forbidden') }}";
        }
      })();

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

@extends('layouts.portal')

@section('title', $lesson->title)

@section('content')
  <a href="{{ route('courses.show', $course) }}" class="text-sm text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">&larr; {{ __('app.lesson_back_to_course') }}</a>

  <h1 class="text-2xl font-serif font-bold mt-3">{{ $lesson->title }}</h1>
  @if($lesson->description)
    <p class="mt-2 text-ink-600 dark:text-ink-300">{{ $lesson->description }}</p>
  @endif

  <div class="mt-6 aspect-video w-full max-w-3xl bg-ink-950 rounded-xl overflow-hidden shadow-lg flex items-center justify-center">
    <p id="player-status" class="text-ink-400 text-sm">{{ __('app.lesson_loading') }}</p>
  </div>

  <div class="mt-4 flex items-center gap-3">
    <button id="play-pause-btn" type="button" hidden
            class="border border-gold-500/30 font-semibold px-4 py-2 rounded-lg hover:bg-gold-50 dark:hover:bg-ink-950 transition">
      {{ __('app.lesson_play') }}
    </button>
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

  @push('scripts')
    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
      // The YouTube IFrame API script loads asynchronously and may finish before or after
      // our own iframe is inserted, so both sides check in with this small gate.
      let ytApiReady = false;
      let playerIframeReady = false;
      window.onYouTubeIframeAPIReady = function () {
        ytApiReady = true;
        maybeInitPlayer();
      };

      let ytPlayer = null;
      function maybeInitPlayer() {
        if (! ytApiReady || ! playerIframeReady || ytPlayer) return;

        const btn = document.getElementById('play-pause-btn');
        ytPlayer = new YT.Player('lesson-player', {
          events: {
            onReady: function () { btn.hidden = false; },
            onStateChange: function (e) {
              btn.textContent = e.data === YT.PlayerState.PLAYING
                ? "{{ __('app.lesson_pause') }}"
                : "{{ __('app.lesson_play') }}";
            },
          },
        });

        btn.addEventListener('click', function () {
          const state = ytPlayer.getPlayerState();
          if (state === YT.PlayerState.PLAYING) {
            ytPlayer.pauseVideo();
          } else {
            ytPlayer.playVideo();
          }
        });
      }

      (async function () {
        const statusEl = document.getElementById('player-status');
        const container = statusEl.parentElement;

        try {
          const resp = await fetch("{{ route('lessons.embed', $lesson) }}", { credentials: 'same-origin' });

          if (! resp.ok) {
            const data = await resp.json().catch(() => ({}));
            statusEl.textContent = data.message === 'locked' ? "{{ __('app.course_locked_notice') }}" : "{{ __('app.lesson_forbidden') }}";
            return;
          }

          const { embed_url } = await resp.json();
          const iframe = document.createElement('iframe');
          iframe.id = 'lesson-player';
          iframe.className = 'w-full h-full';
          iframe.src = embed_url;
          iframe.setAttribute('frameborder', '0');
          iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
          iframe.setAttribute('allowfullscreen', '');
          container.innerHTML = '';
          container.appendChild(iframe);

          playerIframeReady = true;
          maybeInitPlayer();
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
        const body = document.getElementById('modal-body');
        const cta = document.getElementById('modal-cta');

        if (data.course_completed) {
          title.textContent = "{{ __('app.lesson_course_done_title') }}";
          body.textContent = "{{ __('app.lesson_course_done_body') }}";
          cta.hidden = false;
        } else {
          title.textContent = "{{ __('app.lesson_completed_title') }}";
          body.textContent = '';
          cta.hidden = true;
        }

        modal.hidden = false;
      });

      document.getElementById('modal-close').addEventListener('click', function () {
        window.location.reload();
      });
    </script>
  @endpush
@endsection

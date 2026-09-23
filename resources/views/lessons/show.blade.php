@extends('layouts.portal')

@section('title', $lesson->title)

@section('content')
  <a href="{{ route('courses.show', $course) }}" class="text-sm text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">&larr; {{ __('app.lesson_back_to_course') }}</a>

  <h1 class="text-2xl font-serif font-bold mt-3">{{ $lesson->title }}</h1>
  @if($lesson->description)
    <p class="mt-2 text-ink-600 dark:text-ink-300">{{ $lesson->description }}</p>
  @endif

  {{-- Custom player: the video itself is a hidden YouTube embed (controls=0, no native UI);
       everything below is our own chrome, driven through the IFrame Player API. --}}
  <div id="player-wrap" class="relative mt-6 aspect-video w-full max-w-3xl bg-ink-950 rounded-xl overflow-hidden shadow-lg group">
    <div id="player-frame" class="w-full h-full flex items-center justify-center">
      <p id="player-status" class="text-ink-400 text-sm px-4 text-center">{{ __('app.lesson_loading') }}</p>
    </div>

    <div id="player-controls" hidden
         class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 via-black/60 to-transparent px-4 pt-8 pb-3 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity duration-200">
      <div id="progress-track" class="relative h-1.5 rounded-full bg-white/25 cursor-pointer mb-3">
        <div id="progress-fill" class="absolute inset-y-0 left-0 w-0 rounded-full bg-gold-500"></div>
      </div>

      <div class="flex items-center gap-3 text-white">
        <button id="play-pause-btn" type="button" class="shrink-0 hover:text-gold-400 transition" aria-label="{{ __('app.lesson_play') }}">
          <svg id="icon-play" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
          <svg id="icon-pause" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" hidden><path d="M6 5h4v14H6zm8 0h4v14h-4z"/></svg>
        </button>

        <span id="time-display" class="text-xs font-mono tabular-nums text-white/90 shrink-0">0:00 / 0:00</span>

        <span class="flex-grow"></span>

        <button id="mute-btn" type="button" class="shrink-0 hover:text-gold-400 transition" aria-label="{{ __('app.lesson_mute') }}">
          <svg id="icon-volume" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M3 10v4h4l5 5V5L7 10H3z"/></svg>
          <svg id="icon-muted" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" hidden><path d="M3 10v4h4l5 5V5L7 10H3zm13.5 2L19 9.5 17.5 8 15 10.5 12.5 8 11 9.5l2.5 2.5L11 14.5 12.5 16l2.5-2.5 2.5 2.5 1.5-1.5-2.5-2.5z"/></svg>
        </button>

        <button id="fullscreen-btn" type="button" class="shrink-0 hover:text-gold-400 transition" aria-label="{{ __('app.lesson_fullscreen') }}">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4h4M16 4h4v4M20 16v4h-4M8 20H4v-4"/></svg>
        </button>
      </div>
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
    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
      // The YouTube IFrame API script loads asynchronously and may finish before or after
      // we know which video to play, so both sides check in with this small gate.
      let ytApiReady = false;
      let pendingVideoId = null;
      window.onYouTubeIframeAPIReady = function () {
        ytApiReady = true;
        maybeInitPlayer();
      };

      let ytPlayer = null;
      let progressTimer = null;

      function formatTime(seconds) {
        seconds = Math.max(0, Math.floor(seconds || 0));
        const m = Math.floor(seconds / 60);
        const s = String(seconds % 60).padStart(2, '0');
        return `${m}:${s}`;
      }

      function updateProgress() {
        if (! ytPlayer || typeof ytPlayer.getCurrentTime !== 'function') return;
        const current = ytPlayer.getCurrentTime();
        const duration = ytPlayer.getDuration();
        document.getElementById('time-display').textContent = `${formatTime(current)} / ${formatTime(duration)}`;
        const pct = duration > 0 ? (current / duration) * 100 : 0;
        document.getElementById('progress-fill').style.width = pct + '%';
      }

      function setPlayPauseIcon(isPlaying) {
        document.getElementById('icon-play').hidden = isPlaying;
        document.getElementById('icon-pause').hidden = ! isPlaying;
      }

      function maybeInitPlayer() {
        if (! ytApiReady || ! pendingVideoId || ytPlayer) return;

        const controls = document.getElementById('player-controls');

        // YT.Player() must build its own iframe from videoId/host — binding it to an
        // iframe we construct ourselves leaves the postMessage handshake broken and
        // onReady never fires. The placeholder div (id="lesson-player") gets replaced
        // in place by the iframe the API creates, keeping the same id.
        ytPlayer = new YT.Player('lesson-player', {
          videoId: pendingVideoId,
          playerVars: {
            rel: 0,
            modestbranding: 1,
            controls: 0,
            iv_load_policy: 3,
            disablekb: 1,
            playsinline: 1,
            origin: window.location.origin,
          },
          host: 'https://www.youtube-nocookie.com',
          events: {
            onReady: function () {
              const frame = document.getElementById('lesson-player');
              if (frame) frame.classList.add('pointer-events-none');
              controls.hidden = false;
              progressTimer = setInterval(updateProgress, 250);
            },
            onStateChange: function (e) {
              setPlayPauseIcon(e.data === YT.PlayerState.PLAYING);
            },
          },
        });

        document.getElementById('play-pause-btn').addEventListener('click', function () {
          const state = ytPlayer.getPlayerState();
          if (state === YT.PlayerState.PLAYING) {
            ytPlayer.pauseVideo();
          } else {
            ytPlayer.playVideo();
          }
        });

        const progressTrack = document.getElementById('progress-track');
        progressTrack.addEventListener('click', function (e) {
          const rect = progressTrack.getBoundingClientRect();
          const ratio = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
          const duration = ytPlayer.getDuration();
          if (duration > 0) ytPlayer.seekTo(duration * ratio, true);
        });

        const muteBtn = document.getElementById('mute-btn');
        muteBtn.addEventListener('click', function () {
          if (ytPlayer.isMuted()) {
            ytPlayer.unMute();
            document.getElementById('icon-volume').hidden = false;
            document.getElementById('icon-muted').hidden = true;
          } else {
            ytPlayer.mute();
            document.getElementById('icon-volume').hidden = true;
            document.getElementById('icon-muted').hidden = false;
          }
        });

        document.getElementById('fullscreen-btn').addEventListener('click', function () {
          const wrap = document.getElementById('player-wrap');
          if (wrap.requestFullscreen) wrap.requestFullscreen();
        });
      }

      (async function () {
        const statusEl = document.getElementById('player-status');
        const container = document.getElementById('player-frame');

        try {
          const resp = await fetch("{{ route('lessons.embed', $lesson) }}", { credentials: 'same-origin' });

          if (! resp.ok) {
            const data = await resp.json().catch(() => ({}));
            statusEl.textContent = data.message === 'locked' ? "{{ __('app.course_locked_notice') }}" : "{{ __('app.lesson_forbidden') }}";
            return;
          }

          const { video_id } = await resp.json();
          const placeholder = document.createElement('div');
          placeholder.id = 'lesson-player';
          placeholder.className = 'w-full h-full';
          container.innerHTML = '';
          container.appendChild(placeholder);

          pendingVideoId = video_id;
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

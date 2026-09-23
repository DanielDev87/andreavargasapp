@extends('layouts.admin')

@section('title', __('app.admin_attendance_title'))

@section('content')
  <div class="mb-6">
    <a href="{{ route('admin.courses.index') }}" class="text-sm text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">&larr; {{ __('app.admin_courses_title') }}</a>
    <h1 class="text-2xl font-bold mt-2">{{ __('app.admin_attendance_title') }} — {{ $course->title }}</h1>
    <p class="text-sm text-ink-500 dark:text-ink-400 mt-1">{{ __('app.admin_attendance_subtitle') }}</p>
  </div>

  {{-- Scan panel: camera QR reading or manual code entry, either one calls the same check-in endpoint --}}
  <div class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-6 shadow-md mb-8">
    <h2 class="font-bold text-gold-600 dark:text-gold-400">{{ __('app.admin_attendance_scan_title') }}</h2>

    <div id="scan-result" class="hidden mt-3 p-3 rounded-lg text-sm font-semibold"></div>

    <div class="mt-4 flex flex-col md:flex-row gap-6">
      <div class="md:w-64 shrink-0">
        <video id="scan-video" class="w-full aspect-square rounded-lg bg-ink-950 object-cover" muted playsinline hidden></video>
        <canvas id="scan-canvas" class="hidden"></canvas>
        <button id="scan-toggle-btn" type="button" class="mt-3 w-full shine-sweep bg-gold-500 text-ink-950 font-semibold px-4 py-2 rounded-lg hover:bg-gold-400 transition text-sm">
          {{ __('app.admin_attendance_scan_start') }}
        </button>
      </div>

      <div class="flex-grow">
        <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_attendance_scan_manual_label') }}</label>
        <form id="manual-code-form" class="flex gap-2">
          <input id="manual-code-input" type="text" maxlength="8" autocomplete="off"
                 class="flex-grow rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 uppercase tracking-widest font-mono"
                 placeholder="XXXXXXXX">
          <button type="submit" class="shrink-0 bg-gold-500 text-ink-950 font-semibold px-4 py-2 rounded-lg hover:bg-gold-400 transition text-sm">{{ __('app.admin_attendance_scan_manual_submit') }}</button>
        </form>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl shadow-md">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left border-b border-gold-500/10 text-ink-500 dark:text-ink-400 uppercase text-xs tracking-wide">
          <th class="px-5 py-3">{{ __('app.admin_attendance_table_name') }}</th>
          <th class="px-5 py-3">{{ __('app.admin_attendance_table_email') }}</th>
          <th class="px-5 py-3">{{ __('app.admin_attendance_table_code') }}</th>
          <th class="px-5 py-3">{{ __('app.admin_attendance_table_status') }}</th>
          <th class="px-5 py-3 text-right">{{ __('app.admin_courses_table_actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($purchases as $purchase)
          <tr class="border-b border-gold-500/5 last:border-0">
            <td class="px-5 py-4 font-medium">{{ $purchase->user->name }}</td>
            <td class="px-5 py-4 text-ink-500 dark:text-ink-400">{{ $purchase->user->email }}</td>
            <td class="px-5 py-4 font-mono tracking-widest">{{ $purchase->attendance_code }}</td>
            <td class="px-5 py-4">
              <span class="text-xs font-semibold uppercase tracking-wide px-2 py-0.5 rounded {{ $purchase->checked_in_at ? 'bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' }}">
                {{ $purchase->checked_in_at ? __('app.admin_attendance_status_present') : __('app.admin_attendance_status_pending') }}
              </span>
            </td>
            <td class="px-5 py-4 text-right">
              @unless($purchase->checked_in_at)
                <form method="POST" action="{{ route('admin.attendance.check-in', [$course, $purchase]) }}" class="inline">
                  @csrf
                  <button type="submit" class="text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.admin_attendance_checkin_button') }}</button>
                </form>
              @endunless
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-5 py-8 text-center text-ink-500 dark:text-ink-400">{{ __('app.admin_attendance_empty') }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
      (function () {
        const scanUrl = "{{ route('admin.attendance.scan', $course) }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const resultEl = document.getElementById('scan-result');

        function showResult(message, kind) {
          resultEl.textContent = message;
          resultEl.className = 'mt-3 p-3 rounded-lg text-sm font-semibold ' + (
            kind === 'ok' ? 'bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-300' :
            kind === 'warn' ? 'bg-gold-50 dark:bg-ink-950 text-gold-700 dark:text-gold-300' :
            'bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-300'
          );
        }

        async function submitCode(code) {
          if (! code) return;

          try {
            const resp = await fetch(scanUrl, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
              body: JSON.stringify({ code }),
            });
            const data = await resp.json();

            if (! resp.ok || ! data.ok) {
              showResult(data.message || "{{ __('app.admin_attendance_scan_notfound') }}", 'error');
              return;
            }

            if (data.already) {
              showResult("{{ __('app.admin_attendance_scan_already') }} " + data.name, 'warn');
            } else {
              showResult("{{ __('app.admin_attendance_scan_success') }} " + data.name, 'ok');
              setTimeout(() => window.location.reload(), 1200);
            }
          } catch (e) {
            showResult("{{ __('app.admin_attendance_scan_notfound') }}", 'error');
          }
        }

        // Manual code entry
        document.getElementById('manual-code-form').addEventListener('submit', function (e) {
          e.preventDefault();
          const input = document.getElementById('manual-code-input');
          submitCode(input.value.trim().toUpperCase());
          input.value = '';
        });

        // Camera QR scanning
        const video = document.getElementById('scan-video');
        const canvas = document.getElementById('scan-canvas');
        const ctx = canvas.getContext('2d');
        const toggleBtn = document.getElementById('scan-toggle-btn');
        let stream = null;
        let scanning = false;
        let lastCode = null;
        let lastCodeAt = 0;

        function stopCamera() {
          scanning = false;
          if (stream) {
            stream.getTracks().forEach(t => t.stop());
            stream = null;
          }
          video.hidden = true;
          toggleBtn.textContent = "{{ __('app.admin_attendance_scan_start') }}";
        }

        function tick() {
          if (! scanning) return;

          if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);

            if (code && code.data) {
              const now = Date.now();
              if (code.data !== lastCode || now - lastCodeAt > 4000) {
                lastCode = code.data;
                lastCodeAt = now;
                submitCode(code.data.trim().toUpperCase());
              }
            }
          }

          requestAnimationFrame(tick);
        }

        toggleBtn.addEventListener('click', async function () {
          if (scanning) {
            stopCamera();
            return;
          }

          try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = stream;
            video.hidden = false;
            await video.play();
            scanning = true;
            toggleBtn.textContent = "{{ __('app.admin_attendance_scan_stop') }}";
            requestAnimationFrame(tick);
          } catch (e) {
            showResult("{{ __('app.admin_attendance_scan_camera_error') }}", 'error');
          }
        });
      })();
    </script>
  @endpush
@endsection

@extends('layouts.admin')

@section('title', __('app.admin_content_title'))

@section('content')
  <div class="flex items-center justify-between mb-2">
    <div>
      <a href="{{ route('admin.courses.index') }}" class="text-sm text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">&larr; {{ __('app.admin_content_back') }}</a>
      <h1 class="text-2xl font-bold mt-1">{{ $course->title }}</h1>
    </div>
  </div>

  <div class="mt-8 space-y-6">
    @forelse($modules as $module)
      <div class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl shadow-md overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gold-500/10 bg-gold-50/50 dark:bg-ink-950/40">
          <h2 class="font-bold text-ink-900 dark:text-white">{{ $module->order }}. {{ $module->title }}</h2>

          <div class="flex items-center gap-3 text-sm">
            <details class="relative">
              <summary class="cursor-pointer list-none text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.admin_edit') }}</summary>
              <form method="POST" action="{{ route('admin.modules.update', [$course, $module]) }}" class="absolute right-0 z-10 mt-2 w-80 bg-white dark:bg-ink-900 border border-gold-500/20 rounded-xl p-4 shadow-xl space-y-3">
                @csrf @method('put')
                <input name="title" value="{{ $module->title }}" required
                       class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm">
                <textarea name="description" rows="2" class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm">{{ $module->description }}</textarea>
                <input name="order" type="number" value="{{ $module->order }}" required
                       class="w-24 rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm">
                <button type="submit" class="shine-sweep bg-gold-500 text-ink-950 font-semibold px-4 py-1.5 rounded-lg text-sm hover:bg-gold-400 transition">{{ __('app.admin_save') }}</button>
              </form>
            </details>

            <form method="POST" action="{{ route('admin.modules.destroy', [$course, $module]) }}" onsubmit="return confirm('{{ __('app.admin_module_delete_confirm') }}')">
              @csrf @method('delete')
              <button type="submit" class="text-red-600 dark:text-red-400 font-semibold hover:text-red-500">{{ __('app.admin_courses_delete') }}</button>
            </form>
          </div>
        </div>

        <div class="p-5 space-y-2">
          @forelse($module->lessons as $lesson)
            <details class="group border border-gold-500/10 rounded-lg">
              <summary class="cursor-pointer list-none flex items-center justify-between px-4 py-3">
                <span class="text-sm">
                  <span class="text-ink-400 dark:text-ink-500">{{ $lesson->order }}.</span>
                  {{ $lesson->title }}
                  @if($lesson->is_free)
                    <span class="ml-2 text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">{{ __('app.admin_lesson_free') }}</span>
                  @endif
                </span>
                <span class="text-gold-600 dark:text-gold-400 font-semibold text-sm">{{ __('app.admin_edit') }}</span>
              </summary>

              <form method="POST" action="{{ route('admin.lessons.update', [$course, $module, $lesson]) }}" class="px-4 pb-4 space-y-3">
                @csrf @method('put')
                <input name="title" value="{{ $lesson->title }}" required placeholder="{{ __('app.admin_lesson_title_placeholder') }}"
                       class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm">
                <textarea name="description" rows="2" class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm">{{ $lesson->description }}</textarea>

                <div>
                  <input name="video_id" value="{{ $lesson->video_id }}" required placeholder="{{ __('app.admin_lesson_video_id') }}"
                         class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm font-mono">
                  <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">{{ __('app.admin_lesson_video_id_hint') }}</p>
                </div>

                <div class="flex flex-wrap items-end gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 mb-1">{{ __('app.admin_field_order') }}</label>
                    <input name="order" type="number" value="{{ $lesson->order }}" required class="w-20 rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 mb-1">{{ __('app.admin_lesson_duration') }}</label>
                    <input name="duration_seconds" type="number" min="0" value="{{ $lesson->duration_seconds }}" class="w-28 rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm">
                  </div>
                  <label class="flex items-center gap-2 text-sm pb-2">
                    <input type="checkbox" name="is_free" value="1" @checked($lesson->is_free)>
                    {{ __('app.admin_lesson_free') }}
                  </label>
                </div>

                <div class="flex items-center gap-4">
                  <button type="submit" class="shine-sweep bg-gold-500 text-ink-950 font-semibold px-4 py-1.5 rounded-lg text-sm hover:bg-gold-400 transition">{{ __('app.admin_save') }}</button>
                </div>
              </form>

              <form method="POST" action="{{ route('admin.lessons.destroy', [$course, $module, $lesson]) }}" onsubmit="return confirm('{{ __('app.admin_lesson_delete_confirm') }}')" class="px-4 pb-4">
                @csrf @method('delete')
                <button type="submit" class="text-red-600 dark:text-red-400 font-semibold text-sm hover:text-red-500">{{ __('app.admin_courses_delete') }}</button>
              </form>
            </details>
          @empty
            <p class="text-sm text-ink-500 dark:text-ink-400">{{ __('app.admin_lesson_empty') }}</p>
          @endforelse

          <details class="mt-3">
            <summary class="cursor-pointer list-none text-gold-600 dark:text-gold-400 font-semibold text-sm hover:text-gold-500">+ {{ __('app.admin_lesson_add') }}</summary>
            <form method="POST" action="{{ route('admin.lessons.store', [$course, $module]) }}" class="mt-3 space-y-3 max-w-md">
              @csrf
              <input name="title" required placeholder="{{ __('app.admin_lesson_title_placeholder') }}"
                     class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm">
              <div>
                <input name="video_id" required placeholder="{{ __('app.admin_lesson_video_id') }}"
                       class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm font-mono">
                <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">{{ __('app.admin_lesson_video_id_hint') }}</p>
              </div>
              <input name="duration_seconds" type="number" min="0" placeholder="{{ __('app.admin_lesson_duration') }}"
                     class="w-40 rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm">
              <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_free" value="1">
                {{ __('app.admin_lesson_free') }}
              </label>
              <button type="submit" class="shine-sweep bg-gold-500 text-ink-950 font-semibold px-4 py-1.5 rounded-lg text-sm hover:bg-gold-400 transition">{{ __('app.admin_create') }}</button>
            </form>
          </details>
        </div>
      </div>
    @empty
      <p class="text-ink-500 dark:text-ink-400">{{ __('app.admin_module_empty') }}</p>
    @endforelse

    <details class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl shadow-md p-5">
      <summary class="cursor-pointer list-none text-gold-600 dark:text-gold-400 font-semibold">+ {{ __('app.admin_module_add') }}</summary>
      <form method="POST" action="{{ route('admin.modules.store', $course) }}" class="mt-4 space-y-3 max-w-md">
        @csrf
        <input name="title" required placeholder="{{ __('app.admin_module_add_placeholder') }}"
               class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm">
        <textarea name="description" rows="2" placeholder="{{ __('app.admin_field_description') }}"
                  class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm"></textarea>
        <button type="submit" class="shine-sweep bg-gold-500 text-ink-950 font-semibold px-4 py-1.5 rounded-lg text-sm hover:bg-gold-400 transition">{{ __('app.admin_create') }}</button>
      </form>
    </details>
  </div>
@endsection

@extends('layouts.admin')

@section('title', __('app.admin_courses_title'))

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">{{ __('app.admin_courses_title') }}</h1>
    <a href="{{ route('admin.courses.create') }}" class="shine-sweep bg-gold-500 text-ink-950 font-semibold px-5 py-2 rounded-lg hover:bg-gold-400 transition">{{ __('app.admin_courses_create') }}</a>
  </div>

  <div class="flex gap-1 mb-6 border-b border-gold-500/10">
    <a href="{{ route('admin.courses.index') }}"
       class="px-4 py-2 text-sm font-semibold border-b-2 {{ $view === 'active' ? 'border-gold-500 text-gold-600 dark:text-gold-400' : 'border-transparent text-ink-500 dark:text-ink-400 hover:text-gold-500' }}">
      {{ __('app.admin_courses_tab_active') }}
    </a>
    <a href="{{ route('admin.courses.index', ['view' => 'past']) }}"
       class="px-4 py-2 text-sm font-semibold border-b-2 {{ $view === 'past' ? 'border-gold-500 text-gold-600 dark:text-gold-400' : 'border-transparent text-ink-500 dark:text-ink-400 hover:text-gold-500' }}">
      {{ __('app.admin_courses_tab_past') }}
    </a>
  </div>

  <div class="overflow-x-auto bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl shadow-md">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left border-b border-gold-500/10 text-ink-500 dark:text-ink-400 uppercase text-xs tracking-wide">
          <th class="px-5 py-3">{{ __('app.admin_field_type') }}</th>
          <th class="px-5 py-3">{{ __('app.admin_courses_table_title') }}</th>
          <th class="px-5 py-3">{{ __('app.admin_courses_table_price') }}</th>
          <th class="px-5 py-3 text-right">{{ __('app.admin_courses_table_actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($courses as $course)
          <tr class="border-b border-gold-500/5 last:border-0">
            <td class="px-5 py-4">
              <span class="text-xs font-semibold uppercase tracking-wide px-2 py-1 rounded {{ $course->isPresencial() ? 'bg-gold-500/10 text-gold-600 dark:text-gold-400' : 'bg-ink-100 dark:bg-ink-800 text-ink-500 dark:text-ink-300' }}">
                {{ $course->isPresencial() ? __('app.admin_type_presencial') : __('app.admin_type_virtual') }}
              </span>
            </td>
            <td class="px-5 py-4 font-medium">
              {{ $course->title }}
              @if($view === 'past')
                <span class="ml-1 text-xs font-semibold uppercase tracking-wide px-2 py-0.5 rounded bg-ink-100 dark:bg-ink-800 text-ink-500 dark:text-ink-400">{{ __('app.admin_courses_past_badge') }}</span>
              @elseif($course->is_hidden)
                <span class="ml-1 text-xs font-semibold uppercase tracking-wide px-2 py-0.5 rounded bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20">{{ __('app.admin_courses_hidden_badge') }}</span>
              @endif
            </td>
            <td class="px-5 py-4">{{ number_format($course->price, 2) }}</td>
            <td class="px-5 py-4 text-right space-x-3 whitespace-nowrap">
              @if($course->isPresencial())
                <a href="{{ route('admin.attendance.index', $course) }}" class="text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.admin_courses_attendance') }}</a>
              @endif
              <a href="{{ route('admin.courses.content', $course) }}" class="text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.admin_courses_content') }}</a>
              <a href="{{ route('admin.courses.edit', $course) }}" class="text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.admin_courses_edit') }}</a>
              <form method="POST" action="{{ route('admin.courses.toggle-visibility', $course) }}" class="inline">
                @csrf
                <button type="submit" class="text-ink-600 dark:text-ink-300 font-semibold hover:text-gold-500">
                  {{ $course->is_hidden ? __('app.admin_courses_show') : __('app.admin_courses_hide') }}
                </button>
              </form>
              <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" class="inline" onsubmit="return confirm('{{ __('app.admin_courses_delete_confirm') }}')">
                @csrf @method('delete')
                <button type="submit" class="text-red-600 dark:text-red-400 font-semibold hover:text-red-500">{{ __('app.admin_courses_delete') }}</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="px-5 py-8 text-center text-ink-500 dark:text-ink-400">{{ $view === 'past' ? __('app.admin_courses_past_empty') : '—' }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-6">{{ $courses->links() }}</div>
@endsection

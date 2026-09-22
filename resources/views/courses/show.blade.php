@extends('layouts.portal')

@section('title', $course->title)

@section('content')
  <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-10">
    <div>
      <h1 class="text-3xl font-serif font-bold text-shine">{{ $course->title }}</h1>
      <p class="mt-3 text-ink-600 dark:text-ink-300 max-w-2xl">{{ $course->description }}</p>
    </div>

    @if(! $hasPurchase)
      <a href="{{ route('purchases.create', $course) }}" class="shrink-0 shine-sweep bg-gold-500 text-ink-950 font-bold px-6 py-3 rounded-lg hover:bg-gold-400 transition whitespace-nowrap text-center">
        {{ __('app.checkout_title') }} — ${{ number_format($course->price, 0, ',', '.') }}
      </a>
    @endif
  </div>

  <div class="space-y-5">
    @forelse($modules as $module)
      <div class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl shadow-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gold-500/10 bg-gold-50/50 dark:bg-ink-950/40">
          <h2 class="font-bold text-ink-900 dark:text-white">{{ $module->order }}. {{ $module->title }}</h2>
          @if($module->description)
            <p class="text-sm text-ink-500 dark:text-ink-400 mt-1">{{ $module->description }}</p>
          @endif
        </div>

        <ul class="divide-y divide-gold-500/5">
          @php $ordered = $course->orderedLessons()->get(); @endphp
          @foreach($module->lessons as $lesson)
            @php
              $index = $ordered->search(fn($l) => $l->id === $lesson->id);
              $previous = $ordered->slice(0, $index);
              $unlocked = $hasPurchase && collect($previous)->every(fn($pl) => in_array($pl->id, $progress));
              $isDone = in_array($lesson->id, $progress);
            @endphp
            <li class="px-5 py-3 flex items-center justify-between">
              @if($unlocked)
                <a href="{{ route('lessons.show', [$course, $module, $lesson]) }}" class="flex items-center gap-3 hover:text-gold-600 dark:hover:text-gold-400 transition">
                  <span class="text-xs w-5 h-5 flex items-center justify-center rounded-full {{ $isDone ? 'bg-emerald-500 text-white' : 'bg-gold-500/20 text-gold-700 dark:text-gold-300' }}">
                    {{ $isDone ? '✓' : $lesson->order }}
                  </span>
                  {{ $lesson->title }}
                </a>
              @else
                <span class="flex items-center gap-3 text-ink-400 dark:text-ink-500">
                  <span class="text-xs w-5 h-5 flex items-center justify-center rounded-full bg-ink-100 dark:bg-ink-800">🔒</span>
                  {{ $lesson->title }}
                </span>
              @endif

              @if($lesson->duration_seconds)
                <span class="text-xs text-ink-400 dark:text-ink-500">{{ gmdate('i:s', $lesson->duration_seconds) }}</span>
              @endif
            </li>
          @endforeach
        </ul>
      </div>
    @empty
      <p class="text-ink-500 dark:text-ink-400">{{ __('app.course_no_modules') }}</p>
    @endforelse
  </div>

  @if($modules->isNotEmpty() && ! $hasPurchase)
    <p class="mt-6 text-sm text-ink-500 dark:text-ink-400">{{ __('app.course_locked_notice') }}</p>
  @endif
@endsection

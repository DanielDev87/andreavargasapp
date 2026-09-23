@extends('layouts.admin')

@section('title', __('app.admin_comments_title'))

@section('content')
  <h1 class="text-2xl font-bold mb-1">{{ __('app.admin_comments_title') }}</h1>
  <p class="text-sm text-ink-500 dark:text-ink-400 mb-6">{{ __('app.admin_comments_subtitle') }}</p>

  <div class="space-y-6">
    @forelse($comments as $comment)
      @php $answered = $comment->admin_replies_count > 0; @endphp
      <div class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <span class="font-semibold text-sm">{{ $comment->user->name }}</span>
            <span class="text-xs text-ink-400 dark:text-ink-500">{{ $comment->created_at->diffForHumans() }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wide px-2 py-0.5 rounded {{ $answered ? 'bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' }}">
              {{ $answered ? __('app.admin_comments_answered_badge') : __('app.admin_comments_pending_badge') }}
            </span>
            <a href="{{ route('courses.show', $comment->course) }}" class="text-xs font-semibold text-gold-600 dark:text-gold-400 hover:text-gold-500">{{ $comment->course->title }} &rarr;</a>
          </div>
        </div>
        <p class="mt-2 text-ink-700 dark:text-ink-200 whitespace-pre-line">{{ $comment->body }}</p>

        @if($comment->replies->isNotEmpty())
          <div class="mt-4 ml-6 space-y-4 border-l-2 border-gold-500/15 pl-4">
            @foreach($comment->replies as $reply)
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-semibold text-sm">{{ $reply->user->name }}</span>
                  @if($reply->user->is_admin)
                    <span class="text-xs font-semibold uppercase tracking-wide bg-gold-500/10 text-gold-600 dark:text-gold-400 border border-gold-500/20 px-2 py-0.5 rounded">{{ __('app.comments_instructor_badge') }}</span>
                  @endif
                  <span class="text-xs text-ink-400 dark:text-ink-500">{{ $reply->created_at->diffForHumans() }}</span>
                </div>
                <p class="mt-1 text-ink-700 dark:text-ink-200 whitespace-pre-line">{{ $reply->body }}</p>
              </div>
            @endforeach
          </div>
        @endif

        <form method="POST" action="{{ route('admin.comments.reply', $comment) }}" class="mt-4 flex gap-2">
          @csrf
          <textarea name="body" rows="2" required placeholder="{{ __('app.admin_comments_reply_placeholder') }}"
                    class="flex-grow rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm"></textarea>
          <button type="submit" class="shrink-0 shine-sweep bg-gold-500 text-ink-950 font-semibold px-4 py-2 rounded-lg text-sm hover:bg-gold-400 transition">{{ __('app.admin_comments_reply_submit') }}</button>
        </form>
      </div>
    @empty
      <p class="text-ink-500 dark:text-ink-400 text-sm">{{ __('app.admin_comments_empty') }}</p>
    @endforelse
  </div>

  <div class="mt-6">{{ $comments->links() }}</div>
@endsection

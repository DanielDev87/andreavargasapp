{{-- Expects: $course, $comments (top-level Comment models with `user` and `replies.user` eager loaded) --}}
<section class="mt-10">
  <h2 class="text-xl font-serif font-bold">{{ __('app.comments_title') }}</h2>
  <p class="text-sm text-ink-500 dark:text-ink-400 mt-1">{{ __('app.comments_subtitle') }}</p>

  <div class="mt-6 space-y-6">
    @forelse($comments as $comment)
      <div class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-5 shadow-sm">
        <div class="flex items-center gap-2">
          <span class="font-semibold text-sm">{{ $comment->user->name }}</span>
          @if($comment->user->is_admin)
            <span class="text-xs font-semibold uppercase tracking-wide bg-gold-500/10 text-gold-600 dark:text-gold-400 border border-gold-500/20 px-2 py-0.5 rounded">{{ __('app.comments_instructor_badge') }}</span>
          @endif
          <span class="text-xs text-ink-400 dark:text-ink-500">{{ $comment->created_at->diffForHumans() }}</span>
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

        <details class="mt-3">
          <summary class="cursor-pointer list-none text-sm font-semibold text-gold-600 dark:text-gold-400 hover:text-gold-500">{{ __('app.comments_reply') }}</summary>
          <form method="POST" action="{{ route('comments.store', $course) }}" class="mt-3 flex gap-2">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
            <textarea name="body" rows="2" required placeholder="{{ __('app.comments_reply_placeholder') }}"
                      class="flex-grow rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm"></textarea>
            <button type="submit" class="shrink-0 shine-sweep bg-gold-500 text-ink-950 font-semibold px-4 py-2 rounded-lg text-sm hover:bg-gold-400 transition">{{ __('app.comments_submit') }}</button>
          </form>
        </details>
      </div>
    @empty
      <p class="text-ink-500 dark:text-ink-400 text-sm">{{ __('app.comments_empty') }}</p>
    @endforelse
  </div>

  <form method="POST" action="{{ route('comments.store', $course) }}" class="mt-6">
    @csrf
    <textarea name="body" rows="3" required placeholder="{{ __('app.comments_placeholder') }}"
              class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 text-sm"></textarea>
    <button type="submit" class="mt-2 shine-sweep bg-gold-500 text-ink-950 font-semibold px-5 py-2 rounded-lg text-sm hover:bg-gold-400 transition">{{ __('app.comments_submit') }}</button>
  </form>
</section>

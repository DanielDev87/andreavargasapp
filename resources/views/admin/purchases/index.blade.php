@extends('layouts.admin')

@section('title', __('app.admin_purchases_title'))

@section('content')
  <h1 class="text-2xl font-bold mb-6">{{ __('app.admin_purchases_title') }}</h1>

  <div class="overflow-x-auto bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl shadow-md">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left border-b border-gold-500/10 text-ink-500 dark:text-ink-400 uppercase text-xs tracking-wide">
          <th class="px-5 py-3">{{ __('app.admin_purchases_table_user') }}</th>
          <th class="px-5 py-3">{{ __('app.admin_purchases_table_course') }}</th>
          <th class="px-5 py-3">{{ __('app.admin_purchases_table_method') }}</th>
          <th class="px-5 py-3">{{ __('app.admin_purchases_table_status') }}</th>
          <th class="px-5 py-3 text-right">{{ __('app.admin_purchases_table_actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($purchases as $p)
          <tr class="border-b border-gold-500/5 last:border-0">
            <td class="px-5 py-4 font-medium">{{ $p->user->name }}</td>
            <td class="px-5 py-4">{{ optional($p->course)->title ?? '—' }}</td>
            <td class="px-5 py-4">{{ $p->payment_method ? __('app.admin_purchases_method_'.$p->payment_method) : '—' }}</td>
            <td class="px-5 py-4"><span class="text-xs font-semibold uppercase tracking-wide">{{ __('app.profile_status_'.$p->status) }}</span></td>
            <td class="px-5 py-4 text-right">
              @if($p->status !== 'completed')
                <form method="POST" action="{{ route('admin.purchases.approve', $p) }}">
                  @csrf
                  <button type="submit" class="text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.admin_purchases_approve') }}</button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-5 py-8 text-center text-ink-500 dark:text-ink-400">{{ __('app.admin_purchases_empty') }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-6">{{ $purchases->links() }}</div>
@endsection

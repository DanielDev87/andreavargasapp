@extends('layouts.admin')

@section('title', __('app.admin_validations_title'))

@section('content')
  <h1 class="text-2xl font-bold mb-6">{{ __('app.admin_validations_title') }}</h1>

  <div class="overflow-x-auto bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl shadow-md">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left border-b border-gold-500/10 text-ink-500 dark:text-ink-400 uppercase text-xs tracking-wide">
          <th class="px-5 py-3">{{ __('app.admin_validations_table_user') }}</th>
          <th class="px-5 py-3">{{ __('app.admin_validations_table_course') }}</th>
          <th class="px-5 py-3">{{ __('app.admin_validations_table_status') }}</th>
          <th class="px-5 py-3 text-right">{{ __('app.admin_validations_table_actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($requests as $r)
          <tr class="border-b border-gold-500/5 last:border-0">
            <td class="px-5 py-4 font-medium">{{ $r->user->name }}</td>
            <td class="px-5 py-4">{{ optional($r->course)->title ?? '—' }}</td>
            <td class="px-5 py-4">
              <span class="text-xs font-semibold uppercase tracking-wide">{{ __('app.admin_validation_status_'.$r->status) }}</span>
            </td>
            <td class="px-5 py-4 text-right">
              <a href="{{ route('admin.validations.show', $r) }}" class="text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.admin_validations_view') }}</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="px-5 py-8 text-center text-ink-500 dark:text-ink-400">{{ __('app.admin_validations_empty') }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-6">{{ $requests->links() }}</div>
@endsection

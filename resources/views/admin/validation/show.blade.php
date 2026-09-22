@extends('layouts.admin')

@section('title', __('app.admin_validation_detail_title'))

@section('content')
  <a href="{{ route('admin.validations.index') }}" class="text-sm text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">&larr; {{ __('app.admin_validation_back') }}</a>
  <h1 class="text-2xl font-bold mt-2 mb-6">{{ __('app.admin_validation_detail_title') }} #{{ $request->id }}</h1>

  <div class="grid gap-6 lg:grid-cols-2">
    <div class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-6 shadow-md space-y-3">
      <p><span class="font-semibold text-ink-500 dark:text-ink-400">{{ __('app.admin_validation_user') }}:</span> {{ $request->user->name }}</p>
      <p><span class="font-semibold text-ink-500 dark:text-ink-400">{{ __('app.admin_validation_email') }}:</span> {{ $request->user->email }}</p>
      <p><span class="font-semibold text-ink-500 dark:text-ink-400">{{ __('app.admin_validation_status') }}:</span> {{ __('app.admin_validation_status_'.$request->status) }}</p>

      @if($request->ai_score !== null)
        <p><span class="font-semibold text-ink-500 dark:text-ink-400">{{ __('app.admin_validation_ai_score') }}:</span> {{ number_format($request->ai_score, 2) }}</p>
      @endif

      <div class="flex gap-4 pt-2">
        <a href="{{ asset('storage/'.$request->diploma_path) }}" target="_blank" class="text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.admin_validation_diploma') }}: {{ __('app.admin_validation_view_doc') }} &rarr;</a>
      </div>
      <div class="flex gap-4">
        <a href="{{ asset('storage/'.$request->id_document_path) }}" target="_blank" class="text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.admin_validation_id_doc') }}: {{ __('app.admin_validation_view_doc') }} &rarr;</a>
      </div>

      @if(in_array($request->status, ['pending', 'processing']))
        <form method="POST" action="{{ route('admin.validations.review', $request) }}" class="flex gap-3 pt-4">
          @csrf
          <button name="action" value="approve" class="bg-emerald-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-emerald-400 transition">{{ __('app.admin_validation_approve') }}</button>
          <button name="action" value="reject" class="bg-red-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-red-400 transition">{{ __('app.admin_validation_reject') }}</button>
        </form>
      @endif
    </div>

    @if($request->ai_report)
      <div class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-6 shadow-md">
        <h2 class="font-semibold text-ink-500 dark:text-ink-400 mb-3">{{ __('app.admin_validation_ai_report') }}</h2>
        <pre class="text-xs whitespace-pre-wrap break-words text-ink-700 dark:text-ink-200">{{ json_encode($request->ai_report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
      </div>
    @endif
  </div>
@endsection

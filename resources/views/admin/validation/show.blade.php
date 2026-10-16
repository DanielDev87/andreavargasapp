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

      <p><span class="font-semibold text-ink-500 dark:text-ink-400">{{ __('app.admin_validation_method') }}:</span> {{ __('app.validation_method_'.$request->review_method) }}</p>
      <p><span class="font-semibold text-ink-500 dark:text-ink-400">{{ __('app.admin_validation_consent') }}:</span>
        @if($request->data_consent_at)
          {{ $request->data_consent_at->format('Y-m-d H:i') }} ({{ __('app.data_policy_version', ['version' => $request->data_policy_version]) }})
        @else
          {{ __('app.admin_validation_consent_missing') }}
        @endif
      </p>

      @if($request->ai_score !== null)
        <p><span class="font-semibold text-ink-500 dark:text-ink-400">{{ __('app.admin_validation_ai_score') }}:</span> {{ number_format($request->ai_score, 2) }}</p>
      @endif

      <div class="flex gap-4 pt-2">
        <a href="{{ route('admin.validations.document', [$request, 'diploma']) }}" target="_blank" class="text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.admin_validation_diploma') }}: {{ __('app.admin_validation_view_doc') }} &rarr;</a>
      </div>
      <div class="flex gap-4">
        <a href="{{ route('admin.validations.document', [$request, 'id']) }}" target="_blank" class="text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">{{ __('app.admin_validation_id_doc') }}: {{ __('app.admin_validation_view_doc') }} &rarr;</a>
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
      @php $report = $request->ai_report; @endphp
      <div class="bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-6 shadow-md">
        <h2 class="font-semibold text-ink-500 dark:text-ink-400 mb-3">{{ __('app.admin_validation_ai_report') }}</h2>

        @if(isset($report['error']))
          <p class="text-sm text-red-600 dark:text-red-400">{{ __('app.admin_validation_ai_error_'.$report['error']) }}</p>
        @elseif(isset($report['recommendation']))
          <p class="text-sm font-semibold {{ $report['recommendation'] === 'approve' ? 'text-emerald-600 dark:text-emerald-400' : 'text-gold-700 dark:text-gold-400' }}">
            {{ __('app.admin_validation_ai_recommendation_'.$report['recommendation']) }}
          </p>
          <p class="mt-2 text-sm text-ink-700 dark:text-ink-200">{{ $report['observations'] }}</p>

          <ul class="mt-4 space-y-1.5 text-sm">
            @foreach([
              'is_dental_degree' => true,
              'is_identity_document' => true,
              'documents_legible' => true,
              'holder_names_match' => true,
              'matches_account_name' => true,
              'tampering_signs' => false,
            ] as $check => $expected)
              @php $passed = ($report[$check] ?? null) === $expected; @endphp
              <li class="flex items-center gap-2">
                <span class="font-bold {{ $passed ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}" aria-hidden="true">{{ $passed ? '✓' : '✗' }}</span>
                <span>{{ __('app.admin_validation_check_'.$check) }}<span class="sr-only">: {{ $passed ? __('app.admin_validation_check_ok') : __('app.admin_validation_check_failed') }}</span></span>
              </li>
            @endforeach
          </ul>

          <dl class="mt-4 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
            @foreach(['degree_holder_name', 'id_holder_name', 'id_document_type', 'id_document_number', 'degree_title', 'institution', 'graduation_date', 'issuing_country'] as $field)
              <dt class="text-ink-500 dark:text-ink-400">{{ __('app.admin_validation_field_'.$field) }}</dt>
              <dd class="font-medium">{{ ($report[$field] ?? '') !== '' ? $report[$field] : '—' }}</dd>
            @endforeach
          </dl>

          @if(($report['id_document_number'] ?? '') !== '')
            <a href="{{ config('services.rethus_lookup_url') }}" target="_blank" rel="noopener"
               class="inline-block mt-4 text-sm text-gold-600 dark:text-gold-400 font-semibold hover:text-gold-500">
              {{ __('app.admin_validation_rethus', ['number' => $report['id_document_number']]) }} &rarr;
            </a>
          @endif

          <p class="mt-4 text-xs text-ink-400">{{ __('app.admin_validation_ai_disclaimer') }}</p>
        @else
          <pre class="text-xs whitespace-pre-wrap break-words text-ink-700 dark:text-ink-200">{{ json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        @endif
      </div>
    @endif
  </div>
@endsection

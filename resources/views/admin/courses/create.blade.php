@extends('layouts.admin')

@section('title', __('app.admin_course_create_title'))

@section('content')
  <h1 class="text-2xl font-bold mb-6">{{ __('app.admin_course_create_title') }}</h1>

  <form method="POST" action="{{ route('admin.courses.store') }}" class="max-w-xl bg-white dark:bg-ink-900 border border-gold-500/15 rounded-xl p-6 shadow-md space-y-5">
    @csrf

    <div>
      <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_type') }}</label>
      <select name="type" id="course-type" class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2">
        <option value="virtual" {{ old('type', 'virtual') === 'virtual' ? 'selected' : '' }}>{{ __('app.admin_type_virtual') }}</option>
        <option value="presencial" {{ old('type') === 'presencial' ? 'selected' : '' }}>{{ __('app.admin_type_presencial') }}</option>
      </select>
    </div>

    <div>
      <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_title') }}</label>
      <input name="title" value="{{ old('title') }}" required
             class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gold-500/40">
    </div>

    <div>
      <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_description') }}</label>
      <textarea name="description" rows="4"
                class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gold-500/40">{{ old('description') }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_category') }}</label>
        <input name="category" value="{{ old('category') }}"
               class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_rating') }}</label>
        <input name="rating" value="{{ old('rating') }}" type="number" step="0.1" min="0" max="5"
               class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2">
      </div>
    </div>

    <div>
      <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_price') }}</label>
      <input name="price" value="{{ old('price') }}" type="number" step="0.01" required
             class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gold-500/40">
    </div>

    {{-- Presencial-only fields --}}
    <div data-field-group="presencial" class="space-y-4 border-t border-gold-500/10 pt-5">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_event_date') }}</label>
          <input name="event_date" value="{{ old('event_date') }}" type="date"
                 class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_location') }}</label>
          <input name="location" value="{{ old('location') }}"
                 class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2">
        </div>
      </div>
      <div class="grid grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_capacity') }}</label>
          <input name="capacity" value="{{ old('capacity') }}" type="number" min="0"
                 class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_seats_taken') }}</label>
          <input name="seats_taken" value="{{ old('seats_taken', 0) }}" type="number" min="0"
                 class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_deposit') }}</label>
          <input name="deposit_amount" value="{{ old('deposit_amount') }}" type="number" step="0.01" min="0"
                 class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2">
        </div>
      </div>
    </div>

    {{-- Virtual-only fields --}}
    <div data-field-group="virtual" class="grid grid-cols-2 gap-4 border-t border-gold-500/10 pt-5">
      <div>
        <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_duration_hours') }}</label>
        <input name="duration_hours" value="{{ old('duration_hours') }}" type="number" min="0"
               class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-semibold text-ink-600 dark:text-ink-300 mb-1">{{ __('app.admin_field_level') }}</label>
        <input name="level" value="{{ old('level') }}"
               class="w-full rounded-lg border border-gold-500/20 bg-white dark:bg-ink-950 px-3 py-2">
      </div>
    </div>

    <button type="submit" class="shine-sweep bg-gold-500 text-ink-950 font-semibold px-5 py-2 rounded-lg hover:bg-gold-400 transition">{{ __('app.admin_create') }}</button>
  </form>

  @push('scripts')
    <script>
      const typeSelect = document.getElementById('course-type');
      function toggleCourseFields() {
        const isPresencial = typeSelect.value === 'presencial';
        document.querySelectorAll('[data-field-group="presencial"]').forEach(el => el.hidden = ! isPresencial);
        document.querySelectorAll('[data-field-group="virtual"]').forEach(el => el.hidden = isPresencial);
      }
      typeSelect.addEventListener('change', toggleCourseFields);
      toggleCourseFields();
    </script>
  @endpush
@endsection

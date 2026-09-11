{{-- Filters for the activity list.

     A plain GET form, so the current filter is in the URL and can be bookmarked,
     shared and reloaded. Filtering happens in SQL rather than in the DataTable
     because the date range asks about a related table -- "has a session in this
     window" -- which the rendered rows cannot answer.

     Applies to the Active list; the Archive tab is deliberately left whole. --}}

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.wellness.activities.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-sm-6 col-lg-3">
                    <label for="f_type" class="form-label small mb-1">
                        <i class="ri-price-tag-3-line me-1"></i>{{ __('Activity Type') }}
                    </label>
                    <select name="type" id="f_type" class="form-select form-select-sm">
                        <option value="">{{ __('All Types') }}</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected((string) $filters['type'] === (string) $type->id)>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <label for="f_method" class="form-label small mb-1">
                        <i class="ri-user-follow-line me-1"></i>{{ __('Registration Method') }}
                    </label>
                    <select name="method" id="f_method" class="form-select form-select-sm">
                        <option value="">{{ __('All Methods') }}</option>
                        @foreach ($methods as $value => $label)
                            <option value="{{ $value }}" @selected($filters['method'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-lg-2">
                    <label for="f_from" class="form-label small mb-1">
                        <i class="ri-calendar-line me-1"></i>{{ __('Session From') }}
                    </label>
                    <input type="date" name="from" id="f_from" class="form-control form-control-sm"
                        value="{{ $filters['from'] }}" max="{{ $filters['to'] }}">
                </div>

                <div class="col-sm-6 col-lg-2">
                    <label for="f_to" class="form-label small mb-1">
                        <i class="ri-calendar-check-line me-1"></i>{{ __('Session To') }}
                    </label>
                    <input type="date" name="to" id="f_to" class="form-control form-control-sm"
                        value="{{ $filters['to'] }}" min="{{ $filters['from'] }}">
                </div>

                <div class="col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                        <i class="ri-filter-3-line me-1"></i>{{ __('Apply') }}
                    </button>
                    @if ($filtersActive)
                        <a href="{{ route('admin.wellness.activities.index') }}"
                            class="btn btn-sm btn-light" title="{{ __('Reset') }}">
                            <i class="ri-close-line"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        @if ($filtersActive)
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-2 border-top">
                <span class="text-muted small">{{ __('Filtering by') }}:</span>

                @if ($filters['type'])
                    <span class="badge bg-primary-subtle text-primary">
                        {{ __('Activity Type') }}: {{ $types->firstWhere('id', (int) $filters['type'])?->name ?? $filters['type'] }}
                    </span>
                @endif

                @if ($filters['method'])
                    <span class="badge bg-primary-subtle text-primary">
                        {{ __('Registration Method') }}: {{ $methods[$filters['method']] ?? $filters['method'] }}
                    </span>
                @endif

                @if ($filters['from'] || $filters['to'])
                    <span class="badge bg-primary-subtle text-primary">
                        {{ __('Session date') }}:
                        {{ $filters['from'] ?: __('any') }} &rarr; {{ $filters['to'] ?: __('any') }}
                    </span>
                @endif

                <span class="text-muted small ms-auto">
                    {{ __(':count activity(s) matched', ['count' => $activities->count()]) }}
                </span>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var from = document.getElementById('f_from');
        var to = document.getElementById('f_to');

        if (!from || !to) {
            return;
        }

        // Keep the pair bounded as the admin picks: the "to" picker cannot go
        // before "from", and vice versa. Both are "YYYY-MM-DD", where string
        // order is chronological order, so they compare directly.
        function sync() {
            to.min = from.value || '';
            from.max = to.value || '';

            to.setCustomValidity(
                from.value && to.value && to.value < from.value
                    ? @json(__('Session To cannot be earlier than Session From.'))
                    : ''
            );
        }

        from.addEventListener('change', sync);
        to.addEventListener('change', sync);
        sync();
    });
</script>
@endpush

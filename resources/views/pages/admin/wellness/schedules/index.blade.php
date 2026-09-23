@extends('layouts_.vertical', ['page_title' => __('Wellness Schedules')])

@section('css')
    @include('layouts_.shared.admin-datatable-css')
    @include('pages.admin.wellness.partials.page-css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="page-title mb-0">{{ $activity->name }}</h4>
            <small class="text-muted">
                {{ $activity->type?->name ?? __('No type') }} &middot;
                <span class="badge {{ $activity->status->badgeClass() }}">{{ $activity->status->label() }}</span>
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('wellness.activities.edit', $activity->encrypted_id) }}" class="btn btn-outline-warning">
                <i class="ri-edit-box-line me-1"></i> {{ __('Edit Activity') }}
            </a>
            <a href="{{ route('admin.wellness.activities.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> {{ __('Back') }}
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                <i class="ri-add-line me-1"></i> {{ __('Add Schedule') }}
            </button>
        </div>
    </div>

    @include('pages.admin.wellness.partials.errors')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm nowrap w-100 align-middle js-datatable">
                            <thead class="table-light">
                                <tr>
                                    <th class="no-sort">{{ __('No') }}</th>
                                    <th>{{ __('Session') }}</th>
                                    <th>{{ __('Location') }}</th>
                                    <th>{{ __('Registration Window') }}</th>
                                    <th>{{ __('Seats') }}</th>
                                    <th>{{ __('Queue') }}</th>
                                    <th>{{ __('Attended') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="no-sort">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                    @php($series = $seriesPositions[$schedule->id] ?? null)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        {{-- data-order keeps the column sorting chronologically; the
                                             rendered text starts with a weekday, which would otherwise
                                             sort Fri before Mon. --}}
                                        <td data-order="{{ $schedule->start_at->timestamp }}">
                                            <div class="fw-semibold">{{ $schedule->start_at->translatedFormat('D, d M Y') }}</div>
                                            <small class="text-muted">
                                                {{ $schedule->start_at->format('H:i') }} &ndash; {{ $schedule->end_at->format('H:i') }}
                                            </small>
                                            @if ($series)
                                                <div>
                                                    <span class="badge bg-secondary-subtle text-secondary" title="{{ __('Repeats :frequency until :until', [
                                                        'frequency' => strtolower(__($schedule->recurrence_frequency->label())),
                                                        'until' => $schedule->recurrence_until?->translatedFormat('D, d M Y'),
                                                    ]) }}">
                                                        <i class="ri-repeat-line me-1"></i>{{ $series['position'] }}/{{ $series['total'] }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $schedule->location ?: '-' }}</td>
                                        {{-- Sorted on the opening moment; a schedule with no opening
                                             bound is open from the start, so it sorts first. --}}
                                        <td data-order="{{ $schedule->registration_start_at?->timestamp ?? 0 }}">
                                            @if ($schedule->registration_start_at || $schedule->registration_end_at)
                                                <small>{{ $schedule->registration_start_at?->translatedFormat('D, d M H:i') ?? __('anytime') }}</small>
                                                <small class="d-block text-muted">
                                                    &rarr; {{ $schedule->registration_end_at?->translatedFormat('D, d M H:i') ?? __('session end') }}
                                                </small>
                                            @else
                                                <small class="text-muted">{{ __('Always open') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($schedule->quota === null)
                                                <span class="badge bg-info-subtle text-info">{{ $schedule->taken_seats }} / {{ __('unlimited') }}</span>
                                            @else
                                                <span class="badge {{ $schedule->taken_seats >= $schedule->quota ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                                    {{ $schedule->taken_seats }} / {{ $schedule->quota }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $schedule->queued_seats }}</td>
                                        <td>{{ $schedule->attended_seats }}</td>
                                        <td><span class="badge {{ $schedule->status->badgeClass() }}">{{ $schedule->status->label() }}</span></td>
                                        <td>
                                            <a href="{{ route('admin.wellness.registrations.index', $schedule->encrypted_id) }}"
                                                class="btn btn-outline-primary btn-sm" title="{{ __('Participants') }}">
                                                <i class="ri-group-line"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-warning btn-sm js-edit-schedule"
                                                data-url="{{ route('wellness.schedules.update', $schedule->encrypted_id) }}"
                                                data-start="{{ $schedule->start_at->format('Y-m-d\TH:i') }}"
                                                data-end="{{ $schedule->end_at->format('Y-m-d\TH:i') }}"
                                                data-location="{{ $schedule->location }}"
                                                data-quota="{{ $schedule->quota }}"
                                                data-regstart="{{ $schedule->registration_start_at?->format('Y-m-d\TH:i') }}"
                                                data-regend="{{ $schedule->registration_end_at?->format('Y-m-d\TH:i') }}"
                                                data-status="{{ $schedule->status->value }}"
                                                data-following="{{ $series ? $series['total'] - $series['position'] : 0 }}"
                                                data-label="{{ $schedule->start_at->translatedFormat('D, d M Y H:i') }}"
                                                data-bs-toggle="modal" data-bs-target="#editScheduleModal" title="{{ __('Edit') }}">
                                                <i class="ri-edit-box-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm js-archive"
                                                data-form="archive-schedule-{{ $schedule->id }}"
                                                data-text="{{ __('This schedule will be archived.') }}">
                                                <i class="ri-archive-line"></i>
                                            </button>
                                            <form id="archive-schedule-{{ $schedule->id }}" class="d-none"
                                                action="{{ route('wellness.schedules.archive', $schedule->encrypted_id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create --}}
    <div class="modal fade" id="createScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('wellness.schedules.store', $activity->encrypted_id) }}" method="POST">
                    @csrf
                    {{-- Lets the page reopen this modal, with the typed values intact,
                         when validation bounces the submit back. --}}
                    <input type="hidden" name="form_origin" value="create">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">{{ __('Add Schedule') }}</h5>
                            <small class="text-muted">{{ $activity->name }}</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        @include('pages.admin.wellness.schedules._fields', ['prefix' => 'create', 'statuses' => $statuses, 'frequencies' => $frequencies, 'showRepeat' => true])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save Schedule') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit --}}
    <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="" id="editScheduleForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">{{ __('Edit Schedule') }}</h5>
                            <small class="text-muted js-edit-subtitle"></small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        @include('pages.admin.wellness.schedules._fields', ['prefix' => 'edit', 'statuses' => $statuses, 'frequencies' => $frequencies, 'showScope' => true])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Update Schedule') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('layouts_.shared.admin-datatable-js')
    @include('pages.admin.wellness.partials.confirm-js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var MAX_OCCURRENCES = {{ (int) config('wellness.recurrence.max_occurrences', 260) }};
            var LOCALE = @json(str_replace('_', '-', app()->getLocale()));
            var TEXT = {
                one: @json(__('One session only.')),
                pickUntil: @json(__('Pick a repeat-until date.')),
                summary: @json(__(':count sessions, last on :date')),
                capped: @json(__('Capped at the maximum number of sessions.')),
                endsBefore: @json(__('Ends before it starts.'))
            };

            function pad(n) { return (n < 10 ? '0' : '') + n; }

            // Reassembled from parts so the preview reads the same way as the
            // table behind it (D, d M Y) -- toLocaleDateString would order the
            // day and month by locale instead.
            function formatDate(date) {
                var parts = new Intl.DateTimeFormat(LOCALE, {
                    weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
                }).formatToParts(date).reduce(function (acc, part) {
                    acc[part.type] = part.value;
                    return acc;
                }, {});

                return parts.weekday + ', ' + parts.day + ' ' + parts.month + ' ' + parts.year;
            }

            // Mirrors WellnessRecurrenceFrequency::nth() -- always counted from the
            // base date, with monthly clamping to the length of the target month.
            function nth(base, n, freq) {
                if (freq === 'daily' || freq === 'weekly') {
                    var d = new Date(base.getTime());
                    d.setDate(base.getDate() + n * (freq === 'weekly' ? 7 : 1));
                    return d;
                }
                var target = new Date(base.getFullYear(), base.getMonth() + n, 1,
                    base.getHours(), base.getMinutes());
                var lastDay = new Date(target.getFullYear(), target.getMonth() + 1, 0).getDate();
                target.setDate(Math.min(base.getDate(), lastDay));
                return target;
            }

            function occurrences(start, untilValue, freq) {
                var until = new Date(untilValue + 'T23:59:59');
                if (isNaN(start) || isNaN(until) || until < start) { return []; }

                var out = [];
                for (var n = 0; out.length < MAX_OCCURRENCES; n++) {
                    var occurrence = nth(start, n, freq);
                    if (n > 0 && occurrence > until) { break; }
                    out.push(occurrence);
                }
                return out;
            }

            // ------------------------------------------------ duration hint
            function wireDuration(prefix) {
                var startEl = document.getElementById(prefix + '-start_at');
                var endEl = document.getElementById(prefix + '-end_at');
                var hint = endEl.parentElement.querySelector('.js-duration-hint');

                function sync() {
                    var start = new Date(startEl.value);
                    var end = new Date(endEl.value);
                    if (isNaN(start) || isNaN(end)) { hint.innerHTML = '&nbsp;'; return; }

                    var minutes = Math.round((end - start) / 60000);
                    if (minutes <= 0) {
                        hint.textContent = TEXT.endsBefore;
                        hint.classList.add('text-danger');
                        return;
                    }
                    hint.classList.remove('text-danger');
                    hint.textContent = Math.floor(minutes / 60) + 'h ' + pad(minutes % 60) + 'm';
                }

                startEl.addEventListener('change', sync);
                endEl.addEventListener('change', sync);
                return sync;
            }

            var syncCreateDuration = wireDuration('create');
            var syncEditDuration = wireDuration('edit');

            // ------------------------------------------------------- repeat
            var frequency = document.getElementById('create-repeat_frequency');
            var until = document.getElementById('create-repeat_until');
            var createStart = document.getElementById('create-start_at');
            var summary = document.querySelector('.js-repeat-summary');

            function setSummary(html, variant) {
                summary.innerHTML = html;
                summary.className = 'alert alert-' + variant + ' py-2 px-3 mb-0 w-100 small js-repeat-summary';
            }

            function syncRepeat() {
                var repeats = frequency.value !== '';

                // "Repeat until" only means anything once a frequency is chosen,
                // and is required from that moment on -- keep the two in step
                // rather than letting the server reject the pair.
                until.disabled = !repeats;
                until.required = repeats;
                until.min = (createStart.value || '').slice(0, 10);

                if (!repeats) {
                    until.value = '';
                    setSummary(TEXT.one, 'secondary');
                    return;
                }

                var dates = (until.value && createStart.value)
                    ? occurrences(new Date(createStart.value), until.value, frequency.value)
                    : [];

                if (dates.length === 0) {
                    setSummary(TEXT.pickUntil, 'secondary');
                    return;
                }

                // Repeating as far as the start date itself is still one session.
                if (dates.length === 1) {
                    setSummary(TEXT.one, 'secondary');
                    return;
                }

                var capped = dates.length >= MAX_OCCURRENCES;

                setSummary(
                    TEXT.summary
                        .replace(':count', '<strong>' + dates.length + '</strong>')
                        .replace(':date', formatDate(dates[dates.length - 1]))
                        + (capped ? '<div class="mt-1">' + TEXT.capped + '</div>' : ''),
                    capped ? 'warning' : 'info'
                );
            }

            frequency.addEventListener('change', syncRepeat);
            until.addEventListener('change', syncRepeat);
            createStart.addEventListener('change', syncRepeat);

            // --------------------------------------------------- edit modal
            var scopeWrapper = document.getElementById('edit-scope-wrapper');
            var scopeCount = scopeWrapper.querySelector('.js-scope-count');
            var scopeThis = document.getElementById('edit-scope-this');
            var editSubtitle = document.querySelector('.js-edit-subtitle');

            document.querySelectorAll('.js-edit-schedule').forEach(function (button) {
                button.addEventListener('click', function () {
                    var data = this.dataset;
                    document.getElementById('editScheduleForm').action = data.url;
                    document.getElementById('edit-start_at').value = data.start || '';
                    document.getElementById('edit-end_at').value = data.end || '';
                    document.getElementById('edit-location').value = data.location || '';
                    document.getElementById('edit-quota').value = data.quota || '';
                    document.getElementById('edit-registration_start_at').value = data.regstart || '';
                    document.getElementById('edit-registration_end_at').value = data.regend || '';
                    document.getElementById('edit-status').value = data.status || 'open';
                    editSubtitle.textContent = data.label || '';
                    syncEditDuration();

                    // The choice is only offered when there is something after
                    // this occurrence to carry the edit to.
                    var following = parseInt(data.following || '0', 10);
                    scopeWrapper.classList.toggle('d-none', following < 1);
                    scopeCount.textContent = following > 0 ? '(+' + following + ')' : '';
                    scopeThis.checked = true;
                });
            });

            syncCreateDuration();
            syncRepeat();

            // A bounced submit keeps the typed values through old(); reopen the
            // modal so they are not left invisible behind the page.
            @if ($errors->any() && old('form_origin') === 'create')
                new bootstrap.Modal(document.getElementById('createScheduleModal')).show();
            @endif
        });
    </script>
@endpush

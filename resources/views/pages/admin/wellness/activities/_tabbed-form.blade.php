{{-- The activity form body, shared by create and edit so the two screens cannot
     drift apart. The caller owns the <form> element (method and action differ),
     this owns everything inside it.

     Expects: $activity (null on create), $schedules (collection, empty on create),
     $types, $statuses, $methods, $scheduleStatuses, $submitLabel. --}}

@php
    $activity = $activity ?? null;
    $schedules = $schedules ?? collect();

    // Which tab a validation failure belongs to, so the right one opens and gets
    // the error marker on load.
    $scheduleHasErrors = collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'schedules'));
    $detailsHasErrors = collect($errors->keys())->contains(fn ($key) => ! str_starts_with($key, 'schedules'));

    // Short month names for the collapsed session summary, in the admin's language.
    $monthLabels = array_map(fn ($month) => __($month), ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']);
@endphp

<ul class="nav nav-tabs nav-bordered mb-3" id="wa_tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $scheduleHasErrors ? '' : 'active' }}" id="wa_tab_details"
            data-bs-toggle="tab" data-bs-target="#wa_pane_details" type="button" role="tab"
            aria-controls="wa_pane_details" aria-selected="{{ $scheduleHasErrors ? 'false' : 'true' }}">
            <i class="ri-information-line me-1"></i>{{ __('Activity Details') }}
            <i class="ri-error-warning-fill text-danger ms-1 js-tab-error {{ $detailsHasErrors ? '' : 'd-none' }}"
                title="{{ __('This tab has errors') }}"></i>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $scheduleHasErrors ? 'active' : '' }}" id="wa_tab_schedules"
            data-bs-toggle="tab" data-bs-target="#wa_pane_schedules" type="button" role="tab"
            aria-controls="wa_pane_schedules" aria-selected="{{ $scheduleHasErrors ? 'true' : 'false' }}">
            <i class="ri-calendar-line me-1"></i>{{ __('Schedules') }}
            <span class="badge bg-secondary ms-1" id="wa_schedule_count">0</span>
            <i class="ri-error-warning-fill text-danger ms-1 js-tab-error {{ $scheduleHasErrors ? '' : 'd-none' }}"
                title="{{ __('This tab has errors') }}"></i>
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade {{ $scheduleHasErrors ? '' : 'show active' }}" id="wa_pane_details"
        role="tabpanel" aria-labelledby="wa_tab_details">
        @include('pages.admin.wellness.activities._form', ['activity' => $activity])
    </div>

    <div class="tab-pane fade {{ $scheduleHasErrors ? 'show active' : '' }}" id="wa_pane_schedules"
        role="tabpanel" aria-labelledby="wa_tab_schedules">
        @include('pages.admin.wellness.activities._schedule-rows', ['schedules' => $schedules])
    </div>
</div>

{{-- Sticky so Save stays reachable however long the sessions list gets. --}}
<div class="wa-actions d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
    <span class="text-muted small">
        @if ($activity)
            <i class="ri-time-line me-1"></i>{{ __('Last updated :when', ['when' => $activity->updated_at?->diffForHumans()]) }}
        @else
            <i class="ri-information-line me-1"></i>{{ __('Sessions are optional — you can add them later.') }}
        @endif
    </span>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.wellness.activities.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
        <button type="submit" class="btn btn-primary" @disabled($types->isEmpty())>
            <i class="ri-check-line me-1"></i>{{ $submitLabel }}
        </button>
    </div>
</div>

@push('scripts')
@include('pages.admin.wellness.schedules._range-js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('wa_form');
        var rows = document.getElementById('wa_schedule_rows');
        var template = document.getElementById('wa_schedule_template');
        var addButton = document.getElementById('wa_add_schedule');
        var toggleAll = document.getElementById('wa_toggle_all');
        var empty = document.getElementById('wa_schedule_empty');
        var counter = document.getElementById('wa_schedule_count');

        if (!form || !rows || !template || !addButton) {
            return;
        }

        var BADGES = {
            open: 'bg-success-subtle text-success',
            closed: 'bg-warning-subtle text-warning',
            completed: 'bg-primary-subtle text-primary',
            cancelled: 'bg-danger-subtle text-danger'
        };

        // Row indices only have to be unique and ascending -- Laravel re-keys the
        // array, so gaps left by removing a row in the middle are harmless.
        var nextIndex = rows.querySelectorAll('.js-schedule-row').length;

        function pad(n) {
            return (n < 10 ? '0' : '') + n;
        }

        var toLocalValue = WellnessSessionRange.toLocalValue;

        // "2026-10-01T08:00" -> parts. Parsed by hand rather than through Date()
        // so a half-typed value never renders as "Invalid Date".
        function formatStamp(value) {
            var m = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/.exec(value || '');

            if (!m) {
                return null;
            }

            var months = @json($monthLabels);

            return {
                date: pad(+m[3]) + ' ' + months[+m[2] - 1] + ' ' + m[1],
                time: m[4] + ':' + m[5]
            };
        }

        // Mirrors the schedule modal, so the same field block reads the same way
        // on both screens.
        var HINTS = {
            confirmOff: @json(__('Seats are given out and taken straight away.')),
            confirmOn: @json(__('Employees must confirm their seat by this time, or it passes to the next person in the queue.')),
            endsBefore: @json(__('Ends before it starts.'))
        };

        function fillHints(row) {
            var duration = row.querySelector('.js-duration-hint');
            var confirmHint = row.querySelector('.js-confirm-hint');

            if (duration) {
                var from = Date.parse(row.querySelector('.js-start-at').value);
                var to = Date.parse(row.querySelector('.js-end-at').value);

                if (isNaN(from) || isNaN(to)) {
                    duration.innerHTML = '&nbsp;';
                    duration.classList.remove('text-danger');
                } else if (to <= from) {
                    duration.textContent = HINTS.endsBefore;
                    duration.classList.add('text-danger');
                } else {
                    var minutes = Math.round((to - from) / 60000);
                    duration.textContent = Math.floor(minutes / 60) + 'h ' + pad(minutes % 60) + 'm';
                    duration.classList.remove('text-danger');
                }
            }

            if (confirmHint) {
                var confirmBy = row.querySelector('.js-confirm-by');
                var on = confirmBy && confirmBy.value !== '';

                confirmHint.textContent = on ? HINTS.confirmOn : HINTS.confirmOff;
                confirmHint.className = 'alert alert-' + (on ? 'info' : 'secondary')
                    + ' py-2 px-3 mb-0 w-100 small js-confirm-hint';
            }
        }

        function summarise(row) {
            var start = formatStamp(row.querySelector('.js-start-at').value);
            var end = formatStamp(row.querySelector('.js-end-at').value);
            var location = row.querySelector('.js-location').value.trim();
            var quota = row.querySelector('.js-quota').value.trim();
            var parts = [];

            if (start) {
                parts.push(start.date + ', ' + start.time + (end ? ' – ' + end.time : ''));
            }

            if (location) {
                parts.push(location);
            }

            parts.push(quota ? @json(__(':count seats')).replace(':count', quota) : @json(__('Unlimited seats')));

            return start ? parts.join('  ·  ') : @json(__('Not scheduled yet'));
        }

        // Same date rules as the schedule modal -- see schedules/_range-js.
        function refreshRow(row, position) {
            WellnessSessionRange.enforce(row);
            row.querySelector('.js-schedule-title').textContent = @json(__('Session')) + ' ' + position;
            row.querySelector('.js-schedule-summary').textContent = summarise(row);

            var select = row.querySelector('.js-status');
            var badge = row.querySelector('.js-schedule-badge');

            badge.textContent = select.options[select.selectedIndex].text;
            badge.className = 'badge js-schedule-badge ' + (BADGES[select.value] || 'bg-secondary-subtle text-secondary');
        }

        function refresh() {
            var all = rows.querySelectorAll('.js-schedule-row');

            all.forEach(function (row, i) {
                refreshRow(row, i + 1);
                fillHints(row);
            });

            counter.textContent = all.length;
            counter.className = 'badge ms-1 ' + (all.length ? 'bg-primary' : 'bg-secondary');
            empty.classList.toggle('d-none', all.length > 0);
            toggleAll.hidden = all.length < 2;
        }

        function addRow() {
            var markup = template.innerHTML.replace(/__INDEX__/g, nextIndex);
            nextIndex += 1;

            var holder = document.createElement('div');
            holder.innerHTML = markup.trim();

            var row = holder.firstElementChild;
            rows.appendChild(row);
            refresh();

            row.querySelector('.js-start-at').focus();

            return row;
        }

        addButton.addEventListener('click', addRow);

        empty.addEventListener('click', function (event) {
            if (event.target.closest('.js-add-first')) {
                addRow();
            }
        });

        rows.addEventListener('click', function (event) {
            var row = event.target.closest('.js-schedule-row');

            if (!row) {
                return;
            }

            if (event.target.closest('.js-remove-schedule')) {
                // Sessions holding seats have the button disabled, but re-check:
                // a click could still arrive from assistive tech or devtools.
                if (row.dataset.locked) {
                    return;
                }

                row.remove();
                refresh();

                return;
            }

            // Duplicating is the quick way to build a weekly series: copy every
            // value across, then nudge the dates a week on so only the exceptions
            // need editing. The copy is always a new session, never a second
            // reference to the original -- the template carries no id field.
            if (event.target.closest('.js-duplicate-schedule')) {
                var copy = addRow();

                ['.js-start-at', '.js-end-at', '.js-location', '.js-quota', '.js-status',
                    '.js-reg-start', '.js-reg-end', '.js-confirm-by'].forEach(function (sel) {
                    var source = row.querySelector(sel);
                    var target = copy.querySelector(sel);

                    if (source && target) {
                        target.value = source.value;
                    }
                });

                // Every date moves together, so the copy keeps the same shape a
                // week later -- a registration window or deadline left on the
                // original week would already be in the past.
                ['.js-start-at', '.js-end-at', '.js-reg-start', '.js-reg-end', '.js-confirm-by'].forEach(function (sel) {
                    var field = copy.querySelector(sel);
                    var stamp = field ? Date.parse(field.value) : NaN;

                    if (!isNaN(stamp)) {
                        field.value = toLocalValue(new Date(stamp + 7 * 24 * 60 * 60 * 1000));
                    }
                });

                refresh();
            }
        });

        // Keep the collapsed summary honest as fields change.
        rows.addEventListener('input', function (event) {
            var row = event.target.closest('.js-schedule-row');

            if (row) {
                refreshRow(row, Array.prototype.indexOf.call(rows.children, row) + 1);
            }
        });

        rows.addEventListener('change', function (event) {
            var row = event.target.closest('.js-schedule-row');

            if (!row) {
                return;
            }

            // Picking a start with no end yet: assume an hour, which is the common
            // case and still editable.
            if (event.target.classList.contains('js-start-at')) {
                WellnessSessionRange.defaultEnd(row);
            }

            refreshRow(row, Array.prototype.indexOf.call(rows.children, row) + 1);
        });

        // Flip the chevron with the collapse it controls.
        rows.addEventListener('show.bs.collapse', function (event) {
            event.target.closest('.js-schedule-row')
                .querySelector('.js-schedule-toggle i').className = 'ri-arrow-up-s-line fs-5';
        });

        rows.addEventListener('hide.bs.collapse', function (event) {
            event.target.closest('.js-schedule-row')
                .querySelector('.js-schedule-toggle i').className = 'ri-arrow-down-s-line fs-5';
        });

        toggleAll.addEventListener('click', function () {
            var panes = rows.querySelectorAll('.collapse');
            var anyOpen = Array.prototype.some.call(panes, function (pane) {
                return pane.classList.contains('show');
            });

            panes.forEach(function (pane) {
                bootstrap.Collapse.getOrCreateInstance(pane, { toggle: false })[anyOpen ? 'hide' : 'show']();
            });

            toggleAll.querySelector('[data-label]').textContent = anyOpen ? @json(__('Expand all')) : @json(__('Collapse all'));
        });

        // The form is novalidate so the browser cannot block submit on a required
        // field sitting in the hidden tab -- it refuses to focus one and reports
        // nothing. Check by hand, then open the offending tab first.
        form.addEventListener('submit', function (event) {
            if (form.checkValidity()) {
                return;
            }

            event.preventDefault();

            var invalid = form.querySelector(':invalid');
            var pane = invalid.closest('.tab-pane');

            if (pane && !pane.classList.contains('active')) {
                bootstrap.Tab.getOrCreateInstance(
                    document.querySelector('[data-bs-target="#' + pane.id + '"]')
                ).show();
            }

            var collapsed = invalid.closest('.collapse');
            if (collapsed && !collapsed.classList.contains('show')) {
                bootstrap.Collapse.getOrCreateInstance(collapsed, { toggle: false }).show();
            }

            // Let the tab and collapse finish animating before asking the browser
            // to focus and report, otherwise the control is still hidden.
            setTimeout(function () {
                invalid.reportValidity();
                invalid.focus();
            }, 350);
        });

        refresh();
    });
</script>
@endpush

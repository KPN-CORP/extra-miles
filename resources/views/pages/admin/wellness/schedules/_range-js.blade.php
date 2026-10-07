{{-- Client-side date rules for one session's fields (_session-fields). Shared by
     the schedule modal on the Sessions page and the Schedule tab of the activity
     form, so both pickers refuse exactly the same values -- the server rules
     (WellnessActivityScheduleRequest / WellnessActivityRequest) are the same too.

     Works on any element that contains one set of session fields, found by their
     js-* classes:

       WellnessSessionRange.enforce(container)   re-apply every bound and message
       WellnessSessionRange.defaultEnd(container) empty end -> start + 1 hour
       WellnessSessionRange.toLocalValue(date)    Date -> "YYYY-MM-DDTHH:mm"

     Guarded, so a page that ends up including it twice still gets one copy. --}}
<script>
    window.WellnessSessionRange = window.WellnessSessionRange || (function () {
        var MESSAGES = {
            endAfterStart: @json(__('End time must be later than the start time.')),
            regOrder: @json(__('Registration must close no earlier than it opens.')),
            regWithin: @json(__('Registration must close no later than the session ends.'))
        };

        function pad(n) {
            return (n < 10 ? '0' : '') + n;
        }

        function toLocalValue(date) {
            return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate())
                + 'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
        }

        // The later of two values, ignoring blanks. Bounds are built from this so
        // a field is held by every rule that applies to it at once.
        function laterOf(a, b) {
            if (!a) {
                return b || '';
            }

            if (!b) {
                return a || '';
            }

            return a > b ? a : b;
        }

        // `min`/`max` steer the native picker, but the real check is
        // setCustomValidity: `min` is inclusive while the server wants the end
        // strictly after the start. Values are "YYYY-MM-DDTHH:mm", where string
        // order is chronological order, so they compare directly.
        //
        // Every pair is bounded in both directions -- session start/end, and
        // registration opens/closes -- so whichever field the admin is editing is
        // the one the picker restrains, and the error never lands on a field they
        // never touched.
        function enforce(container) {
            var start = container.querySelector('.js-start-at');
            var end = container.querySelector('.js-end-at');
            var regStart = container.querySelector('.js-reg-start');
            var regEnd = container.querySelector('.js-reg-end');
            var confirmBy = container.querySelector('.js-confirm-by');

            if (!start || !end) {
                return;
            }

            // Confirming has to happen before the session it is for, and not
            // before sign-ups open.
            if (confirmBy) {
                confirmBy.min = regStart ? regStart.value : '';
                confirmBy.max = start.value || '';
            }

            var hasWindow = regStart && regEnd;

            end.min = laterOf(start.value, hasWindow ? regEnd.value : '');
            end.setCustomValidity(
                start.value && end.value && end.value <= start.value ? MESSAGES.endAfterStart : ''
            );

            if (!hasWindow) {
                return;
            }

            // registration_end_at: after_or_equal registration_start_at,
            //                      before_or_equal end_at.
            regEnd.min = regStart.value || '';
            regEnd.max = end.value || '';

            // The reciprocal of the first of those, so "Opens" cannot be pushed
            // past "Closes" either.
            regStart.max = regEnd.value || '';

            var outOfOrder = regStart.value && regEnd.value && regEnd.value < regStart.value;

            regStart.setCustomValidity(outOfOrder ? MESSAGES.regOrder : '');

            var regMessage = '';

            if (outOfOrder) {
                regMessage = MESSAGES.regOrder;
            } else if (end.value && regEnd.value && regEnd.value > end.value) {
                regMessage = MESSAGES.regWithin;
            }

            regEnd.setCustomValidity(regMessage);
        }

        // Picking a start with no end yet: assume an hour, which is the common
        // case and still editable.
        function defaultEnd(container) {
            var start = container.querySelector('.js-start-at');
            var end = container.querySelector('.js-end-at');
            var stamp = start ? Date.parse(start.value) : NaN;

            if (end && !end.value && !isNaN(stamp)) {
                end.value = toLocalValue(new Date(stamp + 60 * 60 * 1000));
            }
        }

        return { enforce: enforce, defaultEnd: defaultEnd, toLocalValue: toLocalValue };
    })();
</script>

// Wellness sessions store a single start/end datetime (Asia/Jakarta), unlike
// events which keep date and time in separate columns -- so dateTimeHelper
// does not fit here.

export function parseSessionDate(value) {
    if (!value) return null;
    // "2026-09-02 08:00:00" -> a Date the browser reads as local time.
    return new Date(String(value).replace(' ', 'T'));
}

export function formatSession(schedule) {
    const start = parseSessionDate(schedule?.start_at);
    const end = parseSessionDate(schedule?.end_at);

    if (!start) {
        return { day: '', month: '', year: '', time: '', full: '', isPast: false };
    }

    const two = (n) => String(n).padStart(2, '0');
    const time = end
        ? `${two(start.getHours())}:${two(start.getMinutes())} - ${two(end.getHours())}:${two(end.getMinutes())}`
        : `${two(start.getHours())}:${two(start.getMinutes())}`;

    return {
        day: start.getDate(),
        month: start.toLocaleString('en-US', { month: 'short' }),
        year: start.getFullYear(),
        weekday: start.toLocaleString('en-US', { weekday: 'long' }),
        time,
        full: `${start.toLocaleString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })}, ${time}`,
        isPast: end ? end < new Date() : start < new Date(),
    };
}

// Matches the badge colours used by the admin side, so both surfaces read the same.
export function statusStyle(status) {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-700';
        case 'pending':
            return 'bg-blue-100 text-blue-700';
        case 'waitlisted':
            return 'bg-yellow-100 text-yellow-700';
        case 'rejected':
            return 'bg-red-100 text-red-700';
        case 'cancelled':
            return 'bg-stone-200 text-stone-600';
        default:
            return 'bg-stone-100 text-stone-600';
    }
}

export function seatLabel(schedule) {
    if (!schedule) return '';
    if (schedule.quota === null || schedule.quota === undefined) return 'Unlimited seats';
    if (schedule.is_full) return 'Full - waitlist only';
    return `${schedule.remaining_seats} of ${schedule.quota} seats left`;
}

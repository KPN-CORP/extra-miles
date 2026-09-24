import { localeTag, translate } from './localeHelper';

export function dateTimeHelper(event) {
    const startDate = new Date(event.start_date);
    const deadline = new Date(event.regist_deadline);
    const endDate = new Date(event.end_date);
    const today = new Date();

    // Normalize time to midnight for accurate date-only comparison
    startDate.setHours(0, 0, 0, 0);
    deadline.setHours(0, 0, 0, 0);
    endDate.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);


    // Determine event status
    const isClosed = today > endDate;
    const isOngoing = startDate <= today && today <= endDate;
    const closedRegistration = today > deadline && today < startDate ;

    // Nilai mentah (bahasa Inggris) dipertahankan karena dipakai untuk logika
    // perbandingan di beberapa halaman; label terjemahannya dikirim terpisah.
    const eventStatus = isOngoing ? 'Ongoing' : ( closedRegistration ? 'Closed Registration' : 'Closed');
    const eventStatusLabel = translate(`event.status.${eventStatus}`, { defaultValue: eventStatus });

    const locale = localeTag();

    // Format month (e.g., "May" / "Mei")
    const month = startDate.toLocaleString(locale, { month: 'short' });
    const endMonth = endDate.toLocaleString(locale, { month: 'short' });

    // Format day (e.g., "14")
    const day = startDate.getDate();

    const endDay = endDate.getDate();

    const totalDay = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;


    const year = startDate.toLocaleString(locale, { year: 'numeric' });
    const endYear = endDate.toLocaleString(locale, { year: 'numeric' });

    // Format start and end times (e.g., "09:00")
    const startTime = event.time_start?.replace(/:/g, ':').slice(0, 5) || '';
    const endTime = event.time_end
    ? event.time_end.replace(/:/g, ':').slice(0, 5)
    : translate('date.end');

    const daysUntilCalc = () => {
        const now = new Date();
        const endDate = new Date(event.end_date);

        endDate.setHours(0, 0, 0, 0);
        now.setHours(0, 0, 0, 0);

        const diffTime = endDate - now;
        if (diffTime < 0) return { key: 'Ended', label: translate('date.ended') };
        if (diffTime === 0) return { key: 'Today', label: translate('date.today') };

        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        if (diffDays < 7) {
          return { key: `${diffDays} days`, label: translate('date.day', { count: diffDays }) };
        }

        const diffWeeks = Math.floor(diffDays / 7);
        if (diffWeeks < 4) {
          return { key: `${diffWeeks} weeks`, label: translate('date.week', { count: diffWeeks }) };
        }

        const diffMonths = Math.floor(diffDays / 30);
        return { key: `${diffMonths} months`, label: translate('date.month', { count: diffMonths }) };
      };

    const until = daysUntilCalc();

    return {
        month,
        day,
        year,
        startTime,
        endTime,
        eventStatus,
        eventStatusLabel,
        isOngoing,
        isClosed,
        startDate,
        endDate,
        // Nilai mentah untuk logika ("Ended", "Today", "3 days").
        daysUntil: until.key,
        // Teks siap tampil dalam bahasa aktif.
        daysUntilLabel: until.label,
        closedRegistration,
        endDay,
        totalDay,
        endYear,
        endMonth,
    };
}

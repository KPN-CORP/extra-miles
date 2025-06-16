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

    const eventStatus = isOngoing ? 'Ongoing' : ( closedRegistration ? 'Closed Registration' : 'Closed');

    // Format month (e.g., "May")
    const month = startDate.toLocaleString('en-US', { month: 'short' });
    const endMonth = endDate.toLocaleString('en-US', { month: 'short' });

    // Format day (e.g., "14")
    const day = startDate.getDate();

    const endDay = endDate.getDate();

    const totalDay = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;


    const year = startDate.toLocaleString('en-US', { year: 'numeric' });
    const endYear = endDate.toLocaleString('en-US', { year: 'numeric' });

    // Format start and end times (e.g., "09:00")
    const startTime = event.time_start?.replace(/:/g, ':').slice(0, 5) || '';
    const endTime = event.time_end
    ? event.time_end.replace(/:/g, ':').slice(0, 5)
    : 'end';

    const daysUntilCalc = () => {
        const now = new Date();
        const endDate = new Date(event.end_date);
      
        endDate.setHours(0, 0, 0, 0);
        now.setHours(0, 0, 0, 0);
      
        const diffTime = endDate - now;
        if (diffTime < 0) return 'Ended';
        if (diffTime === 0) return 'Today';
      
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        if (diffDays < 7) {
          return `${diffDays} day${diffDays !== 1 ? 's' : ''}`;
        }
      
        const diffWeeks = Math.floor(diffDays / 7);
        if (diffWeeks < 4) {
          return `${diffWeeks} week${diffWeeks !== 1 ? 's' : ''}`;
        }
      
        const diffMonths = Math.floor(diffDays / 30);
        return `${diffMonths} month${diffMonths !== 1 ? 's' : ''}`;
      };

    const daysUntil = daysUntilCalc();

    return {
        month,
        day,
        year,
        startTime,
        endTime,
        eventStatus,
        isOngoing,
        isClosed,
        startDate,
        endDate,
        daysUntil,
        closedRegistration,
        endDay,
        totalDay,
        endYear,
        endMonth,
    };
}
import React, { useEffect, useState } from 'react';

const CountdownTimer = ({ endDateTime, onEnd }) => {
  const [timeLeft, setTimeLeft] = useState({});
  const [eventEnded, setEventEnded] = useState(false);

  const calculateTimeLeft = () => {
    const end = new Date(endDateTime);
    const now = new Date();
    const diff = end - now;

    if (diff <= 0) {
      return null;
    }

    return {
      days: Math.floor(diff / (1000 * 60 * 60 * 24)),
      hours: Math.floor((diff / (1000 * 60 * 60)) % 24),
      minutes: Math.floor((diff / (1000 * 60)) % 60),
      seconds: Math.floor((diff / 1000) % 60),
    };
  };

  useEffect(() => {
    const timer = setInterval(() => {
      const left = calculateTimeLeft();

      if (!left) {
        clearInterval(timer);
        setEventEnded(true);
        setTimeLeft(null);
        if (onEnd) onEnd(); // Optional callback when countdown ends
      } else {
        setTimeLeft(left);
      }
    }, 1000);

    return () => clearInterval(timer);
  }, [endDateTime, onEnd]);

  return (
    <div className="w-full text-center justify-center">
      {eventEnded || !timeLeft ? (
        <span>Event has ended.</span>
      ) : (
        <span>
          {timeLeft.days}d {timeLeft.hours}h {timeLeft.minutes}m {timeLeft.seconds}s
        </span>
      )}
    </div>
  );
};

export default CountdownTimer;

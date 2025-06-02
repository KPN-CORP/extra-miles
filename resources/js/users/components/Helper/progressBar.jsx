import { useEffect, useState } from "react";

const VoteProgressBar = ({ percentage = 0 }) => {
  const [animatedWidth, setAnimatedWidth] = useState("0%");

  useEffect(() => {
    // Animate width after mount
    const timeout = setTimeout(() => {
      setAnimatedWidth(`${percentage}%`);
    }, 100); // slight delay to trigger transition

    return () => clearTimeout(timeout);
  }, [percentage]);

  return (
    <div className="relative w-full h-3.5 bg-zinc-300 rounded">
      <div
        className="absolute top-0 left-0 h-3.5 bg-sky-500 rounded transition-all duration-700 ease-in-out"
        style={{ width: animatedWidth }}
      />
      <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-xs font-semibold whitespace-nowrap">
        {percentage}% votes
      </div>
    </div>
  );
};

export default VoteProgressBar;

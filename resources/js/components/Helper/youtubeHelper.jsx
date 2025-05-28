import { useState } from 'react';
import YouTube from 'react-youtube';

const YouTubePlayer = ({ videoId }) => {
  const [error, setError] = useState(false);

  const opts = {
    width: '100%',
    height: '100%',
    playerVars: {
      autoplay: 0,
    },
  };

  const handleError = () => {
    setError(true);
  };

  return (
    <div className="aspect-video w-full rounded-xl shadow-md overflow-hidden bg-black relative">
      {!error ? (
        <YouTube videoId={videoId} opts={opts} className="w-full h-full" onError={handleError} />
      ) : (
        <div className="w-full h-full relative">
            <img
            src={`https://img.youtube.com/vi/${videoId}/hqdefault.jpg`}
            alt="Video thumbnail"
            className="w-full h-full object-cover"
            onError={(e) => {
              e.target.onerror = null; // prevent infinite loop
              e.target.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="; // transparent 1x1 GIF
            }}
            />
            <button onClick={() => window.open(`https://www.youtube.com/watch?v=${videoId}`, '_blank')} className="absolute inset-0 flex justify-center items-center bg-black bg-opacity-40 w-full h-full">
                <div className="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg shadow-lg hover:bg-red-700 transition flex items-center gap-2">
                    <span>Open In YouTube</span>
                    <i className="ri-youtube-fill text-2xl"></i>
                </div>
            </button>
        </div>
      )}
    </div>
  );
};

export default YouTubePlayer;

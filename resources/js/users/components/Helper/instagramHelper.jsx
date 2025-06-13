import { useEffect, useState } from 'react';

const InstagramPlayer = ({ reelId }) => {
  const [thumbnail, setThumbnail] = useState(null);  

  useEffect(() => {
    if (!reelId) return;

    fetch(`https://noembed.com/embed?url=https://www.instagram.com/reel/${reelId}/`)
      .then((res) => res.json())
      .then((data) => setThumbnail(data.thumbnail_url))
      .catch(() => setThumbnail(null));
  }, [reelId]);

  const handleClick = () => {
    window.open(`https://www.instagram.com/reel/${reelId}/`, '_blank');
  };

  return (
    <div
      onClick={handleClick}
      className="aspect-[9/16] w-full rounded shadow-md overflow-hidden bg-black relative cursor-pointer group"
    >
      {thumbnail ? (
        <img
          src={thumbnail}
          alt="Instagram Reel"
          className="w-full h-full object-cover group-hover:brightness-75 transition"
        />
      ) : (
        <div className="w-full h-full bg-gray-800 flex items-center justify-center text-white">
          Loading...
        </div>
      )}

      <div className="absolute inset-0 flex justify-center items-center bg-black bg-opacity-0 group-hover:bg-opacity-50 transition">
        <div className="px-3 py-3 bg-pink-600 text-white font-semibold rounded-lg shadow-lg flex items-center gap-2">
          <i className="ri-instagram-fill text-xl"></i>
          <span className="text-xs">Open in Instagram</span>
        </div>
      </div>
    </div>
  );
};

export default InstagramPlayer;

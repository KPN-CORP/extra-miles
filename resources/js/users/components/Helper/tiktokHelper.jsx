import { useEffect, useState } from 'react';

const TikTokPlayer = ({ videoId }) => {
  const [thumbnail, setThumbnail] = useState(null);
  
  useEffect(() => {
    fetch(`https://api.microlink.io/?url=https://www.tiktok.com/@lifeatkpn/video/${videoId}`)
      .then((res) => res.json())
      .then((data) => {
        const thumb = data?.data?.image?.url || null;
        setThumbnail(thumb);
      })
      .catch(() => setThumbnail(null));
  }, [videoId]);

  const handleClick = () => {
    window.open(`https://www.tiktok.com/@lifeatkpn/video/${videoId}`, '_blank');
  };

  return (
    <div
      onClick={handleClick}
      className="aspect-[9/16] w-full rounded shadow-md overflow-hidden bg-black relative cursor-pointer group"
    >
      {thumbnail ? (
        <img
          src={thumbnail}
          alt="TikTok Video"
          className="w-full h-full object-cover group-hover:brightness-75 transition"
        />
      ) : (
        <div className="w-full h-full bg-gray-800 flex items-center justify-center text-white">
          Loading...
        </div>
      )}

      <div className="absolute inset-0 flex justify-center items-center bg-black bg-opacity-0 group-hover:bg-opacity-50 transition">
        <div className="px-3 py-3 bg-black text-white font-semibold rounded-lg shadow-lg flex items-center gap-2">
          <i className="ri-tiktok-fill text-xl"></i>
          <span className="text-xs">Open in TikTok</span>
        </div>
      </div>
    </div>
  );
};

export default TikTokPlayer;

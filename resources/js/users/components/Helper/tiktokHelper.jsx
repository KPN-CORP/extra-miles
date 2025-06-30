import { useEffect, useState } from "react";

const TikTokPlayer = ({ videoId }) => {
  const [thumbnail, setThumbnail] = useState(null);
  const tiktokUrl = `https://www.tiktok.com/@lifeaatkpn/video/${videoId}`;
  const embedUrl = `https://www.tiktok.com/oembed?url=${tiktokUrl}`;

  useEffect(() => {
    fetch(embedUrl)
      .then((res) => res.json())
      .then((data) => {
        setThumbnail(data.thumbnail_url);
      })
      .catch((err) => {
        console.error("Failed to fetch TikTok thumbnail:", err);
      });
  }, [videoId]);

  return (
    <div className="aspect-[9/16] w-full rounded shadow-md overflow-hidden bg-black relative group">
      {thumbnail ? (
        <img
          src={thumbnail}
          alt="TikTok Thumbnail"
          className="w-full h-full object-cover"
        />
      ) : (
        <div className="w-full h-full flex items-center justify-center bg-gray-800 text-white text-sm">
          Loading thumbnail...
        </div>
      )}
      <div className="absolute inset-0 flex justify-center items-center bg-black bg-opacity-0 group-hover:bg-opacity-50 transition">
        <a
          href={tiktokUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center gap-2 text-white bg-black px-4 py-2 text-xs rounded-lg hover:bg-gray-800 transition"
        >
          <i className="ri-tiktok-line text-xl" />
          Open on TikTok
        </a>
      </div>
    </div>
  );
};

export default TikTokPlayer;
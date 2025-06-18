const TikTokPlayer = ({ videoId }) => {
    const tiktokUrl = `https://www.tiktok.com/@lifeaatkpn/video/${videoId}`;
    const thumbnailUrl = `https://i1.muscdn.com/video/${videoId}/~tplv-dmt-logom:640:360.webp`;
  
    return (
      <div className="aspect-[9/16] w-full rounded shadow-md overflow-hidden bg-black relative group">
        <img
          src={thumbnailUrl}
          alt="TikTok Thumbnail"
          className="w-full h-full object-cover"
        />
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
  
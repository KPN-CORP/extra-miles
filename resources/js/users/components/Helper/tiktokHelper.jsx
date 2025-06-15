const TikTokPlayer = ({
    videoId,
    autoplay = 0,
    loop = 0,
    controls = 0,
    playButton = 0,
    volumeControl = 0,
    fullscreenButton = 0,
    timestamp = 0,
    musicInfo = 0,
    description = 1,
  }) => {
    const playerUrl = `https://www.tiktok.com/player/v1/${videoId}?autoplay=${autoplay}&loop=${loop}&controls=${controls}&play_button=${playButton}&volume_control=${volumeControl}&fullscreen_button=${fullscreenButton}&timestamp=${timestamp}&music_info=${musicInfo}&description=${description}`;
    const tiktokUrl = `https://www.tiktok.com/@lifeaatkpn/video/${videoId}`; // Replace @username if you know it
  
    return (
      <div className="aspect-[9/16] w-full rounded shadow-md overflow-hidden bg-black relative grou">
        <iframe
          title="TikTok Video"
          width="100%"
          height="100%"
          allowFullScreen
          src={playerUrl}
          className="rounded shadow"
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
  
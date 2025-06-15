const TikTokPlayer = ({
    videoId,
    autoplay = 0,
    loop = 0,
    controls = 1,
    playButton = 1,
    volumeControl = 1,
    fullscreenButton = 1,
    timestamp = 1,
    musicInfo = 1,
    description = 1,
  }) => {
    const playerUrl = `https://www.tiktok.com/player/v1/${videoId}?autoplay=${autoplay}&loop=${loop}&controls=${controls}&play_button=${playButton}&volume_control=${volumeControl}&fullscreen_button=${fullscreenButton}&timestamp=${timestamp}&music_info=${musicInfo}&description=${description}`;
  
    return (
      <div className="w-full aspect-[9/16] max-w-[605px]">
        <iframe
          title="TikTok Video"
          width="100%"
          height="100%"
          allowFullScreen
          src={playerUrl}
          className="rounded shadow"
        />
      </div>
    );
  };
  
  export default TikTokPlayer;
  
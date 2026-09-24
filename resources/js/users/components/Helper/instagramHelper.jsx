import { InstagramEmbed } from 'react-social-media-embed';
import { useTranslation } from 'react-i18next';

const InstagramPlayer = ({ postId }) => {
  const { t } = useTranslation();
  return (
    <div className="aspect-[9/16] w-full rounded shadow-md overflow-hidden bg-black relative group">
      <InstagramEmbed
        url={`https://www.instagram.com/lifeatkpn/reel/${postId}/`}
        width="100%"
        className="w-full h-full pointer-events-none"
      />
      {/* Overlay tombol di tengah bawah */}
      <div className="absolute inset-0 flex justify-center items-center bg-black bg-opacity-0 group-hover:bg-opacity-50 transition">
        <a
          href={`https://www.instagram.com/lifeatkpn/reel/${postId}/`}
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center gap-2 px-4 py-2 text-white bg-gradient-to-r from-yellow-400 via-pink-500 to-purple-600 font-semibold rounded-lg"
        >
          <i className="ri-instagram-line text-xl"></i>
          <span className="text-[10px]">{t('media.viewOnInstagram')}</span>
        </a>
      </div>
    </div>
  );
};

export default InstagramPlayer;

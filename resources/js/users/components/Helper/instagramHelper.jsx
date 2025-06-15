import { InstagramEmbed } from 'react-social-media-embed';

const InstagramPlayer = ({ postId }) => {
  return (
    <div className="aspect-[9/16] w-full rounded shadow-md overflow-hidden bg-black relative group">
      <InstagramEmbed
        url={`https://www.instagram.com/p/${postId}/`}
        width="100%"
        className="w-full h-full"
      />
        <div className="absolute inset-0 flex justify-center items-center bg-black bg-opacity-0 group-hover:bg-opacity-50 transition">
            <div className="p-2 bg-gradient-to-r from-yellow-400 via-pink-500 to-purple-600 text-white font-semibold rounded-lg shadow-lg flex items-center gap-2">
                <i className="ri-instagram-line text-xl"></i>
                <span className="text-xs">View on Instagram</span>
            </div>
        </div>
    </div>
  );
};

export default InstagramPlayer;
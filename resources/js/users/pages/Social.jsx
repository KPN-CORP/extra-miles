import React, { useEffect, useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import axios from "axios";

import { Swiper, SwiperSlide } from "swiper/react";
import "swiper/css";
import "swiper/css/bundle";
import { FreeMode } from "swiper/modules";

import BannerLoader from "../components/Loader/BannerLoader";

import { useApiUrl } from "../components/context/ApiContext";
import { showAlert } from "../components/Helper/alertHelper";
import { useAuth } from "../components/context/AuthContext";
import { getImageUrl } from "../components/Helper/imagePath";

import { motion } from "motion/react";
import SocialLoader from "../components/Loader/SocialLoader";
import YouTubePlayer from "../components/Helper/youtubeHelper";
import InstagramPlayer from "../components/Helper/instagramHelper";
import TikTokPlayer from "../components/Helper/tiktokHelper";

const pageVariants = {
  initial: { opacity: 0, x: 0 },
  animate: { opacity: 1, x: 0 },
  exit: { opacity: 0, x: 0 },
};

export default function Social() {
  const navigate = useNavigate();
  const location = useLocation();
  const bounds = location.state?.bounds;

  const [initialStyle, setInitialStyle] = useState(null);
  const apiUrl = useApiUrl();
  const { token } = useAuth();
  
  const [latestYoutube, setLatestYoutube] = useState([]);
  const [latestIG, setLatestIG] = useState([]);
  const [latestTiktok, setLatestTiktok] = useState([]);
  const [loading, setLoading] = useState(true);  

  useEffect(() => {
    const fetchContent = async () => {
      try {
        const res = await axios.get(`${apiUrl}/api/social`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        
        // Ambil hanya kategori 'youtube' lalu urutkan berdasarkan created_at terbaru
        const sortedYoutube = res.data
        .filter((item) => item.category?.toLowerCase() === 'youtube')
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        
        // Ambil 3 konten youtube terbaru
        const sortedLatestYoutube = sortedYoutube.slice(0, 3);
        
        setLatestYoutube(sortedLatestYoutube);
        
        const sortedLatestIG = res.data
        .filter((item) => item.category?.toLowerCase() === 'instagram')
        .slice(0, 1); // Get only the first data
        setLatestIG(sortedLatestIG);
        
        const sortedLatestTiktok = res.data
        .filter((item) => item.category?.toLowerCase() === 'tiktok')
        .slice(0, 1);
        setLatestTiktok(sortedLatestTiktok);
  
      } catch (err) {
        console.log(err);
        
        // showAlert({
        //   icon: "warning",
        //   title: "Connection Ended",
        //   text: "Unable to connect to the server. Please try again later.",
        //   timer: 2500,
        //   showConfirmButton: false,
        // }).then(() => {
        //   window.location.href = "https://kpncorporation.darwinbox.com/";
        // });
      } finally {
        setLoading(false);
      }
    };
  
    if (token) fetchContent();

    if (bounds) {
      const scaleX = bounds.width / window.innerWidth;
      const scaleY = bounds.height / window.innerHeight;
      const offsetX = bounds.left + bounds.width / 2 - window.innerWidth / 2;
      const offsetY = bounds.top + bounds.height / 2 - window.innerHeight / 2;

      setInitialStyle({
        scaleX,
        scaleY,
        offsetX,
        offsetY,
        borderRadius: 16,
      });
    }
  }, [apiUrl, token, bounds]);

  if (!initialStyle) return null; 

  return (
    <div className="w-full min-h-screen flex flex-col bg-gradient-to-br from-stone-50 to-orange-200 overflow-auto">
    <motion.div
      initial={{
          opacity: 0,
          scaleX: initialStyle.scaleX,
          scaleY: initialStyle.scaleY,
          x: initialStyle.offsetX,
          y: initialStyle.offsetY,
          borderRadius: initialStyle.borderRadius,
        }}
        animate={{
          opacity: 1,
          scaleX: 1,
          scaleY: 1,
          x: 0,
          y: 0,
          borderRadius: 0,
        }}
        exit={{
          opacity: 0,
          scaleX: initialStyle.scaleX,
          scaleY: initialStyle.scaleY,
          x: initialStyle.offsetX,
          y: initialStyle.offsetY,
          borderRadius: initialStyle.borderRadius,
        }}
        transition={{ duration: 0.5, type: "tween", ease: "easeInOut" }}
      >
      <div className="w-full h-screen relative bg-red-700 text-white overflow-auto min-h-screen p-5">
        {/* Header */}
        <div className="flex items-center justify-between mb-5">
          <button
            onClick={() => navigate(`/`)}
            className="text-xl font-bold flex items-center gap-1 pr-4"
          >
            <i className="ri-arrow-left-line" />
          </button>
          <div style={{ flexBasis: "40px" }} /> {/* placeholder for symmetry */}
        </div>
        <div className="fixed bottom-0 right-0 w-1/2 overflow-hidden z-0 pointer-events-none">
          <img
            className="w-full h-full object-cover"
            src={getImageUrl(apiUrl, 'assets/images/Element Extra Mile 2.png')}
            alt="attribute"
          />
        </div>
        <motion.div
          variants={pageVariants}
          initial="initial"
          animate="animate"
          exit="exit"
          transition={{ duration: 0.3, type: "tween", ease: "easeInOut" }}
        >
          <div className="justify-start mb-4">
            <span className="text-white text-xl font-normal">Our</span>
            <span className="text-white text-xl font-semibold"> <br /></span><span className="text-white text-2xl font-semibold">Social Media <br /></span>
            <span className="text-white text-xl font-normal">Presence</span>
          </div>

          {/* Swiper Latest Youtube */}
          <div className="px-2 py-1 origin-top-left bg-white rounded-full inline-flex justify-center items-center overflow-hidden mb-2">
            <div className="text-center justify-center text-red-700 text-xs font-extrabold">Youtube</div>
          </div>
          <div className="overflow-x-scroll whitespace-nowrap mb-4">
            <Swiper
              modules={[FreeMode]}
              spaceBetween={15}
              slidesPerView={1.25}
              followFinger={true}
              speed={600}
            >
              {latestYoutube.map((item, idx) => {
                return (
                  <SwiperSlide key={item.encrypted_id}>
                    <div
                      onClick={() => item.link && window.open(`https://www.youtube.com/watch?v=${item.link}`, '_blank')}
                      className="w-full aspect-[16/9] relative rounded-lg overflow-hidden cursor-pointer group"
                      aria-label="Open YouTube Video"
                    >
                      {/* YouTube Embed */}
                      {item.link ? (
                        <div className="absolute inset-0 pointer-events-none transition-transform group-hover:scale-[1.02]">
                          <YouTubePlayer key={idx} videoId={item.link} />
                        </div>
                      ) : (
                        <div className="absolute inset-0 flex items-center justify-center bg-orange-50 z-10">
                          <BannerLoader />
                        </div>
                      )}

                      {/* Optional hover overlay */}
                      <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition duration-300" />
                    </div>
                  </SwiperSlide>
                )
              }
              )}
            </Swiper>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <span className="bg-white text-red-700 font-semibold px-2 py-1 rounded-full text-xs">Instagram</span>
              <div className="mt-2 bg-white rounded overflow-hidden">
                {latestIG.map((item, index) => (
                    item.link ? <InstagramPlayer key={index} postId={item.link} /> : null
                ))}
              </div>
            </div>
            <div>
              <span className="bg-white text-red-700 font-semibold px-2 py-1 rounded-full text-xs">Tiktok</span>
              <div className="mt-2 bg-white rounded overflow-hidden">
                {latestTiktok.map((item, index) => (
                  item.link ? <TikTokPlayer key={index} videoId={item.link} /> : null
                ))}
              </div>
            </div>
          </div>
          {/* Footer */}
          <div className="mt-6 text-start space-y-3">
            <p className="text-sm">Never miss an <span className="font-bold">Update!</span></p>
            <div className="flex justify-start space-x-4">
              <a href="https://www.linkedin.com/company/kpn-corp" target="_blank" rel="noopener noreferrer">
                <i className="ri-linkedin-box-fill text-2xl"></i>
              </a>
              <a href="https://www.instagram.com/lifeatkpn" target="_blank" rel="noopener noreferrer">
                <i className="ri-instagram-line text-2xl"></i>
              </a>
              <a href="https://www.youtube.com/@lifeatKPN" target="_blank" rel="noopener noreferrer">
                <i className="ri-youtube-fill text-2xl"></i>
              </a>
              <a href="https://www.tiktok.com/@lifeatkpn" target="_blank" rel="noopener noreferrer">
                <i className="ri-tiktok-fill text-2xl"></i>
              </a>
            </div>
            <div className="text-xs flex justify-start gap-2">
              <span className="ring-1 ring-white px-2 py-1 rounded-full">KPN Corp</span>
              <span className="ring-1 ring-white px-2 py-1 rounded-full">@lifeatkpn</span>
            </div>
          </div>
        </motion.div>
      </div>
      </motion.div>
    </div>
  );
}

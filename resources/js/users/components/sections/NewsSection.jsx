import React, { useEffect, useState } from "react";
import { Swiper, SwiperSlide } from "swiper/react";
import "swiper/css";
import "swiper/css/bundle";
import { FreeMode } from "swiper/modules";
import BannerLoader from "../Loader/BannerLoader";
import { getImageUrl } from "../Helper/imagePath";
import { useNavigate } from 'react-router-dom';
import { useAuth } from "../context/AuthContext";
import { useApiUrl } from "../context/ApiContext";
import axios from "axios";
import { showAlert } from "../Helper/alertHelper";
import NewsSectionLoader from "../Loader/NewsSectionLoader";

export default () => {

  const navigate = useNavigate();
  const apiUrl = useApiUrl();
  const { token } = useAuth();
  const [latestNews, setLatestNews] = useState([]);
  const [isImageLoaded, setIsImageLoaded] = useState(false); // State to track image load
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    const fetchNews = async () => {
      try {
        const res = await axios.get(`${apiUrl}/api/news`, {
          headers: { Authorization: `Bearer ${token}` },
        });
  
        // Preprocess businessUnit
        const newsData = res.data.map((e) => ({
          ...e,
          businessUnit: Array.isArray(e.businessUnit)
            ? e.businessUnit.map((bu) => {
                try {
                  return JSON.parse(bu);
                } catch {
                  return bu;
                }
              })
            : [e.businessUnit],
        }));
  
        // Urutkan berdasarkan created_at (terbaru di atas)
        const sortedNews = [...newsData].sort((a, b) => new Date(b.publish_date) - new Date(a.publish_date));
  
        // Ambil 3 berita terbaru
        const latest = sortedNews.slice(0, 3);
  
        setLatestNews(latest);
      } catch (err) {
        showAlert({
          icon: "warning",
          title: "Connection Ended",
          text: "Unable to connect to the server. Please try again later.",
          timer: 2500,
          showConfirmButton: false,
        }).then(() => {
          console.log(err);          
        });
      } finally {
        setLoading(false);
      }
    };
  
    if (token) fetchNews();
  }, [apiUrl, token]);

  if (loading) {
    return <NewsSectionLoader />;
  }

  return (
    <div className="flex flex-col gap-2">
      {/* Header Section */}
      <div className="flex items-center justify-between">
        <div className="text-red-700 text-sm font-bold">News Update!</div>
        <div onClick={() => navigate(`/news`)} className="text-stone-600 text-xs font-medium cursor-pointer">Show All <i className="ri-arrow-right-line"></i></div>
      </div>

      {/* Swiper Container */}
      <div className="overflow-x-scroll whitespace-nowrap">
        <Swiper
          modules={[FreeMode]}
          spaceBetween={15}
          slidesPerView={2}
          followFinger={true}
          speed={600}
        >
          {/* Slides */}
          {latestNews.map((item) => {
            const newsDate = new Date(item.publish_date);
            const day = newsDate.toLocaleDateString("id-ID", {
              weekday: "long",
              day: "2-digit",
              month: "long",
              year: "numeric",
            });
            return (
              <SwiperSlide key={item.encrypted_id}>
                <div onClick={() => navigate(`/news/${item.encrypted_id}`)} className="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 aspect-[4/3] relative rounded-lg overflow-hidden">
                  {/* Container for Image + Loader */}
                  <div className="absolute inset-0 flex items-center justify-center">
                    {/* Lazy Loaded Image */}
                    <img
                      className={`w-full h-full object-cover transition-opacity duration-300 ${
                        isImageLoaded ? 'opacity-100' : 'opacity-0'}`}
                      src={getImageUrl(apiUrl, item.image)}
                      alt={item.title}
                      decoding="async"
                      loading="lazy"
                      onLoad={() => setIsImageLoaded(true)} // Trigger when image loads
                    />

                    {/* Preloader (only if image not loaded) */}
                    {!isImageLoaded && (
                      <div className="absolute inset-0 flex items-center justify-center bg-orange-50 z-10">
                        <BannerLoader />
                      </div>
                    )}
                  </div>

                  {/* Gradient Overlay */}
                  <div className="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent pointer-events-none"></div>

                  {/* Content */}
                  <div className="p-2 absolute bottom-0 left-0 right-0 text-white pointer-events-none z-10">
                    <div className="text-[10px] font-medium leading-[10px]">{day}</div>
                    <div className="text-xs font-semibold leading-3">{item.title}</div>
                  </div>
                </div>
              </SwiperSlide>
            );
          })}
        </Swiper>
      </div>
    </div>
  );
};
import React, { useEffect, useState, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import axios from "axios";

import { Swiper, SwiperSlide } from "swiper/react";
import "swiper/css";
import "swiper/css/bundle";
import { FreeMode } from "swiper/modules";

import BannerLoader from "../components/Loader/BannerLoader";
import NewsCard from "../components/Cards/NewsCard";

import { useApiUrl } from "../components/context/ApiContext";
import { showAlert } from "../components/Helper/alertHelper";
import { useAuth } from "../components/context/AuthContext";
import { getImageUrl } from "../components/Helper/imagePath";

import { motion } from "framer-motion";
import NewsLoader from "../components/Loader/NewsLoader";

const pageVariants = {
  initial: { opacity: 0, x: 0 },
  animate: { opacity: 1, x: 0 },
  exit: { opacity: 0, x: 0 },
};

const modalVariants = {
    hidden: { y: "-100%", opacity: 0 },
    visible: { y: "50px", opacity: 1 },
    exit: { y: "-100%", opacity: 0 },
};

export default function News() {
  const navigate = useNavigate();
  const apiUrl = useApiUrl();
  const { token } = useAuth();

  const [allNews, setNews] = useState([]);
  const [latestNews, setLatestNews] = useState([]);
  const [loading, setLoading] = useState(true);

  const [selectedDate, setSelectedDate] = useState(null);
  const [selectedBU, setSelectedBU] = useState("All BU");
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedCategory, setSelectedCategory] = useState("All Category");

  // Image loaded state per image id to avoid one global flag
  const [loadedImages, setLoadedImages] = useState({});

  const categories = useMemo(() => {
    const unique = Array.from(
      new Set(allNews.map(news => news.category).filter(Boolean))
    );
    return ["All Category", ...unique];
  }, [allNews]);
  

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
        const sortedNews = [...newsData].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  
        // Ambil 3 berita terbaru
        const latest = sortedNews.slice(0, 3);
  
        setNews(newsData);
        setLatestNews(latest);
      } catch (err) {
        showAlert({
          icon: "warning",
          title: "Connection Ended",
          text: "Unable to connect to the server. Please try again later.",
          timer: 2500,
          showConfirmButton: false,
        }).then(() => {
          window.location.href = "https://kpncorporation.darwinbox.com/";
        });
      } finally {
        setLoading(false);
      }
    };
  
    if (token) fetchNews();
  }, [apiUrl, token]);

  // Memoize filteredNews supaya tidak rerender berlebihan
  const filteredNews = useMemo(() => {
    return allNews.filter((news) => {
      const newsDate = new Date(news.date);
  
      const matchBU =
        selectedBU === "All BU" ||
        (news.businessUnit &&
          news.businessUnit.some((bu) =>
            Array.isArray(bu)
              ? bu.includes(selectedBU)
              : bu === selectedBU
          ));
  
      const matchDate =
        !selectedDate || newsDate.toDateString() === selectedDate.toDateString();
  
      const matchSearch =
        !searchQuery ||
        news.title?.toLowerCase().includes(searchQuery.toLowerCase());
  
      return matchBU && matchDate && matchSearch;
    });
  }, [allNews, selectedBU, selectedDate, searchQuery]);

  const handleImageLoad = (id) => {
    setLoadedImages((prev) => ({ ...prev, [id]: true }));
  };

  return (
    <>
      {loading ? (
        <NewsLoader />
      ) : (
        <>
          <div className="w-full h-screen relative bg-gradient-to-br from-stone-50 to-orange-200 overflow-auto min-h-screen p-5">
            {/* Header */}
            <div className="flex items-center justify-between mb-5">
              <button
                onClick={() => navigate(`/`)}
                className="text-red-700 text-xl font-bold flex items-center gap-1 pr-4"
              >
                <i className="ri-arrow-left-line" />
              </button>
              <div className="text-center text-red-700 text-lg font-bold flex-grow">
                News
              </div>
              <div style={{ flexBasis: "40px" }} /> {/* placeholder for symmetry */}
            </div>
            <motion.div
                variants={pageVariants}
                initial="initial"
                animate="animate"
                exit="exit"
                transition={{ duration: 0.3, ease: "easeInOut" }}
            >
            {/* Search + Filter button */}
            <div className="w-full p-2 bg-white rounded-lg inline-flex items-center gap-2 overflow-hidden mb-4">
              <div className="text-gray-400 text-lg">
                <i className="ri-search-line" />
              </div>
                <input
                    type="text"
                    placeholder="Search"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="flex-1 bg-transparent outline-none text-sm text-gray-700 placeholder-gray-400"
                />
              <div className="text-neutral-300">|</div>
              <button
                type="button"
                onClick={() => setIsModalOpen(true)}
                className="text-red-700 text-lg px-1"
              >
                <i className="ri-equalizer-line" />
              </button>
            </div>

            {/* Swiper Latest News */}
            <div className="flex items-center justify-between mb-1">
              <div className="text-red-700 text-xs font-bold">Latest News</div>
            </div>
            <div className="overflow-x-scroll whitespace-nowrap mb-4">
              <Swiper
                modules={[FreeMode]}
                spaceBetween={15}
                slidesPerView={2}
                followFinger={true}
                speed={600}
              >
                {latestNews.map((item) => {
                    const newsDate = new Date(item.date);
                    const day = newsDate.toLocaleDateString("id-ID", {
                      weekday: "long",
                      day: "2-digit",
                      month: "long",
                      year: "numeric",
                    });
                    return (
                    <SwiperSlide key={item.encrypted_id}>
                        <div onClick={() => navigate(`/news/${item.encrypted_id}`)} className="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 aspect-[4/3] relative rounded-lg overflow-hidden">
                        {/* Image + loader */}
                        <div className="absolute inset-0 flex items-center justify-center">
                            <img
                            className={`w-full h-full object-cover transition-opacity duration-300 ${
                                loadedImages[item.encrypted_id] ? "opacity-100" : "opacity-0"
                            }`}
                            src={getImageUrl(apiUrl, item.image)}
                            alt={item.title}
                            loading="lazy"
                            onLoad={() => handleImageLoad(item.encrypted_id)}
                            />
                            {!loadedImages[item.encrypted_id] && (
                            <div className="absolute inset-0 flex items-center justify-center bg-orange-50 z-10">
                                <BannerLoader />
                            </div>
                            )}
                        </div>

                        {/* Gradient */}
                        <div className="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent pointer-events-none" />

                        {/* Content */}
                        <div className="p-2 absolute bottom-0 left-0 right-0 text-white pointer-events-none z-10">
                            <div className="text-[10px] leading-[10px]">{day}</div>
                            <div className="text-xs font-medium leading-3">{item.title}</div>
                        </div>
                        </div>
                    </SwiperSlide>
                    )
                }
                )}
              </Swiper>
            </div>

            {/* Filter Buttons */}
            <div className="flex gap-2 mb-4 py-2 px-1 overflow-x-auto whitespace-nowrap no-scrollbar">
              {[
                "All BU",
                "KPN Corporation",
                "Property",
                "Cement",
                "Downstream",
                "Plantations",
              ].map((bu) => (
                <button
                  key={bu}
                  onClick={() => setSelectedBU(bu)}
                  className={`px-2 py-1 rounded-full ${
                    selectedBU === bu
                      ? "bg-red-700 text-white text-sm"
                      : "bg-transparent outline outline-1 text-sm outline-stone-400 text-gray-600"
                  }`}
                >
                  {bu}
                </button>
              ))}
            </div>

            {/* News List */}
            <div className="space-y-3">
              {filteredNews.map((news) => {
                const newsDate = new Date(news.date);
                const day = newsDate.toLocaleDateString("id-ID", {
                  weekday: "long",
                  day: "2-digit",
                  month: "long",
                  year: "numeric",
                });

                let businessUnit = "";

                try {
                    const raw = news.businessUnit;
                    const arr = JSON.parse(raw); // ubah string JSON jadi array
                    businessUnit = Array.isArray(arr) ? arr.join(", ") : String(arr);
                } catch (e) {
                    console.error("Invalid JSON in businessUnit:", e);
                    businessUnit = ""; // fallback kalau JSON parse gagal
                }

                const year = newsDate.getFullYear();

                const tags = [businessUnit, year, news.category].filter(Boolean);

                return (
                  <div
                    onClick={() => navigate(`/news/${news.encrypted_id}`)}
                    key={news.encrypted_id}
                    className="cursor-pointer"
                  >
                    <NewsCard
                      image={getImageUrl(apiUrl, news.image)}
                      date={day}
                      title={news.title}
                      tags={tags}
                    />
                  </div>
                );
              })}
            </div>
        </motion.div>
          </div>

          {/* Modal Filter */}
          {isModalOpen && (
            <>
            <motion.div
                key="backdrop"
                onClick={() => setIsModalOpen(false)}
                className="fixed inset-0 z-20 bg-black bg-opacity-80"
                initial={{ opacity: 0 }}
                animate={{ opacity: 0.5 }}
                exit={{ opacity: 0 }}
            />
            <motion.div
                key="modal"
                className="fixed left-0 right-0 z-30 flex justify-center pointer-events-none"
                style={{ top: 0 }}
                variants={modalVariants}
                initial="hidden"
                animate="visible"
                exit="exit"
                transition={{ type: "spring", stiffness: 200, damping: 30 }}
            >
                <div className="pointer-events-auto bg-white rounded-lg p-5 w-11/12 max-w-md shadow-lg relative">
                <h2 className="text-lg font-semibold text-red-700 mb-4">
                    Filters
                </h2>

                <div className="space-y-2">
                    <label className="block text-sm font-medium text-gray-700 mt-2">
                    Categories:
                    </label>
                    <select
                        className="w-full border border-gray-300 rounded px-3 py-1 text-sm"
                        value={selectedCategory}
                        onChange={(e) => setSelectedCategory(e.target.value)}
                        >
                        {categories.map((category) => (
                            <option key={category} value={category}>
                            {category}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="flex justify-end mt-5">
                    <button
                    className="px-4 py-1 bg-red-700 text-white rounded"
                    onClick={() => setIsModalOpen(false)}
                    >
                    Apply
                    </button>
                </div>

                <button
                    className="absolute top-2 right-2 text-gray-400 hover:text-red-600"
                    onClick={() => setIsModalOpen(false)}
                    aria-label="Close modal"
                >
                    <i className="ri-close-line text-xl" />
                </button>
                </div>
            </motion.div>
            </>
          )}
        </>
      )}
    </>
  );
}

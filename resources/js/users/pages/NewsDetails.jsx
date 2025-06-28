import React, { useEffect, useState, useRef, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useApiUrl } from '../components/context/ApiContext';
import { useAuth } from '../components/context/AuthContext';
import { getImageUrl } from '../components/Helper/imagePath';
import NewsLoader from '../components/Loader/NewsLoader';
import { motion } from "motion/react";
import parse from "html-react-parser";
import NewsInteraction from '../components/Helper/NewsInteraction';
import YouTubePlayer from '../components/Helper/youtubeHelper';
import { useNavigationDirection } from "../components/Context/NavigationProvider";

export default function NewsDetails({ onLike }) {
  const { id } = useParams();
  const apiUrl = useApiUrl();
  const { token } = useAuth();
  const navigate = useNavigate();
  const [skipExit, setSkipExit] = useState(false);
  const { direction } = useNavigationDirection();
  
  const pageVariants = {
    initial: { opacity: 0, x: "100%" },     // Masuk dari kanan
    animate: { opacity: 1, x: 0 },       // Diam di tengah
    exit: { opacity: 0, x: "100%" },       // Keluar ke kiri
  };
  
  const pageVariants2 = {
    initial: { opacity: 0, y: 50 },     // Masuk dari kanan
    animate: { opacity: 1, y: 0 },       // Diam di tengah
    exit: { opacity: 0, x: "100%" },       // Keluar ke kiri
  };  
  
  const [news, setNews] = useState(null);
  const [loading, setLoading] = useState(true);
  const likeFnRef = useRef(null);
  const lastTapRef = useRef(0);  

  
  useEffect(() => {
    likeFnRef.current = onLike;
    const fetchNews = async () => {
      try {
        const res = await axios.get(`${apiUrl}/api/news/${id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        setNews(res.data);
      } catch (error) {
        console.error(error);
      } finally {
        setLoading(false);
      }
    };
    
    fetchNews();
  }, [apiUrl, id, token, onLike]);
  
  const handleDoubleTap = useCallback(() => {
    const now = Date.now();

    if (now - lastTapRef.current < 300) {
      likeFnRef.current?.();
    }

    lastTapRef.current = now;
  }, []);

  
  if (loading) return (
    <div className="w-full h-screen relative bg-gradient-to-br from-stone-50 to-orange-200 overflow-auto min-h-screen">
      <motion.div
        variants={pageVariants}
        initial="initial"
        animate="animate"
        exit="exit"
        transition={{ duration: 0.3, ease: "easeInOut" }}
      >
        <NewsLoader />
      </motion.div>
    </div>
  ) 

  if (!news) {
    return (
      <div className="flex flex-col items-center justify-center h-screen bg-gradient-to-br from-stone-50 to-orange-200 p-5">
        <p className="text-red-700 text-xl font-semibold mb-4">News not found.</p>
        <button
          onClick={() => navigate('/')}
          className="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800"
        >
          Go Back Home
        </button>
      </div>
    );
  }

  const newsDate = new Date(news.publish_date);
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

  const hashtag = news?.hashtag || "";
  const tags = hashtag
  .split(',')
  .map(tag => tag.trim())
  .filter(tag => tag); // remove empty strings


  return (
    <div className="w-full h-screen relative bg-gradient-to-br from-stone-50 to-orange-200 overflow-auto min-h-screen p-5">
    {/* Header Section */}
    <div className="flex items-center justify-between mb-5">
        <div className="flex-1">
            <button
                onClick={() => window.history.back()}
                className="text-red-700 text-xl font-bold flex items-start gap-1 pr-4"
            >
                <i className="ri-arrow-left-line"></i>
            </button>
        </div>
        <div className="flex-2 text-center text-red-700 text-lg font-bold">News</div>
        <div className="flex-1" /> {/* Spacer to balance layout */}
    </div>
    <motion.div
        variants={pageVariants2}
        initial="initial"
        animate="animate"
        exit="exit"
        transition={{ duration: 0.3, type: "tween", ease: "easeOut" }}
    >
    <div className="flex flex-col justify-between flex-1 gap-1 mb-4">
        <div className="text-sm text-gray-700">{day}</div>
        <div className="text-lg font-semibold text-red-700">
            {news.title}
        </div>
        <div className="flex gap-1 mt-1 whitespace-nowrap">
            {tags.map((tag, idx) => (
                <span
                key={idx}
                className="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded-md inline-block"
                >
                {tag}
                </span>
            ))}
        </div>
    </div>
    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-3">
        <img
            src={getImageUrl(apiUrl, news.image)}
            alt={news.title}
            className="w-full aspect-[16/9] object-fill rounded"
        />
        {news.link && (
          <YouTubePlayer videoId={news.link} />
        )}
        <div onClick={handleDoubleTap} onTouchStart={handleDoubleTap} className="prose prose-sm leading-relaxed text-stone-800 max-w-none [&>p]:mb-4 [&>h1]:mb-8 [&>h2]:mb-6 [&>h3]:mb-4 [&>h4]:mb-4 [&>h1]:font-semibold [&>h2]:font-semibold [&>h3]:font-semibold [&>h4]:font-semibold [&>ul]:list-disc [&>ul]:pl-6 [&>ul]:mb-4 [&>li]:mb-1 pl-2 pr-1">
          {parse(news.content)}
        </div>
    </div>
    <div className="w-full inline-flex flex-col justify-center items-center gap-3 mb-2">
        <div className="justify-start text-red-700 text-sm font-bold font-['Montserrat'] leading-none">
          <p>Show your love if you liked this!</p>
        </div>
        <NewsInteraction
          newsIdEncrypted={news?.encrypted_id} // yang dikirim dari backend, misalnya via Crypt
          isLikedInitial={news?.news_likes} // bisa dari API backend
          triggerLikeExternally={(fn) => {
            likeFnRef.current = fn;
          }}
        />
    </div>
    </motion.div>
    </div>
  );
}

import React, { useEffect, useState, useRef, useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/Context/AuthContext';
import AppHeader from '../components/Layout/AppHeader';
import { getImageUrl } from '../components/Helper/imagePath';
import NewsLoader from '../components/Loader/NewsLoader';
import { motion } from "motion/react";
import parse from "html-react-parser";
import NewsInteraction from '../components/Helper/NewsInteraction';
import YouTubePlayer from '../components/Helper/youtubeHelper';
import { useNavigationDirection } from "../components/Context/NavigationProvider";
import AppShell from '../components/Layout/AppShell';

export default function NewsDetails({ onLike }) {
  const { id } = useParams();
  const apiUrl = useApiUrl();
  const { token } = useAuth();
  const navigate = useNavigate();
  const [skipExit, setSkipExit] = useState(false);
  const { direction } = useNavigationDirection();
  const { t, i18n } = useTranslation();
  
  const pageVariants = {
    initial: { opacity: 0, x: "100%" },     // Masuk dari kanan
    animate: { opacity: 1, x: 0 },       // Diam di tengah
    exit: { opacity: 0, x: "100%" },       // Keluar ke kiri
  };
  
  const pageVariants2 = {
    initial: { opacity: 0, x: "100%" },     // Masuk dari kanan
    animate: { opacity: 1, x: 0 },       // Diam di tengah
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
    <AppShell nav>
      <motion.div
        variants={pageVariants}
        initial="initial"
        animate="animate"
        exit="exit"
        transition={{ duration: 0.3, type: "tween", ease: "easeInOut" }}
      >
        <NewsLoader />
      </motion.div>
    </AppShell>
  ) 

  if (!news) {
    return (
      <div className="flex flex-col items-center justify-center app-surface app-bg p-5">
        <p className="text-red-700 text-xl font-semibold mb-4">{t('news.notFound')}</p>
        <button
          onClick={() => navigate('/')}
          className="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800"
        >
          {t('common.goBackHome')}
        </button>
      </div>
    );
  }

  const newsDate = new Date(news.publish_date);
  const day = newsDate.toLocaleDateString(i18n.resolvedLanguage === "id" ? "id-ID" : "en-US", {
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
    <AppShell nav>
    <AppHeader title={t('news.title')} backTo="/news" />
    <motion.div
        className="px-5 pt-4"
        variants={pageVariants2}
        initial="initial"
        animate="animate"
        exit="exit"
        transition={{ duration: 0.5, type: "tween", ease: "easeInOut" }}
    >
    <div className="flex flex-col justify-between flex-1 gap-1 mb-4">
        <div className="text-sm text-gray-700">{day}</div>
        <div className="text-lg font-semibold text-red-700">
            {news.title}
        </div>
        <div className="flex flex-wrap gap-2 mt-1">
            {tags.map((tag, idx) => (
                <span
                    key={idx}
                    className="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded-md"
                >
                    {tag}
                </span>
            ))}
        </div>
    </div>
    <div className="grid grid-cols-1 gap-6 mb-3 rail:grid-cols-[320px_minmax(0,1fr)] rail:items-start">
        <img
            src={getImageUrl(apiUrl, news.image)}
            alt={news.title}
            className="w-full aspect-[16/9] object-fill rounded"
        />
        <div className="flex flex-col gap-6">
        {news.link && (
          <YouTubePlayer videoId={news.link} />
        )}
        <div onClick={handleDoubleTap} onTouchStart={handleDoubleTap} className="prose prose-sm leading-relaxed text-stone-800 max-w-none [&>p]:mb-4 [&>h1]:mb-8 [&>h2]:mb-6 [&>h3]:mb-4 [&>h4]:mb-4 [&>h1]:font-semibold [&>h2]:font-semibold [&>h3]:font-semibold [&>h4]:font-semibold [&>ul]:list-disc [&>ul]:pl-6 [&>ul]:mb-4 [&>li]:mb-1 pl-2 pr-1">
          {parse(news.content)}
        </div>
        </div>
    </div>
    <div className="w-full inline-flex flex-col justify-center items-center gap-3 mb-2">
        <div className="justify-start text-red-700 text-sm font-bold leading-none">
          <p>{t('news.showLove')}</p>
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
    </AppShell>
  );
}

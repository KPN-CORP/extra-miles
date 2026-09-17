import React, { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { getImageUrl } from "../Helper/imagePath";
import { useNavigate } from 'react-router-dom';
import { useAuth } from "../Context/AuthContext";
import { useApiUrl } from "../Context/ApiContext";
import axios from "axios";
import { showAlert } from "../Helper/alertHelper";
import SectionHeader from "../Layout/SectionHeader";
import EmptyState from "../Layout/EmptyState";
import NewsSectionLoader from "../Loader/NewsSectionLoader";

export default () => {

  const navigate = useNavigate();
  const apiUrl = useApiUrl();
  const { token } = useAuth();
  const [latestNews, setLatestNews] = useState([]);
  // Keyed per article: one shared boolean made every slide wait on whichever
  // image happened to decode first.
  const [loadedImages, setLoadedImages] = useState({});
  const [loading, setLoading] = useState(true);
  const { t, i18n } = useTranslation();

  useEffect(() => {
    const fetchNews = async () => {
      try {
        // Server sorts by publish_date desc and returns only the 3 we render.
        const res = await axios.get(`${apiUrl}/api/news?limit=3`, {
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

        setLatestNews(newsData);
      } catch (err) {
        showAlert({
          icon: "warning",
          title: t('alerts.connectionEnded'),
          text: t('alerts.connectionEndedText'),
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

  // Settles the skeleton on success *and* failure -- without the error case a
  // broken image left the loader animating forever over the card.
  const settleImage = (id) =>
    setLoadedImages((prev) => (prev[id] ? prev : { ...prev, [id]: true }));

  if (loading) {
    return <NewsSectionLoader />;
  }

  return (
    <section className="flex flex-col gap-3">
      <SectionHeader
        title={t('home.newsUpdate')}
        actionLabel={t('common.showAll')}
        onAction={() => navigate('/news')}
      />

      {latestNews.length === 0 ? (
        <EmptyState icon="ri-newspaper-line" description={t('news.empty')} />
      ) : (
        // Baris geser dengan snap: kartu berikutnya sedikit terlihat sebagai
        // petunjuk bahwa daftar masih bisa digeser.
        <div className="snap-row no-scrollbar -mx-5 -my-1 px-5 py-1">
          {latestNews.map((item) => {
            const newsDate = new Date(item.publish_date);
            const day = newsDate.toLocaleDateString(i18n.resolvedLanguage === "id" ? "id-ID" : "en-US", {
              day: "2-digit",
              month: "short",
              year: "numeric",
            });

            return (
              <button
                key={item.encrypted_id}
                type="button"
                onClick={() => navigate(`/news/${item.encrypted_id}`)}
                className="tap w-[74%] max-w-[280px] text-left"
              >
                <div className="relative aspect-[16/10] rounded-2xl overflow-hidden shadow-card bg-stone-100">
                  <img
                    className={`w-full h-full object-cover transition-opacity duration-300 ${
                      loadedImages[item.encrypted_id] ? 'opacity-100' : 'opacity-0'}`}
                    src={getImageUrl(apiUrl, item.image)}
                    alt={item.title}
                    decoding="async"
                    loading="lazy"
                    onLoad={() => settleImage(item.encrypted_id)}
                    onError={() => settleImage(item.encrypted_id)}
                  />

                  {!loadedImages[item.encrypted_id] && (
                    <div className="absolute inset-0 skeleton" />
                  )}

                  <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent pointer-events-none" />

                  <div className="absolute inset-x-0 bottom-0 p-3 text-white pointer-events-none">
                    <span className="inline-block mb-1.5 px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-bold backdrop-blur-sm">
                      {day}
                    </span>
                    <p className="text-[15px] font-extrabold tracking-[-0.014em] leading-[1.28] clamp-2">{item.title}</p>
                  </div>
                </div>
              </button>
            );
          })}
        </div>
      )}
    </section>
  );
};

import React, { useEffect, useState } from "react"
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router-dom"
import axios from "axios"
import { useApiUrl } from "../context/ApiContext";
import LiveContent from "../../pages/LiveContent";
import { useAuth } from "../context/AuthContext";
import { showAlert } from "../Helper/alertHelper";
import {
  ACTIVITIES_KEY,
  MY_REGISTRATIONS_KEY,
  prefetchWellness,
} from "../Helper/wellnessCache";

export default () => {

  const navigate = useNavigate();
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const { token } = useAuth();
  const apiUrl = useApiUrl();
  const { t } = useTranslation();

  // AnimatePresence runs mode="wait", so the target page only mounts once this
  // one has finished animating out. Warming the wellness endpoints here lets the
  // request run during that animation instead of after it.
  const handleNavigate = (e, path, prefetchKey = null) => {
    if (prefetchKey) prefetchWellness(prefetchKey, apiUrl, token);

    const rect = e.currentTarget.getBoundingClientRect();
    const bounds = {
      top: rect.top,
      left: rect.left,
      width: rect.width,
      height: rect.height,
    };
    navigate(path, { state: { bounds } });
  };
  
  useEffect(() => {
    if (!token) {
      navigate("/")
    }
  }, [navigate, token])

  useEffect(() => {
    const fetchEvent = async () => {
        try {
            const res = await axios.get(`${apiUrl}/api/live-content`, {
                headers: {
                Authorization: `Bearer ${token}`,
                },
            });                    
            setData(res.data);
        } catch (err) {
            console.log(err);
            
        } finally {
            setLoading(false);
        }
    };
    fetchEvent();
}, []);

  const handleOffAir = async () => {
      await showAlert({
          title: t('menu.contentNotAvailable'),
          text: t('menu.contentNotAvailableText'),
          icon: "info",
          timer: 2500,
          showConfirmButton: false
      });
  };

return (
    <div className="self-stretch flex flex-col justify-start items-start gap-3">
        <div className="self-stretch inline-flex justify-center items-start gap-3">
          <button onClick={(e) => handleNavigate(e, "/event")} className="flex-1 min-w-fit w-fit p-3 bg-red-700 rounded-lg shadow-md flex justify-center items-center gap-2 text-white text-[10px] font-semibold">
            {t('menu.upcomingEvents')}
          </button>
          <button
            onClick={() => {
              if (data.content_link) {
                setIsModalOpen(true);
              } else {
                handleOffAir();
              }
            }}
            className={`flex-1 min-w-fit p-3 rounded-lg shadow-md flex justify-center items-center gap-2 text-[10px] font-semibold
              ${data.content_link ? 'bg-white text-red-700 ring-1 ring-red-700 ring-inset' : 'bg-red-700 text-white'}`}
          >
            <span className={`${data.content_link ? 'on-pulse' : 'off-pulse'} me-1`}></span>
            {data.content_link ? t('menu.liveNowActive') : t('menu.liveNow')}
          </button>
        </div>
        <div className="self-stretch inline-flex justify-center items-start gap-3">
          <button onClick={(e) => handleNavigate(e, "/survey")} className="flex-1 min-w-fit w-fit p-3 bg-red-700 rounded-lg shadow-md flex justify-center items-center gap-2 text-white text-[10px] font-semibold">
              {t('menu.yourVoiceMatters')}
          </button>
          <button onClick={(e) => handleNavigate(e, "/social")} className="flex-1 min-w-fit p-3 bg-red-700 rounded-lg shadow-md flex justify-center items-center gap-2 text-white text-[10px] font-semibold">
              {t('menu.socialMedia')}
          </button>
        </div>
        <div className="self-stretch inline-flex justify-center items-start gap-3">
          <button onClick={(e) => handleNavigate(e, "/wellness", ACTIVITIES_KEY)} className="flex-1 min-w-fit w-fit p-3 bg-red-700 rounded-lg shadow-md flex justify-center items-center gap-2 text-white text-[10px] font-semibold">
              {t('menu.wellness')}
          </button>
          <button onClick={(e) => handleNavigate(e, "/wellness/my-registrations", MY_REGISTRATIONS_KEY)} className="flex-1 min-w-fit p-3 bg-white text-red-700 ring-1 ring-red-700 ring-inset rounded-lg shadow-md flex justify-center items-center gap-2 text-[10px] font-semibold">
              {t('menu.myWellness')}
          </button>
        </div>
        <LiveContent isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} id={data.content_link}>
          <h2 className="text-lg font-semibold mb-4">{t('menu.liveEventInfo')}</h2>
          <button
            onClick={() => setIsModalOpen(false)}
            className="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
          >
            {t('common.close')}
          </button>
        </LiveContent>
    </div>
);
};
import React from "react";
import { useTranslation } from "react-i18next";
import LanguageToggle from "../components/Layout/LanguageToggle";
import AppHeader from "../components/Layout/AppHeader";
import AppShell from "../components/Layout/AppShell";
import { useLocation, useParams } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';

import 'react-calendar/dist/Calendar.css';
import '../../../css/calendar-custom.css';
import { useApiUrl } from "../components/Context/ApiContext";
import { showAlert } from "../components/Helper/alertHelper";
import { useAuth } from "../components/Context/AuthContext";
import { getImageUrl } from "../components/Helper/imagePath";
import SurveyForm from '../components/Forms/SurveyForm';
import BannerLoader from "../components/Loader/BannerLoader";
import SurveyLoader from "../components/Loader/SurveyLoader";
import { motion } from "motion/react";
import CountdownTimer from "../components/Helper/CountdownTimer";
import parse from "html-react-parser";
import { SSO_URL } from '../components/Helper/ssoRedirect';

const pageVariants = {
    // initial: { opacity: 0, x: 0 },     // Masuk dari kanan
    // animate: { opacity: 1, x: 0 },       // Diam di tengah
    // exit: { opacity: 0, x: 0 },       // Keluar ke kiri
    initial: { opacity: 0, y: 100 },     // Masuk dari kanan
    animate: { opacity: 1, y: 0 },       // Diam di tengah
    exit: { opacity: 0, y: 100 },       // Keluar ke kiri
};

export default function VoteList() {

    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [loadingBanner, setLoadingBanner] = useState(true);
    const apiUrl = useApiUrl();
    const { id } = useParams();
    const { token } = useAuth(); 
    const location = useLocation();
    const participate = location.state?.participated ?? false;  
    const [participated, setParticipated] = useState(participate);
    
    const [eventEnded, setEventEnded] = useState(false);
    const { t } = useTranslation();
      

    const [selectedItem, setSelectedItem] = useState(null);

    const handleChange = (index) => {
        setSelectedItem(index);
    };

    const handleVoting = async (index) => {
        await showAlert({
            title: t('survey.successTitle'),
            text: t('survey.successText'),
            icon: "success",
            timer: 5000,
            showConfirmButton: false
        });
    };

    useEffect(() => {
        const fetchEvent = async () => {
            try {
                const res = await axios.get(`${apiUrl}/api/survey-vote/${id}`, {
                    headers: {
                      Authorization: `Bearer ${token}`,
                    },
                });                    
                setData(res.data);
            } catch (err) {
                showAlert({
                    icon: 'warning',
                    title: t('alerts.connectionEnded'),
                    text: t('alerts.connectionEndedText'),
                    timer: 2500,
                    showConfirmButton: false,
                }).then(() => {
                    if (document.referrer) {
                    window.history.back();
                    } else {
                    window.location.href = SSO_URL;
                    }

                });
            } finally {
                setLoading(false);
            }
        };
        if(token) {
            fetchEvent();
        }
        const localStatus = localStorage.getItem(`voted-${id}`) ?? participate;
        setParticipated(localStatus);
    }, [token, id]);
    
    if (loading) {
        return (
            <div className="w-full relative app-surface bg-brand-700 overflow-auto">
                <SurveyLoader />
            </div>
        );
      }      

    if (!data) {
        // No event found after loading
        return (
          <div className="flex flex-col items-center justify-center app-surface bg-brand-700 p-5">
            <p className="text-white text-xl font-semibold mb-4">{t('survey.notFound')}</p>
            <button
              onClick={() => window.history.back()}
              className="px-4 py-2 text-white rounded"
              style={{ backgroundColor: '#DEBD69' }}
            >
              {t('common.goBackHome')}
            </button>
          </div>
        );
    }  

    const handleImageLoad = () => {
        setLoadingBanner(false);
    };
  
    return (
        <AppShell surface="brand">
            <AppHeader
                title={t('survey.ongoing')}
                variant="solid"
                backTo="/survey"
                trailing={<LanguageToggle variant="onRed" />}
            />
            <motion.div
                className="px-5 pt-4"
                variants={pageVariants}
                initial="initial"
                animate="animate"
                exit="exit"
                transition={{ duration: 0.3, ease: "easeInOut" }}
            >
                <div className="grid grid-cols-1 gap-6 rail:grid-cols-[320px_minmax(0,1fr)] rail:items-start">
                    {data.banner && (
                        <div className="w-full">
                            {loadingBanner && <BannerLoader className='w-full object-fill rounded-3xl' />}
                            <img
                            src={getImageUrl(apiUrl, data.banner)}
                            alt={data.title}
                            onLoad={handleImageLoad}
                            onError={handleImageLoad}
                            className={`w-full object-fill rounded-3xl border-2 border-white transition-opacity duration-500 ${
                                loadingBanner ? 'opacity-0' : 'opacity-100'
                            }`}
                            />
                        </div>
                    )}

                    <div className="flex flex-col gap-6">
                    <div className="flex-col flex w-full text-center justify-center text-white text-2xl font-bold gap-1">
                        <span className="text-white text-base font-medium">{t('survey.closesIn')}</span>
                        <CountdownTimer
                            endDateTime={`${data.end_date} ${data.time_end}`}
                            onEnd={() => setEventEnded(true)}
                        />
                    </div>
                    <div className="w-full text-justify justify-start">
                        <p className="text-white text-base font-semibold mb-2">{t('survey.greeting', { name: data.fullname })}</p>
                        { participated ? (
                            <p className="text-white text-sm font-normal">{t('survey.thanksParticipating')}
                            <span className="font-semibold"> {data.title}</span>{t('survey.surveySuffix')}</p>
                        ) : eventEnded ? (
                            <div className="text-white text-base font-semibold">
                                {t('survey.closed')}
                            </div>
                        ): (
                            <div className="prose prose-sm leading-relaxed text-white max-w-none [&>p]:mb-4 [&>h1]:mb-8 [&>h2]:mb-6 [&>h3]:mb-4 [&>h4]:mb-4 [&>h1]:font-semibold [&>h2]:font-semibold [&>h3]:font-semibold [&>h4]:font-semibold [&>ul]:list-disc [&>ul]:pl-6 [&>ul]:mb-4 [&>li]:mb-1 pl-2 pr-1">{t('survey.thanksAttending')} <span className="font-semibold">{data.title}</span> {parse(data.description)}</div>
                        )}
                    </div>
                    <div>
                        {/* Form */}
                        { participated || eventEnded ? (
                            <button onClick={() => window.history.back()} className="w-full flex flex-col text-center text-red-700 font-medium shadow-lg px-3 py-2 rounded-lg" style={{ backgroundColor: '#DEBD69' }}>{t('common.goBack')}</button>
                        ) : (
                            <SurveyForm participated={participated} setParticipated={setParticipated} />
                        )}
                    </div>
                    </div>
                </div>
        </motion.div>
        </AppShell>
    );
}
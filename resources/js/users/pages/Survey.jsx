import React from "react";
import { useTranslation } from "react-i18next";
import LanguageToggle from "../components/Layout/LanguageToggle";
import { useNavigate, useLocation } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';

import 'react-calendar/dist/Calendar.css';
import '../../../css/calendar-custom.css';
import AppShell from "../components/Layout/AppShell";
import AppHeader from "../components/Layout/AppHeader";
import { useApiUrl } from "../components/Context/ApiContext";
import { showAlert } from "../components/Helper/alertHelper";
import { useAuth } from "../components/Context/AuthContext";
import { dateTimeHelper } from "../components/Helper/dateTimeHelper";
import { getImageUrl } from "../components/Helper/imagePath";
import SurveyLoader from "../components/Loader/SurveyLoader";
import { motion } from "motion/react";
import { zoomFrom } from '../components/Helper/zoomTransition';

const pageVariants = {
    initial: { opacity: 0, x: 0 },     // Masuk dari kanan
    animate: { opacity: 1, x: 0 },       // Diam di tengah
    exit: { opacity: 0, x: 0 },       // Keluar ke kiri
};

const pageVariants2 = {
    initial: { opacity: 0, y: 100 },     // Masuk dari kanan
    animate: { opacity: 1, y: 0 },       // Diam di tengah
    exit: { opacity: 0, y: 100 },       // Keluar ke kiri
};

export default function Survey() {

    const navigate = useNavigate();
    const location = useLocation();
    const bounds = location.state?.bounds;

    // Diturunkan saat render, bukan state: tanpa bounds nilainya identitas,
    // jadi halaman tetap tampil (hanya tanpa animasi zoom dari tile).
    const initialStyle = zoomFrom(bounds);
  
    const [datas, setData] = useState([]);
    const [dataEventParticipant, setDataEventParticipant] = useState([]);
    const [loading, setLoading] = useState(true);
    const apiUrl = useApiUrl();
    const [selectedDate, setSelectedDate] = useState(new Date());
    const [selectedBU, setSelectedBU] = useState("All BU");
    const { token } = useAuth();
    const { t } = useTranslation();
    
    const mergedData = [
        ...new Map([
            ...datas.map(d => [d.encrypted_id, d]),
            ...dataEventParticipant.map(d => [d.encrypted_id, d])
        ]).values()
    ];

    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                const res = await axios.get(`${apiUrl}/api/survey-vote`, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });
            
                setData(
                    res.data
                        .filter(item =>
                            (item.event_id === null) &&
                            (
                                !item.event_participant || // null/undefined
                                (Array.isArray(item.event_participant) && item.event_participant.length === 0)
                            )
                        )
                        .map(item => {
                            let businessUnit = [];
                            try {
                                businessUnit = typeof item.businessUnit === 'string'
                                    ? JSON.parse(item.businessUnit)
                                    : Array.isArray(item.businessUnit)
                                        ? item.businessUnit
                                        : [];
                            } catch (e) {
                                businessUnit = [];
                            }
                
                            return {
                                ...item,
                                businessUnit,
                                event_participant: [] // dijamin kosong
                            };
                        })
                );                
            
                setDataEventParticipant(
                    res.data.map(item => {
                        let businessUnit = [];
                        try {
                            businessUnit = typeof item.businessUnit === 'string'
                                ? JSON.parse(item.businessUnit)
                                : Array.isArray(item.businessUnit)
                                    ? item.businessUnit
                                    : [];
                        } catch (e) {
                            businessUnit = [];
                        }

                        // const event_participant = Array.isArray(item.event_participant)
                        //     ? item.event_participant.filter(p => p.status === 'Registered')
                        //     : (item.event_participant ? [item.event_participant] : []).filter(p => p.status === 'Registered');
                
                        // Ambil semua event_participant tanpa filter status
                        const event_participant = Array.isArray(item.event_participant)
                            ? item.event_participant
                            : (item.event_participant ? [item.event_participant] : []);
                
                        return {
                            ...item,
                            businessUnit,
                            event_participant
                        };
                    }).filter(e => e.event_participant.length > 0) // hanya yang punya participant
                );                
            
            } catch (err) {
                const status = err.response?.status;
                const message = err.response?.data?.message;
            
                if (status === 400 && message === "Survey/Vote not found") {
                    console.log('Empty data: Survey/Vote not found');
                } else {
                    showAlert({
                        icon: 'warning',
                        title: t('alerts.connectionEnded'),
                        text: t('alerts.connectionEndedText'),
                        timer: 2500,
                        showConfirmButton: false,
                    });
                }
            
                console.log(err); // Optional: keep this for debugging other cases
            } finally {
                setLoading(false);
            }            
        };
        if(token) {
            fetchData();
        }

    }, [token, bounds]);
    

    if (!datas) {
        // No event found after loading
        return (
          <div className="flex flex-col items-center justify-center app-surface app-bg p-5">
            <p className="text-red-700 text-xl font-semibold mb-4">{t('survey.notFound')}</p>
            <button
              onClick={() => navigate('/')}
              className="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800"
            >
              {t('common.goBackHome')}
            </button>
          </div>
        );
    }  
  
    return (
        <AppShell nav>
        <AppHeader
            title={t('survey.yourVoiceMatters')}
            trailing={<LanguageToggle />}
        />
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
          className="w-full flex flex-col"
        >
        {
            <>
            <motion.div
            variants={pageVariants}
            initial="initial"
            animate="animate"
            exit="exit"
            transition={{ duration: 0.5, type: "tween", ease: "easeInOut" }}
            className="px-5 pt-4"
            >
                {/* Judul sudah ada di AppHeader, jadi blok ini menyisakan
                    penjelasan singkat dan ilustrasinya saja. */}
                <div className="flex items-center justify-between gap-3 bg-white rounded-2xl shadow-card p-3">
                    <p className="flex-1 text-stone-600 text-xs leading-relaxed">
                        {t('survey.subtitle')}
                    </p>
                    <div className="w-24 shrink-0 aspect-[4/3] rounded-xl overflow-hidden bg-stone-100">
                        <img
                            className="w-full h-full object-cover"
                            src={getImageUrl(apiUrl, 'assets/images/surveys/banner-survey-img.png')}
                            alt=""
                            aria-hidden="true"
                        />
                    </div>
                </div>
            </motion.div>
            <motion.div
            variants={pageVariants2}
            initial="initial"
            animate="animate"
            exit="exit"
            transition={{ duration: 0.6, ease: "easeInOut" }}
            className={`flex-1 w-full bg-red-700 rounded-t-3xl p-5 overflow-auto ${loading ? 'hidden' : ''}`}
            >
                <div className="grid gap-3 w-full rail:grid-cols-2">
                    {mergedData && mergedData.length > 0 ? (
                        mergedData.map((data, index) => {                                
                            const { daysUntil, daysUntilLabel } = dateTimeHelper(data);
                            const participated = Array.isArray(data.survey_participant) && data.survey_participant.length > 0;
                                                        
                            return (
                                <div onClick={() => navigate(`/${data.category}/${data.encrypted_id}`, {
                                    state: { participated }
                                })} key={data.encrypted_id} className={`w-full px-3 py-2 bg-white rounded-xl shadow-md flex justify-start items-center gap-3 overflow-hidden ${daysUntil === 'Ended' ? 'hidden' : ''}`}>
                                    <div className="relative overflow-hidden">
                                    <img
                                        className="w-10 h-12 object-cover"
                                        src={getImageUrl(apiUrl, data.icon)}
                                        alt=""
                                        />
                                    </div>
                                    <div className="flex-1 py-2 flex justify-start items-center overflow-hidden">
                                        <div className="self-stretch flex flex-col justify-start items-start gap-1">
                                            <div className="min-w-40 justify-start text-stone-700 text-sm font-semibold leading-none capitalize">
                                                {t(`survey.category.${data.category}`, { defaultValue: data.category })}: {data.title}
                                            </div>
                                            <div className="self-stretch flex flex-col justify-center items-start">
                                                <div className="self-stretch inline-flex justify-start items-center gap-4">
                                                    <div className="flex justify-start items-center gap-0.5">
                                                        <div className="justify-start text-stone-600 text-xs font-normal leading-none"><i className="ri-time-line me-1"></i>{t('survey.endsIn', { time: daysUntilLabel })}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {participated ? (
                                        <div className="px-2 py-1 rounded-full shadow-md flex justify-center items-center gap-2 overflow-hidden bg-white ring-1 ring-green-600">
                                            <i className="ri-check-double-line text-xl text-green-600"></i>
                                        </div>
                                        ) : (
                                        <button
                                            className="px-3 py-2 rounded-lg shadow-md flex justify-center items-center gap-2 overflow-hidden"
                                            style={{ backgroundColor: '#DEBD69' }}
                                        >
                                            <div className="text-white text-sm font-semibold leading-tight">{t('survey.joinIn')}</div>
                                        </button>
                                        )}
                                </div>
                            )
                        })
                        )
                     : (
                        <div className="w-full justify-center text-center text-white font-medium py-4">
                            {t('survey.none')}
                        </div>
                    )}
                </div>
            </motion.div>
            </>
        }
        </motion.div>
        </AppShell>
    );
}
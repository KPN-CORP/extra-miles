import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useParams, useNavigate, useLocation } from 'react-router-dom';
import axios from 'axios';
import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/Context/AuthContext';
import AppHeader from "../components/Layout/AppHeader";
import { showAlert } from '../components/Helper/alertHelper';
import PageLoader from '../components/Loader/PageLoader';
import EventLoader from '../components/Loader/EventLoader';
import { PuffLoader } from 'react-spinners';
import { dateTimeHelper } from '../components/Helper/dateTimeHelper';
import { getImageUrl } from '../components/Helper/imagePath';
import { motion } from "motion/react";
import parse from "html-react-parser";
import { zoomFrom } from '../components/Helper/zoomTransition';
import AppShell from '../components/Layout/AppShell';

const pageVariants = {
  initial: { opacity: 0, y: "50%" },     // Masuk dari kanan
  animate: { opacity: 1, y: 0 },       // Diam di tengah
  exit: { opacity: 0, y: "50%" },       // Keluar ke kiri
};

export default function EventDetails() {
  const { id } = useParams();
  const apiUrl = useApiUrl();
  const { token } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const bounds = location.state?.bounds;
  const [skipExit, setSkipExit] = useState(false);
  const { t } = useTranslation();

  // Diturunkan saat render, bukan state: tanpa bounds nilainya identitas,
  // jadi halaman tetap tampil (hanya tanpa animasi zoom dari tile).
  const initialStyle = zoomFrom(bounds);

  const [event, setEvent] = useState(null);
  const [loading, setLoading] = useState(true);
  const [eventParticipant, setEventParticipant] = useState(null);

  const hasRegistered = eventParticipant !== null;

  useEffect(() => {
    const fetchEvent = async () => {
      try {
        const regRes = await axios.get(`${apiUrl}/api/events/check-registration/${id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        setEventParticipant(typeof regRes.data.registered === 'object' ? regRes.data.registered : null);

        const eventRes = await axios.get(`${apiUrl}/api/events/${id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        setEvent(eventRes.data);
      } catch (error) {
        console.error(error);
      } finally {
        setLoading(false);
      }
    };

    fetchEvent();

  }, [apiUrl, id, token, bounds]);

  const confirmAlertConfig = {
    title: t('event.confirmTitle'),
    text: t('event.confirmText'),
    icon: "question",
    showCancelButton: true,
    confirmButtonText: t('event.confirmYes'),
    cancelButtonText: t('common.cancel')
  };

  const cancelAlertConfig = {
    title: t('event.cancelTitle'),
    text: t('event.cancelText'),
    icon: "warning",
    input: "textarea",
    inputPlaceholder: t('event.cancelPlaceholder'),
    inputAttributes: { 'aria-label': t('event.cancelReasonLabel') },
    showCancelButton: true,
    confirmButtonText: t('common.submit'),
    cancelButtonText: t('common.back')
  };
  
  const handleConfirm = async () => {
    const result = await showAlert(confirmAlertConfig);
    if (result.isConfirmed) {
      try {
        await axios.post(`${apiUrl}/api/event-confirmation`, {
          eventId: event.encrypted_id,
          status: 'confirm'
        }, { headers: { Authorization: `Bearer ${token}` } });
        
        await showAlert({
          title: t('event.confirmedTitle'),
          text: t('event.confirmedText'),
          icon: "success"
        });
        
        navigate('/');
      } catch {
        showAlert({
          title: t('alerts.error'),
          text: t('event.confirmFailed'),
          icon: "error"
        });
      }
    }
  };
  
  const handleCancel = async () => {
    const result = await showAlert(cancelAlertConfig);
    if (result.isConfirmed) {
      try {
        await axios.post(`${apiUrl}/api/event-confirmation`, {
          eventId: event.encrypted_id,
          status: 'cancel',
          messages: result.value
        }, { headers: { Authorization: `Bearer ${token}` } });
  
        await showAlert({
          title: t('event.canceledTitle'),
          text: t('event.canceledText'),
          icon: "info"
        });
  
        navigate('/');
      } catch {
        showAlert({
          title: t('alerts.error'),
          text: t('event.cancelFailed'),
          icon: "error"
        });
      }
    }
  };


  if (loading) return (
  <AppShell nav>
    <motion.div
      variants={pageVariants}
      initial="initial"
      animate="animate"
      exit="exit"
      transition={{ duration: 0.5, type: "tween" }}
    >
      <EventLoader />
    </motion.div>
  </AppShell>
  ) 

  if (!event) {
    return (
      <div className="flex flex-col items-center justify-center app-surface app-bg p-5">
        <p className="text-red-700 text-xl font-semibold mb-4">{t('event.notFound')}</p>
        <button
          onClick={() => navigate('/')}
          className="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800"
        >
          {t('common.goBackHome')}
        </button>
      </div>
    );
  }

  const { day, month, year, startTime, endTime, eventStatus, isClosed, isOngoing, closedRegistration, totalDay, endDay, endMonth, endYear } = dateTimeHelper(event);
  
  const formattedDate = totalDay > 1 ? `${day} ${month} ${year}, ${startTime} - ${endDay} ${endMonth} ${endYear}, ${endTime}` : `${day} ${month} ${year}`;

  const getStatusColor = () => {
    if (isClosed || closedRegistration) return { text: 'text-black', bg: 'bg-gray-100' };
    if (isOngoing) return { text: 'text-white', bg: 'bg-blue-500' };
    const status = eventParticipant?.status || event.status;
    switch (status) {
      case 'Waiting List':
      case 'Confirmation':
        return { text: 'text-white', bg: 'bg-yellow-400' };
      case 'Registered':
        return { text: 'text-white', bg: 'bg-green-600' };
      case 'Canceled':
        return { text: 'text-gray-600', bg: 'bg-gray-200' };
      case 'Open Registration':
        return { text: 'text-white', bg: 'bg-green-500' };
      case 'Full Booked':
        return { text: 'text-gray-600', bg: 'bg-gray-200' };
      default:
        return { text: 'text-black', bg: 'bg-gray-100' };
    }
  };

  const statusColor = getStatusColor();
  
  return (
  <AppShell nav>
    <AppHeader title={t('event.upcomingEvents')} backTo="/event" />
    <motion.div
      className="px-5 pt-4"
      variants={pageVariants}
      animate="animate"
      exit="exit"
      transition={{ duration: 0.3, type: "tween", ease: "easeOut" }}
    >
              <div className="grid grid-cols-1 gap-6 rail:grid-cols-[320px_minmax(0,1fr)] rail:items-start">
                  <img
                      src={getImageUrl(apiUrl, event.image)}
                      alt={event.title}
                      className="w-full object-fill rounded-lg"
                  />
                  <div className="flex flex-col gap-6">
                  <div className='flex flex-col gap-2'>
                      <div className="self-stretch inline-flex justify-start items-center gap-4">
                        <div data-color="primary" data-size="H6" data-type="normal" className="bg-white/0 inline-flex flex-col justify-center items-center">
                          <div className={`px-2 py-1 ${statusColor.bg} rounded inline-flex justify-center items-center overflow-hidden`}>
                            <div className={`text-center justify-center ${statusColor.text} text-[10px] font-medium  leading-[10px]`}>{(() => { const s = isClosed || isOngoing || closedRegistration ? eventStatus : (eventParticipant?.status ?? event.status); return t(`event.status.${s}`, { defaultValue: s }); })()}
                            </div>
                            {(event.status === "Ongoing" || isOngoing) && (
                                <div className="ms-1">
                                    <PuffLoader
                                    cssOverride={{
                                        margin: 'auto'
                                      }}
                                    color="#fff"
                                    size={10}
                                    speedMultiplier={1}
                                    />
                                </div>
                              )}
                          </div>
                        </div>
                      </div>
                      <div className="flex gap-2">
                      {(Array.isArray(event.businessUnit) ? event.businessUnit : [t('event.allBu')]).map((unit, i) => (
                          <div key={i} data-color="light" data-size="H6" data-type="normal" className="bg-white/0">
                              <div className="px-2 py-1 bg-zinc-300 rounded inline-flex justify-start items-center overflow-hidden">
                                  <div className="text-center justify-start text-stone-600 text-xs font-medium leading-3">{unit}</div>
                              </div>
                          </div>
                      ))}
                      </div>
                      <div className="self-stretch justify-start text-red-700 text-lg font-semibold">{event.title}</div>
                      <div className="self-stretch flex flex-col justify-start items-start gap-2">
                        <div className="self-stretch inline-flex justify-between items-center">
                          <div className="flex-1 flex justify-start items-center gap-1 text-stone-700">
                            <i className="ri-calendar-line"></i>
                            <div className="flex-1 justify-start text-sm font-normal leading-none">{formattedDate}</div>
                          </div>
                          {totalDay === 1 && (
                          <div className="flex-1 flex justify-start items-center gap-1 text-stone-700">
                            <i className="ri-time-line"></i>
                            <div className="flex-1 justify-start text-sm font-normal leading-none">{startTime} - {endTime}</div>
                          </div>
                          )}
                        </div>
                        {totalDay > 1 && (
                            <div className="self-stretch inline-flex justify-between items-center">
                              <div className="flex-1 flex justify-start items-center gap-1 text-stone-700">
                                <i className="ri-time-line"></i>
                                <div className="flex-1 justify-start text-sm font-normal leading-none">{t('date.day', { count: totalDay })}</div>
                              </div>
                            </div>
                        )}
                        <div className="self-stretch inline-flex justify-start items-center">
                          <div className="flex-1 flex justify-start items-center gap-1 text-stone-700">
                            <i className="ri-map-pin-line"></i>
                            <div className="flex-1 justify-start text-sm font-normal leading-none">{event.event_location}</div>
                          </div>
                          <div className="flex-1 flex justify-start items-center gap-1 text-stone-700">
                            <i className="ri-user-line"></i>
                            <div className="flex-1 justify-start text-sm font-normal leading-none">{event.quota}</div>
                          </div>
                        </div>
                      </div>
                  </div>
                  {event.event_participant?.[0]?.status !== 'Confirmation' && (
                    <div className="prose prose-sm leading-relaxed text-stone-800 max-w-none [&>p]:mb-4 [&>h1]:mb-8 [&>h2]:mb-6 [&>h3]:mb-4 [&>h4]:mb-4 [&>h1]:font-semibold [&>h2]:font-semibold [&>h3]:font-semibold [&>h4]:font-semibold [&>ul]:list-disc [&>ul]:pl-6 [&>ul]:mb-4 [&>li]:mb-1 mb-2">
                      {parse(event.description)}
                    </div>
                  )}
                  {(!hasRegistered && !closedRegistration && !isOngoing && event.status === "Open Registration") && (
                    <button
                      onClick={() => navigate(`/event-registration/${event.encrypted_id}`)}
                      className="self-stretch px-5 py-2.5 bg-red-700 rounded-lg shadow-md inline-flex justify-center items-center gap-2 overflow-hidden"
                      type="button"
                    >
                      <div className="justify-start text-white text-sm font-semibold leading-tight">
                        {t('event.registerNow')}
                      </div>
                    </button>
                  )}
                  {hasRegistered && event.event_participant?.[0]?.status === 'Confirmation' && (
                    <>
                    <div className=''>
                      <p className="text-stone-700 font-medium">{t('event.invitePrompt', { title: event.title })}</p>
                    </div>
                    <div className='inline-flex justify-start items-center gap-4'>
                      <button
                        onClick={() => handleCancel(event)}
                        className={`flex-1 flex px-3 py-2 rounded-lg shadow-md items-center justify-center bg-stone-400 text-white text-sm font-semibold`}
                      >
                        {t('event.cannotJoin')}
                      </button>
                      <button
                        onClick={() => handleConfirm(event)}
                        className={`flex-1 flex px-3 py-2 rounded-lg shadow-md items-center justify-center bg-red-700 text-white text-sm font-semibold`}
                      >
                        {t('event.canJoin')}
                      </button>
                    </div>
                    </>
                  )}
              </div>
              </div>
        </motion.div>
  </AppShell>
  );
}

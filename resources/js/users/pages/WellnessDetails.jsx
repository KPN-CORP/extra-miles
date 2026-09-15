import React, { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import LanguageToggle from '../components/Layout/LanguageToggle';
import AppHeader from '../components/Layout/AppHeader';
import { useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import parse from 'html-react-parser';
import { motion } from 'motion/react';

import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/Context/AuthContext';
import { showAlert } from '../components/Helper/alertHelper';
import CardLoader from '../components/Loader/CardLoader';
import { formatSession, seatLabel, statusStyle } from '../components/Helper/wellnessHelper';
import {
    ACTIVITIES_KEY,
    activityKey,
    getCached,
    invalidateWellness,
    loadWellness,
    MY_REGISTRATIONS_KEY,
} from '../components/Helper/wellnessCache';

const pageVariants = {
    initial: { opacity: 0, x: 0 },
    animate: { opacity: 1, x: 0 },
    exit: { opacity: 0, x: 0 },
};

export default function WellnessDetails() {
    const { id } = useParams();
    const navigate = useNavigate();
    const apiUrl = useApiUrl();
    const { token } = useAuth();
    const { t } = useTranslation();

    // The list page starts this request on tap, so by the time we mount it is
    // usually already in flight -- or cached, in which case we skip the skeleton.
    const cacheKey = activityKey(id);
    const cached = getCached(cacheKey);
    const [activity, setActivity] = useState(cached ?? null);
    const [loading, setLoading] = useState(cached === undefined);
    const [submitting, setSubmitting] = useState(null);

    const fetchActivity = useCallback(async ({ force = false } = {}) => {
        try {
            setActivity(await loadWellness(cacheKey, apiUrl, token, { force }));
        } catch (err) {
            // Only bounce out when there is nothing on screen to fall back to.
            if (getCached(cacheKey) !== undefined) return;

            showAlert({
                icon: 'warning',
                title: t('wellness.notAvailable'),
                text: err.response?.data?.error || t('wellness.activityLoadFailed'),
                timer: 2500,
                showConfirmButton: false,
            }).then(() => navigate('/wellness'));
        } finally {
            setLoading(false);
        }
    }, [apiUrl, cacheKey, token, navigate, t]);

    useEffect(() => {
        fetchActivity();
    }, [fetchActivity]);

    // Registering or cancelling moves seat counts on the list and adds a row to
    // My Wellness, so those cached payloads have to go with it.
    const refreshAfterWrite = () => {
        invalidateWellness([ACTIVITIES_KEY, MY_REGISTRATIONS_KEY]);
        fetchActivity({ force: true });
    };

    const handleRegister = async (schedule) => {
        const when = formatSession(schedule);

        const confirmed = await showAlert({
            icon: schedule.is_full ? 'warning' : 'question',
            title: schedule.is_full
                ? t('wellness.registerConfirm.fullTitle')
                : t('wellness.registerConfirm.title'),
            html: schedule.is_full
                ? t('wellness.registerConfirm.fullHtml', { when: when.full })
                : t('wellness.registerConfirm.html', {
                    when: when.full,
                    location: schedule.location ? `<br/>${schedule.location}` : '',
                }),
            showCancelButton: true,
            confirmButtonText: schedule.is_full ? t('wellness.joinWaitlist') : t('wellness.register'),
            cancelButtonText: t('wellness.registerConfirm.notNow'),
        });

        if (!confirmed.isConfirmed) return;

        setSubmitting(schedule.id);

        try {
            const res = await axios.post(
                `${apiUrl}/api/wellness/registrations`,
                { schedule_id: schedule.id },
                { headers: { Authorization: `Bearer ${token}` } }
            );

            await showAlert({
                icon: 'success',
                title: t('wellness.registerConfirm.doneTitle'),
                text: res.data.message,
                timer: 2600,
                showConfirmButton: false,
            });

            refreshAfterWrite();
        } catch (err) {
            showAlert({
                icon: 'error',
                title: t('wellness.registerConfirm.failedTitle'),
                text: err.response?.data?.error || t('alerts.genericRetry'),
            });
        } finally {
            setSubmitting(null);
        }
    };

    const handleCancel = async (schedule) => {
        const confirmed = await showAlert({
            icon: 'warning',
            title: t('wellness.cancelConfirm.title'),
            text: t('wellness.cancelConfirm.text'),
            showCancelButton: true,
            confirmButtonText: t('wellness.cancelConfirm.yes'),
            cancelButtonText: t('wellness.cancelConfirm.no'),
        });

        if (!confirmed.isConfirmed) return;

        setSubmitting(schedule.id);

        try {
            await axios.post(
                `${apiUrl}/api/wellness/registrations/cancel`,
                { registration_id: schedule.my_registration_id },
                { headers: { Authorization: `Bearer ${token}` } }
            );

            await showAlert({
                icon: 'success',
                title: t('wellness.cancelConfirm.doneTitle'),
                text: t('wellness.cancelConfirm.doneText'),
                timer: 2200,
                showConfirmButton: false,
            });

            refreshAfterWrite();
        } catch (err) {
            showAlert({
                icon: 'error',
                title: t('wellness.cancelConfirm.failedTitle'),
                text: err.response?.data?.error || t('alerts.genericRetry'),
            });
        } finally {
            setSubmitting(null);
        }
    };

    const header = (title) => (
        <AppHeader
            title={title}
            backTo="/wellness"
            trailing={<LanguageToggle />}
        />
    );

    // Only the body waits -- the page chrome is real from the first frame. A
    // full-screen splash here read as if the app were relaunching.
    if (loading) {
        return (
            <motion.div
                variants={pageVariants}
                initial="initial"
                animate="animate"
                exit="exit"
                transition={{ duration: 0.25 }}
                className="w-full app-surface app-bg overflow-auto"
            >
                {header(t('wellness.title'))}
                <div className="app-container px-5 pt-4 pb-10 flex flex-col gap-4">
                    <CardLoader />
                    <CardLoader />
                </div>
            </motion.div>
        );
    }

    if (!activity) return null;

    return (
        <motion.div
            variants={pageVariants}
            initial="initial"
            animate="animate"
            exit="exit"
            transition={{ duration: 0.25 }}
            className="w-full app-surface app-bg overflow-auto"
        >
            {header(activity.name)}

            <div className="app-container px-5 pt-4 pb-10 flex flex-col gap-4">
                {activity.image && (
                    <img
                        src={`${apiUrl}/images/${activity.image}`}
                        alt={activity.name}
                        className="w-full h-40 object-cover rounded-xl shadow-sm"
                    />
                )}

                <div className="bg-white rounded-xl shadow-sm p-4">
                    {activity.type && (
                        <span className="inline-block px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-[10px] font-semibold mb-2">
                            {activity.type}
                        </span>
                    )}
                    <h1 className="text-stone-800 text-base font-semibold">{activity.name}</h1>
                    {activity.description && (
                        // Admin-authored rich text from CKEditor, same as News.
                        <div className="mt-2 text-stone-600 text-xs leading-relaxed wellness-richtext">
                            {parse(activity.description)}
                        </div>
                    )}
                </div>

                <div className="text-stone-700 text-xs font-semibold">{t('wellness.availableSessions')}</div>

                {activity.schedules.length === 0 ? (
                    <div className="bg-white rounded-xl p-6 text-center shadow-sm">
                        <p className="text-stone-500 text-xs">{t('wellness.noSessions')}</p>
                    </div>
                ) : (
                    activity.schedules.map((schedule) => {
                        const when = formatSession(schedule);
                        const busy = submitting === schedule.id;
                        const registered = Boolean(schedule.my_status);
                        const canCancel = registered
                            && ['pending', 'approved', 'waitlisted'].includes(schedule.my_status);

                        return (
                            <div key={schedule.id} className="bg-white rounded-xl shadow-sm p-3">
                                <div className="flex gap-3">
                                    <div className="w-14 shrink-0 rounded-lg bg-red-700 text-white flex flex-col items-center justify-center py-2">
                                        <span className="text-lg font-bold leading-none">{when.day}</span>
                                        <span className="text-[10px] uppercase">{when.month}</span>
                                    </div>

                                    <div className="flex-1 min-w-0 text-[11px] text-stone-600 flex flex-col gap-0.5">
                                        <span className="text-stone-800 font-semibold text-xs">{when.weekday}</span>
                                        <span><i className="ri-time-line me-1"></i>{when.time}</span>
                                        {schedule.location && (
                                            <span className="truncate"><i className="ri-map-pin-line me-1"></i>{schedule.location}</span>
                                        )}
                                        <span><i className="ri-group-line me-1"></i>{seatLabel(schedule)}</span>
                                    </div>
                                </div>

                                <div className="mt-3 flex items-center gap-2">
                                    {registered && (
                                        <span className={`px-2 py-1 rounded-full text-[10px] font-semibold ${statusStyle(schedule.my_status)}`}>
                                            {schedule.my_status_label}
                                        </span>
                                    )}

                                    <div className="flex-1"></div>

                                    {canCancel ? (
                                        <button
                                            disabled={busy}
                                            onClick={() => handleCancel(schedule)}
                                            className="px-3 py-1.5 rounded-lg text-[10px] font-semibold bg-white text-red-700 ring-1 ring-red-700 ring-inset disabled:opacity-50"
                                        >
                                            {busy ? t('common.pleaseWait') : t('wellness.cancel')}
                                        </button>
                                    ) : !schedule.registration_open ? (
                                        <span className="text-[10px] text-stone-400">{t('wellness.registrationClosed')}</span>
                                    ) : (
                                        <button
                                            disabled={busy}
                                            onClick={() => handleRegister(schedule)}
                                            className="px-3 py-1.5 rounded-lg text-[10px] font-semibold bg-red-700 text-white shadow-sm disabled:opacity-50"
                                        >
                                            {busy ? t('common.pleaseWait') : schedule.is_full ? t('wellness.joinWaitlist') : t('wellness.register')}
                                        </button>
                                    )}
                                </div>
                            </div>
                        );
                    })
                )}
            </div>
        </motion.div>
    );
}

import React, { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import LanguageToggle from '../components/Layout/LanguageToggle';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

import AppShell from '../components/Layout/AppShell';
import AppHeader from '../components/Layout/AppHeader';
import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/Context/AuthContext';
import { showAlert } from '../components/Helper/alertHelper';
import CardLoader from '../components/Loader/CardLoader';
import WellnessQrScannerModal from '../components/Helper/WellnessQrScannerModal';
import { formatSession, statusStyle } from '../components/Helper/wellnessHelper';
import {
    ACTIVITIES_KEY,
    getCached,
    invalidateWellness,
    loadWellness,
    MY_REGISTRATIONS_KEY,
} from '../components/Helper/wellnessCache';

export default function MyWellness() {
    const navigate = useNavigate();
    const apiUrl = useApiUrl();
    const { token } = useAuth();
    const { t } = useTranslation();

    // Seeded from the cache so a return trip paints straight away and only
    // revalidates behind the content.
    const cached = getCached(MY_REGISTRATIONS_KEY);
    const [registrations, setRegistrations] = useState(cached ?? []);
    const [loading, setLoading] = useState(cached === undefined);
    const [busyId, setBusyId] = useState(null);
    const [scannerOpen, setScannerOpen] = useState(false);

    const fetchRegistrations = useCallback(async ({ force = false } = {}) => {
        try {
            setRegistrations(await loadWellness(MY_REGISTRATIONS_KEY, apiUrl, token, { force }));
        } catch (err) {
            // Keep whatever is already on screen rather than alerting over it.
            if (getCached(MY_REGISTRATIONS_KEY) !== undefined) return;

            showAlert({
                icon: 'warning',
                title: t('alerts.connectionEnded'),
                text: t('wellness.loadRegistrationsFailed'),
                timer: 2500,
                showConfirmButton: false,
            });
        } finally {
            setLoading(false);
        }
    }, [apiUrl, token, t]);

    useEffect(() => {
        fetchRegistrations();
    }, [fetchRegistrations]);

    // Cancelling or checking in frees a seat, which the activity list shows.
    const refreshAfterWrite = () => {
        invalidateWellness(ACTIVITIES_KEY);
        return fetchRegistrations({ force: true });
    };

    const handleCancel = async (registration) => {
        const confirmed = await showAlert({
            icon: 'warning',
            title: t('wellness.cancelConfirm.title'),
            text: t('wellness.cancelConfirm.text'),
            showCancelButton: true,
            confirmButtonText: t('wellness.cancelConfirm.yes'),
            cancelButtonText: t('wellness.cancelConfirm.no'),
        });

        if (!confirmed.isConfirmed) return;

        setBusyId(registration.id);

        try {
            await axios.post(
                `${apiUrl}/api/wellness/registrations/cancel`,
                { registration_id: registration.id },
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
            setBusyId(null);
        }
    };

    // Feedback is only offered on sessions the employee attended and that have
    // finished -- the backend decides that, `can_submit_feedback` just mirrors it.
    // Sending it again overwrites what they wrote before.
    const handleFeedback = async (registration) => {
        const existing = registration.feedback?.message ?? '';

        const result = await showAlert({
            icon: 'question',
            title: existing ? t('wellness.feedback.editTitle') : t('wellness.feedback.title'),
            text: t('wellness.feedback.text'),
            input: 'textarea',
            inputValue: existing,
            inputPlaceholder: t('wellness.feedback.placeholder'),
            inputAttributes: { maxlength: 2000 },
            showCancelButton: true,
            confirmButtonText: t('wellness.feedback.send'),
            cancelButtonText: t('wellness.feedback.notNow'),
            inputValidator: (value) => (value && value.trim() ? undefined : t('wellness.feedback.required')),
        });

        if (!result.isConfirmed) return;

        setBusyId(registration.id);

        try {
            await axios.post(
                `${apiUrl}/api/wellness/feedback`,
                { registration_id: registration.id, message: result.value.trim() },
                { headers: { Authorization: `Bearer ${token}` } }
            );

            await showAlert({
                icon: 'success',
                title: t('wellness.feedback.doneTitle'),
                text: t('wellness.feedback.doneText'),
                timer: 2200,
                showConfirmButton: false,
            });

            // No seat changed hands, so only this list needs refreshing.
            fetchRegistrations({ force: true });
        } catch (err) {
            showAlert({
                icon: 'error',
                title: t('wellness.feedback.failedTitle'),
                text: err.response?.data?.error || t('alerts.genericRetry'),
            });
        } finally {
            setBusyId(null);
        }
    };

    const checkInAvailable = registrations.some((r) => r.can_check_in);

    return (
        <AppShell nav>
            <AppHeader
                title={t('wellness.myTitle')}
                trailing={<LanguageToggle />}
            />

            <div className="px-5 pt-4 flex flex-col gap-4">
                {checkInAvailable && (
                    <button
                        onClick={() => setScannerOpen(true)}
                        className="tap w-full p-3 bg-brand-700 rounded-2xl shadow-float text-white text-xs font-semibold flex items-center justify-center gap-2"
                    >
                        <i className="ri-qr-scan-2-line"></i> {t('wellness.scanToCheckIn')}
                    </button>
                )}

                {loading ? (
                    <>
                        <CardLoader />
                        <CardLoader />
                    </>
                ) : registrations.length === 0 ? (
                    <div className="bg-white rounded-xl p-6 text-center shadow-sm">
                        <i className="ri-calendar-line text-3xl text-stone-300"></i>
                        <p className="mt-2 text-stone-500 text-xs">{t('wellness.noRegistrations')}</p>
                        <button
                            onClick={() => navigate('/wellness')}
                            className="mt-3 px-4 py-2 rounded-lg bg-red-700 text-white text-[10px] font-semibold"
                        >
                            {t('wellness.browseActivities')}
                        </button>
                    </div>
                ) : (
                    registrations.map((registration) => {
                        const when = formatSession(registration.schedule);
                        const busy = busyId === registration.id;

                        return (
                            <div key={registration.id} className="bg-white rounded-xl shadow-sm p-3">
                                <div className="flex gap-3">
                                    <div className="w-14 shrink-0 rounded-lg bg-red-700 text-white flex flex-col items-center justify-center py-2">
                                        <span className="text-lg font-bold leading-none">{when.day}</span>
                                        <span className="text-[10px] uppercase">{when.month}</span>
                                    </div>

                                    <div className="flex-1 min-w-0">
                                        <div className="text-stone-800 text-sm font-semibold truncate">
                                            {registration.activity?.name}
                                        </div>
                                        {registration.activity?.type && (
                                            <div className="text-[10px] text-red-700 font-medium">
                                                {registration.activity.type}
                                            </div>
                                        )}
                                        <div className="mt-1 text-[10px] text-stone-500 flex flex-col gap-0.5">
                                            <span><i className="ri-time-line me-1"></i>{when.time}</span>
                                            {registration.schedule?.location && (
                                                <span className="truncate">
                                                    <i className="ri-map-pin-line me-1"></i>{registration.schedule.location}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-3 flex items-center gap-2 flex-wrap">
                                    <span className={`px-2 py-1 rounded-full text-[10px] font-semibold ${statusStyle(registration.status)}`}>
                                        {registration.status_label}
                                    </span>

                                    {registration.attended_at && (
                                        <span className="px-2 py-1 rounded-full text-[10px] font-semibold bg-green-100 text-green-700">
                                            <i className="ri-check-double-line me-1"></i>{t('wellness.attended')}
                                        </span>
                                    )}

                                    <div className="flex-1"></div>

                                    {registration.can_check_in && (
                                        <button
                                            onClick={() => setScannerOpen(true)}
                                            className="px-3 py-1.5 rounded-lg text-[10px] font-semibold bg-red-700 text-white"
                                        >
                                            <i className="ri-qr-scan-2-line me-1"></i>{t('wellness.checkIn')}
                                        </button>
                                    )}

                                    {registration.can_submit_feedback && (
                                        <button
                                            disabled={busy}
                                            onClick={() => handleFeedback(registration)}
                                            className="px-3 py-1.5 rounded-lg text-[10px] font-semibold bg-white text-red-700 ring-1 ring-red-700 ring-inset disabled:opacity-50"
                                        >
                                            <i className="ri-chat-1-line me-1"></i>
                                            {registration.feedback ? t('wellness.feedback.edit') : t('wellness.feedback.give')}
                                        </button>
                                    )}

                                    {registration.can_cancel && !registration.attended_at && (
                                        <button
                                            disabled={busy}
                                            onClick={() => handleCancel(registration)}
                                            className="px-3 py-1.5 rounded-lg text-[10px] font-semibold bg-white text-red-700 ring-1 ring-red-700 ring-inset disabled:opacity-50"
                                        >
                                            {busy ? t('common.pleaseWait') : t('wellness.cancel')}
                                        </button>
                                    )}
                                </div>

                                {registration.feedback && (
                                    <div className="mt-2 rounded-lg bg-stone-50 p-2">
                                        <div className="text-[10px] font-semibold text-stone-600">
                                            <i className="ri-chat-quote-line me-1"></i>{t('wellness.feedback.yours')}
                                        </div>
                                        <p className="mt-0.5 text-[10px] text-stone-500 leading-relaxed whitespace-pre-line">
                                            {registration.feedback.message}
                                        </p>
                                    </div>
                                )}

                                {registration.status === 'waitlisted' && (
                                    <p className="mt-2 text-[10px] text-stone-500 leading-relaxed">
                                        {t('wellness.waitlistNote')}
                                    </p>
                                )}
                            </div>
                        );
                    })
                )}
            </div>

            <WellnessQrScannerModal
                isOpen={scannerOpen}
                onClose={() => setScannerOpen(false)}
                onScanSuccess={refreshAfterWrite}
            />
        </AppShell>
    );
}

import React, { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import LanguageToggle from '../components/Layout/LanguageToggle';
import AppHeader from '../components/Layout/AppHeader';
import { useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import parse from 'html-react-parser';

import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/Context/AuthContext';
import { showAlert } from '../components/Helper/alertHelper';
import CardLoader from '../components/Loader/CardLoader';
import { formatDateTime, formatSession, seatLabel, wellnessError } from '../components/Helper/wellnessHelper';
import { ActionButton, ActionRow, CardNote, DateBadge, MetaLine, StatusPill } from '../components/Cards/WellnessSession';
import { getImageUrl } from '../components/Helper/imagePath';
import AppShell from '../components/Layout/AppShell';
import {
    ACTIVITIES_KEY,
    activityKey,
    getCached,
    invalidateWellness,
    loadWellness,
    MY_REGISTRATIONS_KEY,
} from '../components/Helper/wellnessCache';

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
                text: wellnessError(err, 'wellness.activityLoadFailed'),
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
                text: t(`wellness.registerDone.${res.data.status}`, { defaultValue: t('wellness.registerConfirm.doneTitle') }),
                timer: 2600,
                showConfirmButton: false,
            });

            refreshAfterWrite();
        } catch (err) {
            showAlert({
                icon: 'error',
                title: t('wellness.registerConfirm.failedTitle'),
                text: wellnessError(err),
            });
        } finally {
            setSubmitting(null);
        }
    };

    const handleConfirm = async (schedule) => {
        const confirmed = await showAlert({
            icon: 'question',
            title: t('wellness.confirmSeat.title'),
            text: t('wellness.confirmSeat.text'),
            showCancelButton: true,
            confirmButtonText: t('wellness.confirmSeat.yes'),
            cancelButtonText: t('wellness.confirmSeat.no'),
        });

        if (!confirmed.isConfirmed) return;

        setSubmitting(schedule.id);

        try {
            await axios.post(
                `${apiUrl}/api/wellness/registrations/confirm`,
                { registration_id: schedule.my_registration_id },
                { headers: { Authorization: `Bearer ${token}` } }
            );

            await showAlert({
                icon: 'success',
                title: t('wellness.confirmSeat.doneTitle'),
                text: t('wellness.confirmSeat.doneText'),
                timer: 2200,
                showConfirmButton: false,
            });

            refreshAfterWrite();
        } catch (err) {
            showAlert({
                icon: 'error',
                title: t('wellness.confirmSeat.failedTitle'),
                text: wellnessError(err),
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
                text: wellnessError(err),
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
            <AppShell nav>
                {header(t('wellness.title'))}
                <div className="px-5 pt-4 flex flex-col gap-4">
                    <CardLoader />
                    <CardLoader />
                </div>
            </AppShell>
        );
    }

    if (!activity) return null;

    return (
        <AppShell nav>
            {header(activity.name)}

            <div className="px-5 pt-4 flex flex-col gap-4">
                {activity.image && (
                    <img
                        src={getImageUrl(apiUrl, activity.image)}
                        alt={activity.name}
                        className="w-full h-48 object-cover rounded-2xl shadow-card"
                    />
                )}

                <div className="bg-white rounded-2xl shadow-card p-4">
                    {activity.type && (
                        <span className="inline-block px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 text-[12px] font-bold mb-2">
                            {activity.type}
                        </span>
                    )}
                    <h1 className="text-stone-800 text-[19px] font-extrabold leading-tight">{activity.name}</h1>
                    {activity.description && (
                        // Admin-authored rich text from CKEditor, same as News.
                        <div className="mt-2 text-stone-600 text-[14px] leading-relaxed wellness-richtext">
                            {parse(activity.description)}
                        </div>
                    )}
                </div>

                <h2 className="mt-1 text-stone-800 text-[16px] font-extrabold">{t('wellness.availableSessions')}</h2>

                {activity.schedules.length === 0 ? (
                    <div className="bg-white rounded-2xl p-6 text-center shadow-card">
                        <p className="text-stone-500 text-[14px]">{t('wellness.noSessions')}</p>
                    </div>
                ) : (
                    <div className="grid gap-4 rail:grid-cols-2">
                    {activity.schedules.map((schedule) => {
                        const when = formatSession(schedule);
                        const busy = submitting === schedule.id;
                        // Masked by the API: a blacklisted registration arrives as
                        // the ordinary queue status, a cancelled or rejected one as none.
                        const registered = Boolean(schedule.my_status);

                        return (
                            <div key={schedule.id} className="bg-white rounded-2xl shadow-card p-4">
                                <div className="flex gap-3">
                                    <DateBadge day={when.day} month={when.month} />

                                    <div className="flex-1 min-w-0 flex flex-col gap-1">
                                        <span className="text-stone-800 text-[16px] font-bold leading-tight">{when.weekday}</span>
                                        <MetaLine icon="ri-time-line">{when.time}</MetaLine>
                                        {schedule.location && (
                                            <MetaLine icon="ri-map-pin-line" truncate>{schedule.location}</MetaLine>
                                        )}
                                        <MetaLine icon="ri-group-line">{seatLabel(schedule)}</MetaLine>
                                    </div>
                                </div>

                                {registered && (
                                    <div className="mt-3">
                                        <StatusPill status={schedule.my_status} fallback={schedule.my_status_label} />
                                    </div>
                                )}

                                {schedule.can_confirm && schedule.confirm_due_at && (
                                    <CardNote icon="ri-timer-line" tone="warning">
                                        {t('wellness.confirmBy', { date: formatDateTime(schedule.confirm_due_at) })}
                                    </CardNote>
                                )}

                                {/* Already registered: never offer Register again, only
                                    what can be done with the seat they have. Secondary
                                    action on the left, the main one on the right. */}
                                {registered ? (
                                    <ActionRow>
                                        {schedule.can_cancel && (
                                            <ActionButton
                                                variant="secondary"
                                                busy={busy}
                                                busyLabel={t('common.pleaseWait')}
                                                onClick={() => handleCancel(schedule)}
                                            >
                                                {t('wellness.cancel')}
                                            </ActionButton>
                                        )}
                                        {schedule.can_confirm && (
                                            <ActionButton
                                                icon="ri-check-line"
                                                busy={busy}
                                                busyLabel={t('common.pleaseWait')}
                                                onClick={() => handleConfirm(schedule)}
                                            >
                                                {t('wellness.confirmSeat.button')}
                                            </ActionButton>
                                        )}
                                    </ActionRow>
                                ) : schedule.can_register ? (
                                    <ActionRow>
                                        <ActionButton
                                            icon={schedule.is_full ? 'ri-time-line' : 'ri-user-add-line'}
                                            busy={busy}
                                            busyLabel={t('common.pleaseWait')}
                                            onClick={() => handleRegister(schedule)}
                                        >
                                            {schedule.is_full ? t('wellness.joinWaitlist') : t('wellness.register')}
                                        </ActionButton>
                                    </ActionRow>
                                ) : (
                                    <p className="mt-3 pt-3 border-t border-stone-100 text-center text-[13px] font-semibold text-stone-400">
                                        <i className="ri-lock-line me-1" aria-hidden="true" />
                                        {schedule.registration_open ? t('wellness.notAvailable') : t('wellness.registrationClosed')}
                                    </p>
                                )}
                            </div>
                        );
                    })}
                    </div>
                )}
            </div>
        </AppShell>
    );
}

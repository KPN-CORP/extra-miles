import React, { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import LanguageToggle from '../components/Layout/LanguageToggle';
import { useNavigate } from 'react-router-dom';

import AppShell from '../components/Layout/AppShell';
import AppHeader from '../components/Layout/AppHeader';
import EmptyState from '../components/Layout/EmptyState';
import DateFilter, { dateKey } from '../components/Layout/DateFilter';
import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/Context/AuthContext';
import { showAlert } from '../components/Helper/alertHelper';
import CardLoader from '../components/Loader/CardLoader';
import { formatSession, seatLabel } from '../components/Helper/wellnessHelper';
import {
    ACTIVITIES_KEY,
    activityKey,
    getCached,
    loadWellness,
    MY_REGISTRATIONS_KEY,
    prefetchWellness,
} from '../components/Helper/wellnessCache';

/**
 * Semua sesi mendatang satu aktivitas.
 *
 * `sessions` ditambahkan di endpoint /wellness/activities untuk halaman ini;
 * `next_session` dipertahankan agar data lama yang masih tersimpan di cache
 * (sebelum penambahan itu) tetap bisa dirender.
 */
const sessionsOf = (activity) => {
    if (Array.isArray(activity.sessions) && activity.sessions.length > 0) return activity.sessions;

    return activity.next_session ? [activity.next_session] : [];
};

export default function Wellness() {
    const navigate = useNavigate();
    const apiUrl = useApiUrl();
    const { token } = useAuth();
    const { t } = useTranslation();

    // Seeded from the cache when we have been here before, so a repeat visit
    // paints the list immediately and only revalidates in the background.
    const cached = getCached(ACTIVITIES_KEY);
    const [activities, setActivities] = useState(cached ?? []);
    const [selectedType, setSelectedType] = useState('All');
    const [selectedDate, setSelectedDate] = useState(null);
    const [loading, setLoading] = useState(cached === undefined);

    useEffect(() => {
        let active = true;

        loadWellness(ACTIVITIES_KEY, apiUrl, token)
            .then((data) => {
                if (active) setActivities(data);
            })
            .catch(() => {
                // Stale data on screen beats an alert over content that still reads fine.
                if (!active || getCached(ACTIVITIES_KEY) !== undefined) return;

                showAlert({
                    icon: 'warning',
                    title: t('alerts.connectionEnded'),
                    text: t('wellness.loadFailed'),
                    timer: 2500,
                    showConfirmButton: false,
                });
            })
            .finally(() => {
                if (active) setLoading(false);
            });

        return () => {
            active = false;
        };
    }, [apiUrl, token, t]);

    const types = useMemo(
        () => ['All', ...new Set(activities.map((a) => a.type).filter(Boolean))],
        [activities]
    );

    // Tanggal yang punya sesi, diambil dari wellness_activity_schedules lewat
    // sessions[]. Dipakai untuk titik penanda di kalender.
    const sessionDates = useMemo(() => {
        const dates = new Set();
        for (const activity of activities) {
            for (const session of sessionsOf(activity)) dates.add(dateKey(session.start_at));
        }

        return dates;
    }, [activities]);

    /**
     * Satu entri per kartu: aktivitasnya, sesi yang ditampilkan, dan berapa sesi
     * lain yang tersisa.
     *
     * Saat sebuah tanggal dipilih, kartunya menampilkan sesi PADA tanggal itu --
     * bukan sesi terdekat. Menampilkan sesi lain setelah pengguna memilih
     * tanggal tertentu akan terbaca seperti filternya tidak bekerja.
     */
    const visible = useMemo(() => {
        const key = selectedDate ? dateKey(selectedDate) : null;

        return activities
            .filter((activity) => selectedType === 'All' || activity.type === selectedType)
            .map((activity) => {
                const sessions = sessionsOf(activity);

                if (!key) {
                    return {
                        activity,
                        session: activity.next_session ?? sessions[0] ?? null,
                        others: Math.max(0, (activity.upcoming_count ?? sessions.length) - 1),
                    };
                }

                const onDay = sessions.filter((s) => dateKey(s.start_at) === key);
                if (onDay.length === 0) return null;

                return { activity, session: onDay[0], others: onDay.length - 1 };
            })
            .filter(Boolean);
    }, [activities, selectedType, selectedDate]);

    const filtersActive = selectedType !== 'All' || Boolean(selectedDate);

    // Start the detail request on tap so it runs during the route exit
    // animation rather than after the detail page has finished mounting.
    const openActivity = (id) => {
        prefetchWellness(activityKey(id), apiUrl, token);
        navigate(`/wellness/${encodeURIComponent(id)}`);
    };

    const openMyRegistrations = () => {
        prefetchWellness(MY_REGISTRATIONS_KEY, apiUrl, token);
        navigate('/wellness/my-registrations');
    };

    return (
        <AppShell nav>
            <AppHeader
                title={t('wellness.title')}
                trailing={
                    <>
                        <button
                            onClick={() => openMyRegistrations()}
                            className="tap w-11 h-11 grid place-items-center rounded-full text-xl text-brand-700"
                            aria-label={t('wellness.myRegistrations')}
                        >
                            <i className="ri-calendar-check-line"></i>
                        </button>
                        <LanguageToggle />
                    </>
                }
            />

            <div className="px-5 pt-4 flex flex-col gap-4">
                <p className="text-stone-600 text-[11.5px] leading-relaxed">
                    {t('wellness.intro')}
                </p>

                {/* Type filter */}
                {types.length > 1 && (
                    <div className="flex gap-2 overflow-x-auto no-scrollbar pb-1">
                        {types.map((type) => (
                            <button
                                key={type}
                                onClick={() => setSelectedType(type)}
                                className={`tap px-3 py-1.5 rounded-full text-[11px] font-bold whitespace-nowrap ${
                                    selectedType === type
                                        ? 'bg-brand-700 text-white shadow-card'
                                        : 'bg-white text-brand-700 ring-1 ring-brand-700 ring-inset'
                                }`}
                            >
                                {type === 'All' ? t('wellness.allTypes') : type}
                            </button>
                        ))}
                    </div>
                )}

                {/* Tanggalnya berasal dari jadwal aktivitas, jadi hari yang
                    bertitik adalah hari yang benar-benar ada sesinya. */}
                <DateFilter value={selectedDate} onChange={setSelectedDate} markedDates={sessionDates} />

                {/* List */}
                {loading ? (
                    <>
                        <CardLoader />
                        <CardLoader />
                    </>
                ) : visible.length === 0 ? (
                    <EmptyState
                        icon={filtersActive ? 'ri-calendar-2-line' : 'ri-heart-pulse-line'}
                        title={filtersActive ? t('filters.noMatches') : undefined}
                        description={filtersActive ? t('wellness.noSessionsOnDate') : t('wellness.empty')}
                    />
                ) : (
                    <div className="grid gap-4 rail:grid-cols-2">
                    {visible.map(({ activity, session, others }) => {
                        const when = formatSession(session);

                        return (
                            <button
                                key={activity.id}
                                onClick={() => openActivity(activity.id)}
                                className="tap w-full bg-white rounded-2xl shadow-card p-2.5 flex items-center gap-3 text-left"
                            >
                                <span className="w-12 shrink-0 rounded-xl bg-brand-50 text-brand-700 flex flex-col items-center justify-center py-1.5">
                                    <span className="text-[19px] font-extrabold leading-none">{when.day || '-'}</span>
                                    <span className="mt-0.5 text-[9px] font-extrabold uppercase tracking-[0.07em] leading-none">
                                        {when.month}
                                    </span>
                                </span>

                                <span className="flex-1 min-w-0">
                                    <span className="block text-stone-800 text-[14px] font-extrabold tracking-[-0.012em] leading-tight clamp-1">
                                        {activity.name}
                                    </span>
                                    {activity.type && (
                                        <span className="block mt-0.5 text-brand-700 text-[10.5px] font-bold leading-tight">
                                            {activity.type}
                                        </span>
                                    )}
                                    {session && (
                                        <span className="mt-1 flex flex-col gap-0.5 text-stone-500 text-[11px] leading-tight">
                                            <span className="clamp-1">
                                                <i className="ri-time-line me-1" aria-hidden="true" />
                                                {when.time}
                                            </span>
                                            {session.location && (
                                                <span className="clamp-1">
                                                    <i className="ri-map-pin-line me-1" aria-hidden="true" />
                                                    {session.location}
                                                </span>
                                            )}
                                            <span className="clamp-1">
                                                <i className="ri-group-line me-1" aria-hidden="true" />
                                                {seatLabel(session)}
                                            </span>
                                        </span>
                                    )}
                                    {others > 0 && (
                                        <span className="block mt-1 text-stone-400 text-[10px] leading-tight">
                                            {t('wellness.moreSessions', { count: others })}
                                        </span>
                                    )}
                                </span>

                                <i className="ri-arrow-right-s-line text-stone-300 text-lg shrink-0" aria-hidden="true" />
                            </button>
                        );
                    })}
                    </div>
                )}

                {filtersActive && (
                    <button
                        type="button"
                        onClick={() => {
                            setSelectedType('All');
                            setSelectedDate(null);
                        }}
                        className="tap self-center text-brand-700 text-[12px] font-extrabold inline-flex items-center gap-1"
                    >
                        <i className="ri-refresh-line text-sm" aria-hidden="true" />
                        {t('filters.reset')}
                    </button>
                )}
            </div>
        </AppShell>
    );
}

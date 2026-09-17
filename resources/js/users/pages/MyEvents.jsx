import React, { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import AppShell from '../components/Layout/AppShell';
import AppHeader from '../components/Layout/AppHeader';
import EmptyState from '../components/Layout/EmptyState';
import DateFilter, { dateKey } from '../components/Layout/DateFilter';
import MyEventCard from '../components/Cards/MyEventCard';
import ActivityLoader from '../components/Loader/ActivityLoader';
import QRScannerModal from '../components/Helper/QrScannerModal';
import LanguageToggle from '../components/Layout/LanguageToggle';
import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/Context/AuthContext';
import { showAlert } from '../components/Helper/alertHelper';
import {
    bySoonest,
    hasAttended,
    isAwaitingResponse,
    isConfirmedUpcoming,
} from '../components/Helper/eventRules';

/**
 * Daftar lengkap event milik karyawan.
 *
 * Beda dengan beranda: di sini keduanya jadi SATU daftar, dengan dua kotak
 * centang untuk memilih mana yang ikut ditampilkan. Aturan "belum dijawab" dan
 * "sudah dikonfirmasi" diambil dari components/Helper/eventRules supaya jumlah
 * di beranda dan isi halaman ini tidak pernah berbeda.
 *
 * Filter tanggalnya sengaja meniru halaman /event: react-calendar, cocok pada
 * hari yang sama dengan start_date. Dua halaman event sebaiknya tidak punya dua
 * cara memilih tanggal.
 */
export default function MyEvents() {
    const apiUrl = useApiUrl();
    const { token } = useAuth();
    const navigate = useNavigate();
    const { t } = useTranslation();

    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [isQRModalOpen, setIsQRModalOpen] = useState(false);
    const [selectedEvent, setSelectedEvent] = useState(null);

    const [search, setSearch] = useState('');
    const [selectedDate, setSelectedDate] = useState(null);
    const [showAwaiting, setShowAwaiting] = useState(true);
    const [showConfirmed, setShowConfirmed] = useState(true);

    const fetchEvents = async () => {
        try {
            const res = await axios.get(`${apiUrl}/api/my-event`, {
                headers: { Authorization: `Bearer ${token}` },
            });
            setEvents(
                res.data.map((e) => ({
                    ...e,
                    businessUnit: Array.isArray(e.businessUnit) ? e.businessUnit : [e.businessUnit],
                }))
            );
        } catch (err) {
            showAlert({
                icon: 'warning',
                title: t('alerts.connectionEnded'),
                text: t('alerts.connectionEndedText'),
                timer: 2500,
                showConfirmButton: false,
            });
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (token) fetchEvents();
    }, [token]);

    const handleConfirm = (event) => navigate(`/event/${event.encrypted_id}`);

    const handleScanQR = (event) => {
        if (!hasAttended(event)) {
            setSelectedEvent(event);
            setIsQRModalOpen(true);
        }
    };

    // Hanya event yang termasuk salah satu dari dua keadaan itu; sisanya
    // (mis. sudah lewat) memang bukan milik halaman ini.
    const mine = useMemo(
        () =>
            events
                .filter((e) => isAwaitingResponse(e) || isConfirmedUpcoming(e))
                .sort(bySoonest),
        [events]
    );

    // Tanggal yang punya event, untuk penanda di kalender.
    const eventDates = useMemo(
        () => new Set(mine.map((e) => dateKey(e.start_date))),
        [mine]
    );

    const awaitingCount = mine.filter(isAwaitingResponse).length;
    const confirmedCount = mine.length - awaitingCount;

    const visible = useMemo(() => {
        const needle = search.trim().toLowerCase();

        return mine.filter((event) => {
            const awaiting = isAwaitingResponse(event);
            if (awaiting && !showAwaiting) return false;
            if (!awaiting && !showConfirmed) return false;

            // Judul dan lokasi -- keduanya yang tercetak di kartunya.
            if (needle) {
                const haystack = `${event.title ?? ''} ${event.event_location ?? ''}`.toLowerCase();
                if (!haystack.includes(needle)) return false;
            }

            if (selectedDate) {
                const start = new Date(event.start_date);
                if (start.toDateString() !== selectedDate.toDateString()) return false;
            }

            return true;
        });
    }, [mine, search, selectedDate, showAwaiting, showConfirmed]);

    const filtersActive = Boolean(search.trim()) || Boolean(selectedDate) || !showAwaiting || !showConfirmed;

    const resetFilters = () => {
        setSearch('');
        setSelectedDate(null);
        setShowAwaiting(true);
        setShowConfirmed(true);
    };

    return (
        <AppShell nav>
            <AppHeader title={t('activity.myEventsTitle')} trailing={<LanguageToggle />} />

            <div className="px-5 pt-4 flex flex-col gap-4">
                {/* Pencarian */}
                <div className="relative">
                    <i
                        className="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-base"
                        aria-hidden="true"
                    />
                    <input
                        type="search"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder={t('myEvents.searchPlaceholder')}
                        aria-label={t('myEvents.searchPlaceholder')}
                        className="w-full h-11 pl-10 pr-3 rounded-2xl bg-white shadow-card text-[13px] text-stone-800 placeholder:text-stone-400 outline-none focus:ring-[1.5px] focus:ring-brand-700"
                    />
                </div>

                {/* Dua keadaan, dua kotak centang. Keduanya tercentang secara
                    default, jadi halaman ini terbuka dengan daftar lengkap. */}
                <div className="flex flex-wrap items-center gap-2">
                    <StateToggle
                        checked={showAwaiting}
                        onChange={() => setShowAwaiting((v) => !v)}
                        label={t('myEvents.filterAwaiting')}
                        count={awaitingCount}
                        tone="amber"
                    />
                    <StateToggle
                        checked={showConfirmed}
                        onChange={() => setShowConfirmed((v) => !v)}
                        label={t('myEvents.filterConfirmed')}
                        count={confirmedCount}
                        tone="brand"
                    />
                </div>

                {/* Hari yang punya event ditandai titik, jadi tidak perlu
                    menebak tanggal mana yang ada isinya. */}
                <DateFilter value={selectedDate} onChange={setSelectedDate} markedDates={eventDates} />

                {loading ? (
                    <ActivityLoader />
                ) : visible.length > 0 ? (
                    <>
                        {/* Peringatan tenggat hanya relevan kalau ada yang belum dijawab. */}
                        {visible.some(isAwaitingResponse) && (
                            <div className="flex gap-2 p-3 rounded-2xl bg-amber-50 border border-amber-200">
                                <i className="ri-alarm-warning-line text-amber-600 text-base shrink-0" aria-hidden="true" />
                                <p className="text-amber-900 text-[11.5px] leading-relaxed">
                                    {t('activity.confirmationDeadline')}
                                </p>
                            </div>
                        )}

                        <div className="grid gap-2 rail:grid-cols-2">
                            {visible.map((event) => (
                                <MyEventCard
                                    key={event.encrypted_id}
                                    event={event}
                                    onConfirm={handleConfirm}
                                    onScan={handleScanQR}
                                />
                            ))}
                        </div>
                    </>
                ) : (
                    // Kosong karena filter dan kosong karena memang tidak ada
                    // adalah dua hal berbeda; yang pertama harus bisa dibatalkan.
                    <EmptyState
                        icon={filtersActive ? 'ri-search-line' : 'ri-calendar-2-line'}
                        title={filtersActive ? t('filters.noMatches') : undefined}
                        description={filtersActive ? t('filters.noMatchesText') : t('activity.noPendingEvent')}
                    />
                )}

                {filtersActive && (
                    <button
                        type="button"
                        onClick={resetFilters}
                        className="tap self-center text-brand-700 text-[12px] font-extrabold inline-flex items-center gap-1"
                    >
                        <i className="ri-refresh-line text-sm" aria-hidden="true" />
                        {t('filters.reset')}
                    </button>
                )}
            </div>

            <QRScannerModal
                isOpen={isQRModalOpen}
                event={selectedEvent}
                onClose={() => setIsQRModalOpen(false)}
                onScanSuccess={fetchEvents}
            />
        </AppShell>
    );
}

/**
 * Kotak centang berbentuk pil. Tetap sebuah <input type="checkbox"> asli supaya
 * bisa difokus dan dibaca pembaca layar; tampilannya yang diganti.
 */
function StateToggle({ checked, onChange, label, count, tone }) {
    const active = tone === 'amber'
        ? 'bg-amber-50 text-amber-900 ring-amber-300'
        : 'bg-brand-75 text-brand-850 ring-brand-300';

    return (
        <label
            className={`tap inline-flex items-center gap-2 h-9 px-3 rounded-full ring-1 ring-inset cursor-pointer text-[12px] font-bold ${
                checked ? active : 'bg-white text-stone-400 ring-stone-200'
            }`}
        >
            <input
                type="checkbox"
                checked={checked}
                onChange={onChange}
                className="w-4 h-4 accent-brand-700 rounded"
            />
            {label}
            <span className={`text-[11px] font-extrabold ${checked ? '' : 'text-stone-300'}`}>{count}</span>
        </label>
    );
}

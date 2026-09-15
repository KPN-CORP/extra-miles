import React from 'react';
import { useTranslation } from 'react-i18next';
import { useLocation, useNavigate } from 'react-router-dom';

import { MY_REGISTRATIONS_KEY, prefetchWellness } from '../Helper/wellnessCache';
import { useApiUrl } from '../Context/ApiContext';
import { useAuth } from '../Context/AuthContext';

/**
 * Tab bar utama aplikasi.
 *
 * Tab Event dan Wellness menuju daftar milik karyawan sendiri (/my-events dan
 * /wellness/my-registrations), bukan halaman jelajah. Halaman jelajahnya tetap
 * lewat tile Quick Access di beranda -- jadi tab bar menjawab "punyaku apa
 * saja", tile menjawab "apa yang ada".
 *
 * Menu lain (Media Sosial, Live, EVO) tetap tersedia dari grid menu di beranda.
 */
const TABS = [
    { path: '/', labelKey: 'nav.home', icon: 'ri-home-5-line', activeIcon: 'ri-home-5-fill' },
    {
        path: '/my-events',
        labelKey: 'nav.events',
        icon: 'ri-calendar-event-line',
        activeIcon: 'ri-calendar-event-fill',
        // Halaman jelajah dan detail event tetap menyalakan tab ini, supaya
        // tidak ada layar event yang membuat tab bar terlihat mati.
        match: ['/my-events', '/event', '/event-registration'],
    },
    { path: '/news', labelKey: 'nav.news', icon: 'ri-newspaper-line', activeIcon: 'ri-newspaper-fill' },
    {
        path: '/wellness/my-registrations',
        labelKey: 'nav.wellness',
        icon: 'ri-heart-pulse-line',
        activeIcon: 'ri-heart-pulse-fill',
        // '/wellness' sudah mencakup /wellness, /wellness/:id dan
        // /wellness/my-registrations sekaligus.
        match: ['/wellness'],
        prefetch: MY_REGISTRATIONS_KEY,
    },
    { path: '/survey', labelKey: 'nav.survey', icon: 'ri-questionnaire-line', activeIcon: 'ri-questionnaire-fill' },
];

// "/" hanya cocok persis; sisanya juga cocok untuk halaman turunannya
// (mis. /event/xyz tetap menyalakan tab Event).
const matches = (pathname, prefix) =>
    prefix === '/' ? pathname === '/' : pathname === prefix || pathname.startsWith(`${prefix}/`);

// Tab menyala untuk seluruh daftar `match`-nya, bukan cuma tujuannya.
const isActive = (pathname, tab) => (tab.match ?? [tab.path]).some((p) => matches(pathname, p));

export default function BottomNav() {
    const navigate = useNavigate();
    const { pathname } = useLocation();
    const { t } = useTranslation();
    const apiUrl = useApiUrl();
    const { token } = useAuth();

    const go = (tab) => {
        // Dibandingkan dengan tujuannya, bukan dengan `match`: sedang berada di
        // /wellness (halaman jelajah) membuat tab Wellness menyala, tapi
        // menekannya harus tetap membawa ke /wellness/my-registrations.
        if (matches(pathname, tab.path)) return;
        // Permintaan dijalankan selama animasi keluar halaman ini, bukan setelah
        // halaman tujuan selesai mount.
        if (tab.prefetch) prefetchWellness(tab.prefetch, apiUrl, token);
        navigate(tab.path);
    };

    return (
        <nav
            className="fixed inset-x-0 bottom-0 z-40 pb-safe app-blur bg-white/85 border-t border-stone-200/80 shadow-nav"
            aria-label={t('nav.primary')}
        >
            <ul className="app-container flex items-stretch h-[var(--app-nav-h)] px-1">
                {TABS.map((tab) => {
                    const active = isActive(pathname, tab);

                    return (
                        <li key={tab.path} className="flex-1">
                            <button
                                type="button"
                                onClick={() => go(tab)}
                                aria-current={active ? 'page' : undefined}
                                className="tap w-full h-full flex flex-col items-center justify-center gap-1"
                            >
                                {/* Pil di belakang ikon menandai tab aktif; lebih
                                    menyatu dengan bar daripada garis di tepi atas. */}
                                <span
                                    className={`px-3 py-0.5 rounded-full leading-none transition-colors ${
                                        active ? 'bg-brand-50' : ''
                                    }`}
                                >
                                    <i
                                        className={`${active ? tab.activeIcon : tab.icon} text-xl leading-none ${
                                            active ? 'text-brand-700' : 'text-stone-400'
                                        }`}
                                    />
                                </span>
                                <span
                                    className={`text-[10px] leading-none ${
                                        active ? 'text-brand-700 font-bold' : 'text-stone-500 font-medium'
                                    }`}
                                >
                                    {t(tab.labelKey)}
                                </span>
                            </button>
                        </li>
                    );
                })}
            </ul>
        </nav>
    );
}

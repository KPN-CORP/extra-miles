import React from 'react';
import { useTranslation } from 'react-i18next';
import Calendar from 'react-calendar';
import 'react-calendar/dist/Calendar.css';

import { localeTag } from '../Helper/localeHelper';

/**
 * Filter tanggal yang bisa dilipat, dipakai halaman /my-events dan /wellness.
 *
 * Bentuknya mengikuti halaman /event yang sudah ada: react-calendar, cocok pada
 * hari yang sama. Dibungkus <details> supaya kalendernya tidak memakan layar di
 * atas daftar sepanjang waktu.
 *
 * `markedDates` berisi kumpulan tanggal (string 'YYYY-MM-DD') yang punya isi.
 * Hari-hari itu diberi titik kecil, jadi pengguna tidak perlu menebak-nebak
 * tanggal mana yang ada jadwalnya.
 */

// Kunci lokal, bukan toISOString: toISOString memakai UTC, sehingga tanggal
// sore di WIB bisa bergeser sehari.
export const dateKey = (value) => {
    // "2026-09-20" / "2026-09-20 08:00:00" -> dipotong, bukan diparse.
    if (typeof value === 'string') {
        const iso = value.match(/^(\d{4}-\d{2}-\d{2})/);
        if (iso) return iso[1];
    }

    const d = new Date(value);

    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
};

export default function DateFilter({ value, onChange, markedDates = null, className = '' }) {
    const { t } = useTranslation();

    return (
        <details className={`bg-white rounded-2xl shadow-card overflow-hidden ${className}`}>
            <summary className="tap h-11 px-4 flex items-center gap-2 cursor-pointer text-[13px] font-bold text-stone-800 list-none">
                <i className="ri-calendar-2-line text-brand-700 text-base" aria-hidden="true" />
                {value
                    ? value.toLocaleDateString(localeTag(), { day: '2-digit', month: 'short', year: 'numeric' })
                    : t('filters.byDate')}
                <i className="ri-arrow-down-s-line ms-auto text-stone-400 text-base" aria-hidden="true" />
            </summary>

            <div className="px-3 pb-3 flex flex-col gap-2">
                <Calendar
                    locale={localeTag()}
                    onChange={onChange}
                    value={value}
                    className="w-full border-0"
                    tileContent={
                        markedDates
                            ? ({ date, view }) =>
                                view === 'month' && markedDates.has(dateKey(date)) ? (
                                    <span className="has-sessions-dot" aria-hidden="true" />
                                ) : null
                            : undefined
                    }
                />

                {value && (
                    <button
                        type="button"
                        onClick={() => onChange(null)}
                        className="tap self-start text-brand-700 text-[11px] font-extrabold"
                    >
                        {t('filters.clearDate')}
                    </button>
                )}
            </div>
        </details>
    );
}

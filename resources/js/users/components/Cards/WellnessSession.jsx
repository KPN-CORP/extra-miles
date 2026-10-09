import React from 'react';
import { statusLabel, statusStyle } from '../Helper/wellnessHelper';

/**
 * Bagian-bagian kartu sesi wellness, dipakai bersama oleh halaman detail
 * aktivitas (WellnessDetails) dan My Wellness supaya ukuran huruf, ukuran
 * tombol, dan letak tombol di kedua halaman selalu sama.
 *
 * Ukuran tombol mengikuti batas sentuh minimum 44px: tombol aksi ada di baris
 * paling bawah kartu, lebarnya dibagi rata, dan aksi utama selalu di kanan
 * (dekat ibu jari).
 */

/** Kotak tanggal merah di sisi kiri kartu. */
export function DateBadge({ day, month }) {
    return (
        <div className="w-16 shrink-0 rounded-xl bg-brand-700 text-white flex flex-col items-center justify-center py-2.5">
            <span className="text-[24px] font-extrabold leading-none">{day || '-'}</span>
            <span className="mt-1 text-[12px] font-bold uppercase tracking-[0.06em] leading-none">{month}</span>
        </div>
    );
}

/** Satu baris info (jam, lokasi, kursi) dengan ikon di depannya. */
export function MetaLine({ icon, children, truncate = false }) {
    return (
        <span className={`flex items-start gap-1.5 text-[13px] leading-snug text-stone-600 ${truncate ? 'min-w-0' : ''}`}>
            <i className={`${icon} text-[15px] leading-[1.15] text-stone-400 shrink-0`} aria-hidden="true" />
            <span className={truncate ? 'truncate' : ''}>{children}</span>
        </span>
    );
}

/** Label status pendaftaran, warnanya sama dengan sisi admin. */
export function StatusPill({ status, fallback, icon, className = '' }) {
    return (
        <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-bold leading-none ${className || statusStyle(status)}`}>
            {icon && <i className={icon} aria-hidden="true" />}
            {status ? statusLabel(status, fallback) : fallback}
        </span>
    );
}

/**
 * Tombol aksi kartu. `primary` untuk aksi utama (Daftar, Konfirmasi, Check-in),
 * `secondary` untuk aksi pendamping (Batalkan, Feedback).
 */
export function ActionButton({ variant = 'primary', icon, busy, busyLabel, disabled, onClick, children }) {
    const look = variant === 'primary'
        ? 'bg-brand-700 text-white shadow-sm'
        : 'bg-white text-brand-700 ring-1 ring-brand-700 ring-inset';

    return (
        <button
            type="button"
            disabled={disabled || busy}
            onClick={onClick}
            className={`tap flex-1 min-h-[44px] px-4 rounded-xl text-[14px] font-bold inline-flex items-center justify-center gap-1.5 disabled:opacity-50 ${look}`}
        >
            {icon && !busy && <i className={`${icon} text-[16px]`} aria-hidden="true" />}
            {busy ? busyLabel : children}
        </button>
    );
}

/**
 * Baris tombol di bawah kartu, dipisah garis tipis. Tidak dirender bila tidak
 * ada tombol sama sekali, supaya kartu tanpa aksi tidak punya garis kosong.
 */
export function ActionRow({ children }) {
    const items = React.Children.toArray(children).filter(Boolean);

    if (items.length === 0) return null;

    return <div className="mt-3 pt-3 border-t border-stone-100 flex gap-2">{items}</div>;
}

/** Catatan kecil di bawah status (tenggat konfirmasi, keterangan antrean). */
export function CardNote({ icon, children, tone = 'muted' }) {
    const color = tone === 'warning' ? 'bg-amber-50 text-amber-800' : 'bg-stone-50 text-stone-600';

    return (
        <p className={`mt-3 rounded-lg px-3 py-2 text-[12.5px] leading-relaxed flex gap-1.5 ${color}`}>
            {icon && <i className={`${icon} text-[14px] leading-[1.35] shrink-0`} aria-hidden="true" />}
            <span>{children}</span>
        </p>
    );
}

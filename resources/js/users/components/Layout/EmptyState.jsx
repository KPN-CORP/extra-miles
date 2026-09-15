import React from 'react';

// Nada warna untuk kondisi kosong. `success` dipakai saat "tidak ada apa-apa"
// justru kabar baik (mis. tidak ada undangan yang menunggu konfirmasi).
const TONES = {
    neutral: {
        wrap: 'bg-white/80 border-stone-200',
        icon: 'bg-stone-100 text-stone-400',
        title: 'text-stone-700',
        text: 'text-stone-500',
    },
    success: {
        wrap: 'bg-emerald-50/90 border-emerald-200',
        icon: 'bg-emerald-100 text-emerald-600',
        title: 'text-emerald-800',
        text: 'text-emerald-700',
    },
    warning: {
        wrap: 'bg-amber-50/90 border-amber-200',
        icon: 'bg-amber-100 text-amber-600',
        title: 'text-amber-900',
        text: 'text-amber-800',
    },
};

/**
 * Kartu untuk daftar kosong / informasi ringan.
 */
export default function EmptyState({
    icon = 'ri-inbox-line',
    title,
    description,
    tone = 'neutral',
    className = '',
}) {
    const style = TONES[tone] ?? TONES.neutral;

    return (
        <div
            className={`rounded-2xl border px-4 py-5 flex flex-col items-center text-center gap-2 ${style.wrap} ${className}`}
        >
            <span className={`w-11 h-11 rounded-full grid place-items-center text-xl ${style.icon}`}>
                <i className={icon} />
            </span>

            {title && <p className={`text-[13px] font-semibold leading-tight ${style.title}`}>{title}</p>}
            {description && <p className={`text-[11px] leading-relaxed ${style.text}`}>{description}</p>}
        </div>
    );
}

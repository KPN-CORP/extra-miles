import React from 'react';

/**
 * Judul kecil di atas tiap blok konten, dengan tautan opsional di kanan.
 */
export default function SectionHeader({
    title,
    actionLabel,
    onAction,
    // Chevron kanan berarti "pindah halaman". Kalau aksinya hanya membuka
    // daftar di tempat, pemanggil mengirim chevron bawah/atas supaya tidak
    // menjanjikan navigasi yang tidak terjadi.
    actionIcon = 'ri-arrow-right-s-line',
    badge,
    className = '',
}) {
    return (
        <div className={`flex items-center justify-between gap-2 ${className}`}>
            <div className="flex items-center gap-2 min-w-0">
                <h2 className="text-stone-800 text-[17px] font-extrabold tracking-[-0.016em] leading-tight">{title}</h2>

                {/* Jumlah item yang menunggu tindakan, mis. undangan yang belum
                    dikonfirmasi. Sengaja tidak dirender saat nol. */}
                {Boolean(badge) && (
                    <span className="shrink-0 min-w-[19px] h-[19px] px-1.5 grid place-items-center rounded-full bg-brand-700 text-white text-[10.5px] font-extrabold leading-none">
                        {badge}
                    </span>
                )}
            </div>

            {actionLabel && onAction && (
                <button
                    type="button"
                    onClick={onAction}
                    className="tap shrink-0 text-brand-700 text-[11px] font-extrabold inline-flex items-center gap-0.5"
                >
                    {actionLabel}
                    <i className={`${actionIcon} text-sm`} />
                </button>
            )}
        </div>
    );
}

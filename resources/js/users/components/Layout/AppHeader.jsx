import React from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';

/**
 * Header halaman yang menempel di atas layar.
 *
 * `variant="solid"` dipakai halaman berlatar merah penuh (mis. Media Sosial)
 * supaya header menyatu dengan latarnya.
 */
export default function AppHeader({
    title,
    subtitle,
    onBack,
    showBack = true,
    backTo = '/',
    trailing = null,
    variant = 'light',
    className = '',
}) {
    const navigate = useNavigate();
    const { t } = useTranslation();

    const solid = variant === 'solid';

    // Tombol kembali mengikuti riwayat, bukan tujuan tetap: masuk ke halaman ini
    // dari beranda akan kembali ke beranda, dari /my-events kembali ke
    // /my-events. Halaman tidak perlu (dan sebaiknya tidak) mengoper `onBack`
    // hanya untuk memaksa satu tujuan.
    //
    // Tanpa riwayat dalam aplikasi (mis. dibuka langsung dari tautan atau tab
    // baru) navigate(-1) akan keluar dari aplikasi, jadi jatuhkan ke `backTo`.
    const handleBack = () => {
        if (onBack) return onBack();
        if (window.history.state && window.history.state.idx > 0) return navigate(-1);
        return navigate(backTo);
    };

    return (
        <header
            className={`sticky top-0 z-30 pt-safe ${
                solid
                    ? 'bg-brand-700 text-white'
                    : 'app-blur bg-white/80 border-b border-stone-200/70 text-brand-700'
            } ${className}`}
        >
            <div className="app-container h-[var(--app-header-h)] px-2 flex items-center gap-1">
                {showBack ? (
                    <button
                        type="button"
                        onClick={handleBack}
                        aria-label={t('common.back')}
                        className={`tap w-11 h-11 shrink-0 grid place-items-center rounded-full text-xl ${
                            solid ? 'text-white' : 'text-brand-700'
                        }`}
                    >
                        <i className="ri-arrow-left-line" />
                    </button>
                ) : (
                    <span className="w-11 shrink-0" aria-hidden="true" />
                )}

                <div className="flex-1 min-w-0 text-center">
                    <h1 className="text-base font-bold leading-tight clamp-1">{title}</h1>
                    {subtitle && (
                        <p
                            className={`text-[11px] leading-tight clamp-1 ${
                                solid ? 'text-white/80' : 'text-stone-500'
                            }`}
                        >
                            {subtitle}
                        </p>
                    )}
                </div>

                <div className="min-w-[2.75rem] shrink-0 flex items-center justify-end gap-1">
                    {trailing}
                </div>
            </div>
        </header>
    );
}

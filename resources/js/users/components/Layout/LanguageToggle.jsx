import React from 'react';
import { useTranslation } from 'react-i18next';

import { SUPPORTED_LANGUAGES } from '../../i18n';

/**
 * Pemilih bahasa untuk aplikasi karyawan.
 *
 * Pilihan disimpan i18next ke localStorage (lihat konfigurasi detection), jadi
 * komponen ini cukup memanggil changeLanguage tanpa menyimpan apa pun sendiri.
 */
export default function LanguageToggle({ className = '', variant = 'light' }) {
    const { t, i18n } = useTranslation();
    const current = i18n.resolvedLanguage;

    const onRed = variant === 'onRed';
    const onBanner = variant === 'onBanner';

    // Di atas latar merah, pil memakai kaca transparan; di atas banner memakai
    // putih pekat supaya tetap terbaca di bagian artwork yang ramai; di latar
    // terang memakai putih bersih. Semuanya ditentukan di sini supaya pemanggil
    // tidak perlu menimpa kelas warna lewat className.
    const wrapClass = onRed
        ? 'bg-white/15 ring-white/40'
        : onBanner
          ? 'bg-white/85 ring-white/90 shadow-card backdrop-blur-[2px]'
          : 'bg-white/70 ring-stone-300';
    const activeClass = onRed ? 'bg-white text-brand-700' : 'bg-brand-700 text-white';
    const idleClass = onRed ? 'text-white/80' : 'text-stone-500';
    // Di atas banner pilnya sedikit lebih besar: ia berdiri sendiri di sana,
    // tidak menempel pada blok teks seperti varian lain.
    const sizeClass = onBanner ? 'px-2.5 py-1 text-[10.5px]' : 'px-2 py-0.5 text-[10px]';

    return (
        <div
            className={`inline-flex items-center rounded-full p-0.5 ring-1 ring-inset ${wrapClass} ${className}`}
            role="group"
            aria-label={t('app.language')}
        >
            {SUPPORTED_LANGUAGES.map(({ code, short, label }) => (
                <button
                    key={code}
                    type="button"
                    onClick={() => i18n.changeLanguage(code)}
                    aria-pressed={current === code}
                    title={label}
                    className={`${sizeClass} rounded-full font-bold leading-none transition ${
                        current === code ? activeClass : idleClass
                    }`}
                >
                    {short}
                </button>
            ))}
        </div>
    );
}

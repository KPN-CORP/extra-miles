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

    const activeClass =
        variant === 'onRed'
            ? 'bg-white text-red-700'
            : 'bg-red-700 text-white';
    const idleClass =
        variant === 'onRed'
            ? 'text-white/80'
            : 'text-stone-500';

    return (
        <div
            className={`inline-flex items-center rounded-full p-0.5 bg-white/70 ring-1 ring-inset ring-stone-300 ${className}`}
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
                    className={`px-2 py-0.5 rounded-full text-[10px] font-bold leading-none transition ${
                        current === code ? activeClass : idleClass
                    }`}
                >
                    {short}
                </button>
            ))}
        </div>
    );
}

import React from "react";
import { useTranslation } from "react-i18next";

export default () => {

  const { t } = useTranslation();

  return (
    // Chip biru langit yang lama adalah satu-satunya warna non-brand yang
    // tersisa di halaman ini, jadi diganti garis dan teks merah brand -- sama
    // seperti tombol "News & Events Assets" di tampilan lama, tapi tetap berupa
    // baris supaya mudah dipindai bersama kartu lain.
    <a
      href="https://drive.google.com/drive/folders/1zmDJ_yE6zL4itNi-XcG8IO6sFfhAQRdA"
      target="_blank"
      rel="noopener noreferrer"
      title={t('assets.title')}
      aria-label={t('assets.ariaLabel')}
      className="tap w-full bg-white rounded-2xl ring-[1.5px] ring-brand-700 shadow-card p-3 flex items-center gap-3"
    >
      <span className="w-11 h-11 shrink-0 rounded-2xl grid place-items-center bg-brand-75 text-brand-700 text-[22px]">
        <i className="ri-folder-image-fill" aria-hidden="true" />
      </span>

      <span className="flex-1 min-w-0">
        <span className="block text-brand-700 text-[14px] font-extrabold tracking-[-0.012em] leading-tight">
          {t('assets.line1')} {t('assets.line2')}
        </span>
        <span className="block text-stone-500 text-[11px] leading-tight mt-0.5 clamp-1">
          {t('assets.subtitle')}
        </span>
      </span>

      <i className="ri-arrow-right-up-line text-brand-700 text-lg shrink-0" aria-hidden="true" />
    </a>
  );
};

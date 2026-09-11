import React from "react";
import { useTranslation } from "react-i18next";
import { useApiUrl } from "../context/ApiContext";

export default () => {

  const apiUrl = useApiUrl();
  const { t } = useTranslation();

  return (
    <a
      href="https://drive.google.com/drive/folders/1zmDJ_yE6zL4itNi-XcG8IO6sFfhAQRdA"
      target="_blank"
      rel="noopener noreferrer"
      title={t('assets.title')}
      className="inline-flex w-fit px-6 py-1 rounded-lg ring-1 ring-red-700 justify-center items-center shadow-lg bg-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
      aria-label={t('assets.ariaLabel')}
    >
      <div className="text-center text-red-700 text-xs sm:text-sm font-bold leading-tight">
        {t('assets.line1')}<br />{t('assets.line2')}
      </div>
    </a>
  );
};
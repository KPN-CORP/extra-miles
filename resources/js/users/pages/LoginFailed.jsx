import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useSearchParams } from 'react-router-dom';

import { redirectToSso, ssoReturnUrl } from '../components/Helper/ssoRedirect';

// Jeda singkat supaya alasan kegagalan sempat terbaca sebelum pindah halaman.
const REDIRECT_DELAY_MS = 4000;

function LoginFailed() {
  const [searchParams] = useSearchParams();
  const { t } = useTranslation();

  const errorParam = searchParams.get('error');
  const message = errorParam ? decodeURIComponent(errorParam) : t('login.failedFallback');

  const [seconds, setSeconds] = useState(REDIRECT_DELAY_MS / 1000);

  useEffect(() => {
    const tick = setInterval(() => setSeconds((s) => Math.max(0, s - 1)), 1000);
    const timer = setTimeout(redirectToSso, REDIRECT_DELAY_MS);

    return () => {
      clearInterval(tick);
      clearTimeout(timer);
    };
  }, []);

  return (
    <div className="app-surface app-bg flex flex-col items-center justify-center px-8 text-center">
      <span className="w-16 h-16 rounded-full grid place-items-center bg-brand-50 text-brand-700 text-3xl mb-4">
        <i className="ri-error-warning-line" />
      </span>

      <h1 className="text-brand-700 text-xl font-bold">{t('login.failedTitle')}</h1>
      <p className="mt-2 text-stone-600 text-xs leading-relaxed max-w-xs">{message}</p>

      {/* Tautan tetap ada kalau redirect otomatis diblokir browser. */}
      <a
        href={ssoReturnUrl()}
        className="tap mt-6 inline-flex items-center gap-1 bg-brand-700 text-white text-xs font-bold px-5 py-3 rounded-xl shadow-float"
      >
        <i className="ri-arrow-go-back-line" />
        {t('login.backToPortal')}
      </a>

      <p className="mt-3 text-stone-400 text-[10px]">
        {t('login.redirecting', { count: seconds })}
      </p>
    </div>
  );
}

export default LoginFailed;

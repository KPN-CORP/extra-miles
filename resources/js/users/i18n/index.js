import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';

import en from './locales/en.json';
import id from './locales/id.json';

export const SUPPORTED_LANGUAGES = [
  { code: 'en', label: 'English', short: 'EN' },
  { code: 'id', label: 'Bahasa Indonesia', short: 'ID' },
];

// Kunci localStorage dipakai bersama dengan admin (lihat LanguageController).
export const LANGUAGE_STORAGE_KEY = 'em_lang';

i18n
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    resources: {
      en: { translation: en },
      id: { translation: id },
    },
    supportedLngs: SUPPORTED_LANGUAGES.map((l) => l.code),
    fallbackLng: 'en',
    // 'id-ID' / 'en-US' dipetakan ke 'id' / 'en'.
    load: 'languageOnly',
    nonExplicitSupportedLngs: true,
    detection: {
      // Pilihan tersimpan menang; kalau belum ada, ikuti bahasa browser.
      order: ['localStorage', 'navigator', 'htmlTag'],
      lookupLocalStorage: LANGUAGE_STORAGE_KEY,
      caches: ['localStorage'],
    },
    interpolation: {
      // React sudah meng-escape output.
      escapeValue: false,
    },
    returnEmptyString: false,
  });

// Jaga atribut lang pada <html> tetap sinkron untuk aksesibilitas.
const syncHtmlLang = (lng) => {
  if (typeof document !== 'undefined') {
    document.documentElement.setAttribute('lang', lng);
  }
};

syncHtmlLang(i18n.resolvedLanguage || 'en');
i18n.on('languageChanged', syncHtmlLang);

export default i18n;

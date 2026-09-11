import i18n from '../../i18n';

/**
 * BCP 47 tag untuk Intl / toLocaleString, mengikuti bahasa aktif i18next.
 *
 * Dipakai helper non-komponen (dateTimeHelper, wellnessHelper) yang tidak bisa
 * memakai hook useTranslation.
 */
export function localeTag() {
    return i18n.resolvedLanguage === 'id' ? 'id-ID' : 'en-US';
}

// Pembungkus tipis supaya helper biasa tetap ikut bahasa aktif.
export function translate(key, options) {
    return i18n.t(key, options);
}

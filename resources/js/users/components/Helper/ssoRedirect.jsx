/**
 * Ke mana pengguna dikembalikan saat sesi tidak bisa dilanjutkan.
 *
 * Aplikasi karyawan tidak punya halaman login sendiri: token JWT-nya dikirim
 * oleh portal SSO. Jadi ketika token gagal diverifikasi, satu-satunya jalan
 * keluar yang masuk akal adalah kembali ke portal tersebut, bukan menahan
 * pengguna di dalam SPA.
 */

// Bisa ditimpa lewat VITE_SSO_URL bila portalnya pindah; nilai bawaan sama
// dengan URL yang sebelumnya ditulis langsung di banyak berkas.
export const SSO_URL = import.meta.env.VITE_SSO_URL || 'https://kpncorporation.darwinbox.com/';

const originOf = (url) => {
    try {
        return new URL(url).origin;
    } catch {
        return null;
    }
};

/**
 * Halaman asal pengguna, tapi hanya bila benar-benar berasal dari portal SSO.
 *
 * `document.referrer` bisa diisi situs mana pun yang menautkan ke
 * /login-success, jadi nilainya dipakai hanya kalau origin-nya cocok dengan
 * SSO_URL. Selain itu kita kembali ke beranda portal.
 */
export const ssoReturnUrl = () => {
    const referrer = typeof document !== 'undefined' ? document.referrer : '';

    if (referrer && originOf(referrer) === originOf(SSO_URL)) {
        return referrer;
    }

    return SSO_URL;
};

/**
 * Keluar dari SPA menuju portal SSO.
 *
 * Memakai `replace` supaya halaman gagal login tidak tertinggal di riwayat --
 * tombol back sebelumnya mengembalikan pengguna ke URL token yang sudah mati
 * dan memicu percobaan verifikasi yang gagal lagi.
 */
export const redirectToSso = () => {
    window.location.replace(ssoReturnUrl());
};

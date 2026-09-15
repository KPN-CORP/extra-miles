import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';

import LanguageToggle from '../Layout/LanguageToggle';
import { useAuth } from '../Context/AuthContext';

/**
 * Banner kampanye dari tim Corcom.
 *
 * Berkasnya ada di `public/img/`, yang disalin apa adanya oleh `.cpanel.yml`
 * ke docroot -- jadi path root-relative, bukan lewat VITE_API_URL/storage
 * seperti gambar yang diunggah admin.
 *
 * Ukuran aslinya 1920x1080 (16:9). `aspect-[16/9]` memakai rasio yang sama
 * persis, sehingga `object-cover` tidak memotong apa pun sekaligus memesan
 * ruangnya lebih dulu agar layout tidak melompat saat gambar selesai dimuat.
 */
const BANNER_SRC = '/img/banner-extra-mile-2.jpg';

/**
 * Potongan diagonal pita sapaan.
 *
 * Pita ditarik naik 70px lalu sudut kanan-atasnya dipotong 61px, sehingga merah
 * masuk ke sudut kiri-bawah banner yang memang kosong. Kemiringannya naik ke
 * kanan supaya tidak pernah memotong grafis lingkaran di kanan banner.
 *
 * Titik ketiga sengaja di 105%, bukan 100%: dengan begitu clip tidak pernah
 * menyentuh tepi kanan elemen, sehingga tidak muncul garis tipis banner yang
 * bocor di sisi kanan karena pembulatan sub-piksel.
 *
 * Angkanya diukur langsung pada artwork banner, bukan dihitung -- jadi kalau
 * banner-nya diganti, keduanya perlu dilihat ulang.
 */
const BAND_STYLE = {
    marginTop: "-70px",
    clipPath: "polygon(0px 0px, 100% 61px, 105% 100%, 0px 100%)",
};

// Dua huruf pertama dari nama depan dan belakang; jadi avatar tanpa perlu foto
// (endpoint /api/profile tidak mengirim gambar).
const initialsOf = (name) => {
    const parts = String(name ?? '').trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return '?';

    const first = parts[0][0];
    const last = parts.length > 1 ? parts[parts.length - 1][0] : '';

    return (first + last).toUpperCase();
};

export default function HomeHero() {
    const { user } = useAuth();
    const { t } = useTranslation();

    // Banner gagal dimuat tidak boleh menyisakan ikon "gambar rusak"; pita
    // sapaan di bawahnya sudah cukup berdiri sendiri.
    const [bannerFailed, setBannerFailed] = useState(false);

    return (
        <div className="bg-white">
            {/* Area notch ikut putih supaya menyatu dengan banner. */}
            <div className="pt-safe" />

            {!bannerFailed && (
                <div className="relative">
                    <img
                        src={BANNER_SRC}
                        alt={t('home.bannerAlt')}
                        className="block w-full aspect-[16/9] object-cover object-center"
                        loading="eager"
                        fetchPriority="high"
                        onError={() => setBannerFailed(true)}
                    />

                    {/* Pemilih bahasa duduk di atas banner, di sudut yang kosong.
                        Di pita merah tempatnya sudah diambil nama karyawan. */}
                    <LanguageToggle variant="onBanner" className="absolute left-4 top-3.5" />
                </div>
            )}

            {/* Pita sapaan. Tanpa banner (gambar gagal dimuat) tidak ada yang bisa
                dipotong, jadi diagonalnya dilepas dan pitanya rata seperti biasa. */}
            <div
                style={bannerFailed ? undefined : BAND_STYLE}
                className={`relative overflow-hidden bg-gradient-to-br from-brand-700 to-[#a30000] px-5 pb-6 flex items-center gap-3.5 ${
                    bannerFailed ? 'pt-5' : 'pt-12'
                }`}
            >
                {/* Gema lingkaran konsentris dari banner, supaya pita sapaan
                    terbaca sebagai bagian dari kampanye yang sama. */}
                <svg
                    viewBox="0 0 230 230"
                    className="absolute -right-16 -top-12 w-[230px] h-[230px] opacity-[0.15] pointer-events-none"
                    aria-hidden="true"
                >
                    <g fill="none" stroke="#ffffff" strokeWidth="2">
                        <circle cx="115" cy="115" r="40" />
                        <circle cx="115" cy="115" r="61" />
                        <circle cx="115" cy="115" r="82" strokeDasharray="6 10" />
                        <circle cx="115" cy="115" r="103" strokeDasharray="2 14" />
                    </g>
                </svg>

                <span className="relative w-[52px] h-[52px] shrink-0 rounded-full grid place-items-center bg-white/20 ring-[1.5px] ring-white/45 text-white text-base font-extrabold tracking-[0.02em]">
                    {initialsOf(user?.fullname)}
                </span>

                <div className="relative flex-1 min-w-0">
                    {/* NIK di atas, nama di bawah: nomornya jadi label kecil dan
                        nama yang dibaca. Jabatan tidak lagi ditampilkan di sini. */}
                    <p className="text-white/80 text-[11.5px] font-semibold tracking-[0.012em] leading-tight clamp-1">
                        {user?.employee_id || '-'}
                    </p>
                    <p className="mt-0.5 text-white text-xl font-bold tracking-[-0.024em] leading-tight clamp-1">
                        {user?.fullname || '-'}
                    </p>
                </div>
            </div>
        </div>
    );
}

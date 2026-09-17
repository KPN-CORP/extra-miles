import React from 'react';
import { motion } from 'motion/react';

import AppNav from './AppNav';
import BrandWatermark from './BrandWatermark';

// Transisi halus antar halaman -- AnimatePresence di app.jsx berjalan dengan
// mode="wait", jadi halaman lama selesai dulu sebelum yang baru muncul.
const pageVariants = {
    initial: { opacity: 0, y: 8 },
    animate: { opacity: 1, y: 0 },
    exit: { opacity: 0, y: -6 },
};

/**
 * Pembungkus standar setiap halaman aplikasi karyawan.
 *
 * Menyatukan latar, lebar kolom, jarak aman notch, ruang untuk navigasi, dan
 * animasi transisi supaya tiap halaman tidak menulis ulang kombinasi kelas yang
 * sama.
 *
 * Lebar kolom dan bentuk navigasi ikut keadaan layar (lihat --app-column dan
 * --app-rail-w di global.css); halaman tidak perlu tahu sedang di ponsel,
 * ponsel landscape, atau tablet.
 */
export default function AppShell({
    children,
    nav = false,
    surface = 'default',
    watermark = false,
    className = '',
    contentClassName = '',
}) {
    // 'brand' dipakai halaman yang memang berlatar merah penuh (Media Sosial).
    const surfaceClass = surface === 'brand' ? 'bg-brand-700 text-white' : 'app-bg';

    return (
        <motion.div
            variants={pageVariants}
            initial="initial"
            animate="animate"
            exit="exit"
            transition={{ duration: 0.22, ease: 'easeOut' }}
            className={`app-surface ${nav ? 'app-has-nav' : ''} ${surfaceClass} relative w-full ${className}`}
        >
            {/* Cap air digambar lebih dulu dan diberi z-0; konten di bawahnya
                memakai z-10. Keduanya harus punya z-index eksplisit: elemen
                `fixed` selalu dilukis di atas konten yang tidak diposisikan,
                jadi tanpa ini cap airnya menutupi teks. Tidak dipasang di
                halaman berlatar brand (Media Sosial) -- di sana ia tenggelam. */}
            {watermark && surface === 'default' && <BrandWatermark />}

            <div className={`app-container relative z-10 ${nav ? 'pb-nav' : 'pb-8'} ${contentClassName}`}>
                {children}
            </div>

            {nav && <AppNav />}
        </motion.div>
    );
}

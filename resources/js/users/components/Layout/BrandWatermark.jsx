import React from 'react';

/**
 * Elemen lingkaran Extra Mile sebagai cap air yang menetap di kanan bawah.
 *
 * Posisinya `fixed`, jadi ia tidak ikut bergulir bersama konten -- sama seperti
 * tampilan lama. Bedanya sekarang ia benar-benar jadi latar: opacity rendah,
 * duduk di bawah konten, dan `pointer-events: none` supaya tidak pernah mencuri
 * sentuhan dari kartu di atasnya.
 *
 * Aset aslinya (`Element Extra Mile 1.png`) berukuran 3407x3385 dan ~2 MB --
 * terlalu berat untuk ponsel. Yang dipakai di sini versi 420px (~125 KB) yang
 * diturunkan dari berkas itu; kalau artwork-nya diganti, versi kecilnya harus
 * dibuat ulang juga.
 */
const WATERMARK_SRC = '/img/element-extra-mile-watermark.png';

export default function BrandWatermark() {
    return (
        <div
            aria-hidden="true"
            // inset-x-0 + flex justify-center menahannya di dalam kolom 480px
            // yang sama dengan konten, bukan di tepi viewport -- supaya di tablet
            // sempit ia tidak melayang di gutter.
            className="pointer-events-none select-none fixed inset-x-0 bottom-0 z-0 flex justify-center"
        >
            <div className="app-container relative">
                <img
                    src={WATERMARK_SRC}
                    alt=""
                    // Sebagian keluar dari tepi kanan supaya terbaca sebagai
                    // tekstur yang terpotong layar, bukan stiker yang ditempel.
                    // Diangkat setinggi tab bar agar tidak tertimbun di baliknya.
                    className="absolute -right-14 w-56 max-w-none opacity-[0.09]"
                    style={{ bottom: 'calc(var(--app-nav-h) + var(--app-safe-bottom) + 8px)' }}
                    loading="lazy"
                    decoding="async"
                    draggable="false"
                />
            </div>
        </div>
    );
}

/**
 * Nilai awal animasi "membesar dari kartu yang ditekan".
 *
 * Tile di beranda mengirim `bounds` (getBoundingClientRect milik tombolnya)
 * lewat state router, sehingga halaman tujuan bisa mulai dari posisi dan ukuran
 * tile itu lalu memuai ke layar penuh.
 *
 * Yang penting: `bounds` sering TIDAK ada -- saat URL diketik langsung, saat
 * halaman di-refresh, saat dibuka dari tab bar, atau saat kembali lewat
 * fallback `backTo`. Dulu tiap halaman menangani kasus itu dengan
 * `if (!initialStyle) return null`, yang berarti halamannya blank permanen.
 * Di sini ketiadaan `bounds` cukup berarti "tanpa animasi zoom": halaman tampil
 * apa adanya.
 */

// Tanpa skala dan tanpa geser: halaman langsung pada posisi akhirnya.
const NO_ZOOM = {
    scaleX: 1,
    scaleY: 1,
    offsetX: 0,
    offsetY: 0,
    borderRadius: 0,
};

export function zoomFrom(bounds) {
    if (!bounds) return NO_ZOOM;

    return {
        scaleX: bounds.width / window.innerWidth,
        scaleY: bounds.height / window.innerHeight,
        offsetX: bounds.left + bounds.width / 2 - window.innerWidth / 2,
        offsetY: bounds.top + bounds.height / 2 - window.innerHeight / 2,
        // Sama dengan rounded-2xl pada tile asalnya.
        borderRadius: 16,
    };
}

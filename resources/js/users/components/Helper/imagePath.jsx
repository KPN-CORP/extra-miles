// URL gambar unggahan selalu lewat route /images/... (ImageController), bukan
// /storage/... Di server document root cPanel terpisah dari public/ aplikasi,
// jadi symlink storage tidak ada di sana dan semua /storage/... berakhir di
// catch-all SPA -- HTML shell masuk ke tag <img> dan gambar tampil rusak.
//
// Nilai di database bisa berupa path bersarang ("assets/images/news/news_4.jpg")
// maupun nama file polos ("wellness-morning-yoga.png"); keduanya dilayani dari
// storage/app/public oleh route yang sama.
export function getImageUrl(apiUrl, image) {
    if (!image) return '';

    // URL absolut dipakai apa adanya.
    if (/^https?:\/\//i.test(image)) return image;

    const path = String(image)
        .replace(/\\/g, '/')
        .replace(/^\/+/, '')
        .replace(/^storage\//, '');

    // Spasi dan karakter lain pada nama file harus di-encode per segmen supaya
    // garis miring pemisah folder tetap utuh.
    const encoded = path.split('/').map(encodeURIComponent).join('/');

    return `${apiUrl}/images/${encoded}`;
}

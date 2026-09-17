import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './resources/js/**/*.{js,jsx}', // Include React files
        './resources/views/**/*.blade.php', // Include Blade templates (if applicable)
    ],
    theme: {
        extend: {
            colors: {
                // Skala merah KPN. Dipakai lewat `brand-*` supaya nilai brand
                // tidak tercampur dengan `red-*` bawaan Tailwind.
                //
                // brand-700 adalah #c00000, warna yang diambil langsung dari
                // wordmark "EXTRA MILE 2.0" di public/img/extra_mile.png. Nilai
                // lama (#b91c1c) beda tipis dari logo, sehingga header, tile dan
                // tombol terlihat "hampir sama" tapi tidak pernah sama.
                brand: {
                    50: '#fdf0f0',
                    // Permukaan tile Quick Access. Sengaja terpisah dari 50,
                    // yang masih dipakai chip tanggal event dan pil tab aktif.
                    75: '#ffecec',
                    100: '#fde4e1',
                    200: '#fbcdc7',
                    300: '#f7aaa0',
                    400: '#ef7a6b',
                    500: '#e15140',
                    600: '#cc3524',
                    700: '#c00000',
                    800: '#8a0000',
                    900: '#730000',
                    // Merah tua rendah-saturasi untuk teks/ikon di atas tint.
                    // Di luar ramp merah murni (jauh kurang jenuh), tapi diberi
                    // nomor supaya tetap ikut satu skala.
                    850: '#812121',
                },
            },
            boxShadow: {
                // Bayangan lembut khas kartu aplikasi mobile.
                card: '0 2px 12px -4px rgb(120 53 15 / 0.14)',
                // Tile Quick Access: lebih tebal dari kartu biasa dan diwarnai
                // merah tua yang sama dengan ikon/labelnya, supaya permukaan
                // tint-nya terangkat jelas dari latar halaman.
                tile: '0 4px 4px -2px rgb(129 33 33 / 0.20)',
                float: '0 10px 30px -12px rgb(120 53 15 / 0.30)',
                nav: '0 -6px 24px -12px rgb(120 53 15 / 0.35)',
            },
            animation: {
                slideUp: 'slideUp 0.2s ease-out',
                slideDown: 'slideDown 0.2s ease-in',
                marquee: 'marquee 10s linear infinite',
                fadeUp: 'fadeUp 0.35s ease-out both',
                shimmer: 'shimmer 1.4s linear infinite',
            },
            keyframes: {
                slideUp: {
                    '0%': { transform: 'translateY(100%)' },
                    '100%': { transform: 'translateY(0)' },
                },
                slideDown: {
                    '0%': { transform: 'translateY(0)' },
                    '100%': { transform: 'translateY(100%)' },
                },
                fadeUp: {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                shimmer: {
                    '0%': { backgroundPosition: '-200% 0' },
                    '100%': { backgroundPosition: '200% 0' },
                },
            },
            fontFamily: {
                // Albert Sans. Nama family-nya harus tepat "Albert Sans" --
                // "Albert" saja tidak ada di Google Fonts dan akan jatuh
                // diam-diam ke sans-serif generik.
                sans: ['Albert Sans', 'sans-serif'],
                inter: ['Inter', 'sans-serif'], // Add Inter // Add Montserrat
            },
            screens: {
                xs: "360px", // Define the xs breakpoint (e.g., 360px)
                sm: "640px", // Default sm breakpoint
                md: "768px", // Default md breakpoint
                lg: "1024px", // Default lg breakpoint
                xl: "1280px", // Default xl breakpoint
                "2xl": "1536px", // Default 2xl breakpoint
            },
        },
    },
    plugins: [],
};

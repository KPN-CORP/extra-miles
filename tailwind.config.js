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
            animation: {
                slideUp: 'slideUp 0.2s ease-out',
                slideDown: 'slideDown 0.2s ease-in',
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
            },
            fontFamily: {
                sans: ['Montserrat', 'sans-serif'], // Add Montserrat
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

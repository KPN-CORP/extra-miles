import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css", 
                "resources/js/users/main.jsx",
            ],
            refresh: true,
        }),
        react(),
    ],
    build: {
        sourcemap: false,
        rollupOptions: {
            output: {
                globals: {
                //    jquery: 'window.jQuery',
                   jquery: 'window.$'
                }
            }
        }
    },
    resolve: {
        alias: {
            $: "jQuery",
            Swal: path.resolve(__dirname, "node_modules/sweetalert2"),
            select2: path.resolve(__dirname, "node_modules/select2"),
        },
    },
    optimizeDeps: {
        include: ['select2'],
    },
    css: {
        preprocessorOptions: {
            css: {
                importLoaders: 1,
                modules: true,
            },
        },
    },
});

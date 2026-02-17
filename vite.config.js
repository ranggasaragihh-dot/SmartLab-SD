import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost', // Ganti dengan IP Laptop Anda
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],

    build: {
        // Mencegah Vite melakukan preload module script secara agresif
        modulePreload: {
            polyfill: false,
        },
    },
});

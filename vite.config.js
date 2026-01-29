import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
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

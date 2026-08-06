import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],

    server: {
        host: true,
        port: 5173,
        strictPort: true,

        cors: true,

        origin: 'http://192.168.1.21:5173',

        hmr: {
            protocol: 'ws',
            host: '192.168.1.21',
            port: 5173,
        },
    },
});
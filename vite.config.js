import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'
import path from 'path';

export default defineConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            // '@assets': path.resolve(__dirname, './resources/js/assets'),
            '@components': path.resolve(__dirname, './resources/js/components'),
        },
        extensions: ['.js', '.vue', '.json'],
    },
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        cors: {
            origin: [
                'http://golapp.local',
            ],
        },
    }
});
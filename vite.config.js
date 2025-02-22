import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'
import path from 'path';

export default defineConfig({
    build: {
        minify: true,
        sourcemap: true,
        chunkSizeWarningLimit: 1000,
        reportCompressedSize: true
    },
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, './resources/js'),
            // '@assets': path.resolve(__dirname, './resources/js/assets'),
            '@components': path.resolve(__dirname, './resources/js/components'),
            // $: 'jquery',
            // jquery: 'jquery',
            // 'window.jQuery': 'jquery',
            // jQuery: 'jquery'
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
                'https://app.golapp.com.co'
            ],
        },
    }
});
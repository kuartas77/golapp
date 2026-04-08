import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'
import path from 'node:path';

export default defineConfig({
    plugins: [
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        laravel({
            input: ['resources/js/main.js'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, './resources/js'),
            '@components': path.resolve(__dirname, './resources/js/components'),
        },
        extensions: ['.js', '.vue', '.json'],
    },
    // optimizeDeps: {
    //     include: ["quill", "nouislider"],
    // },
    assetsInclude: ["resources/js/assets"],
    server: {
        cors: {
            origin: [
                'http://golapp.local',
            ],
        },
    },
    build: {
        chunkSizeWarningLimit: 1600,
        sourcemap: process.env.NODE_ENV !== 'production',
        reportCompressedSize: true,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('echarts')) return 'echarts';
                        if (id.includes('apexcharts')) return 'apexcharts';
                        if (id.includes('sweetalert2')) return 'sweetalert2';
                        if (id.includes('quill')) return 'quill';
                        if (id.includes('datatables')) return 'datatables';
                        return 'vendor';
                    }
                },
            },
        }
    }
});
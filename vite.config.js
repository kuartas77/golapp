import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'
import path from 'path';

export default defineConfig({
    plugins: [
        vue({
            template: {
                transformAssetUrls: {
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
            '@': path.resolve(__dirname, './resources/js'),
            '@components': path.resolve(__dirname, './resources/js/components'),
        },
        extensions: ['.js', '.vue', '.json'],
    },
    optimizeDeps: {
        include: ["quill", "nouislider"],
    },
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
        sourcemap: true,
        reportCompressedSize: true,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        // Group all vendor dependencies into a single 'vendor' chunk
                        return 'vendor';
                    }
                },
            },
        }
    }
});
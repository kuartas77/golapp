import path from 'node:path';

import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vitest/config';

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
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            '@components': path.resolve(__dirname, './resources/js/components'),
        },
        extensions: ['.js', '.vue', '.json'],
    },
    test: {
        environment: 'jsdom',
        globals: true,
        include: ['resources/js/tests/**/*.spec.js'],
        setupFiles: ['resources/js/tests/setup.js'],
        restoreMocks: true,
        clearMocks: true,
        unstubGlobals: true,
    },
});

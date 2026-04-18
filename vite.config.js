import path from 'node:path';

import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

function stripVendorSourceMapComments() {
    const sourceMapCommentPattern = /\/\*# sourceMappingURL=.*?\*\/|\/\/# sourceMappingURL=.*$/gm;

    return {
        name: 'strip-vendor-source-map-comments',
        enforce: 'pre',
        transform(code, id) {
            if (!id.includes('/node_modules/')) {
                return null;
            }

            if (!/\.(css|js|mjs)(?:$|\?)/.test(id)) {
                return null;
            }

            if (!sourceMapCommentPattern.test(code)) {
                sourceMapCommentPattern.lastIndex = 0;
                return null;
            }

            sourceMapCommentPattern.lastIndex = 0;

            return {
                code: code.replace(sourceMapCommentPattern, ''),
                map: null,
            };
        },
    };
}

function resolveVendorChunk(id) {
    if (!id.includes('/node_modules/')) {
        return undefined;
    }

    if (
        id.includes('/apexcharts/') ||
        id.includes('/vue3-apexcharts/')
    ) {
        return 'apexcharts';
    }

    if (
        id.includes('/datatables.net') ||
        id.includes('/datatables.net-vue3/')
    ) {
        return 'datatables';
    }

    if (
        id.includes('/sweetalert2/') ||
        id.includes('/vue-sweetalert2/')
    ) {
        return 'sweetalert2';
    }

    if (
        id.includes('/bootstrap/') ||
        id.includes('/@popperjs/core/') ||
        id.includes('/perfect-scrollbar/') ||
        id.includes('/vue3-perfect-scrollbar/')
    ) {
        return 'ui';
    }

    if (
        id.includes('/flatpickr/') ||
        id.includes('/vue-flatpickr-component/') ||
        id.includes('/vee-validate/') ||
        id.includes('/yup/')
    ) {
        return 'forms';
    }

    if (
        id.includes('/vue/') ||
        id.includes('/vue-router/') ||
        id.includes('/pinia/') ||
        id.includes('/vue-i18n/') ||
        id.includes('/@vueuse/')
    ) {
        return 'vue-core';
    }

    return 'vendor';
}

export default defineConfig(({ mode }) => ({
    plugins: [
        stripVendorSourceMapComments(),
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
            '@': path.resolve(__dirname, './resources/js'),
            '@components': path.resolve(__dirname, './resources/js/components'),
        },
        extensions: ['.js', '.vue', '.json'],
    },
    optimizeDeps: {
        include: [
            'bootstrap',
            'datatables.net-bs5',
            'datatables.net-responsive-bs5',
            'datatables.net-vue3',
            'flatpickr',
            'sweetalert2',
            'vue-flatpickr-component',
            'vue-sweetalert2',
            'vue3-perfect-scrollbar',
        ],
    },
    server: {
        cors: {
            origin: [
                'http://golapp.local',
            ],
        },
    },
    build: {
        chunkSizeWarningLimit: 700,
        sourcemap: mode !== 'production',
        reportCompressedSize: false,
        rollupOptions: {
            output: {
                manualChunks: resolveVendorChunk,
            },
        },
    },
}));

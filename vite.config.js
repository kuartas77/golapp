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
            input: [
                'resources/css/app.css',
                'resources/js/app.js',

                'resources/layouts/css/dark/elements/alert.css',
                'resources/layouts/css/dark/elements/color_library.css',
                'resources/layouts/css/dark/elements/custom-pagination.css',
                'resources/layouts/css/dark/elements/custom-tree_view.css',
                'resources/layouts/css/dark/elements/custom-typography.css',
                'resources/layouts/css/dark/elements/infobox.css',
                'resources/layouts/css/dark/elements/popover.css',
                'resources/layouts/css/dark/elements/search.css',
                'resources/layouts/css/dark/elements/tooltip.css',
                'resources/layouts/css/dark/loader.css',
                'resources/layouts/css/dark/main.css',
                'resources/layouts/css/dark/plugins.css',
                'resources/layouts/css/dark/scrollspyNav.css',
                'resources/layouts/css/dark/structure.css',
                'resources/layouts/plugins/css/dark/perfect-scrollbar/perfect-scrollbar.css',

                'resources/layouts/css/light/elements/alert.css',
                'resources/layouts/css/light/elements/color_library.css',
                'resources/layouts/css/light/elements/custom-pagination.css',
                'resources/layouts/css/light/elements/custom-tree_view.css',
                'resources/layouts/css/light/elements/custom-typography.css',
                'resources/layouts/css/light/elements/infobox.css',
                'resources/layouts/css/light/elements/popover.css',
                'resources/layouts/css/light/elements/search.css',
                'resources/layouts/css/light/elements/tooltip.css',
                'resources/layouts/css/light/loader.css',
                'resources/layouts/css/light/main.css',
                'resources/layouts/css/light/plugins.css',
                'resources/layouts/css/light/scrollspyNav.css',
                'resources/layouts/css/light/structure.css',
                'resources/layouts/plugins/css/light/perfect-scrollbar/perfect-scrollbar.css',

                'resources/layouts/plugins/src/waves/waves.min.css',
                'resources/layouts/plugins/src/highlight/styles/monokai-sublime.css',
            ],
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
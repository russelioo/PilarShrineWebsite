import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import searchContentPlugin from './build/searchContentPlugin.js';

export default defineConfig({
    plugins: [
        searchContentPlugin(),
        laravel({
            input: ['resources/css/app.css', 'resources/css/portal-theme.css', 'resources/js/app.js', 'resources/js/parish.js', 'resources/js/messages.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue(),
    ],
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/sass/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        ...(process.env.LARAVEL_VITE_DEV
            ? { host: '0.0.0.0', port: Number(process.env.VITE_PORT) || 5173, strictPort: true, hmr: { host: 'localhost' } }
            : {}),
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
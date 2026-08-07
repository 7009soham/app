import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // admin-ui.css is the Tailwind + DaisyUI entry used only by pages
            // extending admin.layouts.tailwind. Kept separate from app.css so
            // Tailwind's preflight cannot reach the unmigrated pages.
            input: [
                'resources/css/app.css',
                'resources/css/admin-ui.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

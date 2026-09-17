import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        {
            name: 'fraca-login-url',
            configureServer() {
                setTimeout(() => {
                    console.log('\n  Open the inventory (not this Vite port):\n  ➜  http://127.0.0.1:8000/login\n');
                }, 100);
            },
        },
    ],
});

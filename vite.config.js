import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        outDir: 'public/build',
        rollupOptions: {
            input: {
                app: 'resources/css/app.css',
                portfolio: 'public/css/portfolio.css',
                portfolioJs: 'public/js/portfolio.js'
            }
        }
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
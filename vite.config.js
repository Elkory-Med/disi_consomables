import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            '~': '/resources'
        }
    },
    optimizeDeps: {
        include: ['apexcharts', 'alpinejs'],
        exclude: []
    },
    build: {
        target: 'esnext',
        chunkSizeWarningLimit: 2000,
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    if (id.includes('node_modules/apexcharts')) {
                        return 'apexcharts';
                    }
                    if (id.includes('node_modules/alpinejs')) {
                        return 'alpine';
                    }
                    if (id.includes('resources/js/charts.js')) {
                        return 'charts';
                    }
                }
            }
        }
    },
    server: {
        hmr: {
            host: 'localhost'
        }
    },
    define: {
        'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'development')
    }
});

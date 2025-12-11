import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            protocol: 'ws',
            host: 'localhost',
            port: 5173,
            clientPort: 5173,
            overlay: true,
        },
        watch: {
            usePolling: true,  // Better file watching on Windows
            interval: 100,
        },
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        },
    },
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
            // Fix: Prevent Sucrase from processing scoped styles
            script: {
                babelParserPlugins: ['jsx']
            }
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    build: {
        // Code splitting for better performance
        rollupOptions: {
            output: {
                // Manual chunks for better caching
                manualChunks: {
                    'vendor': [
                        'vue',
                        '@inertiajs/vue3',
                    ],
                    'heroicons': ['@heroicons/vue/24/outline', '@heroicons/vue/24/solid'],
                },
                // Cache busting with content hashes
                entryFileNames: `assets/[name].[hash].js`,
                chunkFileNames: `assets/[name].[hash].js`,
                assetFileNames: `assets/[name].[hash].[ext]`,
            },
        },
        // Optimize chunk size
        chunkSizeWarningLimit: 1000,
        // Use esbuild for faster minification
        minify: 'esbuild',
        // Source maps for debugging (disable in production)
        sourcemap: false,
    },
    // Optimize dependencies
    optimizeDeps: {
        include: [
            'vue',
            '@inertiajs/vue3',
            '@heroicons/vue/24/outline',
            '@heroicons/vue/24/solid',
        ],
    },
});

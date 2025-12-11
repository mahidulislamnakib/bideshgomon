// CSS imports disabled due to Sucrase parser bug affecting PostCSS
// Workaround: Using Tailwind CDN in app.blade.php
// TODO: Fix Sucrase configuration or upgrade to newer parser
// import '../css/app.css';
// import '../css/layout-fixes.css';
// import '../css/performance.css';
// import 'flag-icons/css/flag-icons.min.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Import PWA Manager - PERMANENTLY DISABLED
// import { pwa } from './pwa';


// Import lazy load directives
import { lazyLoadDirective, lazyLoadBgDirective } from './directives/lazyLoad';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Clear expired cache on app start


createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);
        
        // Register global directives
        app.directive('lazy', lazyLoadDirective);
        app.directive('lazy-bg', lazyLoadBgDirective);
        
        // Global error handler - prevents reload on uncaught errors
        app.config.errorHandler = (err, instance, info) => {
            console.error('Vue Error:', err);
            console.error('Component:', instance?.$options?.name || 'Unknown');
            console.error('Error Info:', info);
            // Log to monitoring service here if available
            // Do NOT reload the page
        };
        
        return app.mount(el);
    },
    progress: {
        color: '#e4282b',
        showSpinner: true,
        includeCSS: true,
        delay: 250
    }
});

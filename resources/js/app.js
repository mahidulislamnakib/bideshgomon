// Import CSS - World Class UI/UX Redesign
import '../css/app.css';

import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';
import { Toaster } from 'vue-sonner';

// Import stores
import { useAuthStore } from './stores/auth';
import { useWalletStore } from './stores/wallet';
import { useNotificationStore } from './stores/notifications';

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
        const pinia = createPinia();
        
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia);
        
        // Register global components
        app.component('Toaster', Toaster);
        
        // Register global directives
        app.directive('lazy', lazyLoadDirective);
        app.directive('lazy-bg', lazyLoadBgDirective);
        
        // Initialize stores with page props
        const authStore = useAuthStore();
        const walletStore = useWalletStore();
        const notificationStore = useNotificationStore();
        
        if (props.initialPage.props) {
            authStore.initFromPageProps(props.initialPage.props);
            walletStore.initFromPageProps(props.initialPage.props);
            notificationStore.initFromPageProps(props.initialPage.props);
        }
        
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

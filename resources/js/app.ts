import 'vue-sonner/style.css';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { useSonnerToast } from '@/composables/useSonnerToast';
import { Toaster } from '@/components/ui/sonner';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia);

        // Global error handler for unhandled Vue errors
        app.config.errorHandler = (error, instance, info) => {
            console.error('Global Vue Error Handler:', error);
            console.error('Component instance:', instance);
            console.error('Error info:', info);

            // Show toast notification for unhandled errors
            const toast = useSonnerToast();
            toast.error('Application Error', {
                description: 'An unexpected error occurred. Please reload the page if the problem persists.'
            });

            // In production, you might want to send this to an error reporting service
            if (import.meta.env.PROD) {
                // Example: Send to error reporting service
                // errorReportingService.captureException(error, { extra: { info, instance } });
            }
        };

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

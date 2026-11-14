import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h, type DefineComponent } from 'vue';
import { configureEcho } from '@laravel/echo-vue';

configureEcho({
    broadcaster: 'reverb',
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue', { import: 'default' });

void createInertiaApp({
    resolve: (name) => {
        const page = pages[`./pages/${name}.vue`];
        if (!page) throw new Error(`Page not found: ${name}`);
        return page();
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: '#4B5563',
    },
});

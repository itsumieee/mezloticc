import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { AnimatePresence } from 'framer-motion';
import '../css/app.css';

const pages = import.meta.glob('./Pages/**/*.jsx');

createInertiaApp({
    resolve: async (name) => {
        const page = pages[`./Pages/${name}.jsx`];

        if (!page) {
            throw new Error(`Inertia page "${name}" was not found.`);
        }

        return page();
    },
    setup({ el, App, props }) {
        createRoot(el).render(
            <React.StrictMode>
                <AnimatePresence mode="wait">
                    <App {...props} />
                </AnimatePresence>
            </React.StrictMode>,
        );
    },
    progress: {
        color: '#ed4b43',
        showSpinner: false,
    },
});

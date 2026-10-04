import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        react(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('/node_modules/three/')) return 'three-core';
                    if (id.includes('/node_modules/three-stdlib/')) return 'three-stdlib';
                    if (id.includes('/node_modules/@react-three/fiber/')) return 'react-three-fiber';
                    if (id.includes('/node_modules/@react-three/drei/')) return 'react-three-drei';
                },
            },
        },
    },
});

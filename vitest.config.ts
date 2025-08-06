import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [vue()],
    test: {
        globals: true,
        environment: 'jsdom',
        setupFiles: ['./resources/js/tests/setup.ts'],
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, './resources/js'),
        },
    },
});

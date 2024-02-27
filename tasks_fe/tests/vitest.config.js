import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        exclude: ['**/node_modules/**', '**/playwright/**'],
        reporters: ['verbose'],
        cacheDir: '/tmp/',
        logHeapUsage: true,
        coverage: {
            enabled: true,
            allowExternal: true,
            provider: 'v8',
            reporter: [['text', { maxCols: 120 }]],
            exclude: ['**/tests/**']
        }
    }
});

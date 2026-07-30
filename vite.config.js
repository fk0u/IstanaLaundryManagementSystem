import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

/**
 * Dynamic per-environment Vite config.
 *
 * Reads VITE_DEV_SERVER_URL from .env (or the process environment).
 *
 * - Local dev (default): HMR points to localhost, no CORS tweaks needed.
 * - Sharing via ngrok/tunnel on port 8000: set in .env
 *       VITE_DEV_SERVER_URL=https://abc-123.ngrok-free.app
 *   Vite writes that URL into public/hot so Laravel serves asset URLs from
 *   the tunnelled origin (no CORS / mixed-content errors).
 *
 * NOTE: remember to restart `npm run dev` whenever .env or this value changes.
 */
export default defineConfig(({ mode }) => {
    // loadEnv reads .env, .env.[mode], and process.env; pick up the tunnel URL.
    const env = loadEnv(mode, process.cwd(), '');
    const devServerUrl = env.VITE_DEV_SERVER_URL || process.env.VITE_DEV_SERVER_URL;

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
        ],
        server: {
            host: '0.0.0.0',
            // When tunnelling, force HMR + asset URLs to the public origin so the
            // browser (served over HTTPS via the tunnel) can reach the dev server.
            ...(devServerUrl
                ? {
                      hmr: { host: new URL(devServerUrl).host, protocol: 'wss', clientPort: 443 },
                      origin: devServerUrl,
                      cors: true,
                  }
                : { hmr: { host: 'localhost' } }),
            watch: {
                usePolling: true,
                ignored: ['**/.env', '**/node_modules/**', '**/vendor/**'],
            },
        },
    };
});

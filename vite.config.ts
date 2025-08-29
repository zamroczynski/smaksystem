import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';
import { visualizer } from 'rollup-plugin-visualizer';

const VITE_PORT = Number(process.env.VITE_PORT) || 5173;
const VITE_HOST = process.env.VITE_SERVER_HOST || 'localhost';
const VITE_ORIGIN = process.env.VITE_ORIGIN || `http://${VITE_HOST}:${VITE_PORT}`;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        visualizer(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
    host: '0.0.0.0',           
    port: VITE_PORT,
    origin: VITE_ORIGIN,      
    cors: true,               
    hmr: {
      host: VITE_HOST,        
      protocol: 'ws',         
      clientPort: VITE_PORT,  
    },
  },
});

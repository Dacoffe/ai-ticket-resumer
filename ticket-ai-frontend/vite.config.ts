import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite';

// https://vite.dev/config/

export default defineConfig({
    plugins: [vue(), tailwindcss()],
    server: {
        port: 5173,
        // Em desenvolvimento, o browser fala só com o Vite (mesma origem) e o Vite
        // reencaminha /api para o Laravel. Assim não há problemas de CORS.
        proxy: {
            '/api': 'http://localhost:8000',
        },
    },
});
import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [svelte(), tailwindcss()],
  base: '/movie-svar/',
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    assetsInlineLimit: 0 // Asegura que los assets no se conviertan en base64
  }
});
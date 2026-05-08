import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'

// https://vite.dev/config/
export default defineConfig({
  plugins: [svelte()],
  base: '/my-movie-app2/',
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    assetsInlineLimit: 0 // Asegura que los assets no se conviertan en base64
  }
});
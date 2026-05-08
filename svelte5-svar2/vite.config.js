import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'
import path from 'path';

// https://vite.dev/config/
export default {
  plugins: [svelte()],
  base: '/my-svar-app5/',
  resolve: {
    alias: {
      $lib: path.resolve('./src/lib'),
      $svar: path.resolve('./src/svar')
    }
  }
};
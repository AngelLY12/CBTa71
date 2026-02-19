// @ts-check
import { defineConfig } from 'astro/config';
import tailwindcss from '@tailwindcss/vite';
import react from '@astrojs/react';
import alpinejs from '@astrojs/alpinejs';
import vercel from '@astrojs/vercel';

// https://astro.build/config
// REBUILD TIMESTAMP: 2026-02-10T20:54 - Force Vercel clean build v2
export default defineConfig({
  adapter: vercel(),
  integrations: [react(), alpinejs()],

  vite: {
    plugins: [tailwindcss()],
    build: {
      minify: 'esbuild', // Use esbuild minification (default)
      sourcemap: false,
      rollupOptions: {
        output: {
          manualChunks: undefined // Disable code splitting to avoid cache issues
        }
      }
    }
  },

  output: 'server'
});
// @ts-check
import { defineConfig } from 'astro/config';
import tailwindcss from '@tailwindcss/vite';
import react from '@astrojs/react'; // Importa la integración de React

import vercel from '@astrojs/vercel';

import alpinejs from '@astrojs/alpinejs';

// https://astro.build/config
export default defineConfig({
  // Agrega la integración aquí
  integrations: [react(), alpinejs()],

  vite: {
    plugins: [tailwindcss()]
  },

  adapter: vercel()
});
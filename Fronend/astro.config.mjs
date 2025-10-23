// @ts-check
import { defineConfig } from 'astro/config';
import tailwindcss from '@tailwindcss/vite';
import react from '@astrojs/react'; // Importa la integración de React

import vercel from '@astrojs/vercel';

// https://astro.build/config
export default defineConfig({
  // Agrega la integración aquí
  integrations: [react()],

  vite: {
    plugins: [tailwindcss()]
  },

  adapter: vercel()
});
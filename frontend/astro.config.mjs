// @ts-check
import { defineConfig } from 'astro/config';
import tailwindcss from '@tailwindcss/vite';
import react from '@astrojs/react'; // Importa la integración de React

// https://astro.build/config
export default defineConfig({
  integrations: [react()], // Agrega la integración aquí
  vite: {
    plugins: [tailwindcss()]
  }
});
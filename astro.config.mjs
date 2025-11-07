// @ts-check
import { defineConfig } from 'astro/config';
import tailwindcss from '@tailwindcss/vite';
import react from '@astrojs/react'; // Importa la integración de React

// https://astro.build/config
export default defineConfig({
  integrations: [react()], // Agrega la integración aquí
  vite: {
    plugins: [tailwindcss()]
  },
  server: {
    host: true, // Esto equivale a '0.0.0.0'
    port: 4321  // Puedes cambiar el puerto si lo deseas
  }
});
# Astro Starter Kit: Basics

```sh
npm create astro@latest -- --template basics
```

> 🧑‍🚀 **Seasoned astronaut?** Delete this file. Have fun!

## 🚀 Project Structure

Inside of your Astro project, you'll see the following folders and files:

```text
/
├── public/
│   └── favicon.svg
├── src
│   ├── assets
│   │   └── astro.svg
│   ├── components
│   │   └── Welcome.astro
│   ├── layouts
│   │   └── Layout.astro
│   └── pages
│       └── index.astro
└── package.json
```

To learn more about the folder structure of an Astro project, refer to [our guide on project structure](https://docs.astro.build/en/basics/project-structure/).

## 🧞 Commands

All commands are run from the root of the project, from a terminal:

| Command                   | Action                                           |
| :------------------------ | :----------------------------------------------- |
| `npm install`             | Installs dependencies                            |
| `npm run dev`             | Starts local dev server at `localhost:4321`      |
| `npm run build`           | Build your production site to `./dist/`          |
| `npm run preview`         | Preview your build locally, before deploying     |
| `npm run astro ...`       | Run CLI commands like `astro add`, `astro check` |
| `npm run astro -- --help` | Get help using the Astro CLI                     |

## 👀 Want to learn more?

Feel free to check [our documentation](https://docs.astro.build) or jump into our [Discord server](https://astro.build/chat).

📂 Estructura del Proyecto

La estructura del proyecto sigue una organización clara y modular para facilitar el desarrollo y mantenimiento.

    src/assets: Contiene recursos estáticos como imágenes.
    
    src/components: Componentes reutilizables de Astro.
    
        ConceptForm.astro: El formulario para crear y editar conceptos.
        
        ConceptCard.astro: La tarjeta que muestra los detalles de cada concepto.
        
    src/layouts: Plantillas de página para mantener un diseño consistente.
    
        Layout.astro: El layout principal de la aplicación.
    
    src/pages: Las rutas de la aplicación.
    
        index.astro: La página de inicio (dashboard).
    
        concepts.astro: La página principal de listado de conceptos.
        
        concepts/new.astro: La página para crear un nuevo concepto.
        
        concepts/[id].astro: La ruta dinámica para editar un concepto específico.
    
    src/store.js: La tienda de nanostores para la gestión del estado de los conceptos.
    
    src/styles: Archivos CSS globales y de Tailwind.

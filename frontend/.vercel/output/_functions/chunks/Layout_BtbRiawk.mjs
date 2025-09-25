import { e as createComponent, f as createAstro, n as renderHead, h as addAttribute, o as renderSlot, r as renderTemplate } from './astro/server_DxxZ6Ojl.mjs';
import 'kleur/colors';
import 'clsx';
/* empty css                            */

const $$Astro = createAstro();
const $$Layout = createComponent(($$result, $$props, $$slots) => {
  const Astro2 = $$result.createAstro($$Astro, $$props, $$slots);
  Astro2.self = $$Layout;
  const currentPath = Astro2.url.pathname;
  const { title } = Astro2.props;
  return renderTemplate`<html lang="es"> <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>${title}</title>${renderHead()}</head> <body> <div class="flex h-screen"> <div class="w-[250px] bg-[#2e594d] text-white flex flex-col p-6 fixed h-full shadow-md"> <div class="flex items-center gap-4 mb-8 px-4"> <img src="/Logo.png" alt="Logo CBTA 71" class="h-10 w-10"> <div> <h1 class="text-xl font-bold">CBTA 71</h1> <p class="text-sm">Sistema de pagos</p> </div> </div> <nav class="flex-grow"> <ul class="space-y-2"> <li> <a               href="/"              ${addAttribute([
    "flex items-center gap-4 py-3 px-4 rounded-lg transition-colors duration-200",
    { "bg-white text-[#2e594d] font-bold shadow-sm": currentPath === "/" },
    { "text-white hover:bg-[#3b7666]": currentPath !== "/" }
  ], "class:list")}            > <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-dashboard" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"></path> <path d="M4 4h8v4h-8z"></path> <path d="M4 12h8v8h-8z"></path> <path d="M16 4h4v8h-4z"></path> <path d="M16 16h4v4h-4z"></path> </svg>
Dashboard
</a> </li> <li> <a               href="/concepts"              ${addAttribute([
    "flex items-center gap-4 py-3 px-4 rounded-lg transition-colors duration-200",
    { "bg-white text-[#2e594d] font-bold shadow-sm": currentPath === "/concepts" },
    { "text-white hover:bg-[#3b7666]": currentPath !== "/concepts" }
  ], "class:list")}            > <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-book" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"></path> <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0"></path> <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0"></path> <path d="M3 6v13"></path> <path d="M12 6v13"></path> <path d="M21 6v13"></path> </svg>
Conceptos
</a> </li> <li> <a               href="/students"              ${addAttribute([
    "flex items-center gap-4 py-3 px-4 rounded-lg transition-colors duration-200",
    { "bg-white text-[#2e594d] font-bold shadow-sm": currentPath === "/students" },
    { "text-white hover:bg-[#3b7666]": currentPath !== "/students" }
  ], "class:list")}            > <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"></path> <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path> <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path> <path d="M16 3.161a3 3 0 1 0 3.839 3.839"></path> <path d="M16 14v-1a4 4 0 0 1 4 -4h1"></path> </svg>
Estudiantes
</a> </li> <li> <a               href="/payments"              ${addAttribute([
    "flex items-center gap-4 py-3 px-4 rounded-lg transition-colors duration-200",
    { "bg-white text-[#2e594d] font-bold shadow-sm": currentPath === "/payments" },
    { "text-white hover:bg-[#3b7666]": currentPath !== "/payments" }
  ], "class:list")}            > <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-wallet" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"></path> <path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 0 0 4h12a1 1 0 0 0 1 -1v-3"></path> <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4"></path> </svg>
Pagos
</a> </li> </ul> </nav> <div class="mt-auto pt-4"> <ul> <li> <a               href="/logout"               class="flex items-center gap-4 py-3 px-4 rounded-lg text-white hover:bg-[#3b7666] transition-colors duration-200"            > <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-logout" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"></path> <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"></path> <path d="M7 12h14l-3 -3m0 6l3 -3"></path> </svg>
Salir
</a> </li> </ul> </div> </div> <main class="flex-grow ml-[250px] p-8 bg-[#f0f2f5]"> ${renderSlot($$result, $$slots["default"])} </main> </div> </body></html>`;
}, "C:/Users/josem/Documents/GitHub/CBTa71/frontend/src/layouts/Layout.astro", void 0);

export { $$Layout as $ };

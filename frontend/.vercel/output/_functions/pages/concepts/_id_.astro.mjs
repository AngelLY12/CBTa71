import { e as createComponent, f as createAstro, k as renderComponent, r as renderTemplate, m as maybeRenderHead } from '../../chunks/astro/server_DxxZ6Ojl.mjs';
import 'kleur/colors';
import { $ as $$Layout } from '../../chunks/Layout_BtbRiawk.mjs';
import { $ as $$ConceptForm } from '../../chunks/ConceptForm_B18gf_Zu.mjs';
import { c as conceptsStore } from '../../chunks/store_Blzvo-0M.mjs';
export { renderers } from '../../renderers.mjs';

const $$Astro = createAstro();
const prerender = false;
const $$id = createComponent(($$result, $$props, $$slots) => {
  const Astro2 = $$result.createAstro($$Astro, $$props, $$slots);
  Astro2.self = $$id;
  const { id } = Astro2.params;
  const concepts = conceptsStore.get();
  const concept = concepts.find((c) => c.id === id);
  if (!concept) {
    return new Response(null, {
      status: 404,
      statusText: "Not Found"
    });
  }
  return renderTemplate`${renderComponent($$result, "Layout", $$Layout, { "title": "Editar Concepto - CBTA 71" }, { "default": ($$result2) => renderTemplate` ${maybeRenderHead()}<div class="flex items-center gap-4 mb-8"> <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-invoice" width="32" height="32" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"></path> <path d="M14 3v4a1 1 0 0 0 1 1h4"></path> <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path> <path d="M9 7h1"></path> <path d="M9 13h9"></path> <path d="M13 17h5"></path> </svg> <h1 class="text-3xl font-bold text-gray-800">Editar Concepto</h1> </div> ${renderComponent($$result2, "ConceptForm", $$ConceptForm, { "concept": concept })} ` })}`;
}, "C:/Users/josem/Documents/GitHub/CBTa71/frontend/src/pages/concepts/[id].astro", void 0);

const $$file = "C:/Users/josem/Documents/GitHub/CBTa71/frontend/src/pages/concepts/[id].astro";
const $$url = "/concepts/[id]";

const _page = /*#__PURE__*/Object.freeze(/*#__PURE__*/Object.defineProperty({
  __proto__: null,
  default: $$id,
  file: $$file,
  prerender,
  url: $$url
}, Symbol.toStringTag, { value: 'Module' }));

const page = () => _page;

export { page };

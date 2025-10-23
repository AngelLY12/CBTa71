import { renderers } from './renderers.mjs';
import { c as createExports, s as serverEntrypointModule } from './chunks/_@astrojs-ssr-adapter_DYKqguWe.mjs';
import { manifest } from './manifest_FBKSaBfM.mjs';

const serverIslandMap = new Map();;

const _page0 = () => import('./pages/_image.astro.mjs');
const _page1 = () => import('./pages/concepts/new.astro.mjs');
const _page2 = () => import('./pages/concepts/_id_.astro.mjs');
const _page3 = () => import('./pages/concepts.astro.mjs');
const _page4 = () => import('./pages/index.astro.mjs');
const pageMap = new Map([
    ["node_modules/astro/dist/assets/endpoint/generic.js", _page0],
    ["src/pages/concepts/new.astro", _page1],
    ["src/pages/concepts/[id].astro", _page2],
    ["src/pages/concepts.astro", _page3],
    ["src/pages/index.astro", _page4]
]);

const _manifest = Object.assign(manifest, {
    pageMap,
    serverIslandMap,
    renderers,
    actions: () => import('./_noop-actions.mjs'),
    middleware: () => import('./_noop-middleware.mjs')
});
const _args = {
    "middlewareSecret": "4b5bf993-4687-4931-8359-f1d8b8084fec",
    "skewProtection": false
};
const _exports = createExports(_manifest, _args);
const __astrojsSsrVirtualEntry = _exports.default;
const _start = 'start';
if (Object.prototype.hasOwnProperty.call(serverEntrypointModule, _start)) ;

export { __astrojsSsrVirtualEntry as default, pageMap };

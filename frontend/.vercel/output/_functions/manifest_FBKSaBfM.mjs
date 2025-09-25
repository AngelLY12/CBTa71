import 'kleur/colors';
import { p as decodeKey } from './chunks/astro/server_DxxZ6Ojl.mjs';
import 'clsx';
import 'cookie';
import { N as NOOP_MIDDLEWARE_FN } from './chunks/astro-designed-error-pages_V9MThxUo.mjs';
import 'es-module-lexer';

function sanitizeParams(params) {
  return Object.fromEntries(
    Object.entries(params).map(([key, value]) => {
      if (typeof value === "string") {
        return [key, value.normalize().replace(/#/g, "%23").replace(/\?/g, "%3F")];
      }
      return [key, value];
    })
  );
}
function getParameter(part, params) {
  if (part.spread) {
    return params[part.content.slice(3)] || "";
  }
  if (part.dynamic) {
    if (!params[part.content]) {
      throw new TypeError(`Missing parameter: ${part.content}`);
    }
    return params[part.content];
  }
  return part.content.normalize().replace(/\?/g, "%3F").replace(/#/g, "%23").replace(/%5B/g, "[").replace(/%5D/g, "]");
}
function getSegment(segment, params) {
  const segmentPath = segment.map((part) => getParameter(part, params)).join("");
  return segmentPath ? "/" + segmentPath : "";
}
function getRouteGenerator(segments, addTrailingSlash) {
  return (params) => {
    const sanitizedParams = sanitizeParams(params);
    let trailing = "";
    if (addTrailingSlash === "always" && segments.length) {
      trailing = "/";
    }
    const path = segments.map((segment) => getSegment(segment, sanitizedParams)).join("") + trailing;
    return path || "/";
  };
}

function deserializeRouteData(rawRouteData) {
  return {
    route: rawRouteData.route,
    type: rawRouteData.type,
    pattern: new RegExp(rawRouteData.pattern),
    params: rawRouteData.params,
    component: rawRouteData.component,
    generate: getRouteGenerator(rawRouteData.segments, rawRouteData._meta.trailingSlash),
    pathname: rawRouteData.pathname || void 0,
    segments: rawRouteData.segments,
    prerender: rawRouteData.prerender,
    redirect: rawRouteData.redirect,
    redirectRoute: rawRouteData.redirectRoute ? deserializeRouteData(rawRouteData.redirectRoute) : void 0,
    fallbackRoutes: rawRouteData.fallbackRoutes.map((fallback) => {
      return deserializeRouteData(fallback);
    }),
    isIndex: rawRouteData.isIndex,
    origin: rawRouteData.origin
  };
}

function deserializeManifest(serializedManifest) {
  const routes = [];
  for (const serializedRoute of serializedManifest.routes) {
    routes.push({
      ...serializedRoute,
      routeData: deserializeRouteData(serializedRoute.routeData)
    });
    const route = serializedRoute;
    route.routeData = deserializeRouteData(serializedRoute.routeData);
  }
  const assets = new Set(serializedManifest.assets);
  const componentMetadata = new Map(serializedManifest.componentMetadata);
  const inlinedScripts = new Map(serializedManifest.inlinedScripts);
  const clientDirectives = new Map(serializedManifest.clientDirectives);
  const serverIslandNameMap = new Map(serializedManifest.serverIslandNameMap);
  const key = decodeKey(serializedManifest.key);
  return {
    // in case user middleware exists, this no-op middleware will be reassigned (see plugin-ssr.ts)
    middleware() {
      return { onRequest: NOOP_MIDDLEWARE_FN };
    },
    ...serializedManifest,
    assets,
    componentMetadata,
    inlinedScripts,
    clientDirectives,
    routes,
    serverIslandNameMap,
    key
  };
}

const manifest = deserializeManifest({"hrefRoot":"file:///C:/Users/josem/Documents/GitHub/CBTa71/frontend/","cacheDir":"file:///C:/Users/josem/Documents/GitHub/CBTa71/frontend/node_modules/.astro/","outDir":"file:///C:/Users/josem/Documents/GitHub/CBTa71/frontend/dist/","srcDir":"file:///C:/Users/josem/Documents/GitHub/CBTa71/frontend/src/","publicDir":"file:///C:/Users/josem/Documents/GitHub/CBTa71/frontend/public/","buildClientDir":"file:///C:/Users/josem/Documents/GitHub/CBTa71/frontend/dist/client/","buildServerDir":"file:///C:/Users/josem/Documents/GitHub/CBTa71/frontend/dist/server/","adapterName":"@astrojs/vercel","routes":[{"file":"","links":[],"scripts":[],"styles":[],"routeData":{"type":"page","component":"_server-islands.astro","params":["name"],"segments":[[{"content":"_server-islands","dynamic":false,"spread":false}],[{"content":"name","dynamic":true,"spread":false}]],"pattern":"^\\/_server-islands\\/([^/]+?)\\/?$","prerender":false,"isIndex":false,"fallbackRoutes":[],"route":"/_server-islands/[name]","origin":"internal","_meta":{"trailingSlash":"ignore"}}},{"file":"concepts/new/index.html","links":[],"scripts":[],"styles":[],"routeData":{"route":"/concepts/new","isIndex":false,"type":"page","pattern":"^\\/concepts\\/new\\/?$","segments":[[{"content":"concepts","dynamic":false,"spread":false}],[{"content":"new","dynamic":false,"spread":false}]],"params":[],"component":"src/pages/concepts/new.astro","pathname":"/concepts/new","prerender":true,"fallbackRoutes":[],"distURL":[],"origin":"project","_meta":{"trailingSlash":"ignore"}}},{"file":"concepts/index.html","links":[],"scripts":[],"styles":[],"routeData":{"route":"/concepts","isIndex":false,"type":"page","pattern":"^\\/concepts\\/?$","segments":[[{"content":"concepts","dynamic":false,"spread":false}]],"params":[],"component":"src/pages/concepts.astro","pathname":"/concepts","prerender":true,"fallbackRoutes":[],"distURL":[],"origin":"project","_meta":{"trailingSlash":"ignore"}}},{"file":"index.html","links":[],"scripts":[],"styles":[],"routeData":{"route":"/","isIndex":true,"type":"page","pattern":"^\\/$","segments":[],"params":[],"component":"src/pages/index.astro","pathname":"/","prerender":true,"fallbackRoutes":[],"distURL":[],"origin":"project","_meta":{"trailingSlash":"ignore"}}},{"file":"","links":[],"scripts":[],"styles":[],"routeData":{"type":"endpoint","isIndex":false,"route":"/_image","pattern":"^\\/_image\\/?$","segments":[[{"content":"_image","dynamic":false,"spread":false}]],"params":[],"component":"node_modules/astro/dist/assets/endpoint/generic.js","pathname":"/_image","prerender":false,"fallbackRoutes":[],"origin":"internal","_meta":{"trailingSlash":"ignore"}}},{"file":"","links":[],"scripts":[],"styles":[{"type":"external","src":"/_astro/concepts.C2XsxKpP.css"}],"routeData":{"route":"/concepts/[id]","isIndex":false,"type":"page","pattern":"^\\/concepts\\/([^/]+?)\\/?$","segments":[[{"content":"concepts","dynamic":false,"spread":false}],[{"content":"id","dynamic":true,"spread":false}]],"params":["id"],"component":"src/pages/concepts/[id].astro","prerender":false,"fallbackRoutes":[],"distURL":[],"origin":"project","_meta":{"trailingSlash":"ignore"}}}],"base":"/","trailingSlash":"ignore","compressHTML":true,"componentMetadata":[["C:/Users/josem/Documents/GitHub/CBTa71/frontend/src/pages/concepts.astro",{"propagation":"none","containsHead":true}],["C:/Users/josem/Documents/GitHub/CBTa71/frontend/src/pages/concepts/[id].astro",{"propagation":"none","containsHead":true}],["C:/Users/josem/Documents/GitHub/CBTa71/frontend/src/pages/concepts/new.astro",{"propagation":"none","containsHead":true}],["C:/Users/josem/Documents/GitHub/CBTa71/frontend/src/pages/index.astro",{"propagation":"none","containsHead":true}]],"renderers":[],"clientDirectives":[["idle","(()=>{var l=(n,t)=>{let i=async()=>{await(await n())()},e=typeof t.value==\"object\"?t.value:void 0,s={timeout:e==null?void 0:e.timeout};\"requestIdleCallback\"in window?window.requestIdleCallback(i,s):setTimeout(i,s.timeout||200)};(self.Astro||(self.Astro={})).idle=l;window.dispatchEvent(new Event(\"astro:idle\"));})();"],["load","(()=>{var e=async t=>{await(await t())()};(self.Astro||(self.Astro={})).load=e;window.dispatchEvent(new Event(\"astro:load\"));})();"],["media","(()=>{var n=(a,t)=>{let i=async()=>{await(await a())()};if(t.value){let e=matchMedia(t.value);e.matches?i():e.addEventListener(\"change\",i,{once:!0})}};(self.Astro||(self.Astro={})).media=n;window.dispatchEvent(new Event(\"astro:media\"));})();"],["only","(()=>{var e=async t=>{await(await t())()};(self.Astro||(self.Astro={})).only=e;window.dispatchEvent(new Event(\"astro:only\"));})();"],["visible","(()=>{var a=(s,i,o)=>{let r=async()=>{await(await s())()},t=typeof i.value==\"object\"?i.value:void 0,c={rootMargin:t==null?void 0:t.rootMargin},n=new IntersectionObserver(e=>{for(let l of e)if(l.isIntersecting){n.disconnect(),r();break}},c);for(let e of o.children)n.observe(e)};(self.Astro||(self.Astro={})).visible=a;window.dispatchEvent(new Event(\"astro:visible\"));})();"]],"entryModules":{"\u0000noop-middleware":"_noop-middleware.mjs","\u0000noop-actions":"_noop-actions.mjs","\u0000@astro-page:src/pages/concepts/new@_@astro":"pages/concepts/new.astro.mjs","\u0000@astro-page:src/pages/concepts/[id]@_@astro":"pages/concepts/_id_.astro.mjs","\u0000@astro-page:src/pages/concepts@_@astro":"pages/concepts.astro.mjs","\u0000@astro-page:src/pages/index@_@astro":"pages/index.astro.mjs","\u0000@astrojs-ssr-virtual-entry":"entry.mjs","\u0000@astro-renderers":"renderers.mjs","\u0000@astro-page:node_modules/astro/dist/assets/endpoint/generic@_@js":"pages/_image.astro.mjs","\u0000@astrojs-ssr-adapter":"_@astrojs-ssr-adapter.mjs","\u0000@astrojs-manifest":"manifest_FBKSaBfM.mjs","C:/Users/josem/Documents/GitHub/CBTa71/frontend/node_modules/astro/dist/assets/services/sharp.js":"chunks/sharp_B2XpN6n0.mjs","@astrojs/react/client.js":"_astro/client.DL-_0xdV.js","C:/Users/josem/Documents/GitHub/CBTa71/frontend/src/pages/concepts.astro?astro&type=script&index=0&lang.ts":"_astro/concepts.astro_astro_type_script_index_0_lang.Dxf9uKFD.js","C:/Users/josem/Documents/GitHub/CBTa71/frontend/src/components/ConceptForm.astro?astro&type=script&index=0&lang.ts":"_astro/ConceptForm.astro_astro_type_script_index_0_lang.Ba0K3_kG.js","astro:scripts/before-hydration.js":""},"inlinedScripts":[],"assets":["/_astro/concepts.C2XsxKpP.css","/favicon.svg","/Logo.png","/_astro/client.DL-_0xdV.js","/_astro/ConceptForm.astro_astro_type_script_index_0_lang.Ba0K3_kG.js","/_astro/concepts.astro_astro_type_script_index_0_lang.Dxf9uKFD.js","/_astro/store.C87EM-IA.js","/concepts/new/index.html","/concepts/index.html","/index.html"],"buildFormat":"directory","checkOrigin":true,"serverIslandNameMap":[],"key":"0lsXLRkwNIr9Pq344D3aUWAXuLvMzurX7cz15iyBLQg="});
if (manifest.sessionConfig) manifest.sessionConfig.driverModule = null;

export { manifest };

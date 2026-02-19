/// <reference types="astro/client" />

interface ImportMetaEnv {
    readonly PUBLIC_API_BASE_URL?: string;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}

declare global {
    interface Window {
        AdminAPI: any;
        __adminAPILoaded: boolean;
        __API_BASE_URL__?: string;
        __rolesDataRegistered__: boolean;
    }
}

export {};

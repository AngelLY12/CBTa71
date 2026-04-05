// src/lib/api.js
import axios from "axios";
import { userStore } from "../../data/userStore"; // tu store
import { urlGlobal } from "../../data/global";
import { setAuthData } from "../../data/userStore";

const api = axios.create({
    baseURL: urlGlobal, // ajusta según tu backend
});

// Interceptor de respuesta
api.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error.config;

        if (error.response?.status === 401 && !originalRequest._retry) {
            originalRequest._retry = true;
            try {
                const res = await axios.post(`${urlGlobal}/refresh-token`, {
                    refresh_token: userStore.tokens?.refresh_token,
                });
                const newTokens = res.data.data; // aquí sí defines

                setAuthData(res.data.data);

                // Reintentar la petición original con el nuevo token
                originalRequest.headers["Authorization"] = `Bearer ${newTokens.access_token}`;
                return api(originalRequest);
            } catch (err) {
                localStorage.clear();
                window.location.href = "/";
            }
        }

        return Promise.reject(error);
    }
);

export default api;
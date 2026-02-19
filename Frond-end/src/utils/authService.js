// authService.js - Servicio de autenticación y manejo de tokens
import { API_ENDPOINTS, handleAPIResponse } from '../config/api.js';

// Almacenamiento de tokens
const TOKEN_KEY = 'access_token';
const REFRESH_TOKEN_KEY = 'refresh_token';
const USER_DATA_KEY = 'user_data';

export const AuthService = {
  // Login
  async login(email, password) {
    try {
      const response = await fetch(API_ENDPOINTS.auth.login, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password }),
      });

      const data = await handleAPIResponse(response);

      // Guardar tokens (estructura: data.user_tokens)
      const tokenBundle = data?.data?.user_tokens || data?.data || {};
      const accessToken = tokenBundle.access_token || data?.access_token;
      const refreshToken = tokenBundle.refresh_token || data?.refresh_token;
      const userData = tokenBundle.user_data || data?.user_data;

      if (accessToken) {
        this.setToken(accessToken);
      }
      if (refreshToken) {
        this.setRefreshToken(refreshToken);
      }
      if (userData) {
        this.setUserData(userData);
      }

      return data;
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  },

  // Register
  async register(userData) {
    try {
      const response = await fetch(API_ENDPOINTS.auth.register, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(userData),
      });

      return await handleAPIResponse(response);
    } catch (error) {
      console.error('Register error:', error);
      throw error;
    }
  },

  // Logout
  async logout() {
    const refreshToken = this.getRefreshToken();
    
    try {
      await fetch(API_ENDPOINTS.auth.logout, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.getToken()}`,
          'x-refresh-token': refreshToken,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      });
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      // Limpiar datos locales siempre
      this.clearAuth();
    }
  },

  // Refresh token
  async refreshToken() {
    const refreshToken = this.getRefreshToken();
    if (!refreshToken) {
      throw new Error('No refresh token available');
    }

    try {
      const response = await fetch(API_ENDPOINTS.auth.refresh, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ refresh_token: refreshToken }),
      });

      const data = await handleAPIResponse(response);

      const tokenBundle = data?.data?.user_tokens || data?.data || {};
      const accessToken = tokenBundle.access_token || data?.access_token;
      const newRefreshToken = tokenBundle.refresh_token || data?.refresh_token;
      const userData = tokenBundle.user_data || data?.user_data;

      if (accessToken) {
        this.setToken(accessToken);
      }
      if (newRefreshToken) {
        this.setRefreshToken(newRefreshToken);
      }
      if (userData) {
        this.setUserData(userData);
      }

      return data;
    } catch (error) {
      console.error('Refresh token error:', error);
      this.clearAuth();
      throw error;
    }
  },

  // Forgot password
  async forgotPassword(email) {
    try {
      const response = await fetch(API_ENDPOINTS.auth.forgotPassword, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ email }),
      });

      return await handleAPIResponse(response);
    } catch (error) {
      console.error('Forgot password error:', error);
      throw error;
    }
  },

  // Reset password con token
  async resetPassword(payload) {
    try {
      const response = await fetch(API_ENDPOINTS.auth.resetPassword, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
      });

      return await handleAPIResponse(response);
    } catch (error) {
      console.error('Reset password error:', error);
      throw error;
    }
  },

  // Verificar email (si el frontend maneja el link)
  async verifyEmail(id, hash) {
    try {
      const response = await fetch(API_ENDPOINTS.auth.verifyEmail(id, hash), {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });

      return await handleAPIResponse(response);
    } catch (error) {
      console.error('Verify email error:', error);
      throw error;
    }
  },

  // Enviar notificación de verificación de email (requiere auth)
  async sendEmailVerificationNotification() {
    try {
      const response = await this.authenticatedFetch(API_ENDPOINTS.auth.verificationNotification, {
        method: 'POST',
      });
      return await handleAPIResponse(response);
    } catch (error) {
      console.error('Verification notification error:', error);
      throw error;
    }
  },

  // Get authenticated user
  async getAuthenticatedUser() {
    try {
      const response = await this.authenticatedFetch(API_ENDPOINTS.users.getAuthenticated);
      return await handleAPIResponse(response);
    } catch (error) {
      console.error('Get user error:', error);
      throw error;
    }
  },

  // Fetch con autenticación automática
  async authenticatedFetch(url, options = {}) {
    const token = this.getToken();
    
    if (!token) {
      throw new Error('No authentication token available');
    }

    const isFormData = options?.body instanceof FormData;
    const baseHeaders = {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    };

    // Solo establecer Content-Type si no es FormData y no viene definido por el caller
    const contentTypeHeader = (!isFormData && !(options.headers && options.headers['Content-Type']))
      ? { 'Content-Type': 'application/json' }
      : {};

    const headers = {
      ...baseHeaders,
      ...contentTypeHeader,
      ...options.headers,
    };

    try {
      const response = await fetch(url, {
        ...options,
        headers,
      });

      const isAuthError = response.status === 401 || response.status === 403;
      if (isAuthError) {
        let errorCode = null;
        try {
          const errorData = await response.clone().json();
          errorCode = errorData?.error_code || errorData?.errorCode;
        } catch (error) {
          errorCode = null;
        }

        if (errorCode === 'ACCESS_TOKEN_EXPIRED') {
          try {
            await this.refreshToken();
            const newToken = this.getToken();
            const retryHeaders = {
              ...headers,
              Authorization: `Bearer ${newToken}`,
            };
            return await fetch(url, { ...options, headers: retryHeaders });
          } catch (refreshError) {
            this.clearAuth();
            window.location.href = '/';
            throw refreshError;
          }
        }

        return response;
      }

      return response;
    } catch (error) {
      console.error('Authenticated fetch error:', error);
      throw error;
    }
  },

  // Token management
  getToken() {
    return localStorage.getItem(TOKEN_KEY);
  },

  setToken(token) {
    localStorage.setItem(TOKEN_KEY, token);
  },

  getRefreshToken() {
    return localStorage.getItem(REFRESH_TOKEN_KEY);
  },

  setRefreshToken(token) {
    localStorage.setItem(REFRESH_TOKEN_KEY, token);
  },

  getUserData() {
    const data = localStorage.getItem(USER_DATA_KEY);
    return data ? JSON.parse(data) : null;
  },

  setUserData(userData) {
    localStorage.setItem(USER_DATA_KEY, JSON.stringify(userData));
  },

  clearAuth() {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(REFRESH_TOKEN_KEY);
    localStorage.removeItem(USER_DATA_KEY);
  },

  isAuthenticated() {
    return !!this.getToken();
  },

  // Get user role
  getUserRole() {
    const userData = this.getUserData();
    if (!userData) return null;

    const firstUser = Array.isArray(userData) ? userData[0] : userData;
    if (firstUser && Array.isArray(firstUser.roles) && firstUser.roles.length > 0) {
      return firstUser.roles[0];
    }

    return firstUser?.role || firstUser?.type || null;
  },

  // Check if user has specific role
  hasRole(role) {
    const userRole = this.getUserRole();
    return userRole === role;
  },
};

// Export default
export default AuthService;

/**
 * Dashboard API Service - Staff Version
 * Funciones para interactuar con los endpoints del dashboard del personal financiero
 */

const API_BASE = `${(import.meta.env.PUBLIC_API_BASE_URL ?? (() => { throw new Error('Falta PUBLIC_API_BASE_URL'); })()).replace(/\/$/, '')}/v1`;

// Helper para hacer fetch con manejo automático de token expirado
async function _fetchWithTokenRefresh(url, options = {}) {
  let token = localStorage.getItem('access_token');
  
  if (!token) {
    throw new Error('No hay token, inicia sesión');
  }

  // Primera intentona con el token actual
  let response = await fetch(url, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
      ...options.headers,
    }
  });

  // Si es 401, intentar refrescar el token
  if (response.status === 401) {
    console.warn('⚠️ Token expirado, intentando refrescar...');
    
    try {
      const refreshToken = localStorage.getItem('refresh_token');
      if (!refreshToken) {
        throw new Error('No refresh token available');
      }

      // Intentar refrescar el token
      const refreshResponse = await fetch(`${API_BASE}/refresh-token`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ refresh_token: refreshToken }),
      });

      if (!refreshResponse.ok) {
        throw new Error('No se pudo refrescar el token');
      }

      const refreshData = await refreshResponse.json();
      const tokenBundle = refreshData?.data?.user_tokens || refreshData?.data || {};
      const newAccessToken = tokenBundle.access_token || refreshData?.access_token;
      const newRefreshToken = tokenBundle.refresh_token || refreshData?.refresh_token;

      if (newAccessToken) {
        localStorage.setItem('access_token', newAccessToken);
        token = newAccessToken;
        console.log('✅ Token refrescado exitosamente');
      }

      if (newRefreshToken) {
        localStorage.setItem('refresh_token', newRefreshToken);
      }

      // Reintentar la petición original con el nuevo token
      response = await fetch(url, {
        ...options,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
          ...options.headers,
        }
      });
    } catch (refreshError) {
      console.error('❌ Error refrescando token:', refreshError);
      // Si falla el refresh, limpiar tokens y redirigir al login
      localStorage.removeItem('access_token');
      localStorage.removeItem('refresh_token');
      localStorage.removeItem('user_data');
      window.location.href = '/';
      throw new Error('Sesión expirada, por favor inicia sesión nuevamente');
    }
  }

  // Procesar respuesta
  if (response.status === 204) {
    return { success: true };
  }

  const text = await response.text();
  const data = text ? JSON.parse(text) : {};

  if (!response.ok) {
    const errorMsg = data.message || `Error ${response.status}`;
    throw new Error(errorMsg);
  }

  return data;
}

export const DashboardAPI = {
  /**
   * Limpiar caché del dashboard
   */
  async refreshCache(token) {
    try {
      const data = await _fetchWithTokenRefresh(`${API_BASE}/dashboard-staff/refresh`, {
        method: 'POST',
      });
      return data;
    } catch (err) {
      console.error('❌ Error refreshing dashboard cache:', err);
      throw err;
    }
  },

  /**
   * Obtener todos los conceptos de pago
   */
  async getConcepts(token, onlyThisYear = true, page = 1, perPage = 15, forceRefresh = false) {
    try {
      const url = new URL(`${API_BASE}/dashboard-staff/concepts`);
      url.searchParams.append('only_this_year', onlyThisYear);
      url.searchParams.append('page', page);
      url.searchParams.append('perPage', perPage);
      if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

      const data = await _fetchWithTokenRefresh(url.toString(), {
        method: 'GET',
      });
      return data;
    } catch (err) {
      console.error('❌ Error fetching concepts:', err);
      throw err;
    }
  },

  /**
   * Obtener monto total de pagos realizados
   */
  async getPaymentsMade(token, onlyThisYear = true, forceRefresh = false) {
    try {
      const url = new URL(`${API_BASE}/dashboard-staff/payments`);
      url.searchParams.append('only_this_year', onlyThisYear);
      if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

      const data = await _fetchWithTokenRefresh(url.toString(), {
        method: 'GET',
      });
      return data;
    } catch (err) {
      console.error('❌ Error fetching payments made:', err);
      throw err;
    }
  },

  /**
   * Obtener el número total de estudiantes
   */
  async getStudentsCount(token, onlyThisYear = true, forceRefresh = false) {
    try {
      const url = new URL(`${API_BASE}/dashboard-staff/students`);
      url.searchParams.append('only_this_year', onlyThisYear);
      if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

      const data = await _fetchWithTokenRefresh(url.toString(), {
        method: 'GET',
      });
      return data;
    } catch (err) {
      console.error('❌ Error fetching students count:', err);
      throw err;
    }
  },

  /**
   * Obtener cantidad y monto total de pagos pendientes
   */
  async getPendingPayments(token, onlyThisYear = true, forceRefresh = false) {
    try {
      const url = new URL(`${API_BASE}/dashboard-staff/pending`);
      url.searchParams.append('only_this_year', onlyThisYear);
      if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

      const data = await _fetchWithTokenRefresh(url.toString(), {
        method: 'GET',
      });
      return data;
    } catch (err) {
      console.error('❌ Error fetching pending payments:', err);
      throw err;
    }
  },

  /**
   * Obtener cantidad y monto total de pagos vencidos
   */
  async getOverduePayments(token, onlyThisYear = true, forceRefresh = false) {
    try {
      const url = new URL(`${API_BASE}/dashboard-staff/overdue`);
      url.searchParams.append('only_this_year', onlyThisYear);
      if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

      const data = await _fetchWithTokenRefresh(url.toString(), {
        method: 'GET',
      });
      return data;
    } catch (err) {
      console.error('❌ Error fetching overdue payments:', err);
      throw err;
    }
  },

  /**
   * Crear un payout con todo el balance disponible
   */
  async createPayout(token) {
    try {
      const data = await _fetchWithTokenRefresh(`${API_BASE}/dashboard-staff/payout`, {
        method: 'POST',
      });
      return data;
    } catch (err) {
      console.error('❌ Error creating payout:', err);
      throw err;
    }
  },

  /**
   * Obtener todos los datos del dashboard
   */
  async getAllDashboardData(token, onlyThisYear = true) {
    try {
      const [concepts, payments, students, pending, overdue] = await Promise.all([
        this.getConcepts(token, onlyThisYear),
        this.getPaymentsMade(token, onlyThisYear),
        this.getStudentsCount(token, onlyThisYear),
        this.getPendingPayments(token, onlyThisYear),
        this.getOverduePayments(token, onlyThisYear)
      ]);

      return {
        concepts,
        payments,
        students,
        pending,
        overdue
      };
    } catch (err) {
      console.error('❌ Error fetching all dashboard data:', err);
      throw err;
    }
  }
};

export default DashboardAPI;

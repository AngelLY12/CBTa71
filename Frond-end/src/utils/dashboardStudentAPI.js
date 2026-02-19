/**
 * Student Dashboard API Service
 * Funciones para interactuar con los endpoints del dashboard de estudiantes y padres
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

export const DashboardStudentAPI = {
  /**
   * Limpiar caché del dashboard del estudiante
   * @param {string} token - Token de autenticación
   * @param {number|null} studentId - ID del estudiante (opcional, para padres viendo hijos)
   * @param {string} userRole - Rol del usuario (student|parent)
   * @returns {Promise<Object>}
   */
  async refreshCache(token, studentId = null, userRole = 'student') {
    try {
      const endpoint = studentId 
        ? `${API_BASE}/dashboard/refresh/${studentId}`
        : `${API_BASE}/dashboard/refresh`;
      
      const data = await _fetchWithTokenRefresh(endpoint, {
        method: 'POST',
        headers: {
          'X-User-Role': userRole,
          'X-User-Permission': 'refresh.all.dashboard',
        }
      });
      return data;
    } catch (err) {
      console.error('❌ Error refreshing student dashboard cache:', err);
      throw err;
    }
  },

  /**
   * Obtener historial de pagos del estudiante (paginado)
   * @param {string} token - Token de autenticación
   * @param {number} page - Número de página
   * @param {number} perPage - Registros por página
   * @param {boolean} forceRefresh - Forzar actualización de caché
   * @param {number|null} studentId - ID del estudiante (opcional, para padres)
   * @param {string} userRole - Rol del usuario (student|parent)
   * @returns {Promise<Object>}
   */
  async getPaymentHistory(token, page = 1, perPage = 15, forceRefresh = false, studentId = null, userRole = 'student') {
    try {
      const endpoint = studentId 
        ? `${API_BASE}/dashboard/history/${studentId}`
        : `${API_BASE}/dashboard/history`;
      
      const url = new URL(endpoint);
      url.searchParams.append('page', page);
      url.searchParams.append('perPage', perPage);
      if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

      const data = await _fetchWithTokenRefresh(url.toString(), {
        method: 'GET',
        headers: {
          'X-User-Role': userRole,
          'X-User-Permission': 'view.payments.summary',
        }
      });
      return data;
    } catch (err) {
      console.error('❌ Error fetching payment history:', err);
      throw err;
    }
  },

  /**
   * Obtener total de pagos vencidos del estudiante
   * @param {string} token - Token de autenticación
   * @param {boolean} forceRefresh - Forzar actualización de caché
   * @param {number|null} studentId - ID del estudiante (opcional, para padres)
   * @param {string} userRole - Rol del usuario (student|parent)
   * @returns {Promise<Object>}
   */
  async getOverduePayments(token, forceRefresh = false, studentId = null, userRole = 'student') {
    try {
      const endpoint = studentId 
        ? `${API_BASE}/dashboard/overdue/${studentId}`
        : `${API_BASE}/dashboard/overdue`;
      
      const url = new URL(endpoint);
      if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

      const data = await _fetchWithTokenRefresh(url.toString(), {
        method: 'GET',
        headers: {
          'X-User-Role': userRole,
          'X-User-Permission': 'view.own.overdue.concepts.summary',
        }
      });
      return data;
    } catch (err) {
      console.error('❌ Error fetching overdue payments:', err);
      throw err;
    }
  },

  /**
   * Obtener total de pagos realizados por el estudiante
   * @param {string} token - Token de autenticación
   * @param {boolean} forceRefresh - Forzar actualización de caché
   * @param {number|null} studentId - ID del estudiante (opcional, para padres)
   * @param {string} userRole - Rol del usuario (student|parent)
   * @returns {Promise<Object>}
   */
  async getPaidPayments(token, forceRefresh = false, studentId = null, userRole = 'student') {
    try {
      const endpoint = studentId 
        ? `${API_BASE}/dashboard/paid/${studentId}`
        : `${API_BASE}/dashboard/paid`;
      
      const url = new URL(endpoint);
      if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

      const data = await _fetchWithTokenRefresh(url.toString(), {
        method: 'GET',
        headers: {
          'X-User-Role': userRole,
          'X-User-Permission': 'view.own.paid.concepts.summary',
        }
      });
      return data;
    } catch (err) {
      console.error('❌ Error fetching paid payments:', err);
      throw err;
    }
  },

  /**
   * Obtener total de pagos pendientes del estudiante
   * @param {string} token - Token de autenticación
   * @param {boolean} forceRefresh - Forzar actualización de caché
   * @param {number|null} studentId - ID del estudiante (opcional, para padres)
   * @param {string} userRole - Rol del usuario (student|parent)
   * @returns {Promise<Object>}
   */
  async getPendingPayments(token, forceRefresh = false, studentId = null, userRole = 'student') {
    try {
      const endpoint = studentId 
        ? `${API_BASE}/dashboard/pending/${studentId}`
        : `${API_BASE}/dashboard/pending`;
      
      const url = new URL(endpoint);
      if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

      const data = await _fetchWithTokenRefresh(url.toString(), {
        method: 'GET',
        headers: {
          'X-User-Role': userRole,
          'X-User-Permission': 'view.own.pending.concepts.summary',
        }
      });
      return data;
    } catch (err) {
      console.error('❌ Error fetching pending payments:', err);
      throw err;
    }
  },

  /**
   * Obtener todos los datos del dashboard del estudiante
   * @param {string} token - Token de autenticación
   * @param {number|null} studentId - ID del estudiante (opcional, para padres)
   * @param {string} userRole - Rol del usuario (student|parent)
   * @returns {Promise<Object>}
   */
  async getAllDashboardData(token, studentId = null, userRole = 'student') {
    try {
      const [history, overdue, paid, pending] = await Promise.all([
        this.getPaymentHistory(token, 1, 15, false, studentId, userRole),
        this.getOverduePayments(token, false, studentId, userRole),
        this.getPaidPayments(token, false, studentId, userRole),
        this.getPendingPayments(token, false, studentId, userRole)
      ]);

      return {
        history,
        overdue,
        paid,
        pending
      };
    } catch (err) {
      console.error('❌ Error fetching all student dashboard data:', err);
      throw err;
    }
  }
};

export default DashboardStudentAPI;

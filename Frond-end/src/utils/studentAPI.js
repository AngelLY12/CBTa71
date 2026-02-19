/**
 * Student API Service
 * Centraliza todas las llamadas API para páginas de estudiantes
 * Endpoints: Dashboard, Adeudos, Historial, Tarjetas, Perfil
 */

const API_BASE = `${(import.meta.env.PUBLIC_API_BASE_URL ?? (() => { throw new Error('Falta PUBLIC_API_BASE_URL'); })()).replace(/\/$/, '')}/v1`;

function normalizeStudentPortalRole(role) {
  if (!role) return 'student';
  const roleLower = String(role).toLowerCase().trim();

  if (roleLower === 'student' || roleLower === 'estudiante') return 'student';
  if (roleLower === 'parent' || roleLower === 'padre') return 'parent';
  if (roleLower === 'applicant' || roleLower === 'solicitante' || roleLower === 'aspirante') return 'applicant';
  if (roleLower === 'unverified' || roleLower === 'nverified' || roleLower === 'not_verified' || roleLower === 'sin_verificar' || roleLower === 'sin verificar') return 'unverified';

  return roleLower;
}

function getRoleFromStorage() {
  try {
    const rawUserData = localStorage.getItem('user_data');
    if (!rawUserData) return null;

    const parsedUser = JSON.parse(rawUserData);
    const roles = [];

    if (Array.isArray(parsedUser?.roles)) {
      roles.push(...parsedUser.roles);
    } else if (parsedUser?.roles) {
      roles.push(parsedUser.roles);
    }

    if (parsedUser?.role) roles.push(parsedUser.role);
    if (parsedUser?.role_name) roles.push(parsedUser.role_name);
    if (parsedUser?.type) roles.push(parsedUser.type);

    const firstValidRole = roles
      .map((item) => (typeof item === 'string' ? item : item?.name))
      .find(Boolean);

    return firstValidRole ? normalizeStudentPortalRole(firstValidRole) : null;
  } catch (error) {
    console.warn('No se pudo detectar rol desde user_data:', error);
    return null;
  }
}

function shouldUseStudentId(effectiveRole, studentId) {
  return effectiveRole === 'parent' && !!studentId;
}

function resolveStudentPortalRole(role) {
  const roleValue = typeof role === 'string' ? role.trim() : role;
  const normalizedRoleArg = roleValue ? normalizeStudentPortalRole(roleValue) : '';
  const storageRole = getRoleFromStorage();
  if (normalizedRoleArg && normalizedRoleArg !== 'student') return normalizedRoleArg;
  if (storageRole) return storageRole;
  if (normalizedRoleArg) return normalizedRoleArg;
  return 'student';
}

function resolveApiAccessRole(effectiveRole) {
  if (effectiveRole === 'parent') return 'parent';
  if (effectiveRole === 'applicant') return 'applicant';
  if (effectiveRole === 'unverified') return 'unverified';
  return 'student';
}

function getApiRoleCandidates(effectiveRole) {
  const primaryRole = resolveApiAccessRole(effectiveRole);
  if (primaryRole === 'applicant' || primaryRole === 'unverified') {
    return [primaryRole, 'student'];
  }
  return [primaryRole];
}

/**
 * Helper: Detecta errores de autenticación (401)
 */
function handleAuthError(statusCode) {
  if (statusCode === 401) {
    if (window.__studentApiSessionExpiredHandled__) {
      return true;
    }
    window.__studentApiSessionExpiredHandled__ = true;
    const currentToken = localStorage.getItem('access_token');
    
    // Log para debugging
    console.warn('⚠️ 401 Unauthorized');
    console.warn('Token en localStorage:', currentToken ? 'SÍ (presente)' : 'NO (no encontrado)');

    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    localStorage.removeItem('user_id');
    if (window.location.pathname !== '/login' && window.location.pathname !== '/') {
      window.location.href = '/';
    }
    return true;
  }
  return false;
}

function parseRetryAfterMs(retryAfterHeader) {
  const fallbackMs = 2000;
  if (!retryAfterHeader) return fallbackMs;

  const asNumber = Number(retryAfterHeader);
  if (!Number.isNaN(asNumber) && asNumber > 0) {
    return Math.max(500, Math.min(asNumber * 1000, 10000));
  }

  const asDate = Date.parse(retryAfterHeader);
  if (!Number.isNaN(asDate)) {
    const diff = asDate - Date.now();
    return Math.max(500, Math.min(diff, 10000));
  }

  return fallbackMs;
}

function wait(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function appendCacheBust(urlObj, enabled = false) {
  if (!enabled || !urlObj || !urlObj.searchParams) return;
  urlObj.searchParams.append('_', String(Date.now()));
}

function pickReceiptUrl(payload) {
  const search = (value) => {
    if (!value) return null;
    if (typeof value === 'string') {
      return (value.startsWith('http://') || value.startsWith('https://')) ? value : null;
    }
    if (Array.isArray(value)) {
      for (const item of value) {
        const found = search(item);
        if (found) return found;
      }
      return null;
    }
    if (typeof value === 'object') {
      const direct = value.url || value.receipt_url || value.signed_url || value.link || value.href;
      const directFound = search(direct);
      if (directFound) return directFound;

      const nested = [value.data, value.result, value.receipt, value.payload];
      for (const item of nested) {
        const found = search(item);
        if (found) return found;
      }
    }
    return null;
  };

  return search(payload);
}

export const StudentAPI = {
  /**
   * PAGOS - GET /api/v1/payments/history/{studentId?}
   * Obtener historial de pagos del usuario autenticado
   * @param {number|null} studentId - ID del estudiante (opcional para padres)
   * @param {string} token - Token de autenticacion
   * @param {boolean} forceRefresh - Forzar actualizacion del cache
   * @param {string} role - Rol del usuario (student|parent)
   * @param {number} perPage - Registros por pagina
   * @param {number} page - Numero de pagina
   */
  async getPaymentHistory(studentId, token, forceRefresh = false, role = 'student', perPage = 15, page = 1) {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const url = new URL(studentId ? `${API_BASE}/payments/history/${studentId}` : `${API_BASE}/payments/history`);
      if (perPage) {
        url.searchParams.append('perPage', String(perPage));
      }
      if (page) {
        url.searchParams.append('page', String(page));
      }
      if (forceRefresh) {
        url.searchParams.append('forceRefresh', 'true');
      }

      const maxAttempts = 2;
      for (let attempt = 1; attempt <= maxAttempts; attempt++) {
        const response = await fetch(url.toString(), {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-User-Role': effectiveRole,
            'X-User-Permission': 'view.payments.history'
          }
        });

        if (response.status === 401) {
          handleAuthError(401);
          throw new Error('No autenticado - sesión expirada');
        }

        if (response.status === 429) {
          const retryAfterMs = parseRetryAfterMs(response.headers.get('Retry-After'));
          if (attempt < maxAttempts) {
            console.warn(`⚠️ 429 en historial. Reintentando en ${retryAfterMs}ms (intento ${attempt + 1}/${maxAttempts})`);
            await wait(retryAfterMs);
            continue;
          }
          throw new Error('Has excedido el límite de solicitudes, intenta nuevamente en unos segundos');
        }

        if (!response.ok) {
          const errorData = await response.json().catch(() => ({}));
          throw new Error(errorData.message || 'Error al cargar historial de pagos');
        }

        return await response.json();
      }

      throw new Error('No se pudo cargar el historial de pagos');
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentHistory:', err);
      throw err;
    }
  },

  /**
   * PAGOS - GET /api/v1/payments/history/payment/{id}
   * Obtener detalle de pago por ID
   * @param {number|string} paymentId - ID del pago
   * @param {string} token - Token de autenticacion
   * @param {string} role - Rol del usuario (student|parent)
   */
  async getPaymentById(paymentId, token, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const endpoint = `${API_BASE}/payments/history/payment/${paymentId}`;
      const response = await fetch(endpoint, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': effectiveRole,
          'X-User-Permission': 'view.payments.history'
        }
      });

      if (response.status === 401) {
        handleAuthError(401);
        throw new Error('No autenticado - sesión expirada');
      }

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al cargar detalle del pago');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentById:', err);
      throw err;
    }
  },

  /**
   * PAGOS - GET /api/v1/payments/history/receipt/{paymentId}
   * Descargar recibo de pago en PDF
   * @param {number|string} paymentId - ID del pago
   * @param {string} token - Token de autenticacion
   * @param {string} role - Rol del usuario (student|parent)
   */
  async downloadPaymentReceipt(paymentId, token, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const endpoint = `/api/receipts/${paymentId}?_=${Date.now()}`;
      const response = await fetch(endpointUrl.toString(), {
        method: 'GET',
        cache: 'no-store',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'X-User-Role': effectiveRole,
          'X-User-Permission': 'view.receipt'
        }
      });

      if (response.status === 401) {
        handleAuthError(401);
        throw new Error('No autenticado - sesión expirada');
      }

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al obtener el recibo');
      }

      const payload = await response.json().catch(() => ({}));
      const data = payload?.data || {};
      const receiptUrl = pickReceiptUrl(payload);

      if (!receiptUrl) {
        throw new Error(payload?.message || 'No se recibió URL del recibo');
      }

      return {
        url: receiptUrl,
        expiresIn: data.expires_in ?? null,
        contentType: data.content_type || null,
        message: payload?.message || ''
      };
    } catch (err) {
      console.warn('⚠️ StudentAPI.downloadPaymentReceipt fallback:', err?.message || err);
      throw err;
    }
  },

  /**
   * DASHBOARD - GET /api/v1/dashboard/pending/{studentId?}
   * Obtener total de pagos pendientes del usuario
   * @param {number|null} studentId - ID del estudiante (opcional para padres)
   * @param {string} token - Token de autenticación
   * @param {boolean} forceRefresh - Forzar actualización del caché
   * @param {string} role - Rol del usuario (student|parent)
   */
  async getPendingTotal(studentId, token, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const url = new URL(studentId ? `${API_BASE}/dashboard/pending/${studentId}` : `${API_BASE}/dashboard/pending`);
      if (forceRefresh) {
        url.searchParams.append('forceRefresh', 'true');
      }

      const response = await fetch(url.toString(), {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': effectiveRole,
          'X-User-Permission': 'view.own.pending.concepts.summary'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al cargar pagos pendientes');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPendingTotal:', err);
      throw err;
    }
  },

  /**
   * DASHBOARD - GET /api/v1/dashboard/paid/{studentId?}
   * Obtener total de pagos realizados por el usuario
   * @param {number|null} studentId - ID del estudiante (opcional para padres)
   * @param {string} token - Token de autenticación
   * @param {boolean} forceRefresh - Forzar actualización del caché
   * @param {string} role - Rol del usuario (student|parent)
   */
  async getPaidTotal(studentId, token, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const url = new URL(studentId ? `${API_BASE}/dashboard/paid/${studentId}` : `${API_BASE}/dashboard/paid`);
      if (forceRefresh) {
        url.searchParams.append('forceRefresh', 'true');
      }

      const response = await fetch(url.toString(), {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': effectiveRole,
          'X-User-Permission': 'view.own.paid.concepts.summary'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al cargar pagos realizados');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPaidTotal:', err);
      throw err;
    }
  },

  /**
   * DASHBOARD - GET /api/v1/dashboard/overdue/{studentId?}
   * Obtener total de pagos vencidos del usuario
   * @param {number|null} studentId - ID del estudiante (opcional para padres)
   * @param {string} token - Token de autenticación
   * @param {boolean} forceRefresh - Forzar actualización del caché
   * @param {string} role - Rol del usuario (student|parent)
   */
  async getOverdueTotal(studentId, token, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const url = new URL(studentId ? `${API_BASE}/dashboard/overdue/${studentId}` : `${API_BASE}/dashboard/overdue`);
      if (forceRefresh) {
        url.searchParams.append('forceRefresh', 'true');
      }

      const response = await fetch(url.toString(), {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': effectiveRole,
          'X-User-Permission': 'view.own.overdue.concepts.summary'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al cargar pagos vencidos');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getOverdueTotal:', err);
      throw err;
    }
  },

  /**
   * DASHBOARD - POST /api/v1/dashboard/refresh/{studentId?}
   * Limpiar caché del dashboard
   * @param {number|null} studentId - ID del estudiante (opcional para padres)
   * @param {string} token - Token de autenticación
   * @param {string} role - Rol del usuario (student|parent)
   */
  async refreshDashboardCache(studentId, token, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const endpoint = studentId ? `${API_BASE}/dashboard/refresh/${studentId}` : `${API_BASE}/dashboard/refresh`;
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': effectiveRole,
          'X-User-Permission': 'refresh.all.dashboard'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al limpiar caché');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.refreshDashboardCache:', err);
      throw err;
    }
  },

  /**
   * ADEUDOS - GET /api/v1/pending-payment
   * Obtener pagos pendientes del usuario autenticado
   * @param {number|null} studentId - ID del estudiante (opcional para padres con múltiples hijos)
   * @param {string} token - Token de autenticación
   * @param {boolean} forceRefresh - Forzar actualización del caché
   * @param {string} role - Rol del usuario (student|parent)
   */
  async getPendingPayments(studentId, token, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      // If studentId provided, use /pending-payments/{studentId}
      // Otherwise use /pending-payments for current user
      const endpoint = studentId ? `${API_BASE}/pending-payments/${studentId}` : `${API_BASE}/pending-payments`;
      console.log(`📡 Fetching pending payments from: ${endpoint}`);
      
      const url = new URL(endpoint);
      
      // Agregar query parameters
      if (forceRefresh) {
        url.searchParams.append('forceRefresh', 'true');
      }
      
      const response = await fetch(url.toString(), {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': effectiveRole,
          'X-User-Permission': 'view.pending.concepts'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al cargar pagos pendientes');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPendingPayments:', err);
      throw err;
    }
  },

  /**
   * ADEUDOS - GET /api/v1/pending-payment/overdue
   * Obtener pagos vencidos del usuario autenticado
   * @param {number|null} studentId - ID del estudiante (opcional para padres con múltiples hijos)
   * @param {string} token - Token de autenticación
   * @param {boolean} forceRefresh - Forzar actualización del caché
   * @param {string} role - Rol del usuario (student|parent)
   */
  async getOverduePayments(studentId, token, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      // If studentId provided, use /pending-payments/overdue/{studentId}
      // Otherwise use /pending-payments/overdue for current user
      const endpoint = studentId ? `${API_BASE}/pending-payments/overdue/${studentId}` : `${API_BASE}/pending-payments/overdue`;
      console.log('📡 Fetching overdue payments from:', endpoint);
      
      const url = new URL(endpoint);
      if (forceRefresh) {
        url.searchParams.append('forceRefresh', 'true');
      }
      appendCacheBust(url, forceRefresh);
      
      const response = await fetch(url.toString(), {
        method: 'GET',
        cache: 'no-store',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': effectiveRole,
          'X-User-Permission': 'view.overdue.concepts'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al cargar pagos vencidos');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getOverduePayments:', err);
      throw err;
    }
  },

  /**
   * ADEUDOS - POST /api/v1/pending-payments
   * Generar intento de pago para un concepto pendiente
   * @param {number} conceptId - ID del concepto a pagar
   * @param {string} token - Token de autenticación
   * @param {string} role - Rol del usuario (student|parent)
   */
  async createPaymentAttempt(conceptId, token, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const response = await fetch(`${API_BASE}/pending-payments`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': effectiveRole,
          'X-User-Permission': 'create.payment'
        },
        body: JSON.stringify({ concept_id: conceptId })
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al crear intento de pago');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.createPaymentAttempt:', err);
      throw err;
    }
  },

  /**
   * HISTORIAL - GET /api/v1/history/{studentId?}
   * Obtener historial de pagos del usuario autenticado
   */
  async getPaymentHistoryFull(studentId, token) {
    try {
      const endpoint = studentId ? `${API_BASE}/history/${studentId}` : `${API_BASE}/history`;
      const response = await fetch(endpoint, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al cargar historial completo');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentHistoryFull:', err);
      throw err;
    }
  },

  /**
   * HISTORIAL - GET /api/v1/history/payment/{id}
   * Buscar pago por ID
   */
  async getPaymentById(paymentId, token, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const response = await fetch(`${API_BASE}/history/payment/${paymentId}`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': effectiveRole,
          'X-User-Permission': 'view.payments.history'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al cargar pago');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentById:', err);
      throw err;
    }
  },

  /**
   * TARJETAS - GET /api/v1/cards/{studentId?}
   * Listar métodos de pago del usuario autenticado
   */
  async getPaymentMethods(studentId, token, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      let endpoint = studentId ? `${API_BASE}/cards/${studentId}` : `${API_BASE}/cards`;
      if (forceRefresh) {
        endpoint += (studentId ? '?' : '?') + 'forceRefresh=true';
      }
      const endpointUrl = new URL(endpoint);
      appendCacheBust(endpointUrl, forceRefresh);
      const response = await fetch(endpoint, {
        method: 'GET',
        cache: 'no-store',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': effectiveRole,
          'X-User-Permission': 'delete.card'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al cargar métodos de pago');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentMethods:', err);
      throw err;
    }
  },

  /**
   * TARJETAS - POST /api/v1/cards
   * Registrar un nuevo método de pago
   */
  async createPaymentMethod(cardData, token) {
    try {
      const response = await fetch(`${API_BASE}/cards`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(cardData)
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al registrar método de pago');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.createPaymentMethod:', err);
      throw err;
    }
  },

  /**
   * TARJETAS - DELETE /api/v1/cards/{paymentMethodId}
   * Eliminar un método de pago
   */
  async deletePaymentMethod(paymentMethodId, token) {
    try {
      const response = await fetch(`${API_BASE}/cards/${paymentMethodId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al eliminar método de pago');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.deletePaymentMethod:', err);
      throw err;
    }
  },

  /**
   * PERFIL - GET /api/v1/users/user
   * Obtener usuario autenticado
   */
  async getAuthenticatedUser(token) {
    try {
      const response = await fetch(`${API_BASE}/users/user`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al cargar usuario');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getAuthenticatedUser:', err);
      throw err;
    }
  },

  /**
   * PERFIL - GET /api/v1/users/student-details
   * Obtener detalles de estudiante del usuario autenticado
   */
  async getStudentDetails(token) {
    try {
      const response = await fetch(`${API_BASE}/users/student-details`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al cargar detalles de estudiante');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getStudentDetails:', err);
      throw err;
    }
  },

  /**
   * PERFIL - PATCH /api/v1/users/update
   * Actualizar datos generales del usuario
   */
  async updateUserProfile(userData, token) {
    try {
      const response = await fetch(`${API_BASE}/users/update`, {
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(userData)
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al actualizar perfil');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.updateUserProfile:', err);
      throw err;
    }
  },

  /**
   * PERFIL - PATCH /api/v1/users/update/password
   * Actualizar contraseña del usuario
   */
  async updatePassword(passwordData, token) {
    try {
      const response = await fetch(`${API_BASE}/users/update/password`, {
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(passwordData)
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al actualizar contraseña');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.updatePassword:', err);
      throw err;
    }
  },

  /**
   * DEBTS - GET /api/v1/debts/stripe-payments
   * Obtener pagos desde Stripe
   * @param {string} token - Token de autenticación
   * @param {string} search - Email, CURP o n_control (opcional)
   * @param {number} year - Año específico de los pagos (opcional)
   * @param {boolean} forceRefresh - Forzar actualización del caché (opcional)
   */
  async getStripePayments(token, search = '', year = null, forceRefresh = false) {
    try {
      const url = new URL(`${API_BASE}/debts/stripe-payments`);
      
      // Agregar query parameters
      if (search) {
        url.searchParams.append('search', search);
      }
      if (year) {
        url.searchParams.append('year', year);
      }
      if (forceRefresh) {
        url.searchParams.append('forceRefresh', 'true');
      }
      appendCacheBust(url, forceRefresh);
      
      const response = await fetch(url.toString(), {
        method: 'GET',
        cache: 'no-store',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': 'financial-staff',
          'X-User-Permission': 'view.stripe.payments'
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al cargar pagos de Stripe');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getStripePayments:', err);
      throw err;
    }
  },

  /**
   * DEBTS - POST /api/v1/debts/validate
   * Validar un pago de Stripe
   * @param {string} search - Email, CURP o n_control del estudiante
   * @param {string} paymentIntentId - Payment Intent ID de Stripe
   * @param {string} token - Token de autenticación
   */
  async validateStripePayment(search, paymentIntentId, token) {
    try {
      const response = await fetch(`${API_BASE}/debts/validate`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': 'financial-staff',
          'X-User-Permission': 'validate.debt'
        },
        body: JSON.stringify({
          search,
          payment_intent_id: paymentIntentId
        })
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al validar pago');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.validateStripePayment:', err);
      throw err;
    }
  },

  /**
   * DEBTS - GET /api/v1/debts
   * Listar todos los pagos pendientes con paginación
   * @param {string} token - Token de autenticación
   * @param {object} options - Opciones de búsqueda y paginación
   * @param {string} options.search - Búsqueda por CURP, email o n_control
   * @param {number} options.page - Página número (default: 1)
   * @param {number} options.perPage - Items por página (default: 15)
   * @param {boolean} options.forceRefresh - Forzar actualización del caché
   */
  async getAllPendingDebts(token, options = {}) {
    try {
      const {
        search = '',
        page = 1,
        perPage = 15,
        forceRefresh = false
      } = options;

      const params = new URL(`${API_BASE}/debts`);
      if (search) params.searchParams.append('search', search);
      params.searchParams.append('page', page);
      params.searchParams.append('perPage', perPage);
      if (forceRefresh) params.searchParams.append('forceRefresh', 'true');
      appendCacheBust(params, forceRefresh);

      const response = await fetch(params.toString(), {
        method: 'GET',
        cache: 'no-store',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': 'financial-staff',
          'X-User-Permission': 'view.debts'
        }
      });

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('No autenticado. Por favor inicia sesión.');
        }
        if (response.status === 403) {
          throw new Error('No tienes permiso para ver los adeudos.');
        }
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al obtener adeudos');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getAllPendingDebts:', err);
      throw err;
    }
  },

  /**
   * PAYMENTS - GET /api/v1/payments
   * Listar todos los pagos registrados con paginación
   * @param {string} token - Token de autenticación
   * @param {object} options - Opciones de búsqueda y paginación
   * @param {string} options.search - Búsqueda por email, CURP, n_control o concepto
   * @param {number} options.page - Página número (default: 1)
   * @param {number} options.perPage - Items por página (default: 15)
   * @param {boolean} options.forceRefresh - Forzar actualización del caché
   */
  async getAllPayments(token, options = {}) {
    try {
      const {
        search = '',
        page = 1,
        perPage = 15,
        forceRefresh = false
      } = options;

      const params = new URL(`${API_BASE}/payments`);
      if (search) params.searchParams.append('search', search);
      params.searchParams.append('page', page);
      params.searchParams.append('perPage', perPage);
      if (forceRefresh) params.searchParams.append('forceRefresh', 'true');
      appendCacheBust(params, forceRefresh);

      const response = await fetch(params.toString(), {
        method: 'GET',
        cache: 'no-store',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': 'financial-staff',
          'X-User-Permission': 'view.payments'
        }
      });

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('No autenticado. Por favor inicia sesión.');
        }
        if (response.status === 403) {
          throw new Error('No tienes permiso para ver los pagos.');
        }
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al obtener pagos');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getAllPayments:', err);
      throw err;
    }
  },

  /**
   * PAYMENTS - GET /api/v1/payments/by-concept
   * Listar pagos agrupados por concepto con estadísticas
   * @param {string} token - Token de autenticación
   * @param {object} options - Opciones de búsqueda y paginación
   * @param {string} options.search - Búsqueda por nombre de concepto
   * @param {number} options.page - Página número (default: 1)
   * @param {number} options.perPage - Items por página (default: 15)
   * @param {boolean} options.forceRefresh - Forzar actualización del caché
   */
  async getPaymentsByConcept(token, options = {}) {
    try {
      const {
        search = '',
        page = 1,
        perPage = 15,
        forceRefresh = false
      } = options;

      const params = new URL(`${API_BASE}/payments/by-concept`);
      if (search) params.searchParams.append('search', search);
      params.searchParams.append('page', page);
      params.searchParams.append('perPage', perPage);
      if (forceRefresh) params.searchParams.append('forceRefresh', 'true');
      appendCacheBust(params, forceRefresh);

      const response = await fetch(params.toString(), {
        method: 'GET',
        cache: 'no-store',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': 'financial-staff',
          'X-User-Permission': 'view.payments'
        }
      });

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('No autenticado. Por favor inicia sesión.');
        }
        if (response.status === 403) {
          throw new Error('No tienes permiso para ver los pagos por concepto.');
        }
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al obtener pagos por concepto');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentsByConcept:', err);
      throw err;
    }
  },

  /**
   * STUDENTS - GET /api/v1/payments/students
   * Listar estudiantes con resumen de sus pagos (para financial staff)
   * @param {string} token - Token de autenticación
   * @param {object} options - Opciones de búsqueda y paginación
   * @param {string} options.search - Búsqueda por email, CURP o n_control
   * @param {number} options.page - Página número (default: 1)
   * @param {number} options.perPage - Items por página (default: 15)
   * @param {boolean} options.forceRefresh - Forzar actualización del caché
   */
  async getPaymentStudents(token, options = {}) {
    try {
      const {
        search = '',
        page = 1,
        perPage = 15,
        forceRefresh = false
      } = options;

      const params = new URL(`${API_BASE}/payments/students`);
      if (search) params.searchParams.append('search', search);
      params.searchParams.append('page', page);
      params.searchParams.append('perPage', perPage);
      if (forceRefresh) params.searchParams.append('forceRefresh', 'true');
      appendCacheBust(params, forceRefresh);

      const response = await fetch(params.toString(), {
        method: 'GET',
        cache: 'no-store',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': 'financial-staff',
          'X-User-Permission': 'view.payments.student.summary'
        }
      });

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('No autenticado. Por favor inicia sesión.');
        }
        if (response.status === 403) {
          throw new Error('No tienes permiso para ver el resumen de estudiantes.');
        }
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Error al obtener estudiantes');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentStudents:', err);
      throw err;
    }
  },

  /**
   * CREAR INTENTO DE PAGO - POST /api/v1/pending-payments
   * Generar intento de pago para un concepto pendiente
   * @param {number} conceptId - ID del concepto a pagar
   * @param {string} token - Token de autenticación
   */
  async createPaymentIntent(conceptId, token) {
    try {
      const url = `${API_BASE}/pending-payments`;
      
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': 'student',
          'X-User-Permission': 'create.payment'
        },
        body: JSON.stringify({
          concept_id: conceptId
        })
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        if (response.status === 429) {
          throw new Error('Demasiadas solicitudes. Intenta de nuevo en unos momentos.');
        }
        if (response.status === 422) {
          throw new Error('Concepto inválido o no disponible');
        }
        if (response.status === 502) {
          throw new Error('Error al procesar el pago. Por favor intenta de nuevo.');
        }
        throw new Error(errorData.message || 'Error al crear el intento de pago');
      }

      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.createPaymentIntent:', err);
      throw err;
    }
  }
};

// También disponible globalmente como window.StudentAPI para compatibilidad
if (typeof window !== 'undefined') {
  window.StudentAPI = StudentAPI;
}
console.log('✅ StudentAPI cargado desde /src/utils/studentAPI.js');

/**
 * Student API Service - Public Global Script
 * Centraliza todas las llamadas API para páginas de estudiantes
 * Disponible como window.StudentAPI
 */

const API_BASE_URL = (window.__API_BASE_URL__ || 'https://nginx-production-b390.up.railway.app/api').replace(/\/$/, '');
const API_BASE = `${API_BASE_URL}/v1`;

function normalizeStudentPortalRole(role) {
  if (!role) return 'student';
  const roleLower = String(role)
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[._-]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

  if (roleLower === 'student' || roleLower === 'estudiante' || roleLower === 'alumno' || roleLower === 'alumna') return 'student';
  if (roleLower === 'parent' || roleLower === 'padre' || roleLower === 'madre' || roleLower === 'tutor' || roleLower === 'tutora' || roleLower === 'tutor legal') return 'parent';
  if (roleLower === 'applicant' || roleLower === 'solicitante' || roleLower === 'aspirante') return 'applicant';
  if (roleLower === 'unverified' || roleLower === 'nverified' || roleLower === 'not verified' || roleLower === 'sin verificar' || roleLower === 'no verificado') return 'unverified';

  return roleLower;
}

function extractRoleValue(rawRole) {
  if (!rawRole) return '';
  if (typeof rawRole === 'string') return rawRole;
  if (typeof rawRole === 'object') {
    return rawRole?.name || rawRole?.slug || rawRole?.role || rawRole?.role_name || rawRole?.value || rawRole?.type || '';
  }
  return String(rawRole || '');
}

function shouldUseStudentId(effectiveRole, studentId) {
  return Number.isInteger(studentId) && studentId > 0;
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
      .map((item) => extractRoleValue(item))
      .find(Boolean);

    return firstValidRole ? normalizeStudentPortalRole(firstValidRole) : null;
  } catch (error) {
    console.warn('No se pudo detectar rol desde user_data:', error);
    return null;
  }
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

function handleAuthError(statusCode) {
  if (statusCode === 401) {
    const currentToken = localStorage.getItem('access_token');
    console.warn('⚠️ 401 Unauthorized - Token:', currentToken ? 'present' : 'missing');
    if (window.__studentApiAuthRedirectInProgress) return true;
    window.__studentApiAuthRedirectInProgress = true;
    localStorage.removeItem('access_token');
    localStorage.removeItem('user_id');
    localStorage.removeItem('userId');
    window.location.href = '/login';
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

function buildAuthHeaders(token, role, permission = '') {
  const headers = {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-User-Role': role
  };

  if (permission) {
    headers['X-User-Permission'] = permission;
  }

  return headers;
}

function isSkippableFallbackStatus(statusCode) {
  return statusCode === 400 || statusCode === 403 || statusCode === 404 || statusCode === 405;
}

function isRateLimitedStatus(statusCode) {
  return statusCode === 429;
}

async function parseErrorMessage(response, fallback = 'Error') {
  const errorText = await response.text().catch(() => '');
  if (!errorText) return fallback;

  try {
    const parsed = JSON.parse(errorText);
    return parsed?.message || fallback;
  } catch (_) {
    return errorText;
  }
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

function extractPaymentHistoryItems(payload) {
  if (!payload || typeof payload !== 'object') return [];

  const data = payload?.data || {};
  const paymentHistory = data?.payment_history || payload?.payment_history || data?.history || payload?.history || {};

  if (Array.isArray(paymentHistory?.items)) return paymentHistory.items;
  if (Array.isArray(paymentHistory?.data)) return paymentHistory.data;
  if (Array.isArray(data?.items)) return data.items;
  if (Array.isArray(data)) return data;

  return [];
}

const paymentMethodsRequestState = {
  inFlightPromise: null,
  inFlightKey: '',
  lastResult: null,
  lastKey: '',
  lastFetchedAt: 0
};

const authenticatedUserRequestState = {
  inFlightPromise: null,
  inFlightKey: '',
  lastResult: null,
  lastKey: '',
  lastFetchedAt: 0,
  cooldownUntil: 0
};

window.StudentAPI = {
  async getPaymentHistory(studentId, token, forceRefresh = false, role = 'student', perPage = 15, page = 1) {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const apiRole = resolveApiAccessRole(effectiveRole);
      const endpointCandidates = [];
      const useIdRoute = shouldUseStudentId(effectiveRole, studentId) && effectiveRole !== 'applicant';
      if (useIdRoute) {
        endpointCandidates.push(`${API_BASE}/payments/history/${studentId}`);
      }
      endpointCandidates.push(`${API_BASE}/payments/history`);

      const maxAttempts = 2;

      for (let endpointIndex = 0; endpointIndex < endpointCandidates.length; endpointIndex++) {
        const endpoint = endpointCandidates[endpointIndex];
        const isLastEndpoint = endpointIndex === endpointCandidates.length - 1;
        const url = new URL(endpoint);
        if (perPage) url.searchParams.append('perPage', String(perPage));
        if (page) url.searchParams.append('page', String(page));
        if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

        for (let attempt = 1; attempt <= maxAttempts; attempt++) {
          const response = await fetch(url.toString(), {
            method: 'GET',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-User-Role': apiRole,
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

          if (response.ok) {
            const payload = await response.json();
            const items = extractPaymentHistoryItems(payload);
            if (items.length || isLastEndpoint) {
              return payload;
            }

            console.warn('⚠️ getPaymentHistory devolvió vacío en ruta con ID. Probando ruta general...');
            break;
          }

          if ((response.status === 403 || response.status === 404) && endpointIndex < endpointCandidates.length - 1) {
            break;
          }

          if (response.status === 403 && effectiveRole === 'applicant') {
            console.warn('⚠️ Historial no disponible para solicitante (403). Regresando respuesta vacía controlada.');
            return {
              success: true,
              data: {
                payment_history: {
                  items: [],
                  currentPage: Number(page) || 1,
                  lastPage: 1,
                  perPage: Number(perPage) || 15,
                  total: 0,
                  hasMorePages: false,
                  nextPage: null,
                  previousPage: null
                }
              },
              message: 'Historial no disponible para solicitante'
            };
          }

          const errorData = await response.json().catch(() => ({}));
          throw new Error(errorData.message || 'Error al cargar historial de pagos');
        }
      }

      throw new Error('No se pudo cargar el historial de pagos');
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentHistory:', err);
      throw err;
    }
  },

  async getPaymentById(paymentId, token, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const apiRole = resolveApiAccessRole(effectiveRole);
      const endpoint = `${API_BASE}/payments/history/payment/${paymentId}`;
      const response = await fetch(endpoint, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': apiRole,
          'X-User-Permission': 'view.payments.history'
        }
      });
      if (response.status === 401) handleAuthError(401);
      if (!response.ok) throw new Error((await response.json()).message || 'Error');
      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentById:', err);
      throw err;
    }
  },

  async downloadPaymentReceipt(paymentId, token, role = 'student', receiptOptions = null) {
    try {
      const options = (receiptOptions && typeof receiptOptions === 'object' && !Array.isArray(receiptOptions))
        ? receiptOptions
        : { fallbackUrl: receiptOptions };
      const localReceiptUrl = pickReceiptUrl(options?.fallbackUrl);
      const rawAlternativeIds = Array.isArray(options?.alternativeIds) ? options.alternativeIds : [];
      const alternativeIds = rawAlternativeIds
        .map((value) => String(value || '').trim())
        .filter((value) => value && value !== String(paymentId));
      const preferAlternativeFirst = Boolean(options?.preferAlternativeFirst);

      const effectiveRole = resolveStudentPortalRole(role);
      const apiRole = resolveApiAccessRole(effectiveRole);

      const fetchReceiptByCandidate = async (candidateId) => {
        const endpoint = `/api/receipts/${encodeURIComponent(candidateId)}?_=${Date.now()}`;
        const response = await fetch(endpoint, {
          method: 'GET',
          cache: 'no-store',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'X-User-Role': apiRole,
            'X-User-Permission': 'view.receipt'
          }
        });

        if (response.status === 401) {
          handleAuthError(401);
          throw new Error('No autenticado - sesión expirada');
        }

        if (!response.ok) {
          const errorData = await response.json().catch(() => ({}));
          const error = new Error(errorData.message || 'Error al obtener el recibo');
          error.status = response.status;
          throw error;
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
      };

      const candidates = preferAlternativeFirst
        ? [...alternativeIds, String(paymentId)]
        : [String(paymentId), ...alternativeIds];
      const uniqueCandidates = Array.from(new Set(candidates.filter(Boolean)));

      let lastRetryableError = null;
      for (const candidateId of uniqueCandidates) {
        try {
          return await fetchReceiptByCandidate(candidateId);
        } catch (candidateError) {
          const status = Number(candidateError?.status || 0);
          const isRetryable = status === 422 || status === 404;
          if (!isRetryable) {
            throw candidateError;
          }
          lastRetryableError = candidateError;
        }
      }

      if (lastRetryableError) throw lastRetryableError;
      throw new Error('No se pudo obtener el recibo');
    } catch (err) {
      const localReceiptUrl = pickReceiptUrl(
        (receiptOptions && typeof receiptOptions === 'object' && !Array.isArray(receiptOptions))
          ? receiptOptions?.fallbackUrl
          : receiptOptions
      );
      if (localReceiptUrl) {
        return {
          url: localReceiptUrl,
          expiresIn: null,
          contentType: null,
          message: 'Recibo obtenido de URL local de respaldo'
        };
      }
      console.warn('⚠️ StudentAPI.downloadPaymentReceipt fallback:', err?.message || err);
      throw err;
    }
  },

  async getPendingTotal(studentId, token, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const apiRoles = getApiRoleCandidates(effectiveRole);
      const useIdRoute = shouldUseStudentId(effectiveRole, studentId);
      const endpointCandidates = [
        ...(useIdRoute ? [`${API_BASE}/dashboard/pending/${studentId}`] : []),
        `${API_BASE}/dashboard/pending`
      ];

      for (const endpoint of endpointCandidates) {
        const url = new URL(endpoint);
        if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

        for (const apiRole of apiRoles) {

          const withPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, apiRole, 'view.own.pending.concepts.summary')
          });

          if (isRateLimitedStatus(withPermission.status)) {
            console.warn('⚠️ getPendingTotal 429. Se devuelve total en 0 para evitar sobrecargar la API.');
            return { success: true, data: { total_pending: { totalAmount: '0.00', totalCount: 0 } } };
          }

          if (withPermission.status === 401) handleAuthError(401);
          if (withPermission.ok) return await withPermission.json();
          if (!isSkippableFallbackStatus(withPermission.status)) {
            throw new Error(await parseErrorMessage(withPermission, 'Error'));
          }

          const withoutPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, apiRole)
          });

          if (isRateLimitedStatus(withoutPermission.status)) {
            console.warn('⚠️ getPendingTotal 429. Se devuelve total en 0 para evitar sobrecargar la API.');
            return { success: true, data: { total_pending: { totalAmount: '0.00', totalCount: 0 } } };
          }

          if (withoutPermission.status === 401) handleAuthError(401);
          if (withoutPermission.ok) return await withoutPermission.json();
          if (!isSkippableFallbackStatus(withoutPermission.status)) {
            throw new Error(await parseErrorMessage(withoutPermission, 'Error'));
          }
        }
      }

      console.warn(`⚠️ getPendingTotal 403 para rol ${effectiveRole}. Se devuelve total en 0.`);
      return { success: true, data: { total_pending: { totalAmount: '0.00', totalCount: 0 } } };
    } catch (err) {
      console.error('❌ StudentAPI.getPendingTotal:', err);
      throw err;
    }
  },

  async getPaidTotal(studentId, token, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const apiRoles = getApiRoleCandidates(effectiveRole);
      const useIdRoute = shouldUseStudentId(effectiveRole, studentId);
      const endpointCandidates = [
        ...(useIdRoute ? [`${API_BASE}/dashboard/paid/${studentId}`] : []),
        `${API_BASE}/dashboard/paid`
      ];

      for (const endpoint of endpointCandidates) {
        const url = new URL(endpoint);
        if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

        for (const apiRole of apiRoles) {

          const withPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, apiRole, 'view.own.paid.concepts.summary')
          });

          if (isRateLimitedStatus(withPermission.status)) {
            console.warn('⚠️ getPaidTotal 429. Se devuelve total en 0 para evitar sobrecargar la API.');
            return { success: true, data: { paid_data: { totalPayments: '0.00', paymentsByMonth: {} } } };
          }

          if (withPermission.status === 401) handleAuthError(401);
          if (withPermission.ok) return await withPermission.json();
          if (!isSkippableFallbackStatus(withPermission.status)) {
            throw new Error(await parseErrorMessage(withPermission, 'Error'));
          }

          const withoutPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, apiRole)
          });

          if (isRateLimitedStatus(withoutPermission.status)) {
            console.warn('⚠️ getPaidTotal 429. Se devuelve total en 0 para evitar sobrecargar la API.');
            return { success: true, data: { paid_data: { totalPayments: '0.00', paymentsByMonth: {} } } };
          }

          if (withoutPermission.status === 401) handleAuthError(401);
          if (withoutPermission.ok) return await withoutPermission.json();
          if (!isSkippableFallbackStatus(withoutPermission.status)) {
            throw new Error(await parseErrorMessage(withoutPermission, 'Error'));
          }
        }
      }

      console.warn(`⚠️ getPaidTotal 403 para rol ${effectiveRole}. Se devuelve total en 0.`);
      return { success: true, data: { paid_data: { totalPayments: '0.00', paymentsByMonth: {} } } };
    } catch (err) {
      console.error('❌ StudentAPI.getPaidTotal:', err);
      throw err;
    }
  },

  async getOverdueTotal(studentId, token, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const apiRoles = getApiRoleCandidates(effectiveRole);
      const useIdRoute = shouldUseStudentId(effectiveRole, studentId);
      const endpointCandidates = [
        ...(useIdRoute ? [`${API_BASE}/dashboard/overdue/${studentId}`] : []),
        `${API_BASE}/dashboard/overdue`
      ];

      for (const endpoint of endpointCandidates) {
        const url = new URL(endpoint);
        if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

        for (const apiRole of apiRoles) {

          const withPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, apiRole, 'view.own.overdue.concepts.summary')
          });

          if (isRateLimitedStatus(withPermission.status)) {
            console.warn('⚠️ getOverdueTotal 429. Se devuelve total en 0 para evitar sobrecargar la API.');
            return { success: true, data: { total_overdue: { totalAmount: '0.00', totalCount: 0 } } };
          }

          if (withPermission.status === 401) handleAuthError(401);
          if (withPermission.ok) return await withPermission.json();
          if (!isSkippableFallbackStatus(withPermission.status)) {
            throw new Error(await parseErrorMessage(withPermission, 'Error'));
          }

          const withoutPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, apiRole)
          });

          if (isRateLimitedStatus(withoutPermission.status)) {
            console.warn('⚠️ getOverdueTotal 429. Se devuelve total en 0 para evitar sobrecargar la API.');
            return { success: true, data: { total_overdue: { totalAmount: '0.00', totalCount: 0 } } };
          }

          if (withoutPermission.status === 401) handleAuthError(401);
          if (withoutPermission.ok) return await withoutPermission.json();
          if (!isSkippableFallbackStatus(withoutPermission.status)) {
            throw new Error(await parseErrorMessage(withoutPermission, 'Error'));
          }
        }
      }

      console.warn(`⚠️ getOverdueTotal 403 para rol ${effectiveRole}. Se devuelve total en 0.`);
      return { success: true, data: { total_overdue: { totalAmount: '0.00', totalCount: 0 } } };
    } catch (err) {
      console.error('❌ StudentAPI.getOverdueTotal:', err);
      throw err;
    }
  },

  async refreshDashboardCache(studentId, token, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const apiRole = resolveApiAccessRole(effectiveRole);

      const endpoint = shouldUseStudentId(effectiveRole, studentId) ? `${API_BASE}/dashboard/refresh/${studentId}` : `${API_BASE}/dashboard/refresh`;
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': apiRole,
          'X-User-Permission': 'refresh.all.dashboard'
        }
      });
      if (!response.ok) throw new Error((await response.json()).message || 'Error');
      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.refreshDashboardCache:', err);
      throw err;
    }
  },

  async getDashboardHistory(studentId, token, page = 1, perPage = 15, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const apiRoles = getApiRoleCandidates(effectiveRole);
      const useIdRoute = shouldUseStudentId(effectiveRole, studentId);
      const endpointCandidates = [
        ...(useIdRoute ? [`${API_BASE}/dashboard/history/${studentId}`] : []),
        `${API_BASE}/dashboard/history`
      ];

      const permissionCandidates = [
        'view.payments.history',
        'view.payments.summary',
        'view.own.paid.concepts.summary'
      ];

      endpointLoop:
      for (const rawEndpoint of endpointCandidates) {
        const isLastEndpoint = rawEndpoint === endpointCandidates[endpointCandidates.length - 1];
        const url = new URL(rawEndpoint);
        url.searchParams.append('page', String(page));
        url.searchParams.append('perPage', String(perPage));
        if (forceRefresh) url.searchParams.append('forceRefresh', 'true');

        for (const apiRole of apiRoles) {
          for (const permission of permissionCandidates) {
            const withPermission = await fetch(url.toString(), {
              method: 'GET',
              headers: buildAuthHeaders(token, apiRole, permission)
            });

            if (withPermission.status === 401) {
              handleAuthError(401);
              throw new Error('No autenticado - sesión expirada');
            }

            if (isRateLimitedStatus(withPermission.status)) {
              console.warn('⚠️ getDashboardHistory 429. Se devuelve historial vacío para evitar sobrecargar la API.');
              return {
                success: true,
                data: {
                  payment_history: {
                    items: [],
                    currentPage: Number(page) || 1,
                    lastPage: 1,
                    perPage: Number(perPage) || 15,
                    total: 0,
                    hasMorePages: false,
                    nextPage: null,
                    previousPage: null
                  }
                }
              };
            }

            if (withPermission.ok) {
              const payload = await withPermission.json();
              const items = extractPaymentHistoryItems(payload);
              if (items.length || isLastEndpoint) return payload;

              console.warn('⚠️ getDashboardHistory devolvió vacío en ruta con ID. Probando ruta general...');
              continue endpointLoop;
            }
            if (!isSkippableFallbackStatus(withPermission.status)) {
              throw new Error(await parseErrorMessage(withPermission, `Error ${withPermission.status}: ${withPermission.statusText}`));
            }
          }

          const withoutPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, apiRole)
          });

          if (withoutPermission.status === 401) {
            handleAuthError(401);
            throw new Error('No autenticado - sesión expirada');
          }

          if (isRateLimitedStatus(withoutPermission.status)) {
            console.warn('⚠️ getDashboardHistory 429. Se devuelve historial vacío para evitar sobrecargar la API.');
            return {
              success: true,
              data: {
                payment_history: {
                  items: [],
                  currentPage: Number(page) || 1,
                  lastPage: 1,
                  perPage: Number(perPage) || 15,
                  total: 0,
                  hasMorePages: false,
                  nextPage: null,
                  previousPage: null
                }
              }
            };
          }

          if (withoutPermission.ok) {
            const payload = await withoutPermission.json();
            const items = extractPaymentHistoryItems(payload);
            if (items.length || isLastEndpoint) return payload;

            console.warn('⚠️ getDashboardHistory (sin permiso) devolvió vacío en ruta con ID. Probando ruta general...');
            continue endpointLoop;
          }
          if (!isSkippableFallbackStatus(withoutPermission.status)) {
            throw new Error(await parseErrorMessage(withoutPermission, `Error ${withoutPermission.status}: ${withoutPermission.statusText}`));
          }
        }
      }

      console.warn(`⚠️ getDashboardHistory 403/404 para rol ${effectiveRole}. Se devuelve historial vacío.`);
      return {
        success: true,
        data: {
          payment_history: {
            items: [],
            currentPage: Number(page) || 1,
            lastPage: 1,
            perPage: Number(perPage) || 15,
            total: 0,
            hasMorePages: false,
            nextPage: null,
            previousPage: null
          }
        }
      };
    } catch (err) {
      console.error('❌ StudentAPI.getDashboardHistory:', err);
      throw err;
    }
  },

  async getPendingPayments(studentId, token, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);

      const apiRoles = getApiRoleCandidates(effectiveRole);
      const useIdRoute = shouldUseStudentId(effectiveRole, studentId);
      const endpointCandidates = [
        ...(useIdRoute ? [`${API_BASE}/pending-payments/${studentId}`] : []),
        `${API_BASE}/pending-payments`
      ];

      console.log(`🔍 [StudentAPI] getPendingPayments - roleArg: ${role}, effectiveRole: ${effectiveRole}, apiRoles: ${apiRoles.join(',')}, studentId: ${studentId}, forceRefresh: ${forceRefresh}`);

      for (const rawEndpoint of endpointCandidates) {
        const url = new URL(rawEndpoint);
        if (forceRefresh) url.searchParams.set('forceRefresh', 'true');

        console.log(`🔍 [StudentAPI] getPendingPayments probando: ${url.toString()}`);

        for (const apiRole of apiRoles) {

          const withPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, apiRole, 'view.pending.concepts')
          });

          console.log(`📡 [StudentAPI] getPendingPayments status (perm:${apiRole}): ${withPermission.status}`);
          if (isRateLimitedStatus(withPermission.status)) {
            console.warn('⚠️ getPendingPayments 429. Se devuelve lista vacía para evitar sobrecargar la API.');
            return { success: true, data: { pending_payments: [] } };
          }
          if (withPermission.status === 401) handleAuthError(401);
          if (withPermission.ok) {
            const data = await withPermission.json();
            console.log(`✅ [StudentAPI] getPendingPayments Success (perm:${apiRole}):`, data);
            return data;
          }
          if (!isSkippableFallbackStatus(withPermission.status)) {
            throw new Error(await parseErrorMessage(withPermission, `Error ${withPermission.status}: ${withPermission.statusText}`));
          }

          const withoutPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, apiRole)
          });

          console.log(`📡 [StudentAPI] getPendingPayments status (sin perm:${apiRole}): ${withoutPermission.status}`);
          if (isRateLimitedStatus(withoutPermission.status)) {
            console.warn('⚠️ getPendingPayments 429. Se devuelve lista vacía para evitar sobrecargar la API.');
            return { success: true, data: { pending_payments: [] } };
          }
          if (withoutPermission.status === 401) handleAuthError(401);
          if (withoutPermission.ok) {
            const data = await withoutPermission.json();
            console.log(`✅ [StudentAPI] getPendingPayments Success (sin perm:${apiRole}):`, data);
            return data;
          }
          if (!isSkippableFallbackStatus(withoutPermission.status)) {
            throw new Error(await parseErrorMessage(withoutPermission, `Error ${withoutPermission.status}: ${withoutPermission.statusText}`));
          }
        }
      }

      console.warn(`⚠️ getPendingPayments 403 para rol ${effectiveRole}. Se devuelve lista vacía.`);
      return { success: true, data: { pending_payments: [] } };
    } catch (err) {
      console.error('❌ StudentAPI.getPendingPayments:', err);
      throw err;
    }
  },

  async getOverduePayments(studentId, token, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);

      const apiRoles = getApiRoleCandidates(effectiveRole);
      const useIdRoute = shouldUseStudentId(effectiveRole, studentId);
      const endpointCandidates = [
        ...(useIdRoute ? [`${API_BASE}/pending-payments/overdue/${studentId}`] : []),
        `${API_BASE}/pending-payments/overdue`
      ];

      for (const rawEndpoint of endpointCandidates) {
        const url = new URL(rawEndpoint);
        if (forceRefresh) url.searchParams.set('forceRefresh', 'true');

        console.log('📡 Fetching overdue payments from:', url.toString());

        for (const apiRole of apiRoles) {

          const withPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, apiRole, 'view.overdue.concepts')
          });

          if (isRateLimitedStatus(withPermission.status)) {
            console.warn('⚠️ getOverduePayments 429. Se devuelve lista vacía para evitar sobrecargar la API.');
            return { success: true, data: { overdue_payments: [] } };
          }

          if (withPermission.status === 401) handleAuthError(401);
          if (withPermission.ok) return await withPermission.json();
          if (!isSkippableFallbackStatus(withPermission.status)) {
            throw new Error(await parseErrorMessage(withPermission, 'Error'));
          }

          const withoutPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, apiRole)
          });

          if (isRateLimitedStatus(withoutPermission.status)) {
            console.warn('⚠️ getOverduePayments 429. Se devuelve lista vacía para evitar sobrecargar la API.');
            return { success: true, data: { overdue_payments: [] } };
          }

          if (withoutPermission.status === 401) handleAuthError(401);
          if (withoutPermission.ok) return await withoutPermission.json();
          if (!isSkippableFallbackStatus(withoutPermission.status)) {
            throw new Error(await parseErrorMessage(withoutPermission, 'Error'));
          }
        }
      }

      console.warn(`⚠️ getOverduePayments 403 para rol ${effectiveRole}. Se devuelve lista vacía.`);
      return { success: true, data: { overdue_payments: [] } };
    } catch (err) {
      console.error('❌ StudentAPI.getOverduePayments:', err);
      throw err;
    }
  },

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
      if (!response.ok) throw new Error((await response.json()).message || 'Error');
      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentHistoryFull:', err);
      throw err;
    }
  },

  async getPaymentMethods(studentId, token, forceRefresh = false, role = 'student') {
    try {
      const effectiveRole = resolveStudentPortalRole(role);
      const cacheKey = `${studentId || 'none'}:${effectiveRole}`;
      const cacheTtlMs = 15000;

      if (!forceRefresh && paymentMethodsRequestState.lastResult && paymentMethodsRequestState.lastKey === cacheKey) {
        const elapsed = Date.now() - paymentMethodsRequestState.lastFetchedAt;
        if (elapsed >= 0 && elapsed < cacheTtlMs) {
          return paymentMethodsRequestState.lastResult;
        }
      }

      if (!forceRefresh && paymentMethodsRequestState.inFlightPromise && paymentMethodsRequestState.inFlightKey === cacheKey) {
        return await paymentMethodsRequestState.inFlightPromise;
      }

      const loadPaymentMethods = async () => {
        const endpointCandidates = [
          ...(studentId ? [`${API_BASE}/cards/${studentId}`] : []),
          `${API_BASE}/cards`
        ];

        for (const endpoint of endpointCandidates) {
          const url = new URL(endpoint);
          if (forceRefresh) url.searchParams.set('forceRefresh', 'true');

          const withPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, effectiveRole, 'view.cards')
          });

          if (withPermission.status === 401) {
            handleAuthError(401);
            throw new Error('No autenticado - sesión expirada');
          }

          if (withPermission.ok) {
            return await withPermission.json();
          }

          if (isRateLimitedStatus(withPermission.status)) {
            if (paymentMethodsRequestState.lastResult && paymentMethodsRequestState.lastKey === cacheKey) {
              console.warn('⚠️ getPaymentMethods 429. Usando cache reciente para evitar bloqueo.');
              return paymentMethodsRequestState.lastResult;
            }
            console.warn('⚠️ getPaymentMethods 429. Se devuelve lista vacía para evitar sobrecargar la API.');
            return { success: true, data: { cards: [] }, message: 'Rate limited' };
          }

          if (!isSkippableFallbackStatus(withPermission.status)) {
            throw new Error(await parseErrorMessage(withPermission, 'Error'));
          }

          const withoutPermission = await fetch(url.toString(), {
            method: 'GET',
            headers: buildAuthHeaders(token, effectiveRole)
          });

          if (withoutPermission.status === 401) {
            handleAuthError(401);
            throw new Error('No autenticado - sesión expirada');
          }

          if (withoutPermission.ok) {
            return await withoutPermission.json();
          }

          if (isRateLimitedStatus(withoutPermission.status)) {
            if (paymentMethodsRequestState.lastResult && paymentMethodsRequestState.lastKey === cacheKey) {
              console.warn('⚠️ getPaymentMethods 429 (sin permiso). Usando cache reciente para evitar bloqueo.');
              return paymentMethodsRequestState.lastResult;
            }
            console.warn('⚠️ getPaymentMethods 429 (sin permiso). Se devuelve lista vacía para evitar sobrecargar la API.');
            return { success: true, data: { cards: [] }, message: 'Rate limited' };
          }

          if (!isSkippableFallbackStatus(withoutPermission.status)) {
            throw new Error(await parseErrorMessage(withoutPermission, 'Error'));
          }
        }

        return { success: true, data: { cards: [] } };
      };

      paymentMethodsRequestState.inFlightKey = cacheKey;
      paymentMethodsRequestState.inFlightPromise = loadPaymentMethods();

      const result = await paymentMethodsRequestState.inFlightPromise;
      paymentMethodsRequestState.lastResult = result;
      paymentMethodsRequestState.lastKey = cacheKey;
      paymentMethodsRequestState.lastFetchedAt = Date.now();
      return result;
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentMethods:', err);
      throw err;
    } finally {
      paymentMethodsRequestState.inFlightPromise = null;
      paymentMethodsRequestState.inFlightKey = '';
    }
  },

  async getAuthenticatedUser(token) {
    try {
      const userCacheStorageKey = 'studentapi_authenticated_user_cache_v1';
      const userCooldownStorageKey = 'studentapi_authenticated_user_cooldown_until_v1';
      const cacheKey = String(token || '').slice(-24);
      const cacheTtlMs = 15000;

      let persistedCache = null;
      try {
        const rawCached = localStorage.getItem(userCacheStorageKey);
        if (rawCached) {
          const parsed = JSON.parse(rawCached);
          const isValidShape = parsed && typeof parsed === 'object' && parsed.cacheKey && parsed.data;
          if (isValidShape && parsed.cacheKey === cacheKey) {
            persistedCache = parsed;
          }
        }
      } catch (_) {}

      if (persistedCache?.data) {
        const persistedAgeMs = Date.now() - Number(persistedCache.fetchedAt || 0);
        if (persistedAgeMs >= 0 && persistedAgeMs < 30000) {
          authenticatedUserRequestState.lastResult = persistedCache.data;
          authenticatedUserRequestState.lastKey = cacheKey;
          authenticatedUserRequestState.lastFetchedAt = Number(persistedCache.fetchedAt || Date.now());
          return persistedCache.data;
        }
      }

      const now = Date.now();
      if (!authenticatedUserRequestState.cooldownUntil || authenticatedUserRequestState.cooldownUntil < now) {
        const persistedCooldown = Number(localStorage.getItem(userCooldownStorageKey) || '0');
        if (Number.isFinite(persistedCooldown) && persistedCooldown > authenticatedUserRequestState.cooldownUntil) {
          authenticatedUserRequestState.cooldownUntil = persistedCooldown;
        }
      }

      if (authenticatedUserRequestState.cooldownUntil > now) {
        if (authenticatedUserRequestState.lastResult && authenticatedUserRequestState.lastKey === cacheKey) {
          return authenticatedUserRequestState.lastResult;
        }
        if (persistedCache?.data) {
          return persistedCache.data;
        }
        throw new Error('Sincronización de usuario en pausa temporal por límite de solicitudes');
      }

      if (authenticatedUserRequestState.lastResult && authenticatedUserRequestState.lastKey === cacheKey) {
        const elapsed = Date.now() - authenticatedUserRequestState.lastFetchedAt;
        if (elapsed >= 0 && elapsed < cacheTtlMs) {
          return authenticatedUserRequestState.lastResult;
        }
      }

      if (authenticatedUserRequestState.inFlightPromise && authenticatedUserRequestState.inFlightKey === cacheKey) {
        return await authenticatedUserRequestState.inFlightPromise;
      }

      const loadAuthenticatedUser = async () => {
        const maxAttempts = 2;

        for (let attempt = 1; attempt <= maxAttempts; attempt++) {
          const response = await fetch(`${API_BASE}/users/user`, {
            method: 'GET',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          });

          if (response.status === 401) {
            handleAuthError(401);
            throw new Error('No autenticado - sesión expirada');
          }

          if (isRateLimitedStatus(response.status)) {
            if (attempt < maxAttempts) {
              const retryAfterMs = parseRetryAfterMs(response.headers.get('Retry-After'));
              console.warn(`⚠️ getAuthenticatedUser 429. Reintentando en ${retryAfterMs}ms (intento ${attempt + 1}/${maxAttempts})`);
              await wait(retryAfterMs);
              continue;
            }

            const retryAfterMs = parseRetryAfterMs(response.headers.get('Retry-After'));
            const cooldownMs = Math.max(120000, Math.min(retryAfterMs * 3, 300000));
            authenticatedUserRequestState.cooldownUntil = Date.now() + cooldownMs;
            try {
              localStorage.setItem(userCooldownStorageKey, String(authenticatedUserRequestState.cooldownUntil));
            } catch (_) {}

            if (authenticatedUserRequestState.lastResult) {
              console.warn('⚠️ getAuthenticatedUser 429. Usando último usuario en caché para evitar bloqueo.');
              return authenticatedUserRequestState.lastResult;
            }

            if (persistedCache?.data) {
              const persistedAgeMs = Date.now() - Number(persistedCache.fetchedAt || 0);
              if (persistedAgeMs >= 0 && persistedAgeMs < 1800000) {
                console.warn('⚠️ getAuthenticatedUser 429. Usando cache persistente para evitar bloqueo.');
                return persistedCache.data;
              }
            }

            throw new Error('Has excedido el límite de solicitudes, intenta nuevamente en unos segundos');
          }

          if (!response.ok) {
            throw new Error(await parseErrorMessage(response, 'Error'));
          }

          authenticatedUserRequestState.cooldownUntil = 0;
          try {
            localStorage.removeItem(userCooldownStorageKey);
          } catch (_) {}

          return await response.json();
        }

        throw new Error('No se pudo obtener el usuario autenticado');
      };

      authenticatedUserRequestState.inFlightKey = cacheKey;
      authenticatedUserRequestState.inFlightPromise = loadAuthenticatedUser();

      const result = await authenticatedUserRequestState.inFlightPromise;
      authenticatedUserRequestState.lastResult = result;
      authenticatedUserRequestState.lastKey = cacheKey;
      authenticatedUserRequestState.lastFetchedAt = Date.now();

      try {
        localStorage.setItem(userCacheStorageKey, JSON.stringify({
          cacheKey,
          fetchedAt: authenticatedUserRequestState.lastFetchedAt,
          data: result
        }));
      } catch (_) {}

      return result;
    } catch (err) {
      const errorMessage = String(err?.message || err || '').toLowerCase();
      const isRateLimited = errorMessage.includes('429') || errorMessage.includes('límite de solicitudes') || errorMessage.includes('limite de solicitudes') || errorMessage.includes('too many requests');
      if (isRateLimited) {
        console.warn('⚠️ StudentAPI.getAuthenticatedUser rate-limited:', err?.message || err);
      } else {
        console.error('❌ StudentAPI.getAuthenticatedUser:', err);
      }
      throw err;
    } finally {
      authenticatedUserRequestState.inFlightPromise = null;
      authenticatedUserRequestState.inFlightKey = '';
    }
  },

  async createPaymentIntent(conceptId, token) {
    try {
      const effectiveRole = resolveStudentPortalRole('student');
      const response = await fetch(`${API_BASE}/pending-payments`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': effectiveRole
        },
        body: JSON.stringify({ concept_id: conceptId })
      });
      if (!response.ok) throw new Error((await response.json()).message || 'Error');
      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.createPaymentIntent:', err);
      throw err;
    }
  },

  async getCareers(token, options = {}) {
    try {
      const { role = 'financial-staff', permission = 'view.careers' } = options;
      const response = await fetch(`${API_BASE}/careers`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': role,
          'X-User-Permission': permission
        }
      });
      if (!response.ok) throw new Error((await response.json()).message || 'Error');
      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getCareers:', err);
      throw err;
    }
  },

  async getPaymentStudents(token, options = {}) {
    try {
      const { search = '', page = 1, perPage = 15, forceRefresh = false, role = 'financial-staff', permission = 'view.payments.student.summary' } = options;
      const params = new URL(`${API_BASE}/payments/students`);
      if (search) params.searchParams.append('search', search);
      params.searchParams.append('page', page);
      params.searchParams.append('perPage', perPage);
      if (forceRefresh) params.searchParams.append('forceRefresh', 'true');
      
      const response = await fetch(params.toString(), {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': role,
          'X-User-Permission': permission
        }
      });
      if (!response.ok) throw new Error((await response.json()).message || 'Error');
      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentStudents:', err);
      throw err;
    }
  },

  async getAllPendingDebts(token, options = {}) {
    try {
      const { search = '', page = 1, perPage = 15, forceRefresh = false } = options;
      const params = new URL(`${API_BASE}/debts`);
      if (search) params.searchParams.append('search', search);
      params.searchParams.append('page', page);
      params.searchParams.append('perPage', perPage);
      if (forceRefresh) params.searchParams.append('forceRefresh', 'true');
      
      const response = await fetch(params.toString(), {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': 'financial-staff',
          'X-User-Permission': 'view.debts'
        }
      });
      if (!response.ok) throw new Error((await response.json()).message || 'Error');
      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getAllPendingDebts:', err);
      throw err;
    }
  },

  async getStripePayments(token, search = '', year = null, forceRefresh = false) {
    try {
      const url = new URL(`${API_BASE}/debts/stripe-payments`);
      if (search) url.searchParams.append('search', search);
      if (year) url.searchParams.append('year', year);
      if (forceRefresh) url.searchParams.append('forceRefresh', 'true');
      
      const response = await fetch(url.toString(), {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': 'financial-staff',
          'X-User-Permission': 'view.stripe.payments'
        }
      });
      if (!response.ok) throw new Error((await response.json()).message || 'Error');
      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getStripePayments:', err);
      throw err;
    }
  },

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
        body: JSON.stringify({ search, payment_intent_id: paymentIntentId })
      });
      if (!response.ok) throw new Error((await response.json()).message || 'Error');
      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.validateStripePayment:', err);
      throw err;
    }
  },

  async getAllPayments(token, options = {}) {
    try {
      const { search = '', page = 1, perPage = 15, forceRefresh = false } = options;
      const params = new URL(`${API_BASE}/payments`);
      if (search) params.searchParams.append('search', search);
      params.searchParams.append('page', page);
      params.searchParams.append('perPage', perPage);
      if (forceRefresh) params.searchParams.append('forceRefresh', 'true');
      
      const response = await fetch(params.toString(), {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': 'financial-staff',
          'X-User-Permission': 'view.payments'
        }
      });
      if (!response.ok) throw new Error((await response.json()).message || 'Error');
      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getAllPayments:', err);
      throw err;
    }
  },

  async getPaymentsByConcept(token, options = {}) {
    try {
      const { search = '', page = 1, perPage = 15, forceRefresh = false } = options;
      const params = new URL(`${API_BASE}/payments/by-concept`);
      if (search) params.searchParams.append('search', search);
      params.searchParams.append('page', page);
      params.searchParams.append('perPage', perPage);
      if (forceRefresh) params.searchParams.append('forceRefresh', 'true');
      
      const response = await fetch(params.toString(), {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-User-Role': 'financial-staff',
          'X-User-Permission': 'view.payments'
        }
      });
      if (!response.ok) throw new Error((await response.json()).message || 'Error');
      return await response.json();
    } catch (err) {
      console.error('❌ StudentAPI.getPaymentsByConcept:', err);
      throw err;
    }
  }
};

console.log('✅ StudentAPI cargado desde /public/studentAPI.js (v20260218r32)');

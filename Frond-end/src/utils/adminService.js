// adminService.js - Servicio para operaciones administrativas
import { API_ENDPOINTS, handleAPIResponse, buildQueryString } from '../config/api.js';
import AuthService from './authService.js';

// Headers base para operaciones administrativas
const withAdminHeaders = (headers = {}) => ({
  'X-User-Role': 'admin',
  ...headers,
});

// Wrapper para fetch autenticado con refresh automático
const authenticatedRequest = async (url, options = {}) => {
  const response = await AuthService.authenticatedFetch(url, {
    ...options,
    headers: withAdminHeaders(options.headers),
  });
  return handleAPIResponse(response);
};

export const AdminService = {
  /**
   * CRUD de Usuarios
   */
  
  // Obtener lista de usuarios con paginación y filtros
  async getUsers({ page = 1, perPage = 15, status = 'activo', forceRefresh = false } = {}) {
    try {
      const queryParams = buildQueryString({ page, perPage, status, forceRefresh });
      const url = `${API_ENDPOINTS.adminActions.showUsers}${queryParams}`;

      return await authenticatedRequest(url, { method: 'GET' });
    } catch (error) {
      console.error('Error al obtener usuarios:', error);
      throw error;
    }
  },

  // Obtener detalles de un usuario específico
  async getUserById(userId, forceRefresh = false) {
    try {
      const queryParams = buildQueryString({ forceRefresh });
      const url = `${API_ENDPOINTS.adminActions.showUserById(userId)}${queryParams}`;

      return await authenticatedRequest(url, { method: 'GET' });
    } catch (error) {
      console.error(`Error al obtener usuario ${userId}:`, error);
      throw error;
    }
  },

  // Registrar nuevo usuario (Admin)
  async registerUser(userData) {
    try {
      return await authenticatedRequest(API_ENDPOINTS.adminActions.register, {
        method: 'POST',
        headers: {
          'X-User-Permission': 'create.user',
        },
        body: JSON.stringify(userData),
      });
    } catch (error) {
      console.error('Error al registrar usuario:', error);
      throw error;
    }
  },

  // Actualizar usuario
  async updateUser(userId, userData) {
    try {
      return await authenticatedRequest(API_ENDPOINTS.adminActions.updateUser(userId), {
        method: 'PUT',
        body: JSON.stringify(userData),
      });
    } catch (error) {
      console.error(`Error al actualizar usuario ${userId}:`, error);
      throw error;
    }
  },

  // Eliminar usuario
  async deleteUser(userId) {
    try {
      const response = await AuthService.authenticatedFetch(API_ENDPOINTS.adminActions.deleteUser(userId), {
        method: 'DELETE',
        headers: withAdminHeaders(),
      });

      if (response.status === 409) {
        const data = await response.json().catch(() => ({}));
        return {
          success: true,
          message: data.message || 'El usuario ya estaba marcado como eliminado',
          code: 'ALREADY_DELETED',
        };
      }

      return await handleAPIResponse(response);
    } catch (error) {
      console.error(`Error al eliminar usuario ${userId}:`, error);
      throw error;
    }
  },

  // Eliminar múltiples usuarios
  async deleteUsers(userIds) {
    try {
      const response = await AuthService.authenticatedFetch(API_ENDPOINTS.adminActions.deleteUsers, {
        method: 'DELETE',
        headers: withAdminHeaders(),
        body: JSON.stringify({ user_ids: userIds }),
      });

      if (response.status === 409) {
        const data = await response.json().catch(() => ({}));
        return {
          success: true,
          message: data.message || 'Algunos usuarios ya estaban eliminados',
          code: 'ALREADY_DELETED',
        };
      }

      return await handleAPIResponse(response);
    } catch (error) {
      console.error('Error al eliminar usuarios:', error);
      throw error;
    }
  },

  // Deshabilitar usuarios
  async disableUsers(userIds) {
    try {
      return await authenticatedRequest(API_ENDPOINTS.adminActions.disableUsers, {
        method: 'POST',
        body: JSON.stringify({ user_ids: userIds }),
      });
    } catch (error) {
      console.error('Error al deshabilitar usuarios:', error);
      throw error;
    }
  },

  // Deshabilitar temporalmente usuarios
  async temporaryDisableUsers(userIds) {
    try {
      return await authenticatedRequest(API_ENDPOINTS.adminActions.temporaryDisableUsers, {
        method: 'POST',
        body: JSON.stringify({ user_ids: userIds }),
      });
    } catch (error) {
      console.error('Error al deshabilitar temporalmente usuarios:', error);
      throw error;
    }
  },

  /**
   * Gestión de Estudiantes
   */

  // Asociar detalles de estudiante a un usuario
  async attachStudent(studentData) {
    try {
      return await authenticatedRequest(API_ENDPOINTS.adminActions.attachStudent, {
        method: 'POST',
        headers: {
          'X-User-Permission': 'attach.student',
        },
        body: JSON.stringify(studentData),
      });
    } catch (error) {
      console.error('Error al asociar estudiante:', error);
      throw error;
    }
  },

  // Obtener detalles de estudiante
  async getStudent(studentId) {
    try {
      return await authenticatedRequest(API_ENDPOINTS.adminActions.getStudent(studentId), {
        method: 'GET',
        headers: {
          'X-User-Permission': 'view.student',
        },
      });
    } catch (error) {
      console.error(`Error al obtener estudiante ${studentId}:`, error);
      throw error;
    }
  },

  // Actualizar detalles de estudiante
  async updateStudent(studentId, studentData) {
    try {
      return await authenticatedRequest(API_ENDPOINTS.adminActions.updateStudent(studentId), {
        method: 'PATCH',
        headers: {
          'X-User-Permission': 'update.student',
        },
        body: JSON.stringify(studentData),
      });
    } catch (error) {
      console.error(`Error al actualizar estudiante ${studentId}:`, error);
      throw error;
    }
  },

  // Promoción de estudiantes (incrementar semestre)
  async promoteStudents() {
    try {
      return await authenticatedRequest(API_ENDPOINTS.adminActions.promotion, {
        method: 'POST',
        headers: {
          'X-User-Permission': 'promote.student',
        },
      });
    } catch (error) {
      console.error('Error al promover estudiantes:', error);
      throw error;
    }
  },

  /**
   * Gestión de Permisos y Roles
   */

  // Actualizar permisos de usuarios
  async updatePermissions(permissionsData) {
    try {
      return await authenticatedRequest(API_ENDPOINTS.adminActions.updatePermissions, {
        method: 'POST',
        headers: {
          'X-User-Permission': 'sync.permissions',
        },
        body: JSON.stringify(permissionsData),
      });
    } catch (error) {
      console.error('Error al actualizar permisos:', error);
      throw error;
    }
  },

  // Obtener permisos disponibles
  async getPermissions() {
    try {
      return await authenticatedRequest(API_ENDPOINTS.adminActions.findPermissions, { method: 'GET' });
    } catch (error) {
      console.error('Error al obtener permisos:', error);
      throw error;
    }
  },

  // Obtener roles disponibles
  async getRoles() {
    try {
      return await authenticatedRequest(API_ENDPOINTS.adminActions.findRoles, { method: 'GET' });
    } catch (error) {
      console.error('Error al obtener roles:', error);
      throw error;
    }
  },

  // Obtener rol por ID
  async getRoleById(roleId) {
    try {
      return await authenticatedRequest(API_ENDPOINTS.adminActions.getRoleById(roleId), { method: 'GET' });
    } catch (error) {
      console.error(`Error al obtener rol ${roleId}:`, error);
      throw error;
    }
  },

  /**
   * Importación de Datos
   */

  // Importar usuarios desde Excel
  async importUsers(file) {
    try {
      const formData = new FormData();
      formData.append('file', file);
      const response = await AuthService.authenticatedFetch(API_ENDPOINTS.adminActions.import, {
        method: 'POST',
        headers: withAdminHeaders({ 'X-User-Permission': 'import.users' }),
        body: formData,
      });

      return await handleAPIResponse(response);
    } catch (error) {
      console.error('Error al importar usuarios:', error);
      throw error;
    }
  },

  // Importar detalles de estudiantes desde Excel
  async importStudents(file) {
    try {
      const formData = new FormData();
      formData.append('file', file);
      const response = await AuthService.authenticatedFetch(API_ENDPOINTS.adminActions.importStudents, {
        method: 'POST',
        headers: withAdminHeaders({ 'X-User-Permission': 'import.users' }),
        body: formData,
      });

      return await handleAPIResponse(response);
    } catch (error) {
      console.error('Error al importar estudiantes:', error);
      throw error;
    }
  },
};

export default AdminService;

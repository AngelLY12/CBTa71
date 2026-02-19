import { useState, useEffect } from 'react';
import AdminService from '../utils/adminService.js';

export default function UserManagementTable({ onEdit, onDelete, onRefresh }) {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [pagination, setPagination] = useState({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });
  const [filters, setFilters] = useState({
    status: 'activo',
    searchTerm: '',
  });
  const [selectedUsers, setSelectedUsers] = useState([]);
  const [showBulkActions, setShowBulkActions] = useState(false);

  const normalizeStatus = (status) => {
    const normalized = (status || '').toLowerCase();
    if (normalized === 'active') return 'activo';
    return normalized;
  };

  // Cargar usuarios
  const loadUsers = async (page = 1, forceRefresh = false) => {
    try {
      setLoading(true);
      setError(null);
      
      const response = await AdminService.getUsers({
        page,
        perPage: pagination.perPage,
        status: filters.status,
        forceRefresh,
      });

      if (response.success && response.data?.users) {
        const userData = response.data.users;
        setUsers(userData.items || []);
        setPagination({
          currentPage: userData.currentPage || 1,
          lastPage: userData.lastPage || 1,
          perPage: userData.perPage || 15,
          total: userData.total || 0,
        });
      }
    } catch (err) {
      console.error('Error loading users:', err);
      setError(err.message || 'Error al cargar usuarios');
    } finally {
      setLoading(false);
    }
  };

  // Efecto para cargar usuarios al montar o cambiar filtros
  useEffect(() => {
    loadUsers(1);
  }, [filters.status]);

  // Refresh cuando el padre lo solicite
  useEffect(() => {
    if (onRefresh) {
      loadUsers(pagination.currentPage, true);
    }
  }, [onRefresh]);

  // Manejar cambio de página
  const handlePageChange = (newPage) => {
    if (newPage >= 1 && newPage <= pagination.lastPage) {
      loadUsers(newPage);
    }
  };

  // Seleccionar/deseleccionar usuario
  const toggleUserSelection = (userId) => {
    setSelectedUsers(prev => {
      if (prev.includes(userId)) {
        return prev.filter(id => id !== userId);
      } else {
        return [...prev, userId];
      }
    });
  };

  // Seleccionar/deseleccionar todos
  const toggleSelectAll = () => {
    if (selectedUsers.length === users.length) {
      setSelectedUsers([]);
    } else {
      setSelectedUsers(users.map(u => u.id));
    }
  };

  // Acciones masivas
  const handleBulkDelete = async () => {
    if (!confirm(`¿Estás seguro de eliminar ${selectedUsers.length} usuarios?`)) {
      return;
    }

    try {
      await AdminService.deleteUsers(selectedUsers);
      setSelectedUsers([]);
      loadUsers(pagination.currentPage, true);
      alert('Usuarios eliminados correctamente');
    } catch (err) {
      alert('Error al eliminar usuarios: ' + err.message);
    }
  };

  const handleBulkDisable = async () => {
    if (!confirm(`¿Estás seguro de dar de baja ${selectedUsers.length} usuarios?`)) {
      return;
    }

    try {
      await AdminService.disableUsers(selectedUsers);
      setSelectedUsers([]);
      loadUsers(pagination.currentPage, true);
      alert('Usuarios dados de baja correctamente');
    } catch (err) {
      alert('Error al dar de baja usuarios: ' + err.message);
    }
  };

  const handleBulkTempDisable = async () => {
    if (!confirm(`¿Estás seguro de dar de baja temporal a ${selectedUsers.length} usuarios?`)) {
      return;
    }

    try {
      await AdminService.temporaryDisableUsers(selectedUsers);
      setSelectedUsers([]);
      loadUsers(pagination.currentPage, true);
      alert('Usuarios dados de baja temporal correctamente');
    } catch (err) {
      alert('Error al dar de baja temporal: ' + err.message);
    }
  };

  // Filtrado local por término de búsqueda
  const filteredUsers = users.filter(user => {
    const searchLower = (filters.searchTerm || '').toLowerCase();
    const requestedStatus = normalizeStatus(filters.status);
    const userStatus = normalizeStatus(user.status);

    const matchesStatus = !requestedStatus || userStatus === requestedStatus;
    const matchesSearch = !searchLower || (
      user.fullName?.toLowerCase().includes(searchLower) ||
      user.email?.toLowerCase().includes(searchLower) ||
      user.id?.toString().includes(searchLower)
    );

    return matchesStatus && matchesSearch;
  });

  if (loading && users.length === 0) {
    return (
      <div className="flex justify-center items-center py-12">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-institucional"></div>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {/* Controles y Filtros */}
      <div className="bg-white rounded-lg shadow-sm p-4 space-y-4">
        <div className="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
          {/* Búsqueda */}
          <div className="flex-1 max-w-md">
            <input
              type="text"
              placeholder="Buscar por nombre, email o ID..."
              value={filters.searchTerm}
              onChange={(e) => setFilters(prev => ({ ...prev, searchTerm: e.target.value }))}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-institucional focus:border-transparent"
            />
          </div>

          {/* Filtro de Estado */}
          <div className="flex items-center gap-2">
            <label className="text-sm font-medium text-gray-700">Estado:</label>
            <select
              value={filters.status}
              onChange={(e) => setFilters(prev => ({ ...prev, status: e.target.value }))}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-institucional"
            >
              <option value="activo">Activo</option>
              <option value="baja-temporal">Baja Temporal</option>
              <option value="baja">Baja</option>
              <option value="eliminado">Eliminado</option>
            </select>
          </div>

          {/* Botón Actualizar */}
          <button
            onClick={() => loadUsers(pagination.currentPage, true)}
            className="px-4 py-2 bg-institucional text-white rounded-lg hover:bg-institucional/90 transition-colors"
          >
            🔄 Actualizar
          </button>
        </div>

        {/* Acciones Masivas */}
        {selectedUsers.length > 0 && (
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-center justify-between">
            <span className="text-sm font-medium text-blue-900">
              {selectedUsers.length} usuario(s) seleccionado(s)
            </span>
            <div className="flex gap-2">
              <button
                onClick={handleBulkTempDisable}
                className="px-3 py-1 text-sm bg-yellow-600 text-white rounded hover:bg-yellow-700"
              >
                Baja Temporal
              </button>
              <button
                onClick={handleBulkDisable}
                className="px-3 py-1 text-sm bg-orange-600 text-white rounded hover:bg-orange-700"
              >
                Dar de Baja
              </button>
              <button
                onClick={handleBulkDelete}
                className="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700"
              >
                Eliminar
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Tabla */}
      <div className="bg-white rounded-lg shadow-sm overflow-hidden">
        {error && (
          <div className="bg-red-50 border-l-4 border-red-500 p-4">
            <p className="text-red-700">{error}</p>
          </div>
        )}

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50 border-b border-gray-200">
              <tr>
                <th className="px-4 py-3 text-left">
                  <input
                    type="checkbox"
                    checked={selectedUsers.length === users.length && users.length > 0}
                    onChange={toggleSelectAll}
                    className="rounded border-gray-300 text-institucional focus:ring-institucional"
                  />
                </th>
                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  ID
                </th>
                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nombre Completo
                </th>
                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Email
                </th>
                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estado
                </th>
                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Roles
                </th>
                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Creado
                </th>
                <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredUsers.length === 0 ? (
                <tr>
                  <td colSpan="8" className="px-4 py-8 text-center text-gray-500">
                    No se encontraron usuarios
                  </td>
                </tr>
              ) : (
                filteredUsers.map((user) => {
                  const isEliminated = normalizeStatus(user.status) === 'eliminado';
                  const statusLabel = normalizeStatus(user.status) || 'sin-estado';

                  return (
                  <tr key={user.id} className="hover:bg-gray-50">
                    <td className="px-4 py-3">
                      <input
                        type="checkbox"
                        checked={selectedUsers.includes(user.id)}
                        onChange={() => toggleUserSelection(user.id)}
                        className="rounded border-gray-300 text-institucional focus:ring-institucional"
                      />
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-900">
                      {user.id}
                    </td>
                    <td className="px-4 py-3">
                      <div className="text-sm font-medium text-gray-900">
                        {user.fullName || 'Sin nombre'}
                      </div>
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-600">
                      {user.email}
                    </td>
                    <td className="px-4 py-3">
                      <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                        statusLabel === 'activo'
                          ? 'bg-green-100 text-green-800'
                          : statusLabel === 'baja-temporal'
                          ? 'bg-yellow-100 text-yellow-800'
                          : 'bg-red-100 text-red-800'
                      }`}>
                        {statusLabel}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-600">
                      {user.roles_count || 0} rol(es)
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-600">
                      <div className="text-xs" title={user.created_at}>
                        {user.createdAtHuman || user.created_at}
                      </div>
                    </td>
                    <td className="px-4 py-3 text-right text-sm font-medium">
                      <div className="flex justify-end gap-2">
                        <button
                          onClick={() => onEdit && onEdit(user)}
                          className="text-blue-600 hover:text-blue-900 font-medium"
                          title="Editar"
                        >
                          ✏️ Editar
                        </button>
                        <button
                          onClick={() => !isEliminated && onDelete && onDelete(user.id)}
                          disabled={isEliminated}
                          className={`${isEliminated ? 'text-gray-400 cursor-not-allowed' : 'text-red-600 hover:text-red-900'} font-medium`}
                          title="Eliminar"
                        >
                          🗑️ Eliminar
                        </button>
                      </div>
                    </td>
                  </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>

        {/* Paginación */}
        {pagination.total > pagination.perPage && (
          <div className="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            <div className="flex items-center justify-between">
              <div className="text-sm text-gray-700">
                Mostrando <span className="font-medium">{((pagination.currentPage - 1) * pagination.perPage) + 1}</span> a{' '}
                <span className="font-medium">
                  {Math.min(pagination.currentPage * pagination.perPage, pagination.total)}
                </span>{' '}
                de <span className="font-medium">{pagination.total}</span> usuarios
              </div>
              <div className="flex gap-2">
                <button
                  onClick={() => handlePageChange(pagination.currentPage - 1)}
                  disabled={pagination.currentPage === 1}
                  className="px-3 py-1 rounded border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Anterior
                </button>
                <span className="px-3 py-1 text-sm text-gray-700">
                  Página {pagination.currentPage} de {pagination.lastPage}
                </span>
                <button
                  onClick={() => handlePageChange(pagination.currentPage + 1)}
                  disabled={pagination.currentPage === pagination.lastPage}
                  className="px-3 py-1 rounded border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Siguiente
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

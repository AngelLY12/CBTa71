# 🎨 Ejemplos de UI para Actualizar Permisos Individuales

Este archivo contiene ejemplos de código HTML/Alpine.js que puedes agregar a `roles.astro` para implementar la funcionalidad de actualización de permisos por usuario individual.

---

## 📋 Opción 1: Botones de Acción Rápida en la Tabla

Agrega estos botones en la columna de acciones de cada usuario:

```html
<!-- Agregar en la sección de la tabla de usuarios, dentro del <td> de acciones -->
<td class="p-3">
  <div class="flex gap-2 flex-wrap justify-center">
    
    <!-- Botón: Editar permisos individuales -->
    <button 
      @click="openEditUserPermissionsModal(user.id)"
      class="px-3 py-1 bg-purple-500 hover:bg-purple-600 text-white rounded text-xs font-semibold transition-colors"
      title="Editar permisos de este usuario">
      🔑 Permisos
    </button>
    
    <!-- Botón: Dar acceso rápido a reportes -->
    <button 
      @click="quickAddPermission(user.id, 'reports.view')"
      :disabled="isSaving || user.permissions?.includes('reports.view')"
      class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs disabled:opacity-50 disabled:cursor-not-allowed"
      title="Dar acceso a reportes">
      📊
    </button>
    
    <!-- Botón: Revocar eliminación -->
    <button 
      @click="quickRemovePermission(user.id, 'users.delete')"
      :disabled="isSaving || !user.permissions?.includes('users.delete')"
      class="px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white rounded text-xs disabled:opacity-50 disabled:cursor-not-allowed"
      title="Revocar permiso de eliminación">
      🚫
    </button>
    
  </div>
</td>
```

---

## 🎯 Opción 2: Modal Completo para Editar Permisos Individuales

Agrega este modal después de los modales existentes:

```html
<!-- Modal: Editar Permisos de Usuario Individual -->
<template x-teleport="body">
  <div x-show="showEditUserPermissionsModal" x-cloak 
       class="fixed inset-0 z-[130] flex items-center justify-center p-4">
    
    <!-- Overlay -->
    <div @click="showEditUserPermissionsModal = false" 
         class="absolute inset-0 bg-slate-900/60 backdrop-blur-md"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-2xl p-8 w-full max-w-4xl shadow-2xl max-h-[90vh] overflow-y-auto">
      
      <!-- Header -->
      <div class="mb-6">
        <h3 class="text-2xl font-bold text-slate-800 mb-2">
          🔑 Editar Permisos de Usuario
        </h3>
        <p class="text-slate-600" x-show="editingUser">
          Usuario: <span class="font-bold" x-text="editingUser?.fullName"></span>
        </p>
        <p class="text-sm text-slate-500">
          ID: <span x-text="editingUser?.id"></span> | 
          Rol: <span x-text="editingUser?.roles?.[0] || 'Sin rol'"></span>
        </p>
      </div>
      
      <!-- Permisos Actuales -->
      <div class="mb-6 p-4 bg-blue-50 rounded-lg">
        <h4 class="font-bold text-blue-900 mb-3">📋 Permisos Actuales</h4>
        <div class="flex flex-wrap gap-2">
          <template x-if="!editingUser?.permissions || editingUser.permissions.length === 0">
            <span class="text-sm text-blue-600 italic">Sin permisos asignados</span>
          </template>
          <template x-for="permission in editingUser?.permissions || []" :key="permission">
            <span class="px-3 py-1 bg-blue-200 text-blue-800 rounded-full text-xs font-semibold">
              <span x-text="permission"></span>
            </span>
          </template>
        </div>
      </div>
      
      <!-- Form de Actualización -->
      <form @submit.prevent="submitUserPermissionChanges()" class="space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Agregar Permisos -->
          <div class="p-4 bg-green-50 border border-green-200 rounded-xl">
            <h4 class="font-bold text-green-900 mb-3 flex items-center gap-2">
              <span>➕ Agregar Permisos</span>
              <span class="text-xs bg-green-200 px-2 py-1 rounded-full" 
                    x-text="`(${editPermissionsToAdd.length})`"></span>
            </h4>
            
            <div class="space-y-2 max-h-60 overflow-y-auto">
              <template x-for="permission in availablePermissions" :key="permission.id">
                <label class="flex items-center gap-2 p-2 hover:bg-green-100 rounded-lg cursor-pointer transition-colors">
                  <input 
                    type="checkbox" 
                    :value="permission.name"
                    :checked="editPermissionsToAdd.includes(permission.name)"
                    :disabled="editingUser?.permissions?.includes(permission.name)"
                    @change="togglePermissionToAdd($event.target.value, $event.target.checked)"
                    class="w-4 h-4 text-green-600 rounded focus:ring-green-500">
                  <span class="text-sm font-medium text-green-800" x-text="permission.displayName || permission.name"></span>
                  <span x-show="editingUser?.permissions?.includes(permission.name)" 
                        class="ml-auto text-xs text-green-600 font-bold">
                    ✓ Ya tiene
                  </span>
                </label>
              </template>
            </div>
          </div>
          
          <!-- Eliminar Permisos -->
          <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
            <h4 class="font-bold text-red-900 mb-3 flex items-center gap-2">
              <span>➖ Eliminar Permisos</span>
              <span class="text-xs bg-red-200 px-2 py-1 rounded-full" 
                    x-text="`(${editPermissionsToRemove.length})`"></span>
            </h4>
            
            <div class="space-y-2 max-h-60 overflow-y-auto">
              <template x-for="permission in availablePermissions" :key="permission.id">
                <label class="flex items-center gap-2 p-2 hover:bg-red-100 rounded-lg cursor-pointer transition-colors">
                  <input 
                    type="checkbox" 
                    :value="permission.name"
                    :checked="editPermissionsToRemove.includes(permission.name)"
                    :disabled="!editingUser?.permissions?.includes(permission.name)"
                    @change="togglePermissionToRemove($event.target.value, $event.target.checked)"
                    class="w-4 h-4 text-red-600 rounded focus:ring-red-500">
                  <span class="text-sm font-medium text-red-800" x-text="permission.displayName || permission.name"></span>
                  <span x-show="!editingUser?.permissions?.includes(permission.name)" 
                        class="ml-auto text-xs text-red-400 font-bold">
                    ✗ No tiene
                  </span>
                </label>
              </template>
            </div>
          </div>
          
        </div>
        
        <!-- Botones de Acción -->
        <div class="flex gap-3 pt-4 border-t">
          <button 
            type="button" 
            @click="closeEditUserPermissionsModal()" 
            class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 rounded-lg font-semibold transition-colors">
            Cancelar
          </button>
          <button 
            type="submit" 
            :disabled="isSaving || (editPermissionsToAdd.length === 0 && editPermissionsToRemove.length === 0)" 
            class="flex-1 py-3 bg-institucional hover-institucional text-white rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!isSaving">💾 Guardar Cambios</span>
            <span x-show="isSaving">⏳ Guardando...</span>
          </button>
        </div>
        
      </form>
      
    </div>
  </div>
</template>
```

---

## ⚙️ Funciones JavaScript para Alpine.js

Agrega estas funciones en el objeto `rolesData` de Alpine.js:

```javascript
// Agregar en la sección data() del componente Alpine.js
data() {
  return {
    // ... propiedades existentes ...
    
    // Nuevas propiedades para el modal individual
    showEditUserPermissionsModal: false,
    editingUser: null,
    editPermissionsToAdd: [],
    editPermissionsToRemove: [],
    
    // ... resto de propiedades ...
  }
},

// Agregar estas funciones después de updateUserPermissions()

/**
 * Abrir modal para editar permisos de un usuario específico
 */
async openEditUserPermissionsModal(userId) {
  const user = this.users.find(u => u.id === userId);
  if (!user) {
    this.showNotify('Usuario no encontrado', 'error');
    return;
  }
  
  // Establecer usuario actual
  this.editingUser = user;
  
  // Resetear selecciones
  this.editPermissionsToAdd = [];
  this.editPermissionsToRemove = [];
  
  // Abrir modal
  this.showEditUserPermissionsModal = true;
  
  console.log('📝 Editando permisos de:', user.fullName);
},

/**
 * Cerrar modal de edición individual
 */
closeEditUserPermissionsModal() {
  this.showEditUserPermissionsModal = false;
  this.editingUser = null;
  this.editPermissionsToAdd = [];
  this.editPermissionsToRemove = [];
},

/**
 * Toggle permiso en la lista de agregar
 */
togglePermissionToAdd(permissionName, isChecked) {
  if (isChecked) {
    if (!this.editPermissionsToAdd.includes(permissionName)) {
      this.editPermissionsToAdd.push(permissionName);
    }
  } else {
    const index = this.editPermissionsToAdd.indexOf(permissionName);
    if (index > -1) {
      this.editPermissionsToAdd.splice(index, 1);
    }
  }
  console.log('Permisos a agregar:', this.editPermissionsToAdd);
},

/**
 * Toggle permiso en la lista de eliminar
 */
togglePermissionToRemove(permissionName, isChecked) {
  if (isChecked) {
    if (!this.editPermissionsToRemove.includes(permissionName)) {
      this.editPermissionsToRemove.push(permissionName);
    }
  } else {
    const index = this.editPermissionsToRemove.indexOf(permissionName);
    if (index > -1) {
      this.editPermissionsToRemove.splice(index, 1);
    }
  }
  console.log('Permisos a eliminar:', this.editPermissionsToRemove);
},

/**
 * Enviar cambios de permisos del usuario
 */
async submitUserPermissionChanges() {
  if (!this.editingUser) {
    this.showNotify('No hay usuario seleccionado', 'error');
    return;
  }
  
  const resultado = await this.updateUserPermissions(
    this.editingUser.id,
    this.editPermissionsToAdd,
    this.editPermissionsToRemove
  );
  
  if (resultado.success) {
    this.closeEditUserPermissionsModal();
  }
},

/**
 * Agregar un permiso rápidamente (acción directa)
 */
async quickAddPermission(userId, permissionName) {
  if (!confirm(`¿Agregar permiso "${permissionName}" a este usuario?`)) {
    return;
  }
  
  await this.updateUserPermissions(userId, [permissionName], []);
},

/**
 * Eliminar un permiso rápidamente (acción directa)
 */
async quickRemovePermission(userId, permissionName) {
  if (!confirm(`¿Eliminar permiso "${permissionName}" de este usuario?`)) {
    return;
  }
  
  await this.updateUserPermissions(userId, [], [permissionName]);
},
```

---

## 🎨 Opción 3: Menú Contextual (Dropdown)

Para un menú más compacto con opciones de permisos:

```html
<!-- Agregar en cada fila de usuario -->
<td class="p-3 text-center">
  <div x-data="{ openDropdown: false }" class="relative inline-block">
    
    <!-- Botón principal -->
    <button 
      @click="openDropdown = !openDropdown"
      class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-semibold transition-colors">
      🔑 Permisos ▼
    </button>
    
    <!-- Menú desplegable -->
    <div 
      x-show="openDropdown"
      @click.away="openDropdown = false"
      x-transition
      class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-slate-200 z-50">
      
      <div class="py-2">
        <button 
          @click="openEditUserPermissionsModal(user.id); openDropdown = false"
          class="w-full px-4 py-2 text-left hover:bg-slate-100 flex items-center gap-2">
          <span>✏️</span>
          <span>Editar permisos</span>
        </button>
        
        <hr class="my-2">
        
        <button 
          @click="quickAddPermission(user.id, 'reports.view'); openDropdown = false"
          class="w-full px-4 py-2 text-left hover:bg-green-100 flex items-center gap-2 text-green-700">
          <span>➕</span>
          <span>Dar acceso a reportes</span>
        </button>
        
        <button 
          @click="quickAddPermission(user.id, 'users.create'); openDropdown = false"
          class="w-full px-4 py-2 text-left hover:bg-green-100 flex items-center gap-2 text-green-700">
          <span>➕</span>
          <span>Permitir crear usuarios</span>
        </button>
        
        <hr class="my-2">
        
        <button 
          @click="quickRemovePermission(user.id, 'users.delete'); openDropdown = false"
          class="w-full px-4 py-2 text-left hover:bg-red-100 flex items-center gap-2 text-red-700">
          <span>🚫</span>
          <span>Revocar eliminación</span>
        </button>
        
        <button 
          @click="quickRemovePermission(user.id, 'settings.update'); openDropdown = false"
          class="w-full px-4 py-2 text-left hover:bg-red-100 flex items-center gap-2 text-red-700">
          <span>🚫</span>
          <span>Revocar configuración</span>
        </button>
      </div>
      
    </div>
  </div>
</td>
```

---

## 🔔 Opción 4: Badge Interactivo de Permisos

Muestra los permisos como badges clicables:

```html
<!-- Agregar en la columna de permisos del usuario -->
<td class="p-3">
  <div class="flex flex-wrap gap-1">
    <template x-for="permission in (user.permissions || [])" :key="permission">
      <span 
        @click="quickRemovePermission(user.id, permission)"
        class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold cursor-pointer hover:bg-red-100 hover:text-red-800 transition-colors"
        :title="`Click para revocar: ${permission}`">
        <span x-text="permission"></span>
        <span class="ml-1">×</span>
      </span>
    </template>
    
    <!-- Botón para agregar más -->
    <button 
      @click="openEditUserPermissionsModal(user.id)"
      class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold hover:bg-green-200 transition-colors"
      title="Agregar más permisos">
      + Agregar
    </button>
  </div>
</td>
```

---

## 📱 Opción 5: Card de Usuario con Permisos

Para una vista más detallada tipo tarjetas:

```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  <template x-for="user in paginatedUsers" :key="user.id">
    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
      
      <!-- Header -->
      <div class="flex items-start justify-between mb-4">
        <div>
          <h3 class="font-bold text-slate-800" x-text="user.fullName"></h3>
          <p class="text-sm text-slate-500" x-text="user.email"></p>
        </div>
        <span 
          class="px-3 py-1 rounded-full text-xs font-bold"
          :class="getRoleColor(user.roles?.[0])"
          x-text="user.roles?.[0] || 'Sin rol'">
        </span>
      </div>
      
      <!-- Permisos -->
      <div class="mb-4">
        <h4 class="text-sm font-semibold text-slate-600 mb-2">🔑 Permisos:</h4>
        <div class="flex flex-wrap gap-1">
          <template x-if="!user.permissions || user.permissions.length === 0">
            <span class="text-xs text-slate-400 italic">Sin permisos</span>
          </template>
          <template x-for="permission in (user.permissions || []).slice(0, 3)" :key="permission">
            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">
              <span x-text="permission"></span>
            </span>
          </template>
          <span 
            x-show="user.permissions?.length > 3"
            class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs font-bold">
            +<span x-text="user.permissions.length - 3"></span> más
          </span>
        </div>
      </div>
      
      <!-- Acciones -->
      <button 
        @click="openEditUserPermissionsModal(user.id)"
        class="w-full py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-semibold transition-colors">
        ✏️ Editar Permisos
      </button>
      
    </div>
  </template>
</div>
```

---

## 🎯 Recomendación de Implementación

Para tu aplicación, recomiendo usar:

1. **Opción 1** (Botones de acción rápida) - Para acciones frecuentes y rápidas
2. **Opción 2** (Modal completo) - Para ediciones detalladas con control total
3. Combinar ambas para máxima flexibilidad

### Pasos de Implementación:

1. Copia el modal (Opción 2) al final de [roles.astro](c:\Users\sarah\Documents\GitHub\CBTA\Frond-end\src\pages\roles.astro)
2. Copia las funciones JavaScript al componente Alpine.js
3. Agrega los botones (Opción 1) en la tabla de usuarios existente
4. Prueba con el script PowerShell: `test-update-user-permissions.ps1`

---

**¿Necesitas ayuda para implementar alguna de estas opciones?** 🚀

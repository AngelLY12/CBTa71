# 🚀 Quick Start: Implementar Update Permissions Individual

Esta es una guía rápida para implementar la funcionalidad de actualización de permisos por usuario individual en tu aplicación.

## ✅ Lo que ya está hecho

1. ✅ **Función backend implementada**: `updateUserPermissions(userId, permissionsToAdd, permissionsToRemove)`
2. ✅ **Script de prueba**: `test-update-user-permissions.ps1`
3. ✅ **Documentación completa**: `PERMISSIONS_API_USAGE.md`
4. ✅ **Ejemplos de UI**: `UI_EXAMPLES_PERMISSIONS.md`

## 📝 Pasos para Implementar (5 minutos)

### Paso 1: Verificar que tienes la función (YA ESTÁ HECHO ✅)

La función `updateUserPermissions()` ya fue agregada a tu archivo [roles.astro](c:\Users\sarah\Documents\GitHub\CBTA\Frond-end\src\pages\roles.astro) después de la línea 3491.

```javascript
async updateUserPermissions(userId, permissionsToAdd = [], permissionsToRemove = [])
```

### Paso 2: Agregar propiedades al componente Alpine.js

Encuentra la función `data()` en [roles.astro](c:\Users\sarah\Documents\GitHub\CBTA\Frond-end\src\pages\roles.astro) y agrega estas propiedades:

```javascript
data() {
  return {
    // ... tus propiedades existentes ...
    
    // 👇 AGREGAR ESTAS LÍNEAS
    showEditUserPermissionsModal: false,
    editingUser: null,
    editPermissionsToAdd: [],
    editPermissionsToRemove: [],
  }
}
```

### Paso 3: Copiar funciones auxiliares

Agrega estas funciones después de `updateUserPermissions()`:

```javascript
// Abrir modal para editar permisos individuales
async openEditUserPermissionsModal(userId) {
  const user = this.users.find(u => u.id === userId);
  if (!user) {
    this.showNotify('Usuario no encontrado', 'error');
    return;
  }
  this.editingUser = user;
  this.editPermissionsToAdd = [];
  this.editPermissionsToRemove = [];
  this.showEditUserPermissionsModal = true;
},

closeEditUserPermissionsModal() {
  this.showEditUserPermissionsModal = false;
  this.editingUser = null;
  this.editPermissionsToAdd = [];
  this.editPermissionsToRemove = [];
},

togglePermissionToAdd(permissionName, isChecked) {
  if (isChecked && !this.editPermissionsToAdd.includes(permissionName)) {
    this.editPermissionsToAdd.push(permissionName);
  } else {
    const index = this.editPermissionsToAdd.indexOf(permissionName);
    if (index > -1) this.editPermissionsToAdd.splice(index, 1);
  }
},

togglePermissionToRemove(permissionName, isChecked) {
  if (isChecked && !this.editPermissionsToRemove.includes(permissionName)) {
    this.editPermissionsToRemove.push(permissionName);
  } else {
    const index = this.editPermissionsToRemove.indexOf(permissionName);
    if (index > -1) this.editPermissionsToRemove.splice(index, 1);
  }
},

async submitUserPermissionChanges() {
  if (!this.editingUser) return;
  const resultado = await this.updateUserPermissions(
    this.editingUser.id,
    this.editPermissionsToAdd,
    this.editPermissionsToRemove
  );
  if (resultado.success) this.closeEditUserPermissionsModal();
},

async quickAddPermission(userId, permissionName) {
  if (!confirm(`¿Agregar "${permissionName}"?`)) return;
  await this.updateUserPermissions(userId, [permissionName], []);
},

async quickRemovePermission(userId, permissionName) {
  if (!confirm(`¿Eliminar "${permissionName}"?`)) return;
  await this.updateUserPermissions(userId, [], [permissionName]);
},
```

### Paso 4: Agregar botón en la tabla

Busca la columna de acciones en tu tabla de usuarios y agrega este botón:

```html
<button 
  @click="openEditUserPermissionsModal(user.id)"
  class="px-3 py-1 bg-purple-500 hover:bg-purple-600 text-white rounded text-xs font-semibold">
  🔑 Permisos
</button>
```

### Paso 5: Agregar el modal

Copia este modal completo al final del archivo (antes del `</div>` final):

```html
<!-- Modal: Editar Permisos Individual -->
<template x-teleport="body">
  <div x-show="showEditUserPermissionsModal" x-cloak 
       class="fixed inset-0 z-[130] flex items-center justify-center p-4">
    <div @click="showEditUserPermissionsModal = false" 
         class="absolute inset-0 bg-slate-900/60 backdrop-blur-md"></div>
    
    <div class="relative bg-white rounded-2xl p-8 w-full max-w-4xl shadow-2xl max-h-[90vh] overflow-y-auto">
      <h3 class="text-2xl font-bold text-slate-800 mb-2">🔑 Editar Permisos</h3>
      <p class="text-slate-600 mb-6">
        Usuario: <span class="font-bold" x-text="editingUser?.fullName"></span>
      </p>
      
      <!-- Permisos actuales -->
      <div class="mb-6 p-4 bg-blue-50 rounded-lg">
        <h4 class="font-bold text-blue-900 mb-3">📋 Permisos Actuales</h4>
        <div class="flex flex-wrap gap-2">
          <template x-for="permission in editingUser?.permissions || []" :key="permission">
            <span class="px-3 py-1 bg-blue-200 text-blue-800 rounded-full text-xs font-semibold" 
                  x-text="permission"></span>
          </template>
        </div>
      </div>
      
      <form @submit.prevent="submitUserPermissionChanges()" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Agregar -->
          <div class="p-4 bg-green-50 border border-green-200 rounded-xl">
            <h4 class="font-bold text-green-900 mb-3">➕ Agregar</h4>
            <div class="space-y-2 max-h-60 overflow-y-auto">
              <template x-for="permission in availablePermissions" :key="permission.id">
                <label class="flex items-center gap-2 p-2 hover:bg-green-100 rounded cursor-pointer">
                  <input type="checkbox" :value="permission.name"
                         :checked="editPermissionsToAdd.includes(permission.name)"
                         :disabled="editingUser?.permissions?.includes(permission.name)"
                         @change="togglePermissionToAdd($event.target.value, $event.target.checked)"
                         class="w-4 h-4 text-green-600 rounded">
                  <span class="text-sm font-medium" x-text="permission.displayName || permission.name"></span>
                  <span x-show="editingUser?.permissions?.includes(permission.name)" 
                        class="ml-auto text-xs text-green-600">✓</span>
                </label>
              </template>
            </div>
          </div>
          
          <!-- Eliminar -->
          <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
            <h4 class="font-bold text-red-900 mb-3">➖ Eliminar</h4>
            <div class="space-y-2 max-h-60 overflow-y-auto">
              <template x-for="permission in availablePermissions" :key="permission.id">
                <label class="flex items-center gap-2 p-2 hover:bg-red-100 rounded cursor-pointer">
                  <input type="checkbox" :value="permission.name"
                         :checked="editPermissionsToRemove.includes(permission.name)"
                         :disabled="!editingUser?.permissions?.includes(permission.name)"
                         @change="togglePermissionToRemove($event.target.value, $event.target.checked)"
                         class="w-4 h-4 text-red-600 rounded">
                  <span class="text-sm font-medium" x-text="permission.displayName || permission.name"></span>
                  <span x-show="!editingUser?.permissions?.includes(permission.name)" 
                        class="ml-auto text-xs text-red-400">✗</span>
                </label>
              </template>
            </div>
          </div>
          
        </div>
        
        <div class="flex gap-3">
          <button type="button" @click="closeEditUserPermissionsModal()" 
                  class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 rounded-lg font-semibold">
            Cancelar
          </button>
          <button type="submit" 
                  :disabled="isSaving || (editPermissionsToAdd.length === 0 && editPermissionsToRemove.length === 0)" 
                  class="flex-1 py-3 bg-institucional hover-institucional text-white rounded-lg font-semibold disabled:opacity-50">
            <span x-show="!isSaving">💾 Guardar</span>
            <span x-show="isSaving">⏳ Guardando...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
```

## 🧪 Probar la Funcionalidad

### Opción 1: Desde PowerShell

```powershell
# 1. Obtener tu token (cópialo desde el localStorage del navegador)
$env:ACCESS_TOKEN = "tu_token_aqui"

# 2. Ejecutar el script de prueba
.\test-update-user-permissions.ps1
```

### Opción 2: Desde la Consola del Navegador (F12)

```javascript
// Obtener el componente
const app = Alpine.$data(document.querySelector('[x-data="rolesData"]'));

// Probar actualización directa
await app.updateUserPermissions(4, ['reports.view'], ['users.delete']);

// Probar acción rápida
await app.quickAddPermission(4, 'reports.view');
```

### Opción 3: Desde la UI

1. Ve a la página de roles
2. Click en el botón "🔑 Permisos" de cualquier usuario
3. Selecciona permisos para agregar/eliminar
4. Click en "Guardar"

## 📊 Verificar que Funciona

1. **Abrir consola (F12)** - Verás logs detallados:
   ```
   📤 updateUserPermissions - Enviando para userId 4: {...}
   📥 updateUserPermissions - Respuesta: {...}
   ✅ Permisos actualizados: 2 agregados, 1 eliminados
   ```

2. **Ver notificación** - Aparecerá un mensaje verde en la esquina superior derecha

3. **Ver cambios** - La tabla se recargará automáticamente con los nuevos permisos

## 🎯 Atajos Rápidos

Si solo necesitas acciones rápidas sin el modal completo, usa esto:

```html
<!-- Agregar permiso de reportes -->
<button @click="quickAddPermission(user.id, 'reports.view')">
  📊 Dar Reportes
</button>

<!-- Revocar eliminación -->
<button @click="quickRemovePermission(user.id, 'users.delete')">
  🚫 Sin Delete
</button>
```

## 🔧 Troubleshooting

### Error 401 (No autorizado)
- Verifica que tengas el token en localStorage
- Asegúrate de tener el permiso `sync.permissions`

### Error 422 (Validación)
- Verifica que el userId exista
- Asegúrate de que los permisos sean válidos

### No se ve el botón
- Verifica que copiaste el HTML en el lugar correcto
- Revisa la consola para errores de Alpine.js

### El modal no se abre
- Verifica que agregaste las propiedades en `data()`
- Confirma que las funciones están copiadas correctamente

## 📚 Documentación Adicional

- **Guía completa**: [PERMISSIONS_API_USAGE.md](PERMISSIONS_API_USAGE.md)
- **Ejemplos de UI**: [UI_EXAMPLES_PERMISSIONS.md](UI_EXAMPLES_PERMISSIONS.md)
- **Script de prueba**: [test-update-user-permissions.ps1](../test-update-user-permissions.ps1)

## 🎉 ¡Listo!

Con estos 5 pasos tienes:
- ✅ Actualización de permisos individuales funcional
- ✅ Modal completo con UI amigable
- ✅ Acciones rápidas desde botones
- ✅ Logs detallados para debugging
- ✅ Manejo de errores robusto

**¿Tienes dudas?** Revisa los archivos de documentación o abre la consola (F12) para ver logs detallados.

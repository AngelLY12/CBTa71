# 🔑 Guía de Uso - API de Permisos

Este documento explica cómo usar las funciones de actualización de permisos disponibles en la aplicación.

## 📋 Endpoints Disponibles

### 1. Actualizar Permisos por Usuario Individual

**Endpoint:** `POST /api/v1/admin-actions/update-permissions/{userId}`

**Función Frontend:** `updateUserPermissions(userId, permissionsToAdd, permissionsToRemove)`

#### Descripción
Permite actualizar los permisos de un **usuario específico** identificado por su `userId`.

#### Parámetros

- **userId** (number, requerido): ID del usuario a actualizar
- **permissionsToAdd** (array, opcional): Lista de permisos a agregar
  - Ejemplo: `["users.create", "reports.view"]`
- **permissionsToRemove** (array, opcional): Lista de permisos a eliminar
  - Ejemplo: `["users.delete", "settings.update"]`

#### Headers Requeridos

```javascript
{
  "X-User-Role": "admin|supervisor",
  "X-User-Permission": "sync.permissions",
  "Authorization": "Bearer {token}"
}
```

#### Ejemplo de Uso en Frontend

```javascript
// En Alpine.js (dentro de roles.astro)
async actualizarPermisosUsuario() {
  const userId = 4; // ID del usuario
  const permisosAgregar = ["view.students", "edit.students"];
  const permisosEliminar = ["delete.students"];
  
  const resultado = await this.updateUserPermissions(
    userId,
    permisosAgregar,
    permisosEliminar
  );
  
  if (resultado.success) {
    console.log('✅ Permisos actualizados exitosamente');
  } else {
    console.error('❌ Error:', resultado.message);
  }
}
```

#### Ejemplo de Request Body

```json
{
  "permissionsToAdd": [
    "users.create",
    "reports.view"
  ],
  "permissionsToRemove": [
    "users.delete",
    "settings.update"
  ]
}
```

#### Respuestas

**Éxito (200):**
```json
{
  "success": true,
  "message": "Permisos actualizados correctamente.",
  "data": {
    "updated": [
      {
        "userId": 4,
        "fullName": "Juan Perez",
        "permissions": {
          "added": ["view.students"],
          "removed": ["create.student"]
        }
      }
    ]
  }
}
```

**Error de Validación (422):**
```json
{
  "success": false,
  "message": "Error descriptivo para el usuario",
  "error_code": "VALIDATION_ERROR",
  "errors": {
    "permissionsToAdd": [
      "El campo permissionsToAdd debe ser un array"
    ],
    "userId": [
      "Usuario no encontrado"
    ]
  }
}
```

**No Autorizado (401):**
```json
{
  "success": false,
  "message": "No autorizado: el usuario autenticado no tiene permiso para ejecutar esta acción",
  "error_code": "UNAUTHORIZED"
}
```

---

### 2. Actualizar Permisos Masivos (Múltiples Usuarios)

**Endpoint:** `POST /api/v1/admin-actions/update-permissions`

**Función Frontend:** `updatePermissions()` (ya implementada)

#### Descripción
Permite actualizar permisos para **múltiples usuarios** usando sus CURPs o para todos los usuarios con un rol específico.

#### Parámetros Request Body

Opción 1 - Por CURPs:
```json
{
  "curps": [
    "CURP123456789012345",
    "CURP987654321098765"
  ],
  "permissionsToAdd": ["view.students"],
  "permissionsToRemove": ["delete.students"]
}
```

Opción 2 - Por Rol:
```json
{
  "role": "teacher",
  "permissionsToAdd": ["view.students"],
  "permissionsToRemove": ["delete.students"]
}
```

#### Ejemplo de Uso en Frontend

```javascript
// Ya está implementado en roles.astro
// Se usa desde el modal de permisos cuando seleccionas múltiples usuarios
// La función updatePermissions() se ejecuta automáticamente
```

---

## 🎯 Casos de Uso

### Caso 1: Actualizar permisos de un solo usuario desde un botón

```html
<!-- En tu tabla de usuarios -->
<button @click="updateUserPermissions(user.id, ['view.reports'], [])"
        class="btn-primary">
  Dar acceso a reportes
</button>
```

### Caso 2: Abrir un modal para editar permisos de un usuario específico

```javascript
async abrirModalPermisosIndividual(userId) {
  // Obtener usuario actual
  const usuario = this.users.find(u => u.id === userId);
  
  // Configurar modal con permisos del usuario
  this.selectedUsers = [userId];
  this.permissionsToAdd = [];
  this.permissionsToRemove = [];
  this.showPermissionsModal = true;
  
  console.log('Editando permisos de:', usuario.fullName);
}
```

### Caso 3: Revocar un permiso específico a múltiples usuarios

```javascript
async revocarPermisoMasivo(permiso) {
  if (!confirm(`¿Revocar ${permiso} a ${this.selectedUsers.length} usuarios?`)) {
    return;
  }
  
  // Usar el endpoint masivo (bulk)
  this.permissionsToAdd = [];
  this.permissionsToRemove = [permiso];
  await this.updatePermissions();
}
```

---

## 🔐 Permisos Disponibles

Los permisos típicos en el sistema incluyen:

- `users.create` - Crear usuarios
- `users.view` - Ver usuarios
- `users.edit` - Editar usuarios
- `users.delete` - Eliminar usuarios
- `reports.view` - Ver reportes
- `reports.create` - Crear reportes
- `settings.update` - Actualizar configuración
- `view.students` - Ver estudiantes
- `edit.students` - Editar estudiantes
- `delete.students` - Eliminar estudiantes
- `sync.permissions` - Sincronizar permisos (admin)
- `promote.student` - Promover estudiantes

---

## ⚠️ Consideraciones Importantes

1. **Autorización**: Solo usuarios con rol `admin` o `supervisor` y permiso `sync.permissions` pueden actualizar permisos.

2. **Validación**: Ambos endpoints validan que:
   - Al menos uno de los arrays (`permissionsToAdd` o `permissionsToRemove`) tenga elementos
   - Los permisos existan en el sistema
   - El usuario/usuarios existan

3. **Diferencias entre endpoints**:
   - **Individual (`/{userId}`)**: Actualiza un solo usuario, ideal para ediciones rápidas
   - **Masivo**: Actualiza múltiples usuarios o todos los de un rol, ideal para cambios bulk

4. **Recarga de datos**: Ambas funciones llaman a `loadUsers()` automáticamente después de una actualización exitosa para refrescar la lista.

5. **Manejo de errores**: Ambas funciones:
   - Muestran notificaciones al usuario
   - Registran detalles en la consola para debugging
   - Retornan objetos con `success` y `message` para lógica condicional

---

## 📊 Ejemplo Completo: Botón de Acción Rápida

```html
<!-- Agregar en la tabla de usuarios -->
<td class="p-3 text-center">
  <div class="flex gap-2 justify-center">
    <!-- Dar acceso a reportes -->
    <button 
      @click="updateUserPermissions(user.id, ['reports.view'], [])"
      :disabled="isSaving"
      class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs"
      title="Dar acceso a reportes">
      📊 Reportes
    </button>
    
    <!-- Revocar acceso de eliminación -->
    <button 
      @click="updateUserPermissions(user.id, [], ['users.delete', 'students.delete'])"
      :disabled="isSaving"
      class="px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white rounded text-xs"
      title="Revocar permisos de eliminación">
      🚫 Sin Delete
    </button>
  </div>
</td>
```

---

## 🧪 Testing

### Test con PowerShell

```powershell
# Crear archivo: test-update-user-permissions.ps1

$token = "TU_ACCESS_TOKEN"
$userId = 4
$apiUrl = "https://nginx-production-728f.up.railway.app/api/v1/admin-actions/update-permissions/$userId"

$body = @{
    permissionsToAdd = @("users.create", "reports.view")
    permissionsToRemove = @("users.delete")
} | ConvertTo-Json

$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
    "X-User-Role" = "admin"
    "X-User-Permission" = "sync.permissions"
}

Invoke-RestMethod -Uri $apiUrl -Method POST -Body $body -Headers $headers
```

### Test desde la Consola del Navegador

```javascript
// Obtener el componente Alpine.js
const rolesComponent = Alpine.$data(document.querySelector('[x-data="rolesData"]'));

// Actualizar permisos de un usuario
const resultado = await rolesComponent.updateUserPermissions(
  4, // userId
  ['users.create', 'reports.view'], // agregar
  ['users.delete'] // eliminar
);

console.log('Resultado:', resultado);
```

---

## 📝 Notas Finales

- Siempre verifica que el token de autenticación esté presente antes de llamar estas funciones
- Los cambios se reflejan inmediatamente en la UI después de una actualización exitosa
- Revisa la consola del navegador para logs detallados de cada operación
- En caso de error 401, verifica que el usuario tenga el permiso `sync.permissions`

---

**Última actualización:** 29 de enero de 2026
**Versión de la API:** v1

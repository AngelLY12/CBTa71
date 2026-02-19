# 🎭 Guía de Uso - API de Roles

Este documento explica cómo usar las funciones de actualización de roles disponibles en la aplicación.

## 📋 Endpoints Disponibles

### 1. Actualizar Roles por Usuario Individual

**Endpoint:** `POST /api/v1/admin-actions/updated-roles/{userId}`

**Función Frontend:** `updateUserRoles(userId, rolesToAdd, rolesToRemove)`

#### Descripción
Permite actualizar los roles de un **usuario específico** identificado por su `userId`.

#### Parámetros

- **userId** (number, requerido): ID del usuario a actualizar
- **rolesToAdd** (array, opcional): Lista de roles a agregar
  - Ejemplo: `["editor", "supervisor"]`
- **rolesToRemove** (array, opcional): Lista de roles a eliminar
  - Ejemplo: `["viewer", "assistant"]`

#### Headers Requeridos

```javascript
{
  "X-User-Role": "admin|supervisor",
  "X-User-Permission": "sync.roles",
  "Authorization": "Bearer {token}"
}
```

#### Ejemplo de Uso en Frontend

```javascript
// En Alpine.js (dentro de roles.astro)
async actualizarRolesUsuario() {
  const userId = 4; // ID del usuario
  const rolesAgregar = ["student", "editor"];
  const rolesEliminar = ["guest"];
  
  const resultado = await this.updateUserRoles(
    userId,
    rolesAgregar,
    rolesEliminar
  );
  
  if (resultado.success) {
    console.log('✅ Roles actualizados exitosamente');
  } else {
    console.error('❌ Error:', resultado.message);
  }
}
```

#### Ejemplo de Request Body

```json
{
  "rolesToAdd": [
    "editor",
    "supervisor"
  ],
  "rolesToRemove": [
    "viewer",
    "assistant"
  ]
}
```

#### Respuestas

**Éxito (200):**
```json
{
  "success": true,
  "message": "Roles actualizados correctamente.",
  "data": {
    "updated": {
      "userId": 4,
      "fullName": "Juan Perez",
      "roles": {
        "added": [
          "student"
        ],
        "removed": [
          "guest"
        ]
      }
    }
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
    "rolesToAdd": [
      "El campo rolesToAdd debe ser un array"
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

### 2. Actualizar Roles Masivos (Múltiples Usuarios)

**Endpoint:** `POST /api/v1/admin-actions/update-roles`

**Función Frontend:** `updateRoles()` (ya implementada)

#### Descripción
Permite actualizar roles para **múltiples usuarios** usando sus CURPs o para todos los usuarios con un rol específico.

#### Parámetros Request Body

Opción 1 - Por CURPs:
```json
{
  "curps": [
    "CURP123456789012345",
    "CURP987654321098765"
  ],
  "rolesToAdd": ["student"],
  "rolesToRemove": ["guest"]
}
```

Opción 2 - Por Rol:
```json
{
  "role": "teacher",
  "rolesToAdd": ["supervisor"],
  "rolesToRemove": ["student"]
}
```

#### Ejemplo de Uso en Frontend

```javascript
// Ya está implementado en roles.astro
// Se usa desde el modal de roles cuando seleccionas múltiples usuarios
// La función updateRoles() se ejecuta automáticamente
```

---

## 🎯 Casos de Uso

### Caso 1: Actualizar roles de un solo usuario desde un botón

```html
<!-- En tu tabla de usuarios -->
<button @click="updateUserRoles(user.id, ['editor'], [])"
        class="btn-primary">
  Hacer Editor
</button>
```

### Caso 2: Abrir un modal para editar roles de un usuario específico

```javascript
async abrirModalRolesIndividual(userId) {
  // Obtener usuario actual
  const usuario = this.users.find(u => u.id === userId);
  
  // Configurar modal con roles del usuario
  this.selectedUsers = [userId];
  this.rolesToAdd = [];
  this.rolesToRemove = [];
  this.showRolesModal = true;
  
  console.log('Editando roles de:', usuario.fullName);
}
```

### Caso 3: Revocar un rol específico a múltiples usuarios

```javascript
async revocarRolMasivo(rol) {
  if (!confirm(`¿Revocar rol ${rol} a ${this.selectedUsers.length} usuarios?`)) {
    return;
  }
  
  // Usar el endpoint masivo (bulk)
  this.rolesToAdd = [];
  this.rolesToRemove = [rol];
  await this.updateRoles();
}
```

---

## 🔐 Roles Disponibles

Los roles típicos en el sistema incluyen:

- `admin` - Administrador del sistema
- `supervisor` - Supervisor de personal
- `teacher` - Profesor
- `student` - Estudiante
- `editor` - Editor de contenido
- `viewer` - Solo lectura
- `assistant` - Asistente
- `guest` - Invitado

---

## ⚠️ Consideraciones Importantes

1. **Autorización**: Solo usuarios con rol `admin` o `supervisor` y permiso `sync.roles` pueden actualizar roles.

2. **Validación**: Ambos endpoints validan que:
   - Al menos uno de los arrays (`rolesToAdd` o `rolesToRemove`) tenga elementos
   - Los roles existan en el sistema
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
    <!-- Hacer profesor -->
    <button 
      @click="updateUserRoles(user.id, ['teacher'], ['student'])"
      :disabled="isSaving"
      class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded text-xs"
      title="Asignar rol de profesor">
      👨‍🏫 Profesor
    </button>
    
    <!-- Hacer estudiante -->
    <button 
      @click="updateUserRoles(user.id, ['student'], ['teacher'])"
      :disabled="isSaving"
      class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs"
      title="Asignar rol de estudiante">
      👤 Estudiante
    </button>
    
    <!-- Revocar todos los roles -->
    <button 
      @click="updateUserRoles(user.id, ['guest'], user.roles)"
      :disabled="isSaving"
      class="px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white rounded text-xs"
      title="Convertir a invitado">
      🚪 Invitado
    </button>
  </div>
</td>
```

---

## 🧪 Testing

### Test con PowerShell

```powershell
# Crear archivo: test-update-user-roles.ps1

$token = "TU_ACCESS_TOKEN"
$userId = 4
$apiUrl = "https://nginx-production-728f.up.railway.app/api/v1/admin-actions/updated-roles/$userId"

$body = @{
    rolesToAdd = @("editor", "supervisor")
    rolesToRemove = @("viewer")
} | ConvertTo-Json

$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
    "X-User-Role" = "admin"
    "X-User-Permission" = "sync.roles"
}

Invoke-RestMethod -Uri $apiUrl -Method POST -Body $body -Headers $headers
```

### Test desde la Consola del Navegador

```javascript
// Obtener el componente Alpine.js
const rolesComponent = Alpine.$data(document.querySelector('[x-data="rolesData"]'));

// Actualizar roles de un usuario
const resultado = await rolesComponent.updateUserRoles(
  4, // userId
  ['editor', 'supervisor'], // agregar
  ['viewer'] // eliminar
);

console.log('Resultado:', resultado);
```

---

## 📝 Notas Finales

- Siempre verifica que el token de autenticación esté presente antes de llamar estas funciones
- Los cambios se reflejan inmediatamente en la UI después de una actualización exitosa
- Revisa la consola del navegador para logs detallados de cada operación
- En caso de error 401, verifica que el usuario tenga el permiso `sync.roles`
- Un usuario puede tener múltiples roles simultáneamente

---

**Última actualización:** 29 de enero de 2026
**Versión de la API:** v1

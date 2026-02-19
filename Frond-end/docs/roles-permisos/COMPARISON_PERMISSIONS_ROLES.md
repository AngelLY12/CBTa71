# 🔄 Comparación: Endpoints de Actualización Individual (Permisos vs Roles)

## 📊 Vista Rápida

Ambos endpoints siguen el mismo patrón pero para propósitos diferentes.

| Característica | Permisos | Roles |
|---|---|---|
| **Endpoint** | `POST /update-permissions/{userId}` | `POST /updated-roles/{userId}` |
| **Función JS** | `updateUserPermissions()` | `updateUserRoles()` |
| **Campo Request** | `permissionsToAdd` / `permissionsToRemove` | `rolesToAdd` / `rolesToRemove` |
| **Header Permission** | `sync.permissions` | `sync.roles` |
| **Respuesta data** | `data.updated[0]` | `data.updated` |
| **Estado** | ✅ Implementado | ✅ Implementado |

---

## 🎯 Cuándo Usar Cada Uno

### 🔑 Permisos Individuales

**Cuándo:**
- Controlar acciones específicas de un usuario
- Dar acceso a funcionalidades concretas
- Revocar capacidades específicas

**Ejemplos:**
```javascript
// Dar acceso a reportes
await updateUserPermissions(4, ['reports.view'], []);

// Revocar eliminación de usuarios
await updateUserPermissions(4, [], ['users.delete']);

// Cambiar permisos completos
await updateUserPermissions(4, ['reports.view', 'reports.create'], ['users.delete']);
```

**Permisos típicos:**
- `users.create`, `users.view`, `users.edit`, `users.delete`
- `reports.view`, `reports.create`, `reports.edit`
- `settings.update`, `logs.view`
- `sync.permissions`, `promote.student`

---

### 🎭 Roles Individuales

**Cuándo:**
- Asignar un rol/grupo de usuarios
- Cambiar la categoría o función del usuario
- Gestionar clasificaciones de usuarios

**Ejemplos:**
```javascript
// Hacer profesor
await updateUserRoles(4, ['teacher'], []);

// Cambiar de estudiante a editor
await updateUserRoles(4, ['editor'], ['student']);

// Cambiar a supervisor
await updateUserRoles(4, ['supervisor'], ['teacher', 'student']);
```

**Roles típicos:**
- `admin` - Administrador del sistema
- `supervisor` - Supervisor
- `teacher` - Profesor
- `student` - Estudiante
- `editor` - Editor de contenido
- `viewer` - Solo lectura
- `assistant` - Asistente
- `guest` - Invitado

---

## 🔗 Relación Permisos ↔ Roles

```
Rol "teacher" (1 rol)
    ├─ Permiso: view.students      (ver estudiantes)
    ├─ Permiso: edit.students      (editar estudiantes)
    ├─ Permiso: create.content     (crear contenido)
    └─ Permiso: reports.view       (ver reportes)

Rol "admin" (1 rol)
    ├─ Permiso: users.create       (crear usuarios)
    ├─ Permiso: users.delete       (eliminar usuarios)
    ├─ Permiso: sync.permissions   (sincronizar permisos)
    └─ ... (muchos más)

Usuario puede tener:
    • Rol: teacher
    • Permisos: view.students, edit.students
        (heredados del rol teacher)
```

---

## 📋 Comparación Lado a Lado

### Actualizar Permisos

```javascript
// Función
async updateUserPermissions(userId, permissionsToAdd = [], permissionsToRemove = [])

// Request
POST /v1/admin-actions/update-permissions/{userId}
{
  "permissionsToAdd": ["users.create", "reports.view"],
  "permissionsToRemove": ["users.delete"]
}

// Response
{
  "success": true,
  "data": {
    "updated": [{
      "userId": 4,
      "fullName": "Juan Perez",
      "permissions": {
        "added": ["users.create", "reports.view"],
        "removed": ["users.delete"]
      }
    }]
  }
}

// Uso
const resultado = await updateUserPermissions(
  4,
  ['users.create', 'reports.view'],
  ['users.delete']
);
```

### Actualizar Roles

```javascript
// Función
async updateUserRoles(userId, rolesToAdd = [], rolesToRemove = [])

// Request
POST /v1/admin-actions/updated-roles/{userId}
{
  "rolesToAdd": ["editor", "supervisor"],
  "rolesToRemove": ["viewer"]
}

// Response
{
  "success": true,
  "data": {
    "updated": {
      "userId": 4,
      "fullName": "Juan Perez",
      "roles": {
        "added": ["editor"],
        "removed": ["guest"]
      }
    }
  }
}

// Uso
const resultado = await updateUserRoles(
  4,
  ['editor', 'supervisor'],
  ['viewer']
);
```

---

## 🚨 Diferencias Importantes

### Estructura de Respuesta

**Permisos:**
```javascript
result.data.updated[0]  // Es un ARRAY
// {
//   "userId": 4,
//   "permissions": { "added": [], "removed": [] }
// }
```

**Roles:**
```javascript
result.data.updated     // Es un OBJETO (no array)
// {
//   "userId": 4,
//   "roles": { "added": [], "removed": [] }
// }
```

### Headers

**Permisos:**
```javascript
"X-User-Permission": "sync.permissions"
```

**Roles:**
```javascript
"X-User-Permission": "sync.roles"
```

### Scope

**Permisos:** Muy granulares, controlan acciones específicas

**Roles:** Más amplios, clasifican el tipo de usuario

---

## 💡 Casos de Uso Combinados

Típicamente, usarías ambos juntos:

### Escenario: Promover Estudiante a Profesor

```javascript
// 1. Cambiar rol
await updateUserRoles(userId, ['teacher'], ['student']);

// 2. Asignar permisos de profesor
await updateUserPermissions(userId, 
  ['view.students', 'edit.students', 'reports.view'],
  ['edit.content']  // Revocar permisos de estudiante
);

// O hacerlo en paralelo
await Promise.all([
  updateUserRoles(userId, ['teacher'], ['student']),
  updateUserPermissions(userId, ['view.students', 'edit.students'], ['edit.content'])
]);
```

### Escenario: Dar Acceso Temporal

```javascript
// Solo agregar permiso sin cambiar rol
await updateUserPermissions(userId, ['reports.view'], []);
// El usuario mantiene su rol pero ahora puede ver reportes
```

### Escenario: Revocar Acceso Completo

```javascript
// 1. Cambiar a rol restringido
await updateUserRoles(userId, ['guest'], ['teacher', 'editor']);

// 2. Revocar todos los permisos especiales
await updateUserPermissions(userId, [], ['users.create', 'reports.view', 'settings.update']);
```

---

## 🧪 Testing Ambos Endpoints

### Script PowerShell Combinado

```powershell
# test-update-user-both.ps1

$token = "TU_TOKEN"
$userId = 4

# Actualizar roles
$rolesPayload = @{
    rolesToAdd = @("teacher")
    rolesToRemove = @("student")
} | ConvertTo-Json

# Actualizar permisos
$permissionsPayload = @{
    permissionsToAdd = @("view.students", "reports.view")
    permissionsToRemove = @("edit.content")
} | ConvertTo-Json

$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
    "X-User-Role" = "admin"
}

# Test roles
Write-Host "Testing roles endpoint..." -ForegroundColor Cyan
$rolesResult = Invoke-RestMethod -Uri "https://api/v1/admin-actions/updated-roles/$userId" `
    -Method POST `
    -Headers @{$headers; "X-User-Permission" = "sync.roles"} `
    -Body $rolesPayload

Write-Host "✅ Roles: $($rolesResult.message)" -ForegroundColor Green

# Test permissions
Write-Host "Testing permissions endpoint..." -ForegroundColor Cyan
$permResult = Invoke-RestMethod -Uri "https://api/v1/admin-actions/update-permissions/$userId" `
    -Method POST `
    -Headers @{$headers; "X-User-Permission" = "sync.permissions"} `
    -Body $permissionsPayload

Write-Host "✅ Permisos: $($permResult.message)" -ForegroundColor Green
```

---

## 🎯 Matriz de Decisión

```
¿Qué necesitas hacer?

├─ Cambiar categoría del usuario (teacher → student)
│  └─ Usa: updateUserRoles()
│
├─ Controlar una acción específica (crear reportes)
│  └─ Usa: updateUserPermissions()
│
├─ Asignar múltiples cambios
│  ├─ Si cambia rol Y permisos
│  │  └─ Usa AMBOS (en paralelo o secuencia)
│  │
│  └─ Si solo cambian permisos
│     └─ Usa: updateUserPermissions()
│
└─ Múltiples usuarios
   └─ Usa versión MASIVA (/update-roles o /update-permissions)
```

---

## 📚 Documentación

**Detalle Permisos:** [PERMISSIONS_API_USAGE.md](c:\Users\sarah\Documents\GitHub\CBTA\Frond-end\PERMISSIONS_API_USAGE.md)

**Detalle Roles:** [ROLES_API_USAGE.md](c:\Users\sarah\Documents\GitHub\CBTA\Frond-end\ROLES_API_USAGE.md)

**Implementación Permisos:** [UPDATE_PERMISSIONS_ENDPOINT.md](c:\Users\sarah\Documents\GitHub\CBTA\Frond-end\RESUMEN_PERMISSIONS_IMPLEMENTATION.md)

**Implementación Roles:** [UPDATE_ROLES_ENDPOINT.md](c:\Users\sarah\Documents\GitHub\CBTA\Frond-end\UPDATE_ROLES_ENDPOINT.md)

---

## ✅ Checklist: Usar Ambos Endpoints

- [x] Función `updateUserPermissions()` - Implementada
- [x] Función `updateUserRoles()` - Implementada
- [x] Documentación de permisos - Completa
- [x] Documentación de roles - Completa
- [x] Tests para permisos - test-update-user-permissions.ps1
- [x] Tests para roles - test-update-user-roles.ps1
- [ ] UI para permisos - Ver UI_EXAMPLES_PERMISSIONS.md
- [ ] UI para roles - Adaptar UI_EXAMPLES_PERMISSIONS.md

---

## 🎉 Conclusión

**Tienes ambos endpoints completamente implementados:**

1. ✅ Actualizar permisos individuales
2. ✅ Actualizar roles individuales

**Ambos:**
- Tienen validaciones robustas
- Incluyen manejo de errores
- Generan logs detallados
- Recargan datos automáticamente
- Notifican al usuario

**Siguientes pasos (opcionales):**
- Agregar UI para ambos endpoints
- Crear modales separados o combinados
- Integrar en tabla de usuarios

---

**Última actualización:** 29 de enero de 2026  
**Versión API:** v1  
**Estado:** ✅ Ambos endpoints operativos

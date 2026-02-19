# 🎯 Update User Permissions - Implementación Completa

## 📖 Descripción

Implementación completa del endpoint de actualización de permisos individuales para usuarios en el sistema CBTA.

**Endpoint implementado:** `POST /api/v1/admin-actions/update-permissions/{userId}`

---

## 🗂️ Estructura de Archivos

```
CBTA/
├── test-update-user-permissions.ps1       # Script PowerShell para testing
└── Frond-end/
    ├── PERMISSIONS_API_USAGE.md           # 📚 Guía completa de la API
    ├── UI_EXAMPLES_PERMISSIONS.md         # 🎨 5 opciones de UI con código
    ├── QUICK_START_PERMISSIONS.md         # 🚀 Guía rápida (5 minutos)
    ├── RESUMEN_PERMISSIONS_IMPLEMENTATION.md  # 📋 Resumen ejecutivo
    ├── README_PERMISSIONS.md              # 📖 Este archivo
    └── src/
        └── pages/
            └── roles.astro                # ✅ Función implementada (línea 3491)
```

---

## 🚀 Quick Start

### 1️⃣ La función ya está lista

La función `updateUserPermissions()` ya está implementada en [roles.astro](src/pages/roles.astro):

```javascript
await updateUserPermissions(userId, permissionsToAdd, permissionsToRemove)
```

### 2️⃣ Usar desde código

```javascript
// Ejemplo: Agregar permiso de reportes
const resultado = await this.updateUserPermissions(
  4,                      // userId
  ['reports.view'],       // agregar
  []                      // eliminar
);

if (resultado.success) {
  console.log('✅ Actualizado');
}
```

### 3️⃣ Agregar UI (Opcional)

Sigue la guía: [QUICK_START_PERMISSIONS.md](QUICK_START_PERMISSIONS.md)

### 4️⃣ Probar

```powershell
# PowerShell
$env:ACCESS_TOKEN = "tu_token"
.\test-update-user-permissions.ps1
```

---

## 📚 Documentación

### 🔍 Para Desarrolladores

| Documento | Descripción | Tiempo Lectura |
|-----------|-------------|----------------|
| [PERMISSIONS_API_USAGE.md](PERMISSIONS_API_USAGE.md) | Guía completa de la API con ejemplos | 10 min |
| [UI_EXAMPLES_PERMISSIONS.md](UI_EXAMPLES_PERMISSIONS.md) | 5 opciones de UI con código completo | 5 min |
| [QUICK_START_PERMISSIONS.md](QUICK_START_PERMISSIONS.md) | Implementación paso a paso | 5 min |
| [RESUMEN_PERMISSIONS_IMPLEMENTATION.md](RESUMEN_PERMISSIONS_IMPLEMENTATION.md) | Resumen ejecutivo del proyecto | 3 min |

### 🧪 Para Testing

| Archivo | Descripción |
|---------|-------------|
| [test-update-user-permissions.ps1](../test-update-user-permissions.ps1) | Script PowerShell con colores y detalles |

---

## 🎨 Opciones de Interfaz

### Opción 1: Botones Rápidos ⚡
**Ideal para:** Acciones frecuentes y directas

```html
<button @click="quickAddPermission(user.id, 'reports.view')">
  📊 Dar Reportes
</button>
```

### Opción 2: Modal Completo ⭐ RECOMENDADO
**Ideal para:** Edición detallada con control total

- Vista de permisos actuales
- Checkboxes organizados en agregar/eliminar
- Validación visual automática

### Opción 3: Menú Contextual 📋
**Ideal para:** Interfaces compactas

- Dropdown con múltiples acciones
- Ahorra espacio en pantalla

### Opción 4: Badges Interactivos 🏷️
**Ideal para:** Visualización inline

- Click en badge para eliminar
- Botón "+" para agregar

### Opción 5: Cards de Usuario 🎴
**Ideal para:** Dashboards

- Vista completa del usuario
- Información organizada en tarjetas

**Ver código completo:** [UI_EXAMPLES_PERMISSIONS.md](UI_EXAMPLES_PERMISSIONS.md)

---

## 🔑 API Reference

### Request

```http
POST /api/v1/admin-actions/update-permissions/{userId}
Content-Type: application/json
Authorization: Bearer {token}
X-User-Role: admin
X-User-Permission: sync.permissions
```

```json
{
  "permissionsToAdd": ["users.create", "reports.view"],
  "permissionsToRemove": ["users.delete", "settings.update"]
}
```

### Response 200 (Success)

```json
{
  "success": true,
  "message": "Permisos actualizados correctamente.",
  "data": {
    "updated": [{
      "userId": 4,
      "fullName": "Juan Perez",
      "permissions": {
        "added": ["view.students"],
        "removed": ["create.student"]
      }
    }]
  }
}
```

### Response 401 (Unauthorized)

```json
{
  "success": false,
  "message": "No autorizado: el usuario autenticado no tiene permiso para ejecutar esta acción",
  "error_code": "UNAUTHORIZED"
}
```

### Response 422 (Validation Error)

```json
{
  "success": false,
  "message": "Error descriptivo para el usuario",
  "error_code": "VALIDATION_ERROR",
  "errors": {
    "permissionsToAdd": ["El campo debe ser un array"],
    "userId": ["Usuario no encontrado"]
  }
}
```

---

## 🧪 Testing

### Método 1: PowerShell Script

```powershell
# 1. Configurar token
$env:ACCESS_TOKEN = "tu_token_de_autenticacion"

# 2. Ejecutar
.\test-update-user-permissions.ps1
```

**Output esperado:**
```
================================================
  ✅ RESPUESTA EXITOSA (200)
================================================

📥 Respuesta:
{
  "success": true,
  "message": "Permisos actualizados correctamente.",
  ...
}

📊 Detalles de la actualización:
  Usuario ID: 4
  Nombre: Juan Perez
  ✅ Permisos agregados: 2
     - users.create
     - reports.view
  ❌ Permisos eliminados: 1
     - users.delete
```

### Método 2: Consola del Navegador

```javascript
// 1. Abrir consola (F12)
// 2. Obtener componente Alpine.js
const app = Alpine.$data(document.querySelector('[x-data="rolesData"]'));

// 3. Probar función
const resultado = await app.updateUserPermissions(
  4,                          // userId
  ['reports.view'],           // agregar
  ['users.delete']            // eliminar
);

console.log(resultado);
// { success: true, data: {...} }
```

### Método 3: Interfaz de Usuario

1. Ir a página de roles
2. Click en botón "🔑 Permisos" (después de implementar UI)
3. Seleccionar permisos
4. Guardar
5. Verificar notificación verde

---

## 🔒 Seguridad

### Autenticación Requerida

```javascript
{
  "Authorization": "Bearer {access_token}",  // Token JWT del usuario
  "X-User-Role": "admin|supervisor",         // Rol del usuario
  "X-User-Permission": "sync.permissions"    // Permiso específico
}
```

### Validaciones

✅ Token válido en localStorage  
✅ Usuario con rol `admin` o `supervisor`  
✅ Usuario con permiso `sync.permissions`  
✅ userId debe existir en la base de datos  
✅ Permisos deben ser válidos  
✅ Al menos un array debe tener elementos  

---

## 📊 Comparación: Individual vs Masivo

| Característica | Individual | Masivo (Bulk) |
|----------------|-----------|---------------|
| **Endpoint** | `/update-permissions/{userId}` | `/update-permissions` |
| **ID en URL** | ✅ Sí | ❌ No |
| **Parámetro** | `userId` | `curps[]` o `role` |
| **Usuarios** | 1 | Múltiples |
| **Velocidad** | ⚡ Rápido | 🐢 Más lento |
| **Ideal para** | Ediciones rápidas | Cambios masivos |
| **UI Recomendada** | Modal simple | Modal con selección |

**¿Cuándo usar cada uno?**

- **Individual**: Cambiar permisos de un usuario específico
- **Masivo**: Cambiar permisos de muchos usuarios a la vez

---

## 💡 Ejemplos Prácticos

### Ejemplo 1: Dar acceso a reportes

```javascript
await this.updateUserPermissions(4, ['reports.view'], []);
```

### Ejemplo 2: Revocar permisos de eliminación

```javascript
await this.updateUserPermissions(4, [], ['users.delete', 'students.delete']);
```

### Ejemplo 3: Cambiar permisos completos

```javascript
await this.updateUserPermissions(
  4,
  ['reports.view', 'reports.create'],     // agregar
  ['users.delete', 'settings.update']     // eliminar
);
```

### Ejemplo 4: Con validación del resultado

```javascript
const resultado = await this.updateUserPermissions(4, ['reports.view'], []);

if (resultado.success) {
  console.log('✅ Actualizado correctamente');
  console.log('Datos:', resultado.data);
} else {
  console.error('❌ Error:', resultado.message);
  if (resultado.errors) {
    console.error('Detalles:', resultado.errors);
  }
}
```

---

## 🐛 Troubleshooting

### ❌ Error: "No hay token de autenticación"

**Causa:** No hay token en localStorage

**Solución:**
```javascript
// Verificar token
console.log(localStorage.getItem('access_token'));

// Si no existe, hacer login primero
window.location.href = '/login';
```

---

### ❌ Error 401: "No autorizado"

**Causa:** Usuario sin permisos adecuados

**Solución:**
1. Verificar que el usuario tenga rol `admin` o `supervisor`
2. Verificar que tenga el permiso `sync.permissions`

```javascript
// Ver permisos del usuario actual
const userData = JSON.parse(localStorage.getItem('user_data'));
console.log('Rol:', userData.role);
console.log('Permisos:', userData.permissions);
```

---

### ❌ Error 422: "Error de validación"

**Causa:** Datos inválidos enviados

**Solución:**
```javascript
// Verificar que los arrays sean válidos
console.log('Agregar:', typeof permissionsToAdd, Array.isArray(permissionsToAdd));
console.log('Eliminar:', typeof permissionsToRemove, Array.isArray(permissionsToRemove));

// Asegurarse de que sean strings
permissionsToAdd = permissionsToAdd.map(p => String(p));
```

---

### ❌ Error: "Usuario no encontrado"

**Causa:** userId no existe en la base de datos

**Solución:**
```javascript
// Verificar que el usuario exista primero
const usuarios = await loadUsers();
const existe = usuarios.find(u => u.id === userId);
if (!existe) {
  console.error('❌ Usuario no encontrado:', userId);
}
```

---

## 📝 Logs y Debugging

La función incluye logs detallados:

```javascript
// Al enviar request
📤 updateUserPermissions - Enviando para userId 4: {
  permissionsToAdd: ["reports.view"],
  permissionsToRemove: ["users.delete"]
}

// Al recibir respuesta
📥 updateUserPermissions - Respuesta: {
  success: true,
  message: "Permisos actualizados correctamente.",
  data: {...}
}

// En caso de error
❌ Error en respuesta: {
  success: false,
  message: "Error al actualizar permisos",
  errors: {...}
}
```

**Ver logs:**
1. Abrir DevTools (F12)
2. Ir a pestaña "Console"
3. Ejecutar la función
4. Ver logs detallados

---

## ✅ Checklist de Implementación

### Backend (Completado ✅)
- [x] Función `updateUserPermissions()` implementada
- [x] Validaciones de autenticación
- [x] Validaciones de parámetros
- [x] Manejo de errores
- [x] Logs detallados
- [x] Notificaciones al usuario
- [x] Recarga automática de datos

### Documentación (Completado ✅)
- [x] API Reference completa
- [x] Ejemplos de código
- [x] Casos de uso
- [x] Guías paso a paso
- [x] Script de testing

### Frontend (Opcional ⏳)
- [ ] Agregar propiedades a Alpine.js data()
- [ ] Copiar funciones auxiliares
- [ ] Agregar botón en tabla
- [ ] Agregar modal de edición
- [ ] Probar en navegador

**Ver:** [QUICK_START_PERMISSIONS.md](QUICK_START_PERMISSIONS.md) para completar el frontend

---

## 🎯 Resultado Final

### ✅ Lo que tienes ahora:

1. **Función JavaScript funcional** en [roles.astro](src/pages/roles.astro)
2. **Script de testing** en PowerShell
3. **Documentación completa** (4 archivos)
4. **5 opciones de UI** con código listo para usar
5. **Validaciones y manejo de errores** robusto
6. **Logs detallados** para debugging
7. **Ejemplos prácticos** de uso

### 🎨 Lo que puedes agregar (10 minutos):

1. UI con botones y modal (ver QUICK_START)
2. Testing visual desde la interfaz

---

## 🤝 Contribución

Para agregar más funcionalidades:

1. Agregar permisos en el backend
2. Actualizar la lista de permisos disponibles
3. Probar con el script PowerShell
4. Actualizar documentación

---

## 📞 Soporte

**Documentación:**
- Guía API: [PERMISSIONS_API_USAGE.md](PERMISSIONS_API_USAGE.md)
- UI Examples: [UI_EXAMPLES_PERMISSIONS.md](UI_EXAMPLES_PERMISSIONS.md)
- Quick Start: [QUICK_START_PERMISSIONS.md](QUICK_START_PERMISSIONS.md)

**Testing:**
- Script: [test-update-user-permissions.ps1](../test-update-user-permissions.ps1)

**Código:**
- Implementación: [roles.astro](src/pages/roles.astro) línea 3491

---

## 🏆 Características Destacadas

- ✅ **Fácil de usar**: API simple y clara
- ✅ **Robusto**: Validaciones completas
- ✅ **Informativo**: Logs y notificaciones
- ✅ **Flexible**: 5 opciones de UI
- ✅ **Documentado**: Guías completas
- ✅ **Testeable**: Scripts incluidos
- ✅ **Seguro**: Autenticación y autorización
- ✅ **Profesional**: Código limpio y organizado

---

## 📅 Información

- **Fecha:** 29 de enero de 2026
- **Versión API:** v1
- **Estado:** ✅ Completo y funcional
- **Autor:** GitHub Copilot
- **Tecnologías:** Alpine.js, Astro, REST API

---

## 🎉 ¡Todo Listo!

La implementación del endpoint de actualización de permisos individuales está **completa y lista para usar**.

**Siguiente paso:** Agregar la UI usando [QUICK_START_PERMISSIONS.md](QUICK_START_PERMISSIONS.md) (opcional, 10 minutos)

---

**¿Preguntas?** Revisa la documentación completa en los archivos markdown listados arriba. 📚

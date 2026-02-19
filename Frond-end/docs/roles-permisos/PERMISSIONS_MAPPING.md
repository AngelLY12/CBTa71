# 🔐 Mapeo Correcto de Permisos de la API

Este documento lista todos los permisos disponibles en la API con sus traducciones exactas al español.

## ✅ Permisos Mapeados en roles.astro

El archivo `src/pages/roles.astro` contiene la traducción completa de permisos en la variable `permissionsTranslation` (línea ~3641).

---

## 📋 Permisos de ADMIN

| Nombre en API | Traducción al Español |
|---|---|
| `promote.student` | Promover Estudiante |
| `attach.student` | Asociar Estudiante |
| `view.student` | Ver Detalles del Estudiante |
| `update.student` | Actualizar Estudiante |
| `sync.permissions` | Sincronizar Permisos |
| `view.users` | Ver Usuarios |
| `sync.roles` | Sincronizar Roles |
| `delete.users` | Eliminar Usuarios |
| `disable.users` | Deshabilitar Usuarios |
| `view.permissions` | Ver Permisos |
| `view.roles` | Ver Roles |

---

## 💰 Permisos de FINANCIAL STAFF

| Nombre en API | Traducción al Español |
|---|---|
| `view all financial overview` | Ver Resumen Financiero General |
| `view all pending concepts summary` | Ver Resumen de Conceptos Pendientes |
| `view all students summary` | Ver Resumen General de Estudiantes |
| `view all paid concepts summary` | Ver Resumen de Conceptos Pagados |
| `view concepts history` | Ver Historial de Conceptos |
| `view concepts` | Ver Conceptos de Cobro |
| `create concepts` | Crear Conceptos de Cobro |
| `update concepts` | Actualizar Conceptos de Cobro |
| `finalize concepts` | Finalizar Conceptos de Cobro |
| `disable concepts` | Deshabilitar Conceptos de Cobro |
| `eliminate concepts` | Eliminar Conceptos de Cobro |
| `eliminate.logical.concepts` | Eliminar Conceptos (Borrado Lógico) |
| `view debts` | Ver Deudas de Estudiantes |
| `validate debt` | Validar Deudas |
| `view payments` | Ver Pagos Realizados |
| `view students` | Ver Lista de Estudiantes |

---

## 🎓 Permisos de ESTUDIANTE (Student)

| Nombre en API | Traducción al Español |
|---|---|
| `view own financial overview` | Ver Mi Resumen Financiero |
| `view own pending concepts summary` | Ver Mis Conceptos Pendientes |
| `view own paid concepts summary` | Ver Mis Conceptos Pagados |
| `view own overdue concepts summary` | Ver Mis Conceptos Vencidos |
| `view payments history` | Ver Historial de Pagos |
| `view cards` | Ver Tarjetas Guardadas |
| `create setup` | Crear Nuevo Método de Pago |
| `create and view card` | Crear y Ver Tarjetas |
| `delete card` | Eliminar Tarjeta |
| `view payment history` | Ver Historial de Pagos |
| `view pending concepts` | Ver Conceptos Pendientes |
| `create payment` | Realizar Pago |
| `view overdue concepts` | Ver Conceptos Vencidos |

---

## 🔗 Fuentes de Datos

- **Backend:** `database/seeders/DatabaseSeeder.php`
- **Frontend:** `Frond-end/src/pages/roles.astro` (línea ~3641)

---

## 📝 Cómo Usar los Permisos en Frontend

### En Alpine.js (roles.astro):

```javascript
// Los permisos se cargan automáticamente desde fallbackPermissions
// basado en permissionsTranslation

// Agregar un permiso a un usuario:
const resultado = await this.updateUserPermissions(userId, 
  ['promote.student'],  // Permisos a agregar (nombres exactos de la API)
  []                     // Permisos a eliminar
);

// Remover un permiso:
const resultado = await this.updateUserPermissions(userId,
  [],
  ['view.users']
);
```

### En Requests HTTP:

```javascript
// POST /api/v1/admin-actions/update-permissions/{userId}
fetch(`${API_BASE}/v1/admin-actions/update-permissions/${userId}`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'X-User-Permission': 'sync.permissions'
  },
  body: JSON.stringify({
    permissionsToAdd: ['view.users', 'view.roles'],    // Nombres exactos
    permissionsToRemove: ['delete.users']
  })
});
```

---

## ✨ Cambios Realizados

**Fecha:** 2026-02-04

**Cambio:** Actualizado el objeto `permissionsTranslation` en `roles.astro` para mapear correctamente todos los permisos de la API al español.

**Antes:**
- Permisos incompletos
- Algunas traducciones incorrectas
- Faltaban permisos de estudiante

**Después:**
- ✅ 39 permisos mapeados correctamente
- ✅ Categorías claras (Admin, Financial Staff, Student)
- ✅ Traducciones descriptivas y precisas
- ✅ Build verified (0 errors)

---

## 🧪 Testing

El modal de permisos en `roles.astro` ahora mostrará:
- Lista completa de 39 permisos disponibles
- Traducciones exactas al español
- Separación clara entre permisos a agregar y eliminar
- Fallback automático a lista hardcoded si la API falla

Para probar:
1. Ve a `/roles`
2. Selecciona un usuario
3. Haz clic en "Gestionar Permisos"
4. Verifica que todos los permisos se muestren correctamente

# Sistema de Gestión de Usuarios - CBTA 71

## 📋 Descripción General

Sistema completo de CRUD (Crear, Leer, Actualizar, Eliminar) para la gestión de usuarios y roles del sistema CBTA 71. Implementa todas las APIs de administración con autenticación, paginación, búsqueda, filtros y operaciones masivas.

## 🚀 Características Implementadas

### ✅ Operaciones CRUD Completas
- **Crear Usuario**: Registro de nuevos usuarios con todos los datos requeridos
- **Leer Usuarios**: Listado con paginación, filtros y búsqueda
- **Actualizar Usuario**: Edición de datos personales y académicos
- **Eliminar Usuario**: Eliminación individual o masiva

### 🎓 Gestión de Estudiantes
- Asociar detalles académicos a usuarios existentes
- Obtener información estudiantil completa
- Actualizar datos académicos (carrera, semestre, grupo, taller)
- Promoción automática de estudiantes (incremento de semestre)

### 📥 Importación de Datos
- **Importar Usuarios**: Carga masiva desde archivos Excel (.xlsx)
- **Importar Estudiantes**: Asignación masiva de datos académicos

### 👥 Operaciones Masivas
- Selección múltiple de usuarios
- Baja temporal masiva
- Baja definitiva masiva
- Eliminación masiva

### 🔍 Búsqueda y Filtros
- Búsqueda en tiempo real por nombre, email o ID
- Filtrado por estado (activo, baja temporal, baja, eliminado)
- Paginación del lado del servidor

## 📁 Estructura de Archivos

```
Frond-end/src/
├── config/
│   └── api.js                      # Configuración de endpoints API
├── utils/
│   ├── authService.js              # Servicio de autenticación
│   └── adminService.js             # ⭐ Servicio administrativo (NUEVO)
├── components/
│   ├── UserManagementTable.jsx     # ⭐ Tabla de usuarios (NUEVO)
│   └── UserFormModal.jsx           # ⭐ Modal de formulario (NUEVO)
└── pages/
    ├── user-admin.astro            # ⭐ Página principal de gestión (NUEVO)
    ├── users-management.astro      # ⭐ Versión React alternativa (NUEVO)
    └── roles.astro                 # Página anterior (mantener como backup)
```

## 🔑 APIs Implementadas

### Gestión de Usuarios
```
POST   /api/v1/admin-actions/register           # Registrar usuario (admin)
GET    /api/v1/admin-actions/show-users         # Listar usuarios
GET    /api/v1/admin-actions/show-users/{id}    # Ver detalles de usuario
PATCH  /api/v1/admin-actions/update-user/{id}   # Actualizar usuario
DELETE /api/v1/admin-actions/delete-user/{id}   # Eliminar usuario
DELETE /api/v1/admin-actions/delete-users        # Eliminar múltiples
POST   /api/v1/admin-actions/disable-users       # Dar de baja
POST   /api/v1/admin-actions/temporary-disable-users # Baja temporal
```

### Gestión de Estudiantes
```
POST   /api/v1/admin-actions/attach-student      # Asociar datos académicos
GET    /api/v1/admin-actions/get-student/{id}    # Obtener detalles
PATCH  /api/v1/admin-actions/update-student/{id} # Actualizar detalles
POST   /api/v1/admin-actions/promotion           # Promover estudiantes
```

### Importación
```
POST   /api/v1/admin-actions/import              # Importar usuarios (Excel)
POST   /api/v1/admin-actions/import-students     # Importar estudiantes (Excel)
```

### Permisos y Roles
```
POST   /api/v1/admin-actions/update-permissions  # Actualizar permisos
GET    /api/v1/admin-actions/find-permissions    # Listar permisos
GET    /api/v1/admin-actions/find-roles          # Listar roles
GET    /api/v1/admin-actions/roles/{id}          # Obtener rol por ID
```

## 🛠️ Uso del Sistema

### Acceso a la Página
```
URL: /user-admin
```

### Requisitos de Autenticación
- Token de acceso válido en `localStorage.getItem('access_token')`
- Rol: `admin` o `supervisor`
- Permisos necesarios según la operación

### Crear Nuevo Usuario

1. Click en botón "➕ Nuevo Usuario"
2. Llenar formulario con datos requeridos:
   - **Obligatorios**: Nombre, Apellidos, Email, CURP, Fecha de Nacimiento
   - **Opcionales**: Teléfono, Género, Tipo de Sangre, Dirección
3. Seleccionar si es estudiante (checkbox)
4. Si es estudiante, llenar: ID Carrera, Número de Control, Semestre, Grupo, Taller
5. Click en "💾 Crear Usuario"

### Editar Usuario

1. Click en "✏️ Editar" en la fila del usuario
2. Modificar los datos necesarios
3. Click en "💾 Actualizar Usuario"

### Eliminar Usuario

1. Click en "🗑️ Eliminar" en la fila del usuario
2. Confirmar la acción en el diálogo

### Operaciones Masivas

1. Seleccionar usuarios con checkbox
2. Elegir acción en la barra superior:
   - **Baja Temporal**: Suspende temporalmente
   - **Dar de Baja**: Baja definitiva
   - **Eliminar**: Elimina del sistema

### Importar Usuarios desde Excel

1. Click en "📥 Importar Usuarios"
2. Seleccionar archivo Excel (.xlsx)
3. El archivo debe tener las siguientes columnas:

```
name, last_name, email, password, phone_number, birthdate, gender, curp,
street, city, state, zip_code, stripe_customer_id, blood_type,
registration_date, status, career_id, n_control, semestre, group, workshop
```

### Importar Estudiantes desde Excel

1. Click en "📚 Importar Estudiantes"
2. Seleccionar archivo Excel (.xlsx)
3. El archivo debe tener las siguientes columnas:

```
curp, career_id, n_control, semestre, group, workshop
```

**Nota**: El CURP debe existir en la base de datos.

### Promover Estudiantes

1. Click en "🎓 Promover Estudiantes"
2. Confirmar la acción
3. El sistema:
   - Incrementa el semestre de todos los estudiantes
   - Da de baja a quienes sobrepasan semestre 12

## 📊 Estructura de Datos

### Usuario
```typescript
{
  name: string,              // Nombre(s)
  last_name: string,         // Apellidos
  email: string,             // Correo electrónico
  phone_number: string,      // Teléfono (10 dígitos)
  birthdate: string,         // Fecha de nacimiento (YYYY-MM-DD)
  gender: string,            // hombre | mujer | otro
  curp: string,              // CURP (18 caracteres)
  address: string[],         // [calle, colonia, ciudad]
  blood_type: string,        // O+, O-, A+, A-, B+, B-, AB+, AB-
  registration_date: string, // Fecha de registro
  status: string            // activo | baja-temporal | baja | eliminado
}
```

### Detalles de Estudiante
```typescript
{
  user_id: number,           // ID del usuario
  career_id: number,         // ID de la carrera
  n_control: string,         // Número de control
  semestre: number,          // Semestre actual (1-12)
  group: string,             // Grupo (opcional)
  workshop: string           // Taller (opcional)
}
```

## 🎨 Componentes React

### UserManagementTable
Componente de tabla con todas las funcionalidades:
- Paginación del lado del servidor
- Búsqueda en tiempo real
- Filtrado por estado
- Selección múltiple
- Acciones individuales y masivas

**Props:**
- `onEdit`: Callback para editar usuario
- `onDelete`: Callback para eliminar usuario
- `onRefresh`: Trigger para actualizar datos

### UserFormModal
Modal de formulario para crear/editar usuarios:
- Validación de campos
- Modo estudiante (toggle)
- Manejo de errores
- Loading states

**Props:**
- `isOpen`: Boolean para mostrar/ocultar
- `onClose`: Callback al cerrar
- `user`: Usuario a editar (null para crear nuevo)
- `onSuccess`: Callback al guardar exitosamente

## 🔧 Servicio AdminService

Servicio centralizado para todas las operaciones administrativas.

### Métodos Principales

```javascript
// Usuarios
AdminService.getUsers({ page, perPage, status, forceRefresh })
AdminService.getUserById(userId, forceRefresh)
AdminService.registerUser(userData)
AdminService.updateUser(userId, userData)
AdminService.deleteUser(userId)
AdminService.deleteUsers(userIds)
AdminService.disableUsers(userIds)
AdminService.temporaryDisableUsers(userIds)

// Estudiantes
AdminService.attachStudent(studentData)
AdminService.getStudent(studentId)
AdminService.updateStudent(studentId, studentData)
AdminService.promoteStudents()

// Permisos
AdminService.updatePermissions(permissionsData)
AdminService.getPermissions()
AdminService.getRoles()
AdminService.getRoleById(roleId)

// Importación
AdminService.importUsers(file)
AdminService.importStudents(file)
```

## 🎯 Página user-admin.astro (Alpine.js)

Implementación completa usando Alpine.js para máxima compatibilidad y simplicidad.

### Características
- ✅ Sin dependencias de React/JSX
- ✅ Funciona directamente en el navegador
- ✅ Alpine.js para reactividad
- ✅ Integración completa con todas las APIs
- ✅ Modales nativos
- ✅ Notificaciones toast

### Uso
```html
<div x-data="usersApp()">
  <!-- Todo el contenido reactivo -->
</div>
```

## 🔒 Seguridad

### Headers Requeridos
```javascript
{
  'Authorization': 'Bearer ${token}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-User-Role': 'admin',              // Rol del usuario
  'X-User-Permission': 'create.user'   // Permiso específico
}
```

### Permisos por Operación
- `create.user`: Crear usuarios
- `view.users`: Ver usuarios
- `update.user`: Actualizar usuarios
- `delete.user`: Eliminar usuarios
- `attach.student`: Asociar estudiantes
- `view.student`: Ver estudiantes
- `update.student`: Actualizar estudiantes
- `promote.student`: Promover estudiantes
- `import.users`: Importar usuarios
- `sync.permissions`: Actualizar permisos

## 📱 Responsive Design

El sistema está completamente adaptado para:
- 📱 Móviles (< 768px)
- 💻 Tablets (768px - 1024px)
- 🖥️ Desktop (> 1024px)

## 🎨 Colores Institucionales

```css
.bg-institucional { background-color: #2E594D; }
.text-institucional { color: #2E594D; }
.border-institucional { border-color: #2E594D; }
.hover-institucional:hover { background-color: #234238; }
```

## ⚠️ Notas Importantes

1. **Contraseñas**: Se generan automáticamente por el sistema y se envían al email del usuario
2. **CURP**: Debe ser exactamente de 18 caracteres
3. **Email**: Debe ser válido y único en el sistema
4. **Importación**: Los archivos Excel deben tener exactamente las columnas especificadas
5. **Promoción**: Afecta a TODOS los estudiantes sin importar su estado

## 🐛 Solución de Problemas

### Error de Autenticación
- Verificar que el token esté en localStorage
- Verificar que el token no haya expirado
- Verificar permisos del usuario

### Error al Cargar Usuarios
- Verificar conexión a la API
- Verificar URL base de la API
- Revisar consola del navegador (F12)

### Error al Importar
- Verificar formato del archivo Excel (.xlsx)
- Verificar que las columnas coincidan exactamente
- Revisar que los datos sean válidos

## 📞 Soporte

Para problemas o dudas:
1. Revisar logs en la consola del navegador (F12)
2. Verificar respuestas de la API en Network tab
3. Revisar API: `https://nginx-production-728f.up.railway.app/api/documentation`

---

**Sistema desarrollado para CBTA 71 - Gestión Integral de Usuarios y Estudiantes** 🎓

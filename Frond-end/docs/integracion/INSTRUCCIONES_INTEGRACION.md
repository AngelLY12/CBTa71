# Guía de Integración - API Completa de Administración

## 📋 Resumen de Cambios

Se han implementado los siguientes endpoints adicionales de la API:

### Nuevas Funcionalidades
1. ✅ **Activar usuarios** - POST /v1/admin-actions/activate-users
2. 🎓 **Asociar estudiante** - POST /v1/admin-actions/attach-student  
3. 📝 **Actualizar estudiante** - PATCH /v1/admin-actions/update-student/{id}
4. 📊 **Ver estudiante** - GET /v1/admin-actions/get-student/{id}
5. 📥 **Importar usuarios completos** - POST /v1/admin-actions/import
6. 🔐 **Gestión masiva de roles** - POST /v1/admin-actions/updated-roles
7. 🔑 **Gestión masiva de permisos** - POST /v1/admin-actions/update-permissions
8. 📚 **Obtener roles** - GET /v1/admin-actions/find-roles
9. 🔍 **Buscar permisos** - POST /v1/admin-actions/find-permissions

## 📁 Archivos Generados

1. **NUEVAS_FUNCIONES.js** - Código JavaScript para agregar al componente Alpine
2. **NUEVOS_MODALES.html** - HTML de los 4 nuevos modales
3. **API_IMPLEMENTATION_PLAN.md** - Plan detallado de implementación

## 🔧 Pasos de Integración

### Paso 1: Actualizar Botones de Acción
✅ **YA HECHO** - Los botones se actualizaron con:
- 🎓 Asociar Estudiante
- 🔐 Gestionar Roles  
- 🔑 Permisos
- 📥 Importar

### Paso 2: Actualizar Acciones Masivas
✅ **YA HECHO** - Se agregó el botón "✅ Activar" en la barra de acciones masivas.

### Paso 3: Agregar Nuevas Propiedades al Estado Alpine

Abre `roles.astro` y busca la sección donde se inicializa el estado de Alpine (línea ~607):

```javascript
Alpine.data('rolesData', () => ({
    users: [],
    apiBaseUrl: window.__API_BASE_URL__ || '',
    // ... propiedades existentes ...
    
    // AGREGAR ESTAS NUEVAS PROPIEDADES:
    isStudentPanelOpen: false,
    isRolesPanelOpen: false,
    isPermissionsPanelOpen: false,
    isImportPanelOpen: false,
    studentForm: {
        user_id: null,
        career_id: null,
        n_control: '',
        semestre: 1,
        group: '',
        workshop: ''
    },
    rolesForm: {
        curps: [],
        rolesToAdd: [],
        rolesToRemove: []
    },
    permissionsForm: {
        curps: [],
        role: '',
        permissionsToAdd: [],
        permissionsToRemove: []
    },
    availableRoles: [],
    availablePermissions: [],
    importType: 'users',
```

### Paso 4: Agregar Nuevas Funciones

Copia todo el contenido de **NUEVAS_FUNCIONES.js** y pégalo ANTES de la función `showDebugInfo()` 
(aproximadamente línea ~1540 del archivo roles.astro).

### Paso 5: Agregar Nuevos Modales

Busca en `roles.astro` la línea que dice:
```html
</template>

<!-- Modal de Importación de Estudiantes -->
<template x-teleport="body">
```

**JUSTO ANTES** de esa sección, pega todo el contenido de **NUEVOS_MODALES.html**.

### Paso 6: Actualizar el Getter de selectedCurps

Busca la función `get filteredUsers()` y DESPUÉS de ella agrega:

```javascript
get selectedCurps() {
    return this.users
        .filter(u => this.selectedUsers.includes(u.id))
        .map(u => u.curp)
        .filter(Boolean);
},
```

## 🎨 Características de Diseño Responsive

Todos los nuevos modales incluyen:
- ✅ Diseño responsive (móvil y desktop)
- ✅ Backdrop con blur
- ✅ Animaciones de transición
- ✅ Validación de formularios
- ✅ Estados de carga (isSaving)
- ✅ Mensajes de error/éxito
- ✅ Grid adaptativo para campos

## 🧪 Pruebas Recomendadas

1. **Asociar Estudiante**
   - Selecciona un usuario sin detalles académicos
   - Click en "🎓 Asociar Estudiante"
   - Llena el formulario y guarda

2. **Gestionar Roles**
   - Selecciona varios usuarios (checkboxes)
   - Click en "🔐 Gestionar Roles"
   - Agrega/remueve roles y aplica

3. **Permisos**
   - Selecciona usuarios O especifica un rol
   - Click en "🔑 Permisos"
   - Configura permisos y aplica

4. **Importar**
   - Click en "📥 Importar"
   - Selecciona tipo (Usuarios o Estudiantes)
   - Arrastra un archivo Excel
   - Procesa la importación

5. **Activar Usuarios**
   - Selecciona usuarios con status != activo
   - Click en "✅ Activar" en la barra de acciones masivas
   - Confirma la acción

## 📊 Formato de Archivos Excel

### Importar Usuarios Completos (21 columnas)
```
name | last_name | email | password | phone_number | birthdate | gender | curp |
street | city | state | zip_code | stripe_customer_id | blood_type |
registration_date | status | career_id | n_control | semestre | group | workshop
```

### Importar Detalles de Estudiantes (6 columnas)
```
curp | career_id | n_control | semestre | group | workshop
```
⚠️ Solo se procesarán CURPs que existan en la base de datos.

## 🔍 Validación de Implementación

Verifica que:
- [ ] Los 5 nuevos botones aparecen en la barra superior
- [ ] El botón "✅ Activar" aparece en acciones masivas
- [ ] Cada modal se abre correctamente al hacer click
- [ ] Los formularios tienen todos los campos necesarios
- [ ] Las peticiones a la API incluyen headers correctos
- [ ] Los mensajes de éxito/error se muestran
- [ ] La lista de usuarios se recarga después de cada acción

## 🐛 Troubleshooting

### Error: "selectedCurps is undefined"
**Solución**: Verifica que agregaste el getter `get selectedCurps()` en el código Alpine.

### Error: "isStudentPanelOpen is not defined"
**Solución**: Asegúrate de haber agregado todas las nuevas propiedades al estado inicial de Alpine.

### Los modales no se abren
**Solución**: Verifica que las funciones `openStudentPanel()`, `openRolesPanel()`, etc. están definidas en Alpine.

### Error 401/403 en peticiones
**Solución**: Verifica que los headers `X-User-Role` y `X-User-Permission` sean correctos según la documentación de la API.

## 📞 Soporte

Si encuentras problemas:
1. Abre la consola del navegador (F12)
2. Busca errores en rojo
3. Verifica los logs de peticiones en la pestaña Network
4. Revisa que el token de autenticación esté presente

---

**Última actualización**: Enero 2026
**Compatibilidad**: Alpine.js 3.x, Astro 4.x
**API Base**: https://nginx-production-728f.up.railway.app/api

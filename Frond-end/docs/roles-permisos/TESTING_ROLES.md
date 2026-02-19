# Testing - Página de Roles

## 🔧 Cambios Realizados

### 1. **Autenticación Mejorada**
- La página ahora verifica que exista un token de autenticación antes de cargar
- Si no hay token, redirige automáticamente al login después de 2 segundos
- Logs detallados en consola para debugging

### 2. **Carga de Usuarios desde API**
- Los usuarios se cargan del lado del cliente (no en SSR)
- Incluye el token de autenticación en todas las peticiones
- Endpoint: `GET /v1/admin-actions/showUsers`
- Headers: `Authorization: Bearer {token}`

### 3. **Botón de Debug**
- Nuevo botón "Debug" en la esquina superior derecha
- Muestra información sobre el estado de autenticación
- Verifica si el token está presente en localStorage

### 4. **Enlace desde Dashboard**
- Nueva tarjeta "Gestión de Personal" en el Dashboard
- Link directo a `/roles`

## 📋 Pasos para Probar

### Paso 1: Iniciar Sesión
1. Ve a `http://localhost:4321/` (o la URL de tu servidor)
2. Usa las credenciales de prueba:
   - **Email:** admin@uni.edu
   - **Password:** password123
3. Si el login es exitoso, serás redirigido al Dashboard

### Paso 2: Navegar a Roles
1. Desde el Dashboard, haz clic en la tarjeta "Gestión de Personal"
2. O navega directamente a `http://localhost:4321/roles`

### Paso 3: Verificar la Carga
Deberías ver:
- ⏳ Un mensaje "Cargando usuarios..." mientras se conecta a la API
- ✅ Los usuarios cargados en una tabla (si la API responde correctamente)
- ❌ Un mensaje de error si hay problemas (con detalles en la consola)

### Paso 4: Usar el Botón Debug (Si hay problemas)
1. Haz clic en el botón "Debug" (icono de información)
2. Aparecerá un alert con información básica
3. Abre la consola del navegador (F12) para ver logs detallados

## 🐛 Troubleshooting

### Error: "Debe iniciar sesión para ver los usuarios"
**Causa:** No hay token en localStorage

**Soluciones:**
1. Ve al login (`/`) y vuelve a iniciar sesión
2. Verifica en la consola del navegador (F12):
   ```javascript
   localStorage.getItem('access_token')
   ```
3. Si no hay token, el login no se completó correctamente

### Error: "Error 401" o "Sesión expirada"
**Causa:** El token ha expirado o es inválido

**Soluciones:**
1. Cierra sesión y vuelve a iniciar sesión
2. Limpia localStorage:
   ```javascript
   localStorage.clear()
   ```
3. Vuelve a iniciar sesión

### Error: "Error 404" o "Endpoint no encontrado"
**Causa:** El endpoint `/v1/admin-actions/showUsers` no existe en el backend

**Soluciones:**
1. Verifica en la API: https://nginx-production-728f.up.railway.app/api/documentation
2. El endpoint correcto podría ser diferente (por ejemplo: `/v1/users`, `/v1/admin/users`, etc.)
3. Consulta con Angel (el desarrollador del backend) cuál es el endpoint correcto

### Error: "Error 403" o "Forbidden"
**Causa:** El usuario no tiene permisos para acceder al endpoint

**Soluciones:**
1. Asegúrate de que el usuario tenga rol de "admin" o "financial-staff"
2. Verifica los permisos en el backend
3. Usa las credenciales de prueba con permisos admin

## 📊 Logs en Consola

La página genera logs detallados. Busca estos emojis:
- 🔍 Verificación de autenticación
- 🔑 Información del token
- 🔄 Inicio de carga de usuarios
- 📍 URL del endpoint
- 📡 Status de respuesta
- 📦 Datos recibidos
- ✅ Éxito
- ❌ Error
- ⚠️ Advertencia

## 🔐 Credenciales de Prueba

Si el backend tiene el seeder ejecutado:

```
Administrador:
- Email: admin@uni.edu
- Password: password123

Personal de Caja:
- Email: caja@cbta71.edu.mx
- Password: caja123

Estudiante:
- Email: juan.perez@alumno.cbta71.edu.mx
- Password: student123
```

**Nota:** Solo los usuarios con rol de administrador o staff pueden acceder a `/v1/admin-actions/showUsers`

## 🚀 Próximos Pasos

Si todo funciona correctamente:
1. Los usuarios se cargarán desde la API
2. Podrás agregar, editar y eliminar usuarios
3. Podrás importar usuarios desde Excel
4. Las estadísticas se actualizarán en tiempo real

Si hay errores:
1. Revisa los logs en la consola
2. Usa el botón "Debug" para verificar el estado
3. Consulta este documento para soluciones
4. Contacta al equipo de backend si el problema es con la API

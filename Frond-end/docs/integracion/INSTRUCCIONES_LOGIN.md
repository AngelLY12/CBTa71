# 🔐 INSTRUCCIONES: Cómo Iniciar Sesión y Acceder a Roles

## ❌ Problema Actual

Estás viendo el error: **"No hay token de autenticación en localStorage"**

**Causa:** No has iniciado sesión o tu sesión expiró.

## ✅ SOLUCIÓN - Sigue estos pasos:

### PASO 1: Inicia Sesión

1. **Ve a la página principal:**
   ```
   http://localhost:4321/
   ```
   O si estás en Vercel:
   ```
   https://cbta-eight.vercel.app/
   ```

2. **Ingresa las credenciales:**
   ```
   Email: admin@uni.edu
   Password: password123
   ```

3. **Abre la consola del navegador (F12)** mientras haces login

4. **Verifica que veas estos mensajes:**
   ```
   ✅ Token guardado en localStorage con key: access_token
   🔑 Token (primeros 20 chars): xxxxxxxxxxxxxxxxxxxx...
   ✅ User data guardado
   📥 Respuesta del servidor: {success: true, ...}
   ✅ Bienvenido. Redirigiendo...
   ```

5. **Si el login es exitoso:**
   - Serás redirigido al Dashboard automáticamente
   - El token se habrá guardado en localStorage

### PASO 2: Verifica que el Token se Guardó

En la consola del navegador (F12), ejecuta:
```javascript
localStorage.getItem('access_token')
```

**Deberías ver:** Un string largo (el token)
**Si ves:** `null` → El login falló, intenta de nuevo

### PASO 3: Accede a Roles

1. **Opción A:** Desde el Dashboard, haz clic en la tarjeta **"Gestión de Personal"**

2. **Opción B:** Navega directamente a:
   ```
   http://localhost:4321/roles
   ```

3. **Deberías ver:**
   - ⏳ "Cargando usuarios..." (brevemente)
   - 👥 La tabla de usuarios (si la API funciona)

## 🐛 Si el Login Falla

### Error: "Error 500"

**Significa:** El backend tiene un problema interno

**Solución:**
1. Verifica que el backend esté corriendo en Railway
2. Revisa los logs del backend
3. Contacta a Angel (backend developer)
4. Usa las credenciales del seeder: `admin@uni.edu / password123`

### Error: "CORS"

**Significa:** El backend no permite conexiones desde tu dominio

**Solución:**
1. Verifica que el backend tenga configurado CORS para tu dominio
2. En Railway, el backend debe permitir: `localhost:4321` y `cbta-eight.vercel.app`

### Error: "Network Error" o "Failed to fetch"

**Significa:** No se puede conectar al backend

**Solución:**
1. Verifica que la URL del backend esté correcta: `https://nginx-production-728f.up.railway.app/api`
2. Verifica que el backend esté en línea (API: https://nginx-production-728f.up.railway.app/api/documentation)
3. Verifica tu conexión a internet

## 🔍 Debugging

### Ver qué hay en localStorage:

```javascript
// En la consola del navegador (F12)
Object.keys(localStorage)  // Ver todas las keys
localStorage.getItem('access_token')  // Ver el token
localStorage.getItem('user_data')  // Ver datos del usuario
```

### Limpiar localStorage (si algo está mal):

```javascript
localStorage.clear()  // Borra todo
```

### Ver logs detallados:

Abre la consola del navegador (F12) antes de iniciar sesión. Deberías ver:
- 📤 REQUEST: Datos enviados al backend
- 📊 RESPONSE: Respuesta del backend
- ✅ o ❌: Éxito o error

## ✨ Flujo Correcto Completo

```
1. Usuario va a /
   ↓
2. Ingresa email y password
   ↓
3. Hace clic en "Iniciar Sesión"
   ↓
4. Backend valida credenciales
   ↓
5. Backend devuelve token
   ↓
6. Frontend guarda token en localStorage con key 'access_token'
   ↓
7. Usuario es redirigido al Dashboard
   ↓
8. Usuario hace clic en "Gestión de Personal"
   ↓
9. Roles.astro verifica que existe el token
   ↓
10. Roles.astro hace fetch a /v1/admin-actions/showUsers con el token
    ↓
11. Backend valida el token y devuelve los usuarios
    ↓
12. Los usuarios se muestran en la tabla
```

## 📞 ¿Necesitas Ayuda?

1. **Problema de login:** Contacta al equipo de backend (Angel)
2. **Problema de frontend:** Revisa la consola (F12) y comparte los logs
3. **Credenciales no funcionan:** Verifica que el seeder esté ejecutado en el backend

---

**Nota Importante:** El token se guarda con la key `'access_token'` (no `'authToken'` ni `'token'`). Esta es la key que busca la página de roles.

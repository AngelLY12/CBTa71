# 🔥 SOLUCIÓN RÁPIDA - Problema de Roles

## El Problema
Inicias sesión → Te metes a Roles → Aparece "Sin Autenticación"

## La Causa
El token NO se está guardando después del login

## La Solución (3 Pasos)

### PASO 1: Abre la Consola del Navegador
- Presiona **F12** en tu navegador
- Ve a la pestaña **Console**
- **DEJA LA CONSOLA ABIERTA** todo el tiempo

### PASO 2: Inicia Sesión (CON LA CONSOLA ABIERTA)
1. Ve a: https://cbta-eight.vercel.app/
2. Ingresa:
   - Email: `admin@uni.edu`
   - Password: `password123`
3. Haz clic en "Iniciar Sesión"

### PASO 3: Verifica los Logs en la Consola

**Deberías ver esto:**
```
✅ Token guardado en localStorage con key: access_token
🔑 Token (primeros 20 chars): xxxxxxxxxxxxxxxxxxxx...
✅ Token guardado verificado: SÍ
📥 Respuesta del servidor: {success: true, ...}
✅ Bienvenido. Redirigiendo...
🚀 Redirigiendo a: /Dashboard
```

**Si ves esto, el login funcionó ✅**

**Si NO ves "✅ Token guardado":**
- El backend NO está devolviendo el token
- Contacta a Angel (backend)
- El problema NO es del frontend

### PASO 4: Ve a Roles
1. Haz clic en "Gestión de Personal" desde el Dashboard
2. O ve directo a: https://cbta-eight.vercel.app/roles

**Deberías ver en la consola:**
```
========================================
🔍 ROLES.ASTRO - Verificando autenticación
========================================
📋 Todas las keys en localStorage: [..., 'access_token', ...]
🔑 Token access_token: ENCONTRADO (xxx chars)
✅ Token encontrado, procediendo a cargar usuarios...
```

**Si ves esto, FUNCIONÓ ✅**

---

## ❌ Si Sigue Fallando

### Error: "❌ NO HAY TOKEN DE AUTENTICACIÓN"
**Significado:** El login NO guardó el token

**Verifica:**
1. ¿Viste "✅ Token guardado" en el PASO 3?
   - **NO** → El backend no devuelve el token
   - **SÍ** → Verifica el PASO 4

2. En la consola, escribe:
   ```javascript
   localStorage.getItem('access_token')
   ```
   - Si ves `null` → El token NO se guardó
   - Si ves un texto largo → El token SÍ está guardado

### Error: Backend devuelve 500
**Significado:** El backend tiene un error interno

**Solución:**
- Contacta a Angel
- Revisa los logs en Railway
- Verifica que el seeder esté ejecutado

### Error: Backend devuelve 401 o 403
**Significado:** Token inválido o sin permisos

**Solución:**
- Usa las credenciales correctas
- Verifica que el usuario tenga rol de admin

---

## 📞 Checklist de Debugging

Marca con ✅ lo que ya verificaste:

- [ ] Abrí la consola (F12) ANTES de hacer login
- [ ] Vi el mensaje "✅ Token guardado" después del login
- [ ] Vi "✅ Token guardado verificado: SÍ"
- [ ] El backend respondió con `success: true`
- [ ] Fui redirigido al Dashboard después del login
- [ ] Al ir a Roles, vi "🔍 ROLES.ASTRO - Verificando autenticación"
- [ ] Vi "🔑 Token access_token: ENCONTRADO"

Si marcaste TODO ✅ pero sigue fallando:
- Toma captura de pantalla de la consola
- Compártela con el equipo

---

## 🎯 Resultado Esperado

**Después de seguir estos pasos:**
1. Inicias sesión → ✅ Token se guarda
2. Ves el Dashboard → ✅
3. Haces clic en "Gestión de Personal" → ✅
4. Se abre Roles y carga los usuarios → ✅

**Si algo falla, la consola te dirá EXACTAMENTE qué pasó**

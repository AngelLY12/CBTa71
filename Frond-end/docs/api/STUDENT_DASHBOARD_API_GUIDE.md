# Student Dashboard API - Guía de Implementación

## 📋 Resumen
Esta guía documenta todos los endpoints del dashboard de estudiantes implementados en `public/studentAPI.js` para su uso en páginas de estudiantes y padres.

## 🔑 Autenticación
Todos los endpoints requieren:
- **Authorization**: `Bearer {token}`
- **X-User-Role**: `student` o `parent`
- **X-User-Permission**: Permiso específico por endpoint (ver abajo)

El parámetro opcional `{studentId}` se usa cuando un **padre** consulta datos de un hijo específico.

---

## 📊 Endpoints Disponibles

### 1. 🗑️ Limpiar Caché del Dashboard
**POST** `/api/v1/dashboard/refresh/{studentId?}`

Limpia el caché de datos almacenados en el dashboard (estadísticas, pagos, etc.)

#### Uso:
```javascript
const response = await window.StudentAPI.refreshDashboardCache(
  studentId,  // number | null - ID del estudiante (opcional para padres)
  token,      // string - Token de autenticación
  role        // 'student' | 'parent'
);
```

#### Permisos Requeridos:
- **X-User-Permission**: `refresh.all.dashboard`

#### Respuesta Exitosa (200):
```json
{
  "success": true,
  "message": "Operación completada exitosamente",
  "data": {}
}
```

---

### 2. 📜 Obtener Historial de Pagos
**GET** `/api/v1/dashboard/history/{studentId?}`

Devuelve una lista paginada con el historial de pagos realizados por el usuario autenticado.

#### Uso:
```javascript
const response = await window.StudentAPI.getDashboardHistory(
  studentId,     // number | null - ID del estudiante (opcional para padres)
  token,         // string - Token de autenticación
  page,          // number - Número de página (default: 1)
  perPage,       // number - Registros por página (default: 15)
  forceRefresh,  // boolean - Forzar actualización de caché (default: false)
  role           // 'student' | 'parent'
);
```

#### Permisos Requeridos:
- **X-User-Permission**: `view.payments.summary`

#### Respuesta Exitosa (200):
```json
{
  "success": true,
  "message": "Operación completada exitosamente",
  "data": {
    "payment_history": {
      "items": [
        {
          "id": 123,
          "concept": "Pago de inscripción",
          "amount": "1500.00",
          "amount_received": "1500.00",
          "status": "paid",
          "date": "hace 2 dias"
        }
      ],
      "currentPage": 1,
      "lastPage": 5,
      "perPage": 15,
      "total": 72,
      "hasMorePages": true,
      "nextPage": 2,
      "previousPage": null
    }
  }
}
```

---

### 3. ⏰ Obtener Total de Pagos Vencidos
**GET** `/api/v1/dashboard/overdue/{studentId?}`

Devuelve el monto total de los pagos vencidos asociados al usuario autenticado.

#### Uso:
```javascript
const response = await window.StudentAPI.getOverdueTotal(
  studentId,     // number | null - ID del estudiante (opcional para padres)
  token,         // string - Token de autenticación
  forceRefresh,  // boolean - Forzar actualización de caché (default: false)
  role           // 'student' | 'parent'
);
```

#### Permisos Requeridos:
- **X-User-Permission**: `view.own.overdue.concepts.summary`

#### Respuesta Exitosa (200):
```json
{
  "success": true,
  "message": "Operación completada exitosamente",
  "data": {
    "total_overdue": {
      "totalAmount": "4500.00",
      "totalCount": 3
    }
  }
}
```

---

### 4. ✅ Obtener Total de Pagos Realizados
**GET** `/api/v1/dashboard/paid/{studentId?}`

Devuelve el monto total de pagos completados por el usuario autenticado.

#### Uso:
```javascript
const response = await window.StudentAPI.getPaidTotal(
  studentId,     // number | null - ID del estudiante (opcional para padres)
  token,         // string - Token de autenticación
  forceRefresh,  // boolean - Forzar actualización de caché (default: false)
  role           // 'student' | 'parent'
);
```

#### Permisos Requeridos:
- **X-User-Permission**: `view.own.paid.concepts.summary`

#### Respuesta Exitosa (200):
```json
{
  "success": true,
  "message": "Operación completada exitosamente",
  "data": {},
  "paid_data": {
    "totalPayments": "25000.00",
    "paymentsByMonth": {
      "2024-01": "15000.00",
      "2024-02": "12000.00",
      "2024-03": "18000.00"
    }
  }
}
```

---

### 5. ⏳ Obtener Total de Pagos Pendientes
**GET** `/api/v1/dashboard/pending/{studentId?}`

Devuelve la cantidad y monto total de los pagos pendientes del usuario autenticado.

#### Uso:
```javascript
const response = await window.StudentAPI.getPendingTotal(
  studentId,     // number | null - ID del estudiante (opcional para padres)
  token,         // string - Token de autenticación
  forceRefresh,  // boolean - Forzar actualización de caché (default: false)
  role           // 'student' | 'parent'
);
```

#### Permisos Requeridos:
- **X-User-Permission**: `view.own.pending.concepts.summary`

#### Respuesta Exitosa (200):
```json
{
  "success": true,
  "message": "Operación completada exitosamente",
  "data": {
    "total_pending": {
      "totalAmount": "4500.00",
      "totalCount": 3
    }
  }
}
```

---

## 🎯 Ejemplo de Uso en Portal de Estudiante

### Cargar todos los datos del dashboard al iniciar:

```javascript
async init() {
  const token = localStorage.getItem('access_token');
  const userId = localStorage.getItem('userId');
  
  if (!token || !userId || !window.StudentAPI) {
    this.error = 'Usuario no autenticado';
    return;
  }

  this.loading = true;
  
  try {
    const parsedUserId = parseInt(userId, 10);
    
    // Cargar todos los datos en paralelo
    const [historyRes, overdueRes, paidRes, pendingRes] = await Promise.all([
      window.StudentAPI.getDashboardHistory(parsedUserId, token, 1, 15, false, 'student'),
      window.StudentAPI.getOverdueTotal(parsedUserId, token, false, 'student'),
      window.StudentAPI.getPaidTotal(parsedUserId, token, false, 'student'),
      window.StudentAPI.getPendingTotal(parsedUserId, token, false, 'student')
    ]);

    // Procesar respuestas
    if (historyRes?.success && historyRes.data?.payment_history) {
      this.history = historyRes.data.payment_history.items;
    }

    if (overdueRes?.success && overdueRes.data?.total_overdue) {
      this.overdue = overdueRes.data.total_overdue;
    }

    if (paidRes?.success && paidRes.data?.paid_data) {
      this.paid = paidRes.data.paid_data;
    }

    if (pendingRes?.success && pendingRes.data?.total_pending) {
      this.pending = pendingRes.data.total_pending;
    }

    console.log('✅ Dashboard cargado exitosamente');
  } catch (err) {
    console.error('Error loading dashboard:', err);
    this.error = 'Error al cargar los datos del panel';
  } finally {
    this.loading = false;
  }
}
```

### Refrescar el caché del dashboard:

```javascript
async refreshCache() {
  const token = localStorage.getItem('access_token');
  const userId = localStorage.getItem('userId');
  
  try {
    const response = await window.StudentAPI.refreshDashboardCache(
      userId ? parseInt(userId) : null,
      token,
      'student'
    );
    
    if (response.success) {
      console.log('✅ Caché limpiado');
      // Recargar datos
      await this.init();
    }
  } catch (error) {
    console.error('❌ Error al limpiar caché:', error);
  }
}
```

---

## 🔄 Diferencias entre Endpoints

### `/dashboard/history` vs `/payments/history`

| Característica | `/dashboard/history` | `/payments/history` |
|---------------|---------------------|-------------------|
| Propósito | Historial resumido para dashboard | Historial completo con detalles |
| Paginación | Sí (default: 15 items) | Sí (configurable) |
| Permisos | `view.payments.summary` | `view.payments.history` |
| Uso recomendado | Vista rápida en dashboard | Página completa de historial |
| Caché | Sí (con `forceRefresh` optional) | Sí (con `forceRefresh` optional) |

---

## 🛠️ Manejo de Errores

### Códigos de Error Comunes:

- **401** - No autenticado (token inválido o expirado)
- **403** - No autorizado (permisos insuficientes)
- **404** - No encontrado (estudiante no existe)
- **422** - Error de validación (parámetros incorrectos)
- **429** - Demasiadas solicitudes (rate limit)
- **500** - Error interno del servidor

### Ejemplo de Manejo:

```javascript
try {
  const response = await window.StudentAPI.getPendingTotal(userId, token);
  // Procesar respuesta exitosa
} catch (error) {
  if (error.message.includes('401')) {
    // Redirigir a login
    window.location.href = '/login';
  } else if (error.message.includes('403')) {
    // Mostrar mensaje de permisos insuficientes
    alert('No tienes permisos para ver esta información');
  } else {
    // Error genérico
    console.error('Error:', error.message);
  }
}
```

---

## 📝 Notas Importantes

1. **Caché**: Los endpoints tienen caché automático. Usa `forceRefresh: true` solo cuando sea necesario.

2. **Paginación**: El historial usa paginación. Ajusta `perPage` según tus necesidades (max recomendado: 50).

3. **Roles**: Los padres deben proporcionar `studentId` para ver datos de sus hijos. Los estudiantes pueden omitirlo.

4. **Permisos**: Cada endpoint valida permisos específicos. Asegúrate de que el usuario tenga los permisos correctos asignados en el backend.

5. **Performance**: Usa `Promise.all()` para cargar múltiples endpoints en paralelo y mejorar el tiempo de carga.

---

## 🎨 Implementación Actual

El archivo `PortalEstudiante.astro` ya implementa estos endpoints correctamente:

- ✅ Carga de datos en paralelo al inicializar
- ✅ Botón de refresh con feedback visual
- ✅ Manejo de errores y estados de carga
- ✅ Toast notifications para feedback al usuario
- ✅ Soporte para roles student/parent

---

## 🔗 Referencias

- **API Base URL**: `https://nginx-production-728f.up.railway.app/api/v1`
- **Archivo de Implementación**: `Frond-end/public/studentAPI.js`
- **Página de Estudiante**: `Frond-end/src/pages/Estudiante/PortalEstudiante.astro`
- **Utility (ES Modules)**: `Frond-end/src/utils/dashboardStudentAPI.js` *(nuevo)*

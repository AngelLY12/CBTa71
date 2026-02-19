# Guía de Integración de la API - Sistema CBTA

## 🎯 Resumen Completado

### ✅ Configuración Base
1. **api.js** - Archivo de configuración con TODOS los endpoints de la API documentados
2. **authService.js** - Servicio completo de autenticación con:
   - Login/Register/Logout
   - Manejo automático de tokens (access_token y refresh_token)
   - Refresh automático cuando el token expira
   - Métodos helper para peticiones autenticadas

3. **index.astro** - Login funcional conectado a la API real

---

## 📚 Cómo Usar la API en Cualquier Página

### 1. Importar lo Necesario

```javascript
import { API_ENDPOINTS } from '../config/api.js';
import { AuthService } from '../utils/authService.js';
```

### 2. Hacer Peticiones Autenticadas

#### Método GET
```javascript
try {
  const response = await AuthService.authenticatedFetch(API_ENDPOINTS.users.list);
  const data = await handleAPIResponse(response);
  console.log(data);
} catch (error) {
  console.error('Error:', error);
}
```

#### Método POST
```javascript
try {
  const response = await AuthService.authenticatedFetch(API_ENDPOINTS.concepts.create, {
    method: 'POST',
    body: JSON.stringify({
      concept_name: 'Inscripción',
      amount: 1500,
      start_date: '2025-09-01',
      applies_to: 'todos'
    })
  });
  const data = await handleAPIResponse(response);
  console.log(data);
} catch (error) {
  console.error('Error:', error);
}
```

#### Método PUT/PATCH
```javascript
try {
  const response = await AuthService.authenticatedFetch(
    API_ENDPOINTS.concepts.update(conceptId), 
    {
      method: 'PUT',
      body: JSON.stringify({
        concept_name: 'Nuevo Nombre',
        amount: 2000
      })
    }
  );
  const data = await handleAPIResponse(response);
} catch (error) {
  console.error('Error:', error);
}
```

#### Método DELETE
```javascript
try {
  const response = await AuthService.authenticatedFetch(
    API_ENDPOINTS.concepts.delete(conceptId),
    { method: 'DELETE' }
  );
  const data = await handleAPIResponse(response);
} catch (error) {
  console.error('Error:', error);
}
```

---

## 🗂️ Endpoints Disponibles por Módulo

### 👤 Usuarios y Autenticación
```javascript
// Login
API_ENDPOINTS.auth.login

// Logout
API_ENDPOINTS.auth.logout

// Obtener usuario autenticado
API_ENDPOINTS.users.getAuthenticated

// Actualizar contraseña
API_ENDPOINTS.users.updatePassword

// Listar usuarios (admin)
API_ENDPOINTS.adminActions.showUsers
```

### 🎓 Estudiantes
```javascript
// Listar estudiantes
API_ENDPOINTS.students.list + '?search=email&perPage=15&page=1'

// Obtener estudiante por ID
API_ENDPOINTS.students.get(studentId)

// Agregar detalles de estudiante
API_ENDPOINTS.adminActions.attachStudent

// Actualizar detalles de estudiante
API_ENDPOINTS.adminActions.updateStudent(id)
```

### 💰 Conceptos de Pago
```javascript
// Listar conceptos
API_ENDPOINTS.concepts.list + '?status=activo&perPage=15'

// Crear concepto
API_ENDPOINTS.concepts.create

// Actualizar concepto
API_ENDPOINTS.concepts.update(id)

// Finalizar concepto
API_ENDPOINTS.concepts.finalize(id)

// Activar/Desactivar
API_ENDPOINTS.concepts.activate(id)
API_ENDPOINTS.concepts.disable(id)

// Eliminar (lógico)
API_ENDPOINTS.concepts.deleteLogical(id)
```

### 💳 Pagos
```javascript
// Listar pagos
API_ENDPOINTS.payments.list + '?search=email&perPage=15'

// Pagos por concepto
API_ENDPOINTS.payments.byConcept

// Historial de pagos de usuario
API_ENDPOINTS.paymentHistory.get(userId)
```

### 📊 Dashboard Staff
```javascript
// Obtener estadísticas de conceptos
API_ENDPOINTS.dashboardStaff.concepts + '?only_this_year=true'

// Total de pagos
API_ENDPOINTS.dashboardStaff.payments + '?only_this_year=true'

// Total de estudiantes
API_ENDPOINTS.dashboardStaff.students

// Pagos pendientes
API_ENDPOINTS.dashboardStaff.pending
```

### 📋 Deudas
```javascript
// Listar deudas
API_ENDPOINTS.debts.list + '?search=email'

// Pagos de Stripe
API_ENDPOINTS.debts.stripePayments + '?search=25687290&year=2025'

// Validar pago
API_ENDPOINTS.debts.validate
```

---

## 🔧 Ejemplos Completos por Página

### roles.astro (Gestión de Personal)

```javascript
// En el script de Alpine.js
async loadUsers() {
  try {
    const response = await AuthService.authenticatedFetch(
      `${API_ENDPOINTS.adminActions.showUsers}?perPage=50&status=activo`
    );
    const data = await handleAPIResponse(response);
    this.users = data.data.users.items || [];
  } catch (error) {
    console.error('Error loading users:', error);
    this.showNotify('Error al cargar usuarios', 'error');
  }
}

async saveUser() {
  this.isSaving = true;
  try {
    const url = this.isEditing 
      ? API_ENDPOINTS.adminActions.updateStudent(this.newUser.id)
      : API_ENDPOINTS.adminActions.register;
    
    const method = this.isEditing ? 'PUT' : 'POST';
    
    const response = await AuthService.authenticatedFetch(url, {
      method,
      body: JSON.stringify(this.newUser)
    });
    
    const result = await handleAPIResponse(response);
    if (result.success) {
      this.showNotify('Usuario guardado correctamente');
      await this.loadUsers();
      this.closePanel();
    }
  } catch (error) {
    this.showNotify(error.message || 'Error', 'error');
  } finally {
    this.isSaving = false;
  }
}
```

### students.astro (Gestión de Estudiantes)

```javascript
async loadStudents() {
  try {
    const queryParams = buildQueryString({
      search: this.searchTerm,
      perPage: this.perPage,
      page: this.currentPage,
      forceRefresh: false
    });
    
    const response = await AuthService.authenticatedFetch(
      `${API_ENDPOINTS.students.list}${queryParams}`
    );
    const data = await handleAPIResponse(response);
    this.students = data.data.payments?.items || [];
    this.totalPages = data.data.payments?.last_page || 1;
  } catch (error) {
    console.error('Error loading students:', error);
  }
}
```

### concepts.astro (Conceptos de Pago)

```javascript
async loadConcepts() {
  try {
    const queryParams = buildQueryString({
      status: this.filterStatus,
      perPage: 15,
      page: this.currentPage
    });
    
    const response = await AuthService.authenticatedFetch(
      `${API_ENDPOINTS.concepts.list}${queryParams}`
    );
    const data = await handleAPIResponse(response);
    this.concepts = data.data.concepts?.items || [];
  } catch (error) {
    console.error('Error loading concepts:', error);
  }
}

async finalizeConcept(conceptId) {
  try {
    const response = await AuthService.authenticatedFetch(
      API_ENDPOINTS.concepts.finalize(conceptId),
      { method: 'POST' }
    );
    const result = await handleAPIResponse(response);
    if (result.success) {
      this.showNotify('Concepto finalizado correctamente');
      await this.loadConcepts();
    }
  } catch (error) {
    this.showNotify(error.message, 'error');
  }
}
```

### Dashboard.astro (Dashboard Staff)

```javascript
async loadDashboardStats() {
  try {
    const [conceptsRes, paymentsRes, studentsRes, pendingRes] = await Promise.all([
      AuthService.authenticatedFetch(`${API_ENDPOINTS.dashboardStaff.concepts}?only_this_year=true`),
      AuthService.authenticatedFetch(`${API_ENDPOINTS.dashboardStaff.payments}?only_this_year=true`),
      AuthService.authenticatedFetch(API_ENDPOINTS.dashboardStaff.students),
      AuthService.authenticatedFetch(`${API_ENDPOINTS.dashboardStaff.pending}?only_this_year=true`)
    ]);
    
    const [concepts, payments, students, pending] = await Promise.all([
      handleAPIResponse(conceptsRes),
      handleAPIResponse(paymentsRes),
      handleAPIResponse(studentsRes),
      handleAPIResponse(pendingRes)
    ]);
    
    this.stats = {
      totalConcepts: concepts.data.concepts?.total || 0,
      totalPayments: payments.data.payments_data?.total_amount || 0,
      totalStudents: students.data.total_students || 0,
      totalPending: pending.data.total_pending?.amount || 0
    };
  } catch (error) {
    console.error('Error loading dashboard stats:', error);
  }
}
```

---

## 🚀 Próximos Pasos

1. **roles.astro** - Integrar CRUD completo de usuarios
2. **students.astro** - Conectar listado y búsqueda de estudiantes
3. **concepts.astro** - CRUD de conceptos de pago
4. **payments.astro** - Historial de pagos con filtros
5. **debts.astro** - Gestión de deudas y validación
6. **Dashboard.astro** - Estadísticas del dashboard staff

---

## 🔐 Manejo de Autenticación

El `AuthService` maneja automáticamente:
- ✅ Agregar el token Bearer a todas las peticiones
- ✅ Refresh automático cuando el token expira (401)
- ✅ Redirección al login si el refresh falla
- ✅ Almacenamiento seguro de tokens en localStorage

No necesitas preocuparte por los tokens, solo usa `AuthService.authenticatedFetch()` para todas las peticiones protegidas.

---

## 📝 Notas Importantes

1. **Todos los endpoints requieren autenticación** excepto login y registro
2. **Los parámetros de query** se pueden construir con `buildQueryString()`
3. **Las respuestas** siempre tienen el formato:
   ```json
   {
     "success": true,
     "message": "Mensaje descriptivo",
     "data": { ... }
   }
   ```
4. **Los errores** se lanzan y deben manejarse con try/catch
5. **Paginación** está incluida en los listados con `perPage` y `page`

---

## 🎨 Estado Actual

✅ API configurada completamente  
✅ AuthService funcional  
✅ Login integrado con API real  
⏳ Pendiente: Integrar resto de páginas (roles, students, concepts, payments, dashboard)

---

**API**: https://nginx-production-728f.up.railway.app/api/documentation

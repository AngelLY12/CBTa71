# 📚 APIs Necesarias para Módulo Estudiante

Este documento lista todos los endpoints API necesarios para que funcionen correctamente las páginas del portal estudiante.

---

## 📍 Estructura de Páginas Estudiante

```
Estudiante/
├── PortalEstudiante.astro (Dashboard Principal)
├── Adeudos.astro (Pagos Pendientes)
├── Historial.astro (Historial de Pagos)
├── Tarjetas.astro (Gestión de Tarjetas)
└── components/
    ├── PaymentsTable.astro
    ├── ProgressCircle.astro
    └── CardInfo.astro
```

---

## 🏠 1. PortalEstudiante.astro (Dashboard Principal)

**Propósito:** Mostrar resumen académico y financiero del estudiante.

**APIs Necesarias:**

### 1.1 Obtener datos del dashboard del estudiante
```
GET /api/v1/dashboard/data
```
- **Autenticación:** Token Bearer (Sanctum)
- **Permisos:** `view own financial overview`
- **Headers:**
  ```
  Authorization: Bearer {token}
  X-User-Role: student
  X-User-Permission: view.own.financial.overview
  ```
- **Respuesta esperada:**
  ```json
  {
    "success": true,
    "message": "Dashboard data retrieved",
    "data": {
      "alumnoNombre": "Juan Pérez",
      "pagosPendientes": {
        "monto": "$5,000.00",
        "info": "3 pagos pendientes"
      },
      "pagosRealizados": {
        "monto": "$15,000.00",
        "info": "12 pagos realizados"
      },
      "totalPagado": "$15,000.00",
      "totalPendiente": "$5,000.00",
      "porcentajeProgreso": 75,
      "pagos": [...]
    }
  }
  ```

### 1.2 Obtener conceptos pendientes
```
GET /api/v1/dashboard/pending
```
- **Autenticación:** Token Bearer
- **Permisos:** `view own pending concepts summary`
- **Respuesta:** Lista de conceptos pendientes de pago

### 1.3 Obtener conceptos pagados
```
GET /api/v1/dashboard/paid
```
- **Autenticación:** Token Bearer
- **Permisos:** `view own paid concepts summary`
- **Respuesta:** Lista de conceptos ya pagados

### 1.4 Obtener conceptos vencidos
```
GET /api/v1/dashboard/overdue
```
- **Autenticación:** Token Bearer
- **Permisos:** `view own overdue concepts summary`
- **Respuesta:** Lista de conceptos vencidos

---

## 💰 2. Adeudos.astro (Pagos Pendientes)

**Propósito:** Mostrar todos los pagos pendientes y permitir iniciar pago.

**APIs Necesarias:**

### 2.1 Obtener pagos pendientes del estudiante
```
GET /api/v1/pending-payments/{id}
```
- **Parámetros:** `{id}` = ID del estudiante
- **Autenticación:** Token Bearer
- **Permisos:** `view.pending.concepts`
- **Respuesta:**
  ```json
  {
    "success": true,
    "message": "Operación completada exitosamente",
    "data": {
      "pending_payments": [
        {
          "id": 1,
          "concept_name": "Pago de inscripción",
          "description": "Pago correspondiente al semestre 2025-2",
          "amount": "1500.00",
          "start_date": "2025-11-01",
          "end_date": "2025-12-01"
        }
      ]
    }
  }
  ```

### 2.2 Obtener pagos vencidos
```
GET /api/v1/pending-payments/overdue/{id}
```
- **Parámetros:** `{id}` = ID del estudiante
- **Autenticación:** Token Bearer
- **Permisos:** `view.overdue.concepts`
- **Respuesta:** Lista de pagos con fecha vencida

### 2.3 Generar intento de pago (Stripe)
```
POST /api/v1/pending-payments
```
- **Autenticación:** Token Bearer
- **Permisos:** `create.payment`
- **Body:**
  ```json
  {
    "concept_id": 123
  }
  ```
- **Respuesta:**
  ```json
  {
    "success": true,
    "message": "Operación completada exitosamente",
    "data": {
      "url_checkout": "https://checkout.stripe.com/pay/cs_test_a1b2c3d4e5"
    }
  }
  ```

---

## 📜 3. Historial.astro (Historial de Pagos)

**Propósito:** Mostrar todos los pagos realizados históricos.

**APIs Necesarias:**

### 3.1 Obtener historial de pagos
```
GET /api/v1/history/{id}
```
- **Parámetros:** 
  - `{id}` = ID del estudiante (path)
  - `perPage` = Cantidad por página (default: 15)
  - `page` = Número de página (default: 1)
  - `forceRefresh` = Forzar caché (true/false)
- **Autenticación:** Token Bearer
- **Permisos:** `view.payment.history`
- **Respuesta:**
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
            "balance": "150.00",
            "date": "2025-11-04",
            "status": "completed",
            "reference": "REF123456",
            "url": "https://example.com/receipt/123",
            "payment_method_details": ["Tarjeta de crédito"]
          }
        ],
        "currentPage": 1,
        "lastPage": 5,
        "perPage": 15,
        "total": 72,
        "hasMorePages": true,
        "nextPage": 2
      }
    }
  }
  ```

### 3.2 Obtener detalles de un pago específico (Opcional)
```
GET /api/v1/history/payment/{id}
```
- **Parámetros:** `{id}` = ID del pago
- **Autenticación:** Token Bearer
- **Permisos:** `view.payment`
- **Respuesta:** Detalles completos del pago

---

## 💳 4. Tarjetas.astro (Gestión de Tarjetas)

**Propósito:** Gestionar métodos de pago (tarjetas de crédito).

**APIs Necesarias:**

### 4.1 Obtener tarjetas registradas
```
GET /api/v1/cards
```
- **Autenticación:** Token Bearer
- **Permisos:** `view.cards`
- **Respuesta:**
  ```json
  {
    "success": true,
    "message": "Tarjetas obtenidas",
    "data": {
      "cards": [
        {
          "id": 1,
          "brand": "visa",
          "last4": "4242",
          "exp_month": 12,
          "exp_year": 2025,
          "cardholder_name": "Juan Pérez"
        }
      ]
    }
  }
  ```

### 4.2 Registrar nueva tarjeta
```
POST /api/v1/cards
```
- **Autenticación:** Token Bearer
- **Permisos:** `create.setup`
- **Body:** Datos de la tarjeta (usualmente vía Stripe Elements)
- **Respuesta:** Tarjeta registrada

### 4.3 Guardar tarjeta (Setup Intent)
```
GET /api/v1/cards/save
```
- **Autenticación:** Token Bearer
- **Permisos:** `create.and.view.card`
- **Respuesta:** Intent para guardar tarjeta en Stripe

### 4.4 Eliminar tarjeta
```
DELETE /api/v1/cards/{paymentMethodId}
```
- **Parámetros:** `{paymentMethodId}` = ID del método de pago
- **Autenticación:** Token Bearer
- **Permisos:** `delete.card`
- **Respuesta:** Confirmación de eliminación

---

## 👤 5. Usuario Autenticado

**APIs para obtener datos del usuario logueado:**

### 5.1 Obtener usuario autenticado
```
GET /api/v1/users/user
```
- **Parámetros opcionales:**
  - `forceRefresh` = true/false (forzar caché)
- **Autenticación:** Token Bearer
- **Respuesta:**
  ```json
  {
    "success": true,
    "message": "Operación completada exitosamente",
    "data": {
      "user": {
        "id": 1,
        "curp": "PEMJ950615HDFRZN09",
        "name": "Juan",
        "last_name": "Pérez",
        "email": "juan.perez@example.com",
        "phone_number": "+5215512345678",
        "status": "activo",
        "registration_date": "2024-01-15T12:34:56Z",
        "birthdate": "1995-06-15",
        "gender": "male",
        "address": ["Calle Falsa 123", "Colonia Centro"],
        "blood_type": "O+",
        "studentDetail": {
          "user_id": 1,
          "id": 1,
          "career_id": 10,
          "n_control": "20201234",
          "semestre": 5,
          "group": "A",
          "workshop": "Taller de programación"
        }
      }
    }
  }
  ```

### 5.2 Actualizar datos del usuario
```
PATCH /api/v1/users/update
```
- **Autenticación:** Token Bearer
- **Body:**
  ```json
  {
    "name": "Juan",
    "last_name": "Pérez",
    "email": "juan.perez@example.com",
    "phone_number": "+5215512345678",
    "birthdate": "1990-05-15",
    "gender": "hombre",
    "address": ["Calle 123"],
    "blood_type": "O+"
  }
  ```
- **Respuesta:** Usuario actualizado

### 5.3 Cambiar contraseña
```
PATCH /api/v1/users/update/password
```
- **Autenticación:** Token Bearer
- **Body:**
  ```json
  {
    "currentPassword": "oldPassword123",
    "newPassword": "newPassword456"
  }
  ```
- **Respuesta:** Confirmación de cambio

---

## 🔐 Autenticación General

**Para todos los endpoints, se requiere:**

1. **Token Bearer en Header:**
   ```
   Authorization: Bearer {access_token}
   ```

2. **Headers Recomendados:**
   ```
   Content-Type: application/json
   Accept: application/json
   X-User-Role: student
   ```

3. **Token se obtiene en:** `POST /api/v1/login`

---

## 📊 Resumen de Endpoints por Página

| Página | Método | Endpoint | Descripción |
|--------|--------|----------|-------------|
| **PortalEstudiante** | GET | `/v1/dashboard/data` | Dashboard principal |
| | GET | `/v1/dashboard/pending` | Conceptos pendientes |
| | GET | `/v1/dashboard/paid` | Conceptos pagados |
| | GET | `/v1/dashboard/overdue` | Conceptos vencidos |
| **Adeudos** | GET | `/v1/pending-payments/{id}` | Pagos pendientes |
| | GET | `/v1/pending-payments/overdue/{id}` | Pagos vencidos |
| | POST | `/v1/pending-payments` | Crear pago |
| **Historial** | GET | `/v1/history/{id}` | Historial de pagos |
| | GET | `/v1/history/payment/{id}` | Detalles de pago |
| **Tarjetas** | GET | `/v1/cards` | Listar tarjetas |
| | POST | `/v1/cards` | Registrar tarjeta |
| | GET | `/v1/cards/save` | Setup intent |
| | DELETE | `/v1/cards/{id}` | Eliminar tarjeta |
| **Perfil** | GET | `/v1/users/user` | Obtener usuario |
| | PATCH | `/v1/users/update` | Actualizar usuario |
| | PATCH | `/v1/users/update/password` | Cambiar contraseña |

---

## ✅ Checklist de Implementación

- [ ] PortalEstudiante.astro obtiene `/v1/dashboard/data`
- [ ] Adeudos.astro obtiene `/v1/pending-payments/{id}`
- [ ] Adeudos puede crear pago con `POST /v1/pending-payments`
- [ ] Historial.astro obtiene `/v1/history/{id}` con paginación
- [ ] Tarjetas.astro obtiene `/v1/cards`
- [ ] Tarjetas puede registrar con `POST /v1/cards`
- [ ] Tarjetas puede eliminar con `DELETE /v1/cards/{id}`
- [ ] Usuario autenticado disponible con `/v1/users/user`
- [ ] Perfil puede actualizar con `PATCH /v1/users/update`
- [ ] Perfil puede cambiar contraseña

---

## 🚀 Próximos Pasos

1. Implementar llamadas a API en cada página Estudiante
2. Agregar manejo de errores y loading states
3. Implementar caché con localStorage
4. Agregar refresh automático de datos
5. Implementar logout si token expira (401)

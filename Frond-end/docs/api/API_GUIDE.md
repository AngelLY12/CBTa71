# 📘 Guía de Consumo de API en Astro

Esta guía te muestra cómo consumir datos de tu API en Astro usando el frontmatter.

## 🚀 Inicio Rápido

### 1. Importar la configuración de API

```astro
---
import { API_ENDPOINTS } from '../config/api.js';
---
```

### 2. Hacer fetch en el frontmatter

```astro
---
import Layout from '../layouts/Layout.astro';
import { API_ENDPOINTS } from '../config/api.js';

// Consumir datos de la API
let students = [];
let error = null;

try {
  const response = await fetch(API_ENDPOINTS.students.list);
  
  if (!response.ok) {
    throw new Error(`Error: ${response.status}`);
  }
  
  const data = await response.json();
  students = Array.isArray(data) ? data : (data.data || []);
} catch (err) {
  error = err.message;
}
---

<Layout>
  {error ? (
    <div class="error">{error}</div>
  ) : (
    <div>
      {students.map((student) => (
        <div>
          <h3>{student.nombre} {student.apellidoP}</h3>
          <p>{student.correo}</p>
        </div>
      ))}
    </div>
  )}
</Layout>
```

## 📦 Helpers Disponibles

Hemos creado helpers en `src/utils/apiHelpers.js` para facilitar el consumo de APIs.

### fetchAPI - Consumir un endpoint

```astro
---
import { fetchAPI } from '../utils/apiHelpers.js';
import { API_ENDPOINTS } from '../config/api.js';

const { data, error, status } = await fetchAPI(API_ENDPOINTS.students.list);
---

<div>
  {error ? (
    <p>Error: {error}</p>
  ) : (
    <ul>
      {data.map(item => <li>{item.nombre}</li>)}
    </ul>
  )}
</div>
```

### fetchMultiple - Consumir múltiples endpoints en paralelo

```astro
---
import { fetchMultiple } from '../utils/apiHelpers.js';
import { API_ENDPOINTS } from '../config/api.js';

const results = await fetchMultiple({
  students: API_ENDPOINTS.students.list,
  payments: API_ENDPOINTS.payments.list,
  debts: API_ENDPOINTS.debts.list
});

const students = results.students.data || [];
const payments = results.payments.data || [];
const debts = results.debts.data || [];
---

<div>
  <p>Estudiantes: {students.length}</p>
  <p>Pagos: {payments.length}</p>
  <p>Adeudos: {debts.length}</p>
</div>
```

### paginate - Paginar resultados

```astro
---
import { fetchAPI, paginate } from '../utils/apiHelpers.js';

const { data } = await fetchAPI(API_ENDPOINTS.students.list);
const page = 1;
const perPage = 10;

const paginatedData = paginate(data, page, perPage);
---

<div>
  <p>Página {paginatedData.page} de {paginatedData.totalPages}</p>
  
  {paginatedData.data.map(item => (
    <div>{item.nombre}</div>
  ))}
  
  <button disabled={!paginatedData.hasPrev}>Anterior</button>
  <button disabled={!paginatedData.hasNext}>Siguiente</button>
</div>
```

### filterData - Filtrar datos

```astro
---
import { fetchAPI, filterData } from '../utils/apiHelpers.js';

const { data } = await fetchAPI(API_ENDPOINTS.students.list);

// Filtrar por cualquier campo
const filtered1 = filterData(data, 'juan');

// Filtrar solo en campos específicos
const filtered2 = filterData(data, 'juan', ['nombre', 'apellidoP', 'correo']);
---

<div>
  {filtered2.map(student => (
    <div>{student.nombre}</div>
  ))}
</div>
```

### sortData - Ordenar datos

```astro
---
import { fetchAPI, sortData } from '../utils/apiHelpers.js';

const { data } = await fetchAPI(API_ENDPOINTS.students.list);

// Ordenar ascendente
const sortedAsc = sortData(data, 'nombre', 'asc');

// Ordenar descendente
const sortedDesc = sortData(data, 'semestre', 'desc');
---

<div>
  {sortedAsc.map(student => (
    <div>{student.nombre}</div>
  ))}
</div>
```

## 🎯 Endpoints Disponibles

Todos los endpoints están en `src/config/api.js`:

```javascript
// Estudiantes
API_ENDPOINTS.students.list
API_ENDPOINTS.students.get(id)
API_ENDPOINTS.students.create
API_ENDPOINTS.students.update(id)
API_ENDPOINTS.students.delete(id)

// Pagos
API_ENDPOINTS.payments.list
API_ENDPOINTS.payments.get(id)
API_ENDPOINTS.payments.create
API_ENDPOINTS.payments.history
API_ENDPOINTS.payments.pending

// Adeudos
API_ENDPOINTS.debts.list
API_ENDPOINTS.debts.get(id)
API_ENDPOINTS.debts.validate(id)
API_ENDPOINTS.debts.pending

// Conceptos de Pago
API_ENDPOINTS.paymentConcepts.list
API_ENDPOINTS.paymentConcepts.get(id)
API_ENDPOINTS.paymentConcepts.create
API_ENDPOINTS.paymentConcepts.update(id)
API_ENDPOINTS.paymentConcepts.delete(id)
API_ENDPOINTS.paymentConcepts.activate(id)

// Usuarios
API_ENDPOINTS.users.profile
API_ENDPOINTS.users.update(id)
API_ENDPOINTS.users.list

// Dashboard
API_ENDPOINTS.dashboard.stats
API_ENDPOINTS.dashboard.payments
API_ENDPOINTS.dashboard.summary

// Auth
API_ENDPOINTS.auth.login
API_ENDPOINTS.auth.register
API_ENDPOINTS.auth.logout

// Y muchos más...
```

## 📝 Ejemplos Completos

### Página con lista de estudiantes y búsqueda

```astro
---
import Layout from '../layouts/Layout.astro';
import { fetchAPI, filterData, sortData } from '../utils/apiHelpers.js';
import { API_ENDPOINTS } from '../config/api.js';

const { data: allStudents, error } = await fetchAPI(API_ENDPOINTS.students.list);

// Ordenar por nombre
const sortedStudents = sortData(allStudents || [], 'nombre', 'asc');
---

<Layout title="Estudiantes">
  <div class="container">
    <h1>Lista de Estudiantes</h1>
    
    {error ? (
      <div class="error">
        Error al cargar estudiantes: {error}
      </div>
    ) : (
      <>
        <p>Total: {sortedStudents.length} estudiantes</p>
        
        <div class="grid">
          {sortedStudents.map((student) => (
            <div class="card">
              <h3>{student.nombre} {student.apellidoP}</h3>
              <p>Control: {student.numControl}</p>
              <p>Carrera: {student.carrera}</p>
              <p>Semestre: {student.semestre}</p>
            </div>
          ))}
        </div>
      </>
    )}
  </div>
</Layout>
```

### Página con estadísticas del dashboard

```astro
---
import Layout from '../layouts/Layout.astro';
import { fetchMultiple } from '../utils/apiHelpers.js';
import { API_ENDPOINTS } from '../config/api.js';

const results = await fetchMultiple({
  students: API_ENDPOINTS.students.list,
  payments: API_ENDPOINTS.payments.list,
  debts: API_ENDPOINTS.debts.pending,
  stats: API_ENDPOINTS.dashboard.stats
});

const totalStudents = results.students.data?.length || 0;
const totalPayments = results.payments.data?.length || 0;
const totalDebts = results.debts.data?.length || 0;
---

<Layout title="Dashboard">
  <div class="dashboard">
    <h1>Panel de Control</h1>
    
    <div class="stats-grid">
      <div class="stat-card">
        <h3>Total Estudiantes</h3>
        <p class="stat-number">{totalStudents}</p>
      </div>
      
      <div class="stat-card">
        <h3>Pagos Registrados</h3>
        <p class="stat-number">{totalPayments}</p>
      </div>
      
      <div class="stat-card">
        <h3>Adeudos Pendientes</h3>
        <p class="stat-number">{totalDebts}</p>
      </div>
    </div>
  </div>
</Layout>
```

## 🔍 Ver Ejemplos en Vivo

Hemos creado dos páginas de ejemplo:

1. **`/api-example`** - Ejemplos básicos de consumo de API
2. **`/api-advanced`** - Ejemplos avanzados con helpers

Visita estas páginas para ver el código en acción.

## 🛠️ Manejo de Errores

Siempre maneja errores al consumir APIs:

```astro
---
const { data, error } = await fetchAPI(API_ENDPOINTS.students.list);
---

{error ? (
  <div class="error-message">
    ⚠️ Error: {error}
  </div>
) : data && data.length > 0 ? (
  <div>
    {data.map(item => <div>{item.nombre}</div>)}
  </div>
) : (
  <div class="empty-state">
    No hay datos disponibles
  </div>
)}
```

## 📚 Recursos

- API Base URL: `https://nginx-production-728f.up.railway.app/api`
- API: `https://nginx-production-728f.up.railway.app/api/documentation`
- Archivo de configuración: `src/config/api.js`
- Helpers: `src/utils/apiHelpers.js`

## 💡 Tips

1. **Siempre valida las respuestas**: Usa `if (!response.ok)` antes de parsear JSON
2. **Normaliza los datos**: La API puede devolver arrays directos o `{ data: [] }`
3. **Maneja estados vacíos**: Verifica `data.length === 0`
4. **Usa helpers**: Los helpers en `apiHelpers.js` simplifican el código
5. **Combina requests**: Usa `fetchMultiple` para cargar varios endpoints en paralelo

¡Listo para usar! 🚀

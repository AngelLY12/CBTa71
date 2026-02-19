// Helper function para consumir APIs en Astro
// Uso: const { data, error } = await fetchAPI(API_ENDPOINTS.students.list);

export async function fetchAPI(endpoint, options = {}) {
  try {
    const response = await fetch(endpoint, {
      method: options.method || 'GET',
      headers: {
        'Content-Type': 'application/json',
        ...options.headers
      },
      ...options
    });

    if (!response.ok) {
      return {
        data: null,
        error: `Error ${response.status}: ${response.statusText}`,
        status: response.status
      };
    }

    const result = await response.json();
    
    // Normalizar respuesta (puede venir como array directo o dentro de { data: [] })
    const data = Array.isArray(result) ? result : (result.data || result);

    return {
      data,
      error: null,
      status: response.status
    };
  } catch (error) {
    return {
      data: null,
      error: error.message || 'Error al conectar con la API',
      status: 500
    };
  }
}

// Helper para consumir múltiples endpoints en paralelo
export async function fetchMultiple(endpoints) {
  const promises = Object.entries(endpoints).map(async ([key, url]) => {
    const result = await fetchAPI(url);
    return [key, result];
  });

  const results = await Promise.all(promises);
  return Object.fromEntries(results);
}

// Helper para paginar resultados
export function paginate(data, page = 1, perPage = 10) {
  const start = (page - 1) * perPage;
  const end = start + perPage;
  
  return {
    data: data.slice(start, end),
    page,
    perPage,
    total: data.length,
    totalPages: Math.ceil(data.length / perPage),
    hasNext: end < data.length,
    hasPrev: page > 1
  };
}

// Helper para filtrar y buscar
export function filterData(data, searchTerm, fields = []) {
  if (!searchTerm) return data;
  
  const term = searchTerm.toLowerCase();
  
  return data.filter(item => {
    if (fields.length === 0) {
      // Buscar en todos los campos
      return Object.values(item).some(value => 
        String(value).toLowerCase().includes(term)
      );
    }
    
    // Buscar solo en campos específicos
    return fields.some(field => 
      String(item[field] || '').toLowerCase().includes(term)
    );
  });
}

// Helper para ordenar datos
export function sortData(data, field, order = 'asc') {
  return [...data].sort((a, b) => {
    const valueA = a[field];
    const valueB = b[field];
    
    if (valueA === valueB) return 0;
    
    const comparison = valueA < valueB ? -1 : 1;
    return order === 'asc' ? comparison : -comparison;
  });
}

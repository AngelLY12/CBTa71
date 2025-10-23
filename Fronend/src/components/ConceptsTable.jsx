// src/components/ConceptsTable.jsx
import React from 'react';
import { useStore } from '@nanostores/react';
import { conceptsStore } from '../store.js';

export default function ConceptsTable() {
  const concepts = useStore(conceptsStore);

  return (
    <div className="overflow-x-auto">
      <table className="min-w-full divide-y divide-gray-200">
        <thead className="bg-gray-50">
          <tr>
            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
            <th scope="col" className="relative px-6 py-3">
              <span className="sr-only">Editar</span>
            </th>
          </tr>
        </thead>
        <tbody className="bg-white divide-y divide-gray-200">
          {concepts.map((concept) => (
            <tr key={concept.id}>
              <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{concept.id}</td>
              <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{concept.title}</td>
              <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{concept.amount}</td>
              <td className="px-6 py-4 whitespace-nowrap">
                <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${concept.status === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                  {concept.status}
                </span>
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                {/* --- AQUÍ ESTÁ EL ENLACE QUE FALTABA --- */}
                <a href={`/concepts/${concept.id}`} className="text-indigo-600 hover:text-indigo-900">
                  Editar
                </a>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
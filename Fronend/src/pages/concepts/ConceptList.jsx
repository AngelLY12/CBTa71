// src/components/ConceptList.jsx
import React from 'react';
import { useStore } from '@nanostores/react';
import { conceptsStore } from '../store.js';

export default function ConceptList() {
  const concepts = useStore(conceptsStore);

  return (
    <div className="flex flex-col gap-4">
      {concepts.map(concept => (
        <div key={concept.id} className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          {/* ... (código de la tarjeta) ... */}
        </div>
      ))}
    </div>
  );
}
import { atom } from 'nanostores';

let initialConcepts = [];

// Esta comprobación debe envolver todo el código que usa APIs del navegador.
if (typeof window !== 'undefined') {
  const storedConcepts = localStorage.getItem('concepts');
  if (storedConcepts) {
    initialConcepts = JSON.parse(storedConcepts);
  }
}

const conceptsStore = atom(initialConcepts);

if (typeof window !== 'undefined') {
  conceptsStore.subscribe((value) => {
    localStorage.setItem('concepts', JSON.stringify(value));
  });
}

export { conceptsStore as c };

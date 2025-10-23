// src/types/index.ts
export interface Concept {
    id: number;
    title: string;
    period: string;
    amount: number;
    description: string;
    status: 'active' | 'finished';
}

export type ConceptStatus = 'active' | 'finished';
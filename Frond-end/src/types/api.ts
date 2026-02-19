// 📘 Tipos de TypeScript basados en los schemas del backend Laravel

// ============================================
// AUTH & USER TYPES
// ============================================

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  success: boolean;
  message: string;
  data: {
    access_token: string;
    token_type: string;
    user_data: User;
  };
}

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  role?: 'student' | 'financial staff' | 'admin' | 'parent';
  permissions?: string[];
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

// ============================================
// STUDENT TYPES
// ============================================

export interface Student {
  id: number;
  user_id: number;
  enrollment_number: string;
  name: string;
  last_name: string;
  email: string;
  phone_number?: string;
  birthdate?: string;
  gender?: 'male' | 'female' | 'other';
  curp?: string;
  address?: string[];
  blood_type?: string;
  career_id?: number;
  semester?: number;
  group?: string;
  tutor_name?: string;
  tutor_phone?: string;
  emergency_contact?: string;
  status: 'active' | 'inactive' | 'graduated' | 'suspended';
  registration_date: string;
  created_at: string;
  updated_at: string;
}

export interface StudentsListParams {
  search?: string;
  perPage?: number;
  page?: number;
  status?: 'active' | 'inactive' | 'graduated' | 'suspended';
}

// ============================================
// PAYMENT CONCEPT TYPES
// ============================================

export interface PaymentConcept {
  id: number;
  concept_name: string;
  description?: string;
  amount: number;
  start_date: string;
  end_date: string;
  applies_to: 'todos' | 'semestre' | 'grupo' | 'carrera';
  target_semester?: number;
  target_group?: string;
  target_career_id?: number;
  status: 'activo' | 'inactivo' | 'finalizado' | 'expirado';
  is_recurring: boolean;
  recurrence_type?: 'mensual' | 'semestral' | 'anual';
  can_pay_in_installments: boolean;
  max_installments?: number;
  late_fee_percentage?: number;
  grace_period_days?: number;
  created_by: number;
  finalized_by?: number;
  finalized_at?: string;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
}

export interface CreateConceptRequest {
  concept_name: string;
  description?: string;
  amount: number;
  start_date: string;
  end_date: string;
  applies_to: 'todos' | 'semestre' | 'grupo' | 'carrera';
  target_semester?: number;
  target_group?: string;
  target_career_id?: number;
  is_recurring?: boolean;
  recurrence_type?: 'mensual' | 'semestral' | 'anual';
  can_pay_in_installments?: boolean;
  max_installments?: number;
  late_fee_percentage?: number;
  grace_period_days?: number;
}

export interface ConceptsListParams {
  status?: 'activo' | 'inactivo' | 'finalizado' | 'expirado';
  perPage?: number;
  page?: number;
  search?: string;
}

// ============================================
// PAYMENT TYPES
// ============================================

export interface Payment {
  id: number;
  user_id: number;
  payment_concept_id: number;
  amount_paid: number;
  payment_method: 'stripe' | 'cash' | 'transfer' | 'card';
  stripe_payment_intent_id?: string;
  payment_status: 'pending' | 'completed' | 'failed' | 'refunded';
  payment_date: string;
  receipt_url?: string;
  notes?: string;
  validated_by?: number;
  validated_at?: string;
  created_at: string;
  updated_at: string;
}

export interface CreatePaymentRequest {
  payment_concept_id: number;
  payment_method_id?: string; // Stripe payment method
  amount?: number; // Si se permite pagar parcialmente
}

export interface PaymentsListParams {
  search?: string;
  perPage?: number;
  page?: number;
  payment_status?: 'pending' | 'completed' | 'failed' | 'refunded';
}

// ============================================
// DEBT TYPES
// ============================================

export interface Debt {
  id: number;
  student_id: number;
  payment_concept_id: number;
  amount_due: number;
  amount_paid: number;
  amount_remaining: number;
  due_date: string;
  status: 'pending' | 'paid' | 'overdue' | 'partial';
  late_fee_applied: number;
  created_at: string;
  updated_at: string;
  // Relaciones
  student?: Student;
  concept?: PaymentConcept;
}

export interface DebtsListParams {
  search?: string;
  status?: 'pending' | 'paid' | 'overdue' | 'partial';
  year?: number;
}

export interface ValidatePaymentRequest {
  payment_id: number;
  notes?: string;
}

// ============================================
// DASHBOARD TYPES
// ============================================

export interface DashboardStudentData {
  total_debt: number;
  total_paid: number;
  pending_concepts: number;
  overdue_concepts: number;
  next_due_date?: string;
  recent_payments: Payment[];
}

export interface DashboardStaffData {
  total_students: number;
  active_concepts: number;
  total_collected: number;
  total_pending: number;
  pending_validations: number;
  recent_activity: any[];
}

export interface ConceptsStatsParams {
  only_this_year?: boolean;
}

export interface PaymentsStatsParams {
  only_this_year?: boolean;
}

// ============================================
// CARD (STRIPE) TYPES
// ============================================

export interface PaymentMethod {
  id: string;
  type: 'card';
  card: {
    brand: string;
    last4: string;
    exp_month: number;
    exp_year: number;
  };
  created: number;
}

export interface SetupIntentResponse {
  client_secret: string;
}

// ============================================
// GENERIC API RESPONSE TYPES
// ============================================

export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  error_code?: string;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T = any> {
  data: T[];
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number;
  to: number;
}

export interface ApiError {
  success: false;
  error_code: string;
  message: string;
  errors?: Record<string, string[]>;
}

// ============================================
// QUERY PARAMS HELPER TYPE
// ============================================

export type QueryParams = Record<string, string | number | boolean | undefined>;

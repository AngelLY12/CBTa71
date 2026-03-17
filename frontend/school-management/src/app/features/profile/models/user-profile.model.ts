export interface UserProfile {
  id: number,
  curp: string,
  name: string,
  last_name: string,
  email: string,
  phone_number: string,
  status: string,
  registration_date: string,
  emailVerifiedAt?: string,
  birthdate?: string,
  gender?: string,
  address?: string[],
  blood_type?: string,
  stripe_customer_id?: string
}

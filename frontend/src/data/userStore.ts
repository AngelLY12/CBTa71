// user.ts

export interface UserData {
  id: number;
  fullName: string;
  roles: string[];
  status: string;
  hasUnreadNotifications: boolean;
}

export interface UserTokens {
  access_token: string;
  refresh_token: string;
  token_type: string;
  user_data: UserData; // aquí va el objeto de usuario dentro de tokens
}

// La respuesta real solo trae user_tokens
export interface AuthResponse {
  user_tokens: UserTokens;
}

// Store en memoria + localStorage
export const userStore: { user: UserData | null; tokens: UserTokens | null } = {
  user: null,
  tokens: null,
};

// Guardar datos en memoria y localStorage
export function setAuthData(data: AuthResponse) {
  const normalizedUser: UserData = {
    ...data.user_tokens.user_data,
    roles: normalizeRoles(data.user_tokens.user_data.roles), // ✔ ahora sí es string[]
  };

  userStore.user = normalizedUser;
  userStore.tokens = {
    ...data.user_tokens,
    user_data: normalizedUser,
  };

  localStorage.setItem("tokens", JSON.stringify(userStore.tokens));
  localStorage.setItem("user", JSON.stringify(userStore.user));
}

// Cargar datos desde localStorage
export function loadAuthData() {
  const savedTokens = localStorage.getItem("tokens");
  const savedUser = localStorage.getItem("user");
  if (savedTokens) userStore.tokens = JSON.parse(savedTokens);
  if (savedUser) userStore.user = JSON.parse(savedUser);
}

// Limpiar datos
export function clearAuthData() {
  userStore.user = null;
  userStore.tokens = null;
  localStorage.removeItem("tokens");
  localStorage.removeItem("user");
}

// Obtener roles desde memoria/localStorage
export function getRoles(): string[] {
  return userStore.user?.roles ?? [];
}

export function setRoles(roles: string[]): void {
  if (userStore.user) {
    userStore.user = {
      ...userStore.user,
      roles,
    };
    if (userStore.tokens) {
      userStore.tokens = {
        ...userStore.tokens,
        user_data: userStore.user,
      };
    }
  } else {
    // Inicialización mínima para evitar null
    userStore.user = {
      id: 0,
      fullName: "",
      status: "",
      hasUnreadNotifications: false,
      roles,
    };
  }

  localStorage.setItem("user", JSON.stringify(userStore.user));
  if (userStore.tokens) {
    localStorage.setItem("tokens", JSON.stringify(userStore.tokens));
  }
}

export function normalizeRole(role: string): string {
  if (
    role === "admin" ||
    role === "financiere-staff" ||
    role === "supervisor"
  ) {
    return "staff";
  }
  if (
    role === "applicant"
  ) {
    return "aspirant"
  }

  return role;
}

export function normalizeRoles(roles: string[]): string[] {
  return roles.map(normalizeRole);
}


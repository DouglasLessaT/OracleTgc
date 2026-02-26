import { apiPost, apiGet, apiPatch, setApiOnUnauthorized, type ApiRequestOptions } from '@/lib/apiClient';
import { authDebug } from '@/lib/authDebug';

export interface LoginResult {
  token: string;
  user: ApiUser;
}

export interface ApiUser {
  id: string;
  email: string;
  phone?: string | null;
  name: string;
  type: string;
  plan?: string;
  isActive: boolean;
  lastLoginAt?: string | null;
  scannedCardsCount?: number;
  emailVerified?: boolean;
  canScanCard?: boolean;
  canCreateCollections?: boolean;
  canCreateDecks?: boolean;
  createdAt?: string;
  updatedAt?: string;
}

export async function register(email: string, password: string, name: string) {
  const payload = {
    email: String(email).trim(),
    password: String(password).trim(),
    name: String(name).trim(),
  };
  const res = await apiPost<ApiUser>('api/auth/register', payload, { skipAuth: true });
  return res;
}

export async function login(email: string, password: string) {
  const body = {
    email: String(email).trim(),
    password: String(password).trim(),
  };
  authDebug('login request', { email: body.email, passwordLength: body.password.length, url: 'POST /api/auth/login' });
  try {
    const res = await apiPost<LoginResult>('api/auth/login', body, { skipAuth: true });
    authDebug('login response', { success: res.success, hasToken: !!(res as { data?: { token?: string } }).data?.token, hasData: !!res.data });
    return res;
  } catch (e: unknown) {
    const err = e as { status?: number; message?: string; errors?: Record<string, string> };
    authDebug('login error', {
      status: err?.status,
      message: err?.message,
      errors: err?.errors,
      hint: err?.status === 422 && err?.errors?.credentials
        ? 'API disse credenciais inválidas: usuário não encontrado OU senha não confere. Verifique no backend qual branch falhou (AuthService::login).'
        : undefined,
    });
    throw e;
  }
}

export async function refreshToken(token: string) {
  const res = await apiPost<LoginResult>('api/auth/refresh', undefined, {
    headers: { Authorization: `Bearer ${token}` },
    skipAuth: true,
  });
  return res;
}

/** Resposta do verify-email: user + token JWT (login automático após verificação) */
export interface VerifyEmailResult {
  user: ApiUser;
  token: string;
}

export async function verifyEmail(token: string) {
  const res = await apiPost<VerifyEmailResult>('api/auth/verify-email', { token }, { skipAuth: true });
  return res;
}

export async function resendVerification(email: string) {
  const res = await apiPost('api/auth/resend-verification', { email }, { skipAuth: true });
  return res;
}

/** Envia código de verificação para o e-mail do usuário logado (configurações da conta). */
export async function sendSettingsVerificationCode() {
  const res = await apiPost('api/users/me/send-verification-code');
  return res;
}

export async function getMe(options?: ApiRequestOptions) {
  const res = await apiGet<ApiUser>('api/users/me', options);
  return res;
}

export async function updateMe(data: { name?: string; type?: string; password?: string; email?: string }) {
  const res = await apiPatch<ApiUser>('api/users/me', data);
  return res;
}

export function setOnUnauthorized(cb: () => void) {
  setApiOnUnauthorized(cb);
}

/**
 * Cliente HTTP para a API Oracle TGC.
 * Respeita o formato de resposta: { success, data?, message, errors? }.
 */

const API_BASE =
  (typeof import.meta !== 'undefined' && (import.meta as any).env?.VITE_API_URL) ||
  'http://localhost:8000';

export interface ApiResponse<T = unknown> {
  success: boolean;
  data?: T;
  message?: string;
  errors?: Record<string, string> | string[];
  meta?: Record<string, unknown>;
}

export type ApiError = { message: string; status: number; errors?: ApiResponse['errors'] };

let onUnauthorized: (() => void) | null = null;

export function setApiOnUnauthorized(callback: () => void) {
  onUnauthorized = callback;
}

function getToken(): string | null {
  try {
    return localStorage.getItem('oracle_tgc_token');
  } catch {
    return null;
  }
}

export type ApiRequestOptions = RequestInit & { skipAuth?: boolean; skipOnUnauthorized?: boolean };

export async function apiRequest<T = unknown>(
  path: string,
  options: ApiRequestOptions = {}
): Promise<ApiResponse<T>> {
  const { skipAuth, skipOnUnauthorized, ...fetchOptions } = options;
  const url = path.startsWith('http') ? path : `${API_BASE.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...((fetchOptions.headers as Record<string, string>) || {}),
  };

  const token = getToken();
  if (!skipAuth && token) {
    (headers as Record<string, string>)['Authorization'] = `Bearer ${token}`;
  }

  const res = await fetch(url, {
    ...fetchOptions,
    headers,
  });

  let body: ApiResponse<T>;
  const contentType = res.headers.get('Content-Type') || '';
  try {
    body = contentType.includes('application/json') ? await res.json() : { success: false, message: await res.text() };
  } catch {
    body = { success: false, message: res.statusText || 'Erro de rede' };
  }

  if (res.status === 401 && !skipOnUnauthorized) {
    onUnauthorized?.();
  }

  if (!res.ok) {
    const err: ApiError = {
      message: (body as ApiResponse).message || res.statusText || 'Erro na requisição',
      status: res.status,
      errors: (body as ApiResponse).errors,
    };
    throw err;
  }

  return body as ApiResponse<T>;
}

export async function apiGet<T = unknown>(path: string, options?: ApiRequestOptions): Promise<ApiResponse<T>> {
  return apiRequest<T>(path, { ...options, method: 'GET' });
}

export async function apiPost<T = unknown>(path: string, data?: unknown, options?: RequestInit): Promise<ApiResponse<T>> {
  return apiRequest<T>(path, { ...options, method: 'POST', body: data ? JSON.stringify(data) : undefined });
}

export async function apiPut<T = unknown>(path: string, data?: unknown, options?: RequestInit): Promise<ApiResponse<T>> {
  return apiRequest<T>(path, { ...options, method: 'PUT', body: data ? JSON.stringify(data) : undefined });
}

export async function apiPatch<T = unknown>(path: string, data?: unknown, options?: RequestInit): Promise<ApiResponse<T>> {
  return apiRequest<T>(path, { ...options, method: 'PATCH', body: data ? JSON.stringify(data) : undefined });
}

export async function apiDelete<T = unknown>(path: string, options?: RequestInit): Promise<ApiResponse<T>> {
  return apiRequest<T>(path, { ...options, method: 'DELETE' });
}

export function getApiBaseUrl(): string {
  return API_BASE;
}

/**
 * Retorna URL absoluta para imagens de cartas.
 * Se imageUrl for relativa (ex: storage/cards/mtg/...), usa a base da API para evitar 404 no frontend.
 */
export function getCardImageUrl(imageUrl: string | null | undefined): string {
  if (!imageUrl) return '';
  if (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) return imageUrl;
  const base = API_BASE.replace(/\/$/, '');
  const path = imageUrl.startsWith('/') ? imageUrl : `/${imageUrl}`;
  return `${base}${path}`;
}

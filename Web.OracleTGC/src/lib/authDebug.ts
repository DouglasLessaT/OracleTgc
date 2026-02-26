/**
 * Debug do fluxo de Auth (login, verify, complete profile).
 * Só loga quando VITE_AUTH_DEBUG=true ou import.meta.env.DEV.
 * Nunca loga senha em claro.
 */

const DEBUG =
  typeof import.meta !== 'undefined' &&
  (import.meta.env?.VITE_AUTH_DEBUG === 'true' || import.meta.env?.DEV);

function maskEmail(email: string): string {
  if (!email || email.length < 5) return '***';
  const [local, domain] = email.split('@');
  if (!domain) return local.slice(0, 2) + '***';
  return (local?.slice(0, 2) ?? '') + '***@' + (domain ?? '');
}

export function authDebug(
  tag: string,
  payload: Record<string, unknown> & { password?: string }
): void {
  if (!DEBUG) return;
  const safe = { ...payload };
  if ('password' in safe && safe.password != null) {
    safe.password = '(length=' + String(String(safe.password).length) + ')';
  }
  if ('email' in safe && typeof safe.email === 'string') {
    safe.email = maskEmail(safe.email);
  }
  console.log('[Auth DEBUG]', tag, safe);
}

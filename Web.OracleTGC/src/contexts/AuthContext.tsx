import { createContext, useContext, useState, useCallback, useEffect, ReactNode } from 'react';
import { User, AuthState, UserPlan } from '@/types/user';
import * as authApi from '@/services/authApiService';
import { authDebug } from '@/lib/authDebug';

export interface RegisterResult {
  success: boolean;
  errorMessage?: string;
}

export interface AuthActionResult {
  success: boolean;
  errorMessage?: string;
}

export interface UpdateProfileData {
  name?: string;
  email?: string;
  password?: string;
}

interface AuthContextType extends AuthState {
  login: (email: string, password: string) => Promise<AuthActionResult>;
  register: (email: string, password: string, name: string) => Promise<RegisterResult>;
  verifyToken: (token: string) => Promise<AuthActionResult>;
  completeRegistration: (username: string, plan: UserPlan, password?: string) => Promise<AuthActionResult>;
  updateProfile: (data: UpdateProfileData) => Promise<AuthActionResult>;
  resendCode: (email: string) => Promise<AuthActionResult>;
  logout: () => void;
}

const TOKEN_KEY = 'oracle_tgc_token';

function mapApiUserToUser(apiUser: authApi.ApiUser): User {
  const plan: UserPlan =
    apiUser.plan === 'premium' || apiUser.type === 'premium' || apiUser.type === 'admin' ? 'premium' : 'free';
  return {
    id: apiUser.id,
    email: apiUser.email,
    phone: apiUser.phone ?? undefined,
    username: apiUser.name || apiUser.email.split('@')[0],
    plan,
    createdAt: apiUser.createdAt ?? '',
    followersCount: 0,
    followingCount: 0,
    collectionsCount: 0,
    cardsCount: apiUser.scannedCardsCount ?? 0,
  };
}

const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [authState, setAuthState] = useState<AuthState>({
    isAuthenticated: false,
    user: null,
    isLoading: true,
  });

  const logout = useCallback(() => {
    try {
      localStorage.removeItem(TOKEN_KEY);
    } catch {}
    setAuthState({ isAuthenticated: false, user: null, isLoading: false });
  }, []);

  useEffect(() => {
    authApi.setOnUnauthorized(logout);
  }, [logout]);

  useEffect(() => {
    const token = localStorage.getItem(TOKEN_KEY);
    if (!token) {
      setAuthState((prev) => ({ ...prev, isLoading: false }));
      return;
    }
    // skipOnUnauthorized: evita logout imediato no 401; assim tentamos refresh antes
    authApi
      .getMe({ skipOnUnauthorized: true })
      .then((res) => {
        if (res.success && res.data) {
          setAuthState({
            isAuthenticated: true,
            user: mapApiUserToUser(res.data),
            isLoading: false,
          });
        } else {
          authApi.refreshToken(token).then((refreshRes) => {
            if (refreshRes.success && refreshRes.data?.token) {
              localStorage.setItem(TOKEN_KEY, refreshRes.data.token);
              if (refreshRes.data.user) {
                setAuthState({
                  isAuthenticated: true,
                  user: mapApiUserToUser(refreshRes.data.user),
                  isLoading: false,
                });
              } else {
                logout();
              }
            } else {
              logout();
            }
          }).catch(() => logout());
        }
      })
      .catch(async (err: { status?: number }) => {
        if (err?.status === 401) {
          try {
            const refreshRes = await authApi.refreshToken(token);
            if (refreshRes.success && refreshRes.data?.token) {
              localStorage.setItem(TOKEN_KEY, refreshRes.data.token);
              if (refreshRes.data?.user) {
                setAuthState({
                  isAuthenticated: true,
                  user: mapApiUserToUser(refreshRes.data.user as authApi.ApiUser),
                  isLoading: false,
                });
                return;
              }
            }
          } catch {
            // refresh falhou (token expirado ou inválido)
          }
        }
        logout();
      });
  }, [logout]);

  const login = useCallback(async (email: string, password: string): Promise<AuthActionResult> => {
    authDebug('AuthContext.login called', { email, passwordLength: password?.length ?? 0 });
    setAuthState((prev) => ({ ...prev, isLoading: true }));
    try {
      const res = await authApi.login(email, password);
      authDebug('AuthContext.login result', { success: res.success, hasToken: !!res.data?.token, dataKeys: res.data ? Object.keys(res.data) : [] });
      if (res.success && res.data?.token) {
        localStorage.setItem(TOKEN_KEY, res.data.token);
        setAuthState({
          isAuthenticated: true,
          user: res.data.user ? mapApiUserToUser(res.data.user) : null,
          isLoading: false,
        });
        return { success: true };
      }
      setAuthState((prev) => ({ ...prev, isLoading: false }));
      return { success: false, errorMessage: (res as { message?: string }).message || 'Login falhou.' };
    } catch (e) {
      const err = e as { message?: string; errors?: Record<string, string> };
      authDebug('AuthContext.login catch', { message: err?.message, errors: err?.errors });
      console.error('Login error', e);
      setAuthState((prev) => ({ ...prev, isLoading: false }));
      const msg = err?.errors && typeof err.errors === 'object' ? Object.values(err.errors)[0] : err?.message;
      return { success: false, errorMessage: msg || 'Email ou senha inválidos.' };
    }
  }, []);

  const register = useCallback(async (email: string, password: string, name: string): Promise<RegisterResult> => {
    setAuthState((prev) => ({ ...prev, isLoading: true }));
    try {
      const res = await authApi.register(email, password, name);
      setAuthState((prev) => ({ ...prev, isLoading: false }));
      return { success: !!res.success };
    } catch (e: any) {
      console.error('Register error', e);
      setAuthState((prev) => ({ ...prev, isLoading: false }));
      const msg = e?.message || (typeof e?.errors === 'string' ? e.errors : e?.errors?.required) || 'Erro ao criar conta. Tente novamente.';
      return { success: false, errorMessage: msg };
    }
  }, []);

  const verifyToken = useCallback(async (token: string): Promise<AuthActionResult> => {
    setAuthState((prev) => ({ ...prev, isLoading: true }));
    try {
      const res = await authApi.verifyEmail(token);
      setAuthState((prev) => ({ ...prev, isLoading: false }));
      if (res.success && res.data) {
        const data = res.data as authApi.VerifyEmailResult & authApi.ApiUser;
        const userData = data.user ?? (data.id && data.email ? data : null);
        const token = data.token;
        authDebug('verify-email response', {
          dataKeys: Object.keys(data),
          hasToken: !!token,
          hasUserData: !!userData,
          willSkipPasswordOnCompleteProfile: !!(token && userData),
        });
        if (token && userData) {
          localStorage.setItem(TOKEN_KEY, token);
          setAuthState({
            isAuthenticated: true,
            user: mapApiUserToUser(userData as authApi.ApiUser),
            isLoading: false,
          });
        } else if (import.meta.env?.DEV && !token) {
          console.warn(
            '[Auth] verify-email não retornou token. Atualize a API (AuthController) para retornar { user, token } e evite pedir senha no Complete Profile.'
          );
        }
        return { success: true };
      }
      return { success: false, errorMessage: (res as { message?: string }).message || 'Código inválido.' };
    } catch (e) {
      console.error('Verify error', e);
      setAuthState((prev) => ({ ...prev, isLoading: false }));
      const err = e as { message?: string; errors?: Record<string, string> };
      const msg = err?.errors && typeof err.errors === 'object' ? Object.values(err.errors)[0] : err?.message;
      return { success: false, errorMessage: msg || 'Código inválido ou expirado.' };
    }
  }, []);

  const completeRegistration = useCallback(async (username: string, plan: UserPlan, password?: string): Promise<AuthActionResult> => {
    setAuthState((prev) => ({ ...prev, isLoading: true }));
    try {
      const res = await authApi.updateMe({
        name: username,
        type: plan,
        ...(password && { password }),
      });
      if (res.success && res.data) {
        setAuthState({
          isAuthenticated: true,
          user: mapApiUserToUser(res.data),
          isLoading: false,
        });
        return { success: true };
      }
      return { success: false, errorMessage: (res as { message?: string }).message || 'Não foi possível atualizar.' };
    } catch (e) {
      console.error('Complete profile error', e);
      setAuthState((prev) => ({ ...prev, isLoading: false }));
      const err = e as { message?: string; errors?: Record<string, string> };
      const msg = err?.errors && typeof err.errors === 'object' ? Object.values(err.errors)[0] : err?.message;
      return { success: false, errorMessage: msg || 'Não foi possível atualizar.' };
    }
  }, []);

  const updateProfile = useCallback(async (data: UpdateProfileData): Promise<AuthActionResult> => {
    setAuthState((prev) => ({ ...prev, isLoading: true }));
    try {
      const res = await authApi.updateMe(data);
      if (res.success && res.data) {
        setAuthState((prev) => ({
          ...prev,
          isAuthenticated: true,
          user: mapApiUserToUser(res.data),
          isLoading: false,
        }));
        return { success: true };
      }
      setAuthState((prev) => ({ ...prev, isLoading: false }));
      return { success: false, errorMessage: (res as { message?: string }).message || 'Não foi possível atualizar.' };
    } catch (e) {
      setAuthState((prev) => ({ ...prev, isLoading: false }));
      const err = e as { message?: string; errors?: Record<string, string> };
      const msg = err?.errors && typeof err.errors === 'object' ? Object.values(err.errors)[0] : err?.message;
      return { success: false, errorMessage: msg || 'Não foi possível atualizar.' };
    }
  }, []);

  const resendCode = useCallback(async (email: string): Promise<AuthActionResult> => {
    try {
      const res = await authApi.resendVerification(email);
      if (res.success) return { success: true };
      return { success: false, errorMessage: (res as { message?: string }).message || 'Não foi possível reenviar.' };
    } catch (e) {
      const err = e as { message?: string; errors?: Record<string, string> };
      const msg = err?.errors && typeof err.errors === 'object' ? Object.values(err.errors)[0] : err?.message;
      return { success: false, errorMessage: msg || 'Não foi possível reenviar.' };
    }
  }, []);

  return (
    <AuthContext.Provider
      value={{
        ...authState,
        login,
        register,
        verifyToken,
        completeRegistration,
        updateProfile,
        resendCode,
        logout,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}

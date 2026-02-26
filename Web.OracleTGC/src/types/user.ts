export type UserPlan = 'free' | 'premium';

export interface User {
  id: string;
  email: string;
  phone?: string;
  username: string;
  avatarUrl?: string;
  plan: UserPlan;
  createdAt: string;
  followersCount: number;
  followingCount: number;
  collectionsCount: number;
  cardsCount: number;
}

export interface AuthState {
  isAuthenticated: boolean;
  user: User | null;
  isLoading: boolean;
}

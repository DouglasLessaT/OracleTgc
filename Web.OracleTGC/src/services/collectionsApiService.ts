import { apiGet, apiPost, apiPut, apiPatch, apiDelete } from '@/lib/apiClient';
import type { ApiCollection } from './inventoryApiService';

export type { ApiCollection };

export async function getCollections(): Promise<ApiCollection[]> {
  const res = await apiGet<ApiCollection[]>('api/collections');
  return res.success && Array.isArray(res.data) ? res.data : [];
}

export async function getCollectionById(id: string): Promise<ApiCollection | null> {
  const res = await apiGet<ApiCollection>(`api/collections/${id}`);
  return res.success && res.data ? res.data : null;
}

export interface CreateCollectionPayload {
  name: string;
  game: string;
  description?: string;
  setCode?: string;
  setName?: string;
  targetCount?: number;
  isGameDeck?: boolean;
}

export async function createCollection(payload: CreateCollectionPayload): Promise<ApiCollection | null> {
  const res = await apiPost<ApiCollection>('api/collections', payload);
  return res.success && res.data ? res.data : null;
}

export async function updateCollection(
  id: string,
  payload: Partial<CreateCollectionPayload>
): Promise<ApiCollection | null> {
  const res = await apiPatch<ApiCollection>(`api/collections/${id}`, payload);
  return res.success && res.data ? res.data : null;
}

export async function deleteCollection(id: string): Promise<boolean> {
  const res = await apiDelete(`api/collections/${id}`);
  return res.success;
}

export async function addCardToCollection(collectionId: string, cardId: string): Promise<ApiCollection | null> {
  const res = await apiPost<ApiCollection>(`api/collections/${collectionId}/cards`, { cardId });
  return res.success && res.data ? res.data : null;
}

export async function removeCardFromCollection(collectionId: string, cardId: string): Promise<boolean> {
  const res = await apiDelete(`api/collections/${collectionId}/cards/${cardId}`);
  return res.success;
}

import { apiGet } from '@/lib/apiClient';

/** Estatísticas retornadas por GET /api/inventory/statistics e dentro de inventory */
export interface InventoryStatistics {
  totalCards: number;
  uniqueCards: number;
  collections: number;
  decks: number;
  byGame: Record<string, { cards: number; collections: number; decks: number }>;
}

/** Card como retornado pela API (entidade Card) */
export interface ApiCard {
  id: string;
  name: string;
  game: string;
  imageUrl?: string | null;
  cardType?: string;
  setCode?: string | null;
  setName?: string | null;
  collectorNumber?: string | null;
  number?: string | null;
  rarity?: string | null;
  prices?: { usd?: number | null; eur?: number | null; usdFoil?: number | null } | null;
  isFoil?: boolean;
}

/** Item do inventário (carta + quantidade e metadados) */
export interface ApiInventoryItem {
  id: string;
  card: ApiCard;
  quantity: number;
  condition?: string | null;
  language?: string | null;
  isFoil: boolean;
  purchasePrice?: number | null;
  currentPrice?: number | null;
  totalValue?: number | null;
  profitLoss?: number | null;
  metadata?: Record<string, unknown> | null;
}

/** Coleção como retornada pela API (dentro de inventory ou GET /api/collections) */
export interface ApiCollection {
  id: string;
  name: string;
  game: string;
  description?: string | null;
  setCode?: string | null;
  setName?: string | null;
  cardCount: number;
  targetCount?: number | null;
  isComplete: boolean;
  completionPercentage?: number;
  isDefaultAllCards: boolean;
  isGameDeck: boolean;
  cards?: ApiCard[];
}

/** Inventário completo (GET /api/inventory) */
export interface ApiInventory {
  id: string;
  userId: string;
  name: string;
  description?: string | null;
  statistics: InventoryStatistics;
  items: ApiInventoryItem[];
  collections: ApiCollection[];
  decks: unknown[];
}

export async function getInventory(): Promise<ApiInventory | null> {
  const res = await apiGet<ApiInventory>('api/inventory');
  return res.success && res.data ? res.data : null;
}

export async function getInventoryStatistics(): Promise<InventoryStatistics | null> {
  const res = await apiGet<InventoryStatistics>('api/inventory/statistics');
  return res.success && res.data ? res.data : null;
}

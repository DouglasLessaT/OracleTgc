import { CardGame, CardInfo } from './card';

export interface Collection {
  id: string;
  name: string;
  description?: string;
  game: CardGame;
  coverImage?: string;
  cardsCount: number;
  totalValue: number;
  isPublic: boolean;
  isDeck: boolean;
  createdAt: string;
  updatedAt: string;
  ownerId: string;
  ownerUsername?: string;
}

export interface CollectionCard extends CardInfo {
  collectionId: string;
  quantity: number;
  addedAt: string;
  notes?: string;
}

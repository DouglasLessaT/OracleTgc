export type CardGame = 'mtg' | 'pokemon';

export interface CardPrice {
  usd: number | null;
  usdFoil: number | null;
}

export interface ExchangeRates {
  btc: number;
  brl: number;
}

export interface CardInfo {
  id: string;
  name: string;
  game: CardGame;
  set: string;
  setName: string;
  number: string;
  rarity: string;
  imageUrl: string;
  artist?: string;
  isFoil: boolean;
  prices: CardPrice;
  type?: string;
  text?: string;
}

export interface ScanResult {
  extractedText: string;
  confidence: number;
  cardInfo: CardInfo | null;
  error?: string;
}

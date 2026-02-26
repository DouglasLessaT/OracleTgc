import { CardInfo } from '@/types/card';
import { Collection } from '@/types/collection';
import { User } from '@/types/user';

// Mock Cards
export const mockCards: CardInfo[] = [
  {
    id: '1',
    name: 'Black Lotus',
    game: 'mtg',
    set: 'LEA',
    setName: 'Limited Edition Alpha',
    number: '232',
    rarity: 'Rare',
    imageUrl: 'https://cards.scryfall.io/large/front/b/d/bd8fa327-dd41-4737-8f19-2cf5eb1f7cdd.jpg',
    artist: 'Christopher Rush',
    isFoil: false,
    prices: { usd: 500000, usdFoil: null },
    type: 'Artifact',
  },
  {
    id: '2',
    name: 'Lightning Bolt',
    game: 'mtg',
    set: 'M10',
    setName: 'Magic 2010',
    number: '146',
    rarity: 'Common',
    imageUrl: 'https://cards.scryfall.io/large/front/f/2/f29ba16f-c8fb-42fe-aabf-87089cb214a7.jpg',
    artist: 'Christopher Moeller',
    isFoil: false,
    prices: { usd: 2.50, usdFoil: 8.00 },
    type: 'Instant',
  },
  {
    id: '3',
    name: 'Charizard',
    game: 'pokemon',
    set: 'BS',
    setName: 'Base Set',
    number: '4',
    rarity: 'Holo Rare',
    imageUrl: 'https://images.pokemontcg.io/base1/4_hires.png',
    artist: 'Mitsuhiro Arita',
    isFoil: true,
    prices: { usd: 350, usdFoil: 500 },
    type: 'Fire',
  },
  {
    id: '4',
    name: 'Pikachu',
    game: 'pokemon',
    set: 'BS',
    setName: 'Base Set',
    number: '58',
    rarity: 'Common',
    imageUrl: 'https://images.pokemontcg.io/base1/58_hires.png',
    artist: 'Mitsuhiro Arita',
    isFoil: false,
    prices: { usd: 15, usdFoil: 45 },
    type: 'Electric',
  },
  {
    id: '5',
    name: 'Jace, the Mind Sculptor',
    game: 'mtg',
    set: 'WWK',
    setName: 'Worldwake',
    number: '31',
    rarity: 'Mythic Rare',
    imageUrl: 'https://cards.scryfall.io/large/front/c/8/c8817585-0d32-4d56-9142-0d29512e86a9.jpg',
    artist: 'Jason Chan',
    isFoil: false,
    prices: { usd: 85, usdFoil: 200 },
    type: 'Planeswalker',
  },
  {
    id: '6',
    name: 'Mewtwo',
    game: 'pokemon',
    set: 'BS',
    setName: 'Base Set',
    number: '10',
    rarity: 'Holo Rare',
    imageUrl: 'https://images.pokemontcg.io/base1/10_hires.png',
    artist: 'Ken Sugimori',
    isFoil: true,
    prices: { usd: 120, usdFoil: 250 },
    type: 'Psychic',
  },
];

// Mock Collections
export const mockCollections: Collection[] = [
  {
    id: '1',
    name: 'All Cards - Magic',
    description: 'Todas as minhas cartas de Magic: The Gathering',
    game: 'mtg',
    coverImage: 'https://cards.scryfall.io/large/front/b/d/bd8fa327-dd41-4737-8f19-2cf5eb1f7cdd.jpg',
    cardsCount: 89,
    totalValue: 1250.50,
    isPublic: true,
    isDeck: false,
    createdAt: '2024-01-15',
    updatedAt: '2024-02-01',
    ownerId: '1',
    ownerUsername: 'CardMaster',
  },
  {
    id: '2',
    name: 'All Cards - Pokémon',
    description: 'Todas as minhas cartas Pokémon',
    game: 'pokemon',
    coverImage: 'https://images.pokemontcg.io/base1/4_hires.png',
    cardsCount: 67,
    totalValue: 890.00,
    isPublic: true,
    isDeck: false,
    createdAt: '2024-01-20',
    updatedAt: '2024-01-28',
    ownerId: '1',
    ownerUsername: 'CardMaster',
  },
  {
    id: '3',
    name: 'Deck Mono Red',
    description: 'Meu deck competitivo de Modern',
    game: 'mtg',
    coverImage: 'https://cards.scryfall.io/large/front/f/2/f29ba16f-c8fb-42fe-aabf-87089cb214a7.jpg',
    cardsCount: 60,
    totalValue: 450.00,
    isPublic: false,
    isDeck: true,
    createdAt: '2024-02-01',
    updatedAt: '2024-02-05',
    ownerId: '1',
    ownerUsername: 'CardMaster',
  },
];

// Mock Users for Marketplace/Profile
export const mockUsers: User[] = [
  {
    id: '2',
    email: 'trader@exemplo.com',
    username: 'ProTrader',
    avatarUrl: 'https://api.dicebear.com/7.x/avataaars/svg?seed=ProTrader',
    plan: 'premium',
    createdAt: '2023-06-10',
    followersCount: 250,
    followingCount: 45,
    collectionsCount: 12,
    cardsCount: 1500,
  },
  {
    id: '3',
    email: 'collector@exemplo.com',
    username: 'MasterCollector',
    avatarUrl: 'https://api.dicebear.com/7.x/avataaars/svg?seed=MasterCollector',
    plan: 'premium',
    createdAt: '2022-03-20',
    followersCount: 890,
    followingCount: 120,
    collectionsCount: 25,
    cardsCount: 3200,
  },
];

// Mock Marketplace Listings
export interface MarketplaceListing {
  id: string;
  card: CardInfo;
  seller: User;
  price: number;
  condition: 'mint' | 'near-mint' | 'excellent' | 'good' | 'played';
  quantity: number;
  createdAt: string;
}

export const mockListings: MarketplaceListing[] = [
  {
    id: '1',
    card: mockCards[0],
    seller: mockUsers[0],
    price: 480000,
    condition: 'near-mint',
    quantity: 1,
    createdAt: '2024-02-01',
  },
  {
    id: '2',
    card: mockCards[2],
    seller: mockUsers[1],
    price: 320,
    condition: 'excellent',
    quantity: 2,
    createdAt: '2024-02-03',
  },
  {
    id: '3',
    card: mockCards[4],
    seller: mockUsers[0],
    price: 75,
    condition: 'mint',
    quantity: 4,
    createdAt: '2024-02-05',
  },
];

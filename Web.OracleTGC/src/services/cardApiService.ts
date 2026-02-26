import { CardInfo, CardGame } from '@/types/card';
import TCGdex, { Query } from '@tcgdex/sdk';

// Scryfall API for Magic: The Gathering
const SCRYFALL_API = 'https://api.scryfall.com';

// TCGdex SDK for Pokemon
const tcgdex = new TCGdex('en');

// Simple in-memory cache
const cache = new Map<string, { data: CardInfo[]; timestamp: number }>();
const CACHE_TTL = 5 * 60 * 1000; // 5 minutes

function getCached(key: string): CardInfo[] | null {
  const cached = cache.get(key);
  if (cached && Date.now() - cached.timestamp < CACHE_TTL) {
    return cached.data;
  }
  cache.delete(key);
  return null;
}

function setCache(key: string, data: CardInfo[]): void {
  cache.set(key, { data, timestamp: Date.now() });
}

export async function searchMTGCard(query: string): Promise<CardInfo[]> {
  const cacheKey = `mtg:${query.toLowerCase()}`;
  const cached = getCached(cacheKey);
  if (cached) return cached;

  try {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 5000);
    
    const response = await fetch(
      `${SCRYFALL_API}/cards/search?q=${encodeURIComponent(query)}&order=released&dir=desc`,
      { signal: controller.signal }
    );
    
    clearTimeout(timeout);
    
    if (!response.ok) {
      if (response.status === 404) return [];
      throw new Error('Failed to fetch MTG cards');
    }
    
    const data = await response.json();
    
    const results = data.data.slice(0, 5).map((card: any) => ({
      id: card.id,
      name: card.name,
      game: 'mtg' as CardGame,
      set: card.set.toUpperCase(),
      setName: card.set_name,
      number: card.collector_number,
      rarity: card.rarity,
      imageUrl: card.image_uris?.normal || card.card_faces?.[0]?.image_uris?.normal || '',
      artist: card.artist,
      isFoil: false,
      prices: {
        usd: card.prices?.usd ? parseFloat(card.prices.usd) : null,
        usdFoil: card.prices?.usd_foil ? parseFloat(card.prices.usd_foil) : null,
      },
      type: card.type_line,
      text: card.oracle_text,
    }));
    
    setCache(cacheKey, results);
    return results;
  } catch (error) {
    if ((error as Error).name === 'AbortError') {
      console.warn('MTG search timeout');
      return [];
    }
    console.error('Error searching MTG cards:', error);
    return [];
  }
}

export async function searchPokemonCard(query: string): Promise<CardInfo[]> {
  const cacheKey = `pokemon:${query.toLowerCase()}`;
  const cached = getCached(cacheKey);
  if (cached) return cached;

  try {
    // Use TCGdex SDK for faster Pokemon card search
    const cards = await tcgdex.card.list(
      Query.create()
        .contains('name', query)
        .paginate(1, 5)
    );
    
    if (!cards || cards.length === 0) return [];
    
    // Fetch full card details for the first 5 results
    const fullCards = await Promise.all(
      cards.slice(0, 5).map(async (cardResume) => {
        try {
          const card = await cardResume.getCard();
          return card;
        } catch {
          return null;
        }
      })
    );
    
    const results: CardInfo[] = fullCards
      .filter((card): card is NonNullable<typeof card> => card !== null)
      .map((card) => {
        // Extract prices from TCGdex pricing data (TCGPlayer)
        const tcgplayer = (card as any).pricing?.tcgplayer;
        const normalPrice = tcgplayer?.normal?.marketPrice || tcgplayer?.unlimitedHolofoil?.marketPrice || null;
        const holoPrice = tcgplayer?.holofoil?.marketPrice || tcgplayer?.reverseHolofoil?.marketPrice || null;
        
        return {
          id: card.id || `pokemon-${Date.now()}`,
          name: card.name || 'Unknown',
          game: 'pokemon' as CardGame,
          set: card.set?.id?.toUpperCase() || 'Unknown',
          setName: card.set?.name || 'Unknown Set',
          number: card.localId || '0',
          rarity: card.rarity || 'Unknown',
          imageUrl: card.image ? `${card.image}/high.png` : '',
          artist: card.illustrator || 'Unknown',
          isFoil: false,
          prices: {
            usd: normalPrice || holoPrice,
            usdFoil: holoPrice,
          },
          type: card.category || 'Pokémon',
          text: card.effect || (card as any).description || '',
        };
      });
    
    setCache(cacheKey, results);
    return results;
  } catch (error) {
    console.error('Error searching Pokemon cards:', error);
    return [];
  }
}

export async function searchCard(
  query: string,
  game?: CardGame
): Promise<CardInfo[]> {
  if (!query.trim()) return [];
  
  // Clean and optimize query
  const cleanQuery = query.trim().split(' ').slice(0, 3).join(' ');
  
  if (game === 'mtg') {
    return searchMTGCard(cleanQuery);
  }
  
  if (game === 'pokemon') {
    return searchPokemonCard(cleanQuery);
  }
  
  // Search both APIs in parallel with race condition handling
  const [mtgResults, pokemonResults] = await Promise.all([
    searchMTGCard(cleanQuery),
    searchPokemonCard(cleanQuery),
  ]);
  
  return [...mtgResults, ...pokemonResults];
}

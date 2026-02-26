import { ExchangeRates } from '@/types/card';

// Using free exchange rate APIs
const EXCHANGE_RATE_API = 'https://api.exchangerate-api.com/v4/latest/USD';
const COINGECKO_API = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd';

let cachedRates: ExchangeRates | null = null;
let lastFetch: number = 0;
const CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

export async function getExchangeRates(): Promise<ExchangeRates> {
  const now = Date.now();
  
  if (cachedRates && now - lastFetch < CACHE_DURATION) {
    return cachedRates;
  }
  
  try {
    const [fiatResponse, btcResponse] = await Promise.all([
      fetch(EXCHANGE_RATE_API),
      fetch(COINGECKO_API),
    ]);
    
    const fiatData = await fiatResponse.json();
    const btcData = await btcResponse.json();
    
    const btcPriceInUSD = btcData.bitcoin?.usd || 100000;
    
    cachedRates = {
      brl: fiatData.rates?.BRL || 5.0,
      btc: 1 / btcPriceInUSD,
    };
    
    lastFetch = now;
    return cachedRates;
  } catch (error) {
    console.error('Error fetching exchange rates:', error);
    // Fallback rates
    return {
      brl: 5.0,
      btc: 0.00001,
    };
  }
}

export function convertPrice(
  usdPrice: number,
  rates: ExchangeRates
): { usd: string; brl: string; btc: string } {
  return {
    usd: `$${usdPrice.toFixed(2)}`,
    brl: `R$${(usdPrice * rates.brl).toFixed(2)}`,
    btc: `₿${(usdPrice * rates.btc).toFixed(8)}`,
  };
}

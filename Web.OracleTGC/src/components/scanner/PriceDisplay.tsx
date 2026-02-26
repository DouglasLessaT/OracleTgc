import { DollarSign, Bitcoin } from 'lucide-react';
import { ExchangeRates } from '@/types/card';
import { convertPrice } from '@/services/currencyService';

interface PriceDisplayProps {
  usdPrice: number | null;
  usdFoilPrice: number | null;
  rates: ExchangeRates;
  isFoil: boolean;
}

export function PriceDisplay({ usdPrice, usdFoilPrice, rates, isFoil }: PriceDisplayProps) {
  const price = isFoil ? usdFoilPrice : usdPrice;

  if (price === null) {
    return (
      <div className="text-center p-4 bg-muted/30 rounded-lg">
        <p className="text-muted-foreground text-sm">Preço não disponível</p>
      </div>
    );
  }

  const converted = convertPrice(price, rates);

  return (
    <div className="grid grid-cols-3 gap-3">
      {/* USD */}
      <div className="bg-card p-4 rounded-lg text-center border border-border shadow-sm">
        <div className="flex items-center justify-center gap-1 text-muted-foreground mb-1">
          <DollarSign className="h-4 w-4" />
          <span className="text-xs font-medium">USD</span>
        </div>
        <p className="text-lg font-bold text-foreground">{converted.usd}</p>
      </div>

      {/* BRL */}
      <div className="bg-card p-4 rounded-lg text-center border border-border shadow-sm">
        <div className="flex items-center justify-center gap-1 text-muted-foreground mb-1">
          <span className="text-xs font-bold">R$</span>
          <span className="text-xs font-medium">BRL</span>
        </div>
        <p className="text-lg font-bold text-foreground">{converted.brl}</p>
      </div>

      {/* BTC */}
      <div className="bg-card p-4 rounded-lg text-center border border-border shadow-sm">
        <div className="flex items-center justify-center gap-1 text-muted-foreground mb-1">
          <Bitcoin className="h-4 w-4" />
          <span className="text-xs font-medium">BTC</span>
        </div>
        <p className="text-sm font-bold text-foreground break-all">{converted.btc}</p>
      </div>
    </div>
  );
}

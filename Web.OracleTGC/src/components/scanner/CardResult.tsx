import { useState } from 'react';
import { CardInfo, ExchangeRates } from '@/types/card';
import { PriceDisplay } from './PriceDisplay';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { Sparkles, RotateCcw, ChevronLeft, ChevronRight } from 'lucide-react';
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselPrevious,
  CarouselNext,
  type CarouselApi,
} from '@/components/ui/carousel';
import { useEffect } from 'react';

interface CardResultProps {
  cards: CardInfo[];
  selectedIndex: number;
  rates: ExchangeRates;
  onScanAgain: () => void;
  onCardChange: (index: number) => void;
}

export function CardResult({ cards, selectedIndex, rates, onScanAgain, onCardChange }: CardResultProps) {
  const [isFoil, setIsFoil] = useState(false);
  const [api, setApi] = useState<CarouselApi>();
  const card = cards[selectedIndex];
  const hasFoilPrice = card?.prices.usdFoil !== null;

  useEffect(() => {
    if (!api) return;
    api.scrollTo(selectedIndex);
  }, [api, selectedIndex]);

  useEffect(() => {
    if (!api) return;
    const onSelect = () => {
      onCardChange(api.selectedScrollSnap());
    };
    api.on('select', onSelect);
    return () => {
      api.off('select', onSelect);
    };
  }, [api, onCardChange]);

  if (!card) return null;

  const getRarityColor = (rarity: string) => {
    const rarityLower = rarity.toLowerCase();
    if (rarityLower.includes('mythic') || rarityLower.includes('secret')) {
      return 'bg-destructive/20 text-destructive border-destructive/30';
    }
    if (rarityLower.includes('rare') || rarityLower.includes('holo')) {
      return 'bg-chart-1/20 text-chart-1 border-chart-1/30';
    }
    if (rarityLower.includes('uncommon')) {
      return 'bg-chart-3/20 text-chart-3 border-chart-3/30';
    }
    return 'bg-secondary/20 text-secondary border-secondary/30';
  };

  const getGameBadge = () => {
    if (card.game === 'mtg') {
      return (
        <Badge variant="outline" className="bg-chart-4/20 text-chart-4 border-chart-4/30">
          Magic: The Gathering
        </Badge>
      );
    }
    return (
      <Badge variant="outline" className="bg-chart-2/20 text-chart-2 border-chart-2/30">
        Pokémon TCG
      </Badge>
    );
  };

  return (
    <div className="flex flex-col h-full overflow-y-auto">
         {/* Actions */}
          <div className="pt-4">
            <Button onClick={onScanAgain} className="w-full gap-2" size="lg">
              <RotateCcw className="h-5 w-5" />
              Escanear Outra Carta
            </Button>
          </div>
      <div className="flex flex-col-reverse lg:flex-row lg:gap-8 lg:items-start lg:justify-center p-4">
        {/* Card Info - Left side on desktop */}
        <div className="flex flex-col lg:w-96">
          <Card className="mt-4 lg:mt-0 border-border">
            <CardContent className="p-4 space-y-4">
              {/* Name and Badges */}
              <div className="space-y-2">
                <h2 className="text-xl font-bold text-foreground">{card.name}</h2>
                <div className="flex flex-wrap gap-2">
                  {getGameBadge()}
                  <Badge variant="outline" className={getRarityColor(card.rarity)}>
                    {card.rarity}
                  </Badge>
                </div>
              </div>

              {/* Set Info */}
              <div className="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <p className="text-muted-foreground">Edição</p>
                  <p className="font-medium text-foreground">{card.setName}</p>
                </div>
                <div>
                  <p className="text-muted-foreground">Número</p>
                  <p className="font-medium text-foreground">
                    {card.set} #{card.number}
                  </p>
                </div>
              </div>

              {/* Foil Toggle */}
              {hasFoilPrice && (
                <div className="flex items-center justify-between p-3 bg-accent/50 rounded-lg">
                  <div className="flex items-center gap-2">
                    <Sparkles className="h-4 w-4 text-accent-foreground" />
                    <Label htmlFor="foil-toggle" className="text-sm font-medium">
                      Versão Foil
                    </Label>
                  </div>
                  <Switch
                    id="foil-toggle"
                    checked={isFoil}
                    onCheckedChange={setIsFoil}
                  />
                </div>
              )}

              {/* Prices */}
              <div className="space-y-2">
                <h3 className="font-semibold text-foreground">
                  Preço de Mercado {isFoil && '(Foil)'}
                </h3>
                <PriceDisplay
                  usdPrice={card.prices.usd}
                  usdFoilPrice={card.prices.usdFoil}
                  rates={rates}
                  isFoil={isFoil}
                />
              </div>

              {/* Artist */}
              {card.artist && (
                <p className="text-xs text-muted-foreground text-center">
                  Ilustração: {card.artist}
                </p>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Card Image Carousel - Right side on desktop */}
        <div className="relative">
          <Carousel setApi={setApi} className="w-full max-w-xs mx-auto lg:mx-0">
            <CarouselContent className="-ml-2">
              {cards.map((c, index) => (
                <CarouselItem key={c.id} className="pl-2">
                  <div 
                    className={` m-10 relative aspect-[2.5/3.5] max-h-[45vh] lg:max-h-[60vh] rounded-xl overflow-hidden shadow-xl border-2 transition-all duration-300 ${
                      index === selectedIndex 
                        ? 'border-primary shadow-primary/20' 
                        : 'border-border opacity-80 scale-95'
                    }`}
                  >
                    <img
                      src={c.imageUrl}
                      alt={c.name}
                      className="w-full h-full object-contain bg-card"
                    />
                    {isFoil && index === selectedIndex && (
                      <div className="absolute inset-0 bg-gradient-to-br from-chart-1/30 via-transparent to-chart-3/30 pointer-events-none animate-pulse" />
                    )}
                    {/* Game badge on card */}
                    <div className="absolute top-2 left-2">
                      <Badge 
                        variant="secondary" 
                        className={`text-xs backdrop-blur-sm ${
                          c.game === 'mtg' 
                            ? 'bg-chart-4/80 text-primary-foreground' 
                            : 'bg-chart-2/80 text-primary-foreground'
                        }`}
                      >
                        {c.game === 'mtg' ? 'MTG' : 'PKM'}
                      </Badge>
                    </div>
                  </div>
                </CarouselItem>
              ))}
            </CarouselContent>
            {cards.length > 1 && (
              <>
                <CarouselPrevious className="left-0 bg-background/80 backdrop-blur-sm border-border hover:bg-primary hover:text-primary-foreground transition-colors" />
                <CarouselNext className="right-0 bg-background/80 backdrop-blur-sm border-border hover:bg-primary hover:text-primary-foreground transition-colors" />
              </>
            )}
          </Carousel>
          
          {/* Pagination dots */}
          {cards.length > 1 && (
            <div className="flex items-center justify-center gap-1.5 mt-3">
              {cards.map((_, index) => (
                <button
                  key={index}
                  onClick={() => api?.scrollTo(index)}
                  className={`w-2 h-2 rounded-full transition-all duration-200 ${
                    index === selectedIndex 
                      ? 'bg-primary w-6' 
                      : 'bg-muted-foreground/30 hover:bg-muted-foreground/50'
                  }`}
                  aria-label={`Ir para carta ${index + 1}`}
                />
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

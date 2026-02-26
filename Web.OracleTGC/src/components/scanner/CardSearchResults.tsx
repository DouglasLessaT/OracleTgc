import { CardInfo } from '@/types/card';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ChevronRight } from 'lucide-react';

interface CardSearchResultsProps {
  cards: CardInfo[];
  onSelect: (card: CardInfo) => void;
  extractedText: string;
}

export function CardSearchResults({ cards, onSelect, extractedText }: CardSearchResultsProps) {
  if (cards.length === 0) {
    return (
      <div className="p-6 text-center space-y-4">
        <div className="text-6xl">🔍</div>
        <h3 className="text-lg font-semibold text-foreground">
          Nenhuma carta encontrada
        </h3>
        <p className="text-sm text-muted-foreground">
          Não conseguimos identificar a carta. Tente tirar outra foto com melhor iluminação.
        </p>
        {extractedText && (
          <div className="mt-4 p-3 bg-muted/50 rounded-lg text-left">
            <p className="text-xs text-muted-foreground mb-1">Texto extraído:</p>
            <p className="text-xs font-mono text-foreground/70 whitespace-pre-wrap">
              {extractedText.substring(0, 200)}...
            </p>
          </div>
        )}
      </div>
    );
  }

  return (
    <div className="p-4 space-y-3">
      <h3 className="text-lg font-semibold text-foreground">
        Cartas Encontradas ({cards.length})
      </h3>
      <p className="text-sm text-muted-foreground">
        Selecione a carta correta:
      </p>

      <div className="space-y-2">
        {cards.map((card) => (
          <Card
            key={card.id}
            className="cursor-pointer hover:border-primary transition-colors"
            onClick={() => onSelect(card)}
          >
            <CardContent className="p-3 flex items-center gap-3">
              {/* Thumbnail */}
              <div className="w-12 h-16 rounded overflow-hidden bg-muted flex-shrink-0">
                <img
                  src={card.imageUrl}
                  alt={card.name}
                  className="w-full h-full object-cover"
                />
              </div>

              {/* Info */}
              <div className="flex-1 min-w-0">
                <p className="font-medium text-foreground truncate">{card.name}</p>
                <p className="text-xs text-muted-foreground truncate">
                  {card.setName} • #{card.number}
                </p>
                <div className="flex gap-1 mt-1">
                  <Badge
                    variant="outline"
                    className={
                      card.game === 'mtg'
                        ? 'bg-chart-4/20 text-chart-4 border-chart-4/30 text-xs'
                        : 'bg-chart-2/20 text-chart-2 border-chart-2/30 text-xs'
                    }
                  >
                    {card.game === 'mtg' ? 'MTG' : 'PKM'}
                  </Badge>
                  <Badge variant="secondary" className="text-xs">
                    {card.rarity}
                  </Badge>
                </div>
              </div>

              {/* Arrow */}
              <ChevronRight className="h-5 w-5 text-muted-foreground flex-shrink-0" />
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}

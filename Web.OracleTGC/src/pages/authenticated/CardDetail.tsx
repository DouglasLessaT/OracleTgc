import { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { mockCards } from '@/data/mockData';
import { useAuth } from '@/contexts/AuthContext';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { 
  ArrowLeft, 
  Sparkles, 
  Share2, 
  Plus,
  Crown,
  DollarSign,
  Bitcoin
} from 'lucide-react';

export default function CardDetail() {
  const { id } = useParams();
  const { user } = useAuth();
  const [isFoil, setIsFoil] = useState(false);
  
  const card = mockCards.find(c => c.id === id);
  const isPremium = user?.plan === 'premium';
  
  // Mock exchange rates
  const rates = { brl: 5.0, btc: 0.000015 };

  if (!card) {
    return (
      <div className="min-h-0 p-4 sm:p-6">
        <Card>
          <CardContent className="py-12 text-center">
            <h2 className="text-xl font-semibold">Carta não encontrada</h2>
            <Link to="/collections" className="text-primary hover:underline mt-2 inline-block">
              Voltar para coleções
            </Link>
          </CardContent>
        </Card>
      </div>
    );
  }

  const currentPrice = isFoil && card.prices.usdFoil ? card.prices.usdFoil : card.prices.usd;
  const hasFoilPrice = card.prices.usdFoil !== null;

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

  return (
    <div className="min-h-0 p-4 sm:p-6 space-y-4 sm:space-y-6 pb-8">
      {/* Header */}
      <div className="flex items-center gap-4">
        <Link to="/collections">
          <Button variant="ghost" size="icon">
            <ArrowLeft className="h-5 w-5" />
          </Button>
        </Link>
        <div className="flex-1">
          <h1 className="text-2xl font-bold">{card.name}</h1>
          <p className="text-muted-foreground">{card.setName}</p>
        </div>
        <div className="flex gap-2">
          {isPremium && (
            <Button variant="outline" size="icon">
              <Share2 className="h-4 w-4" />
            </Button>
          )}
          <Dialog>
            <DialogTrigger asChild>
              <Button>
                <Plus className="h-4 w-4 mr-2" />
                Adicionar à Coleção
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Adicionar à Coleção</DialogTitle>
                <DialogDescription>
                  Escolha em qual coleção adicionar esta carta
                </DialogDescription>
              </DialogHeader>
              <div className="py-4 space-y-2">
                <Button variant="outline" className="w-full justify-start">
                  All Cards - {card.game === 'mtg' ? 'Magic' : 'Pokémon'}
                </Button>
                {isPremium && (
                  <Button variant="outline" className="w-full justify-start">
                    <Plus className="h-4 w-4 mr-2" />
                    Criar nova coleção
                  </Button>
                )}
              </div>
              {!isPremium && (
                <Card className="bg-gradient-to-r from-primary/10 to-primary/5 border-primary/20">
                  <CardContent className="flex items-center gap-3 p-3">
                    <Crown className="h-5 w-5 text-primary" />
                    <div className="text-sm">
                      <p className="font-medium">Mais coleções</p>
                      <p className="text-muted-foreground">
                        Upgrade para criar coleções personalizadas
                      </p>
                    </div>
                  </CardContent>
                </Card>
              )}
            </DialogContent>
          </Dialog>
        </div>
      </div>

      {/* Content */}
      <div className="grid md:grid-cols-2 gap-8">
        {/* Image */}
        <div className="relative">
          <div className="aspect-[2.5/3.5] rounded-xl overflow-hidden border-2 border-border shadow-xl">
            <img
              src={card.imageUrl}
              alt={card.name}
              className="w-full h-full object-contain bg-card"
            />
            {isFoil && (
              <div className="absolute inset-0 bg-gradient-to-br from-chart-1/30 via-transparent to-chart-3/30 pointer-events-none animate-pulse" />
            )}
          </div>
          {card.isFoil && (
            <div className="absolute top-4 right-4">
              <Badge variant="secondary" className="gap-1">
                <Sparkles className="h-3 w-3" />
                Foil
              </Badge>
            </div>
          )}
        </div>

        {/* Info */}
        <div className="space-y-6">
          <Card>
            <CardContent className="p-6 space-y-4">
              {/* Badges */}
              <div className="flex flex-wrap gap-2">
                <Badge variant={card.game === 'mtg' ? 'default' : 'secondary'}>
                  {card.game === 'mtg' ? 'Magic: The Gathering' : 'Pokémon TCG'}
                </Badge>
                <Badge variant="outline" className={getRarityColor(card.rarity)}>
                  {card.rarity}
                </Badge>
                {card.type && (
                  <Badge variant="outline">{card.type}</Badge>
                )}
              </div>

              {/* Set Info */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <p className="text-sm text-muted-foreground">Edição</p>
                  <p className="font-medium">{card.setName}</p>
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">Número</p>
                  <p className="font-medium">{card.set} #{card.number}</p>
                </div>
              </div>

              {/* Foil Toggle */}
              {hasFoilPrice && (
                <div className="flex items-center justify-between p-3 bg-accent/50 rounded-lg">
                  <div className="flex items-center gap-2">
                    <Sparkles className="h-4 w-4 text-accent-foreground" />
                    <Label htmlFor="foil-toggle">Versão Foil</Label>
                  </div>
                  <Switch
                    id="foil-toggle"
                    checked={isFoil}
                    onCheckedChange={setIsFoil}
                  />
                </div>
              )}

              {/* Prices */}
              <div className="space-y-3">
                <h3 className="font-semibold">
                  Preço de Mercado {isFoil && '(Foil)'}
                </h3>
                
                {currentPrice ? (
                  <div className="space-y-2">
                    <div className="flex items-center justify-between p-3 bg-muted rounded-lg">
                      <div className="flex items-center gap-2">
                        <DollarSign className="h-4 w-4 text-green-500" />
                        <span className="text-sm">USD</span>
                      </div>
                      <span className="text-xl font-bold">${currentPrice.toFixed(2)}</span>
                    </div>
                    
                    <div className="flex items-center justify-between p-3 bg-muted rounded-lg">
                      <div className="flex items-center gap-2">
                        <span className="text-sm font-medium text-green-500">R$</span>
                        <span className="text-sm">BRL</span>
                      </div>
                      <span className="text-lg font-semibold">
                        R$ {(currentPrice * rates.brl).toFixed(2)}
                      </span>
                    </div>
                    
                    <div className="flex items-center justify-between p-3 bg-muted rounded-lg">
                      <div className="flex items-center gap-2">
                        <Bitcoin className="h-4 w-4 text-orange-500" />
                        <span className="text-sm">BTC</span>
                      </div>
                      <span className="text-lg font-semibold">
                        ₿ {(currentPrice * rates.btc).toFixed(8)}
                      </span>
                    </div>
                  </div>
                ) : (
                  <p className="text-muted-foreground">Preço não disponível</p>
                )}
              </div>

              {/* Artist */}
              {card.artist && (
                <p className="text-xs text-muted-foreground text-center pt-4 border-t">
                  Ilustração: {card.artist}
                </p>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}

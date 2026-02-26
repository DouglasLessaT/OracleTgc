import { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { mockCollections, mockCards } from '@/data/mockData';
import { useAuth } from '@/contexts/AuthContext';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { 
  ArrowLeft, 
  Search, 
  LayoutGrid, 
  List, 
  Share2,
  Settings,
  Plus,
  Sparkles,
  TrendingUp,
  Crown,
  Globe,
  Lock
} from 'lucide-react';

type ViewMode = 'grid' | 'list' | 'icons';
type SortBy = 'name' | 'price' | 'rarity' | 'date';

export default function CollectionDetail() {
  const { id } = useParams();
  const { user } = useAuth();
  const [viewMode, setViewMode] = useState<ViewMode>('grid');
  const [searchQuery, setSearchQuery] = useState('');
  const [sortBy, setSortBy] = useState<SortBy>('name');
  
  const collection = mockCollections.find(c => c.id === id);
  const isPremium = user?.plan === 'premium';
  
  // Mock cards for this collection
  const collectionCards = mockCards.filter(card => 
    collection?.game === card.game
  );

  const filteredCards = collectionCards.filter(card =>
    card.name.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const sortedCards = [...filteredCards].sort((a, b) => {
    switch (sortBy) {
      case 'name':
        return a.name.localeCompare(b.name);
      case 'price':
        return (b.prices.usd || 0) - (a.prices.usd || 0);
      case 'rarity':
        return a.rarity.localeCompare(b.rarity);
      default:
        return 0;
    }
  });

  if (!collection) {
    return (
      <div className="min-h-0 p-4 sm:p-6">
        <Card>
          <CardContent className="py-12 text-center">
            <h2 className="text-xl font-semibold">Coleção não encontrada</h2>
            <Link to="/collections" className="text-primary hover:underline mt-2 inline-block">
              Voltar para coleções
            </Link>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="min-h-0 p-4 sm:p-6 space-y-4 sm:space-y-6 pb-8">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-start gap-4">
        <Link to="/collections">
          <Button variant="ghost" size="icon">
            <ArrowLeft className="h-5 w-5" />
          </Button>
        </Link>
        
        <div className="flex-1">
          <div className="flex items-center gap-3">
            <h1 className="text-2xl font-bold">{collection.name}</h1>
            {collection.isDeck && (
              <Badge variant="secondary">Deck</Badge>
            )}
            {collection.isPublic ? (
              <Badge variant="outline">
                <Globe className="h-3 w-3 mr-1" />
                Público
              </Badge>
            ) : (
              <Badge variant="outline">
                <Lock className="h-3 w-3 mr-1" />
                Privado
              </Badge>
            )}
          </div>
          {collection.description && (
            <p className="text-muted-foreground mt-1">{collection.description}</p>
          )}
        </div>
        
        <div className="flex gap-2">
          {isPremium && (
            <Button variant="outline" size="icon">
              <Share2 className="h-4 w-4" />
            </Button>
          )}
          <Button variant="outline" size="icon">
            <Settings className="h-4 w-4" />
          </Button>
          <Button>
            <Plus className="h-4 w-4 mr-2" />
            Adicionar Carta
          </Button>
        </div>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="p-4">
            <p className="text-sm text-muted-foreground">Total de Cartas</p>
            <p className="text-2xl font-bold">{collection.cardsCount}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <p className="text-sm text-muted-foreground">Valor Total</p>
            <p className="text-2xl font-bold">${collection.totalValue.toFixed(2)}</p>
          </CardContent>
        </Card>
        {isPremium && (
          <>
            <Card>
              <CardContent className="p-4">
                <p className="text-sm text-muted-foreground flex items-center gap-1">
                  <TrendingUp className="h-4 w-4" />
                  Valorização
                </p>
                <p className="text-2xl font-bold text-green-500">+12.5%</p>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="p-4">
                <p className="text-sm text-muted-foreground flex items-center gap-1">
                  <Sparkles className="h-4 w-4" />
                  Épicas/Raras
                </p>
                <p className="text-2xl font-bold">23</p>
              </CardContent>
            </Card>
          </>
        )}
      </div>

      {!isPremium && (
        <Card className="bg-gradient-to-r from-primary/10 to-primary/5 border-primary/20">
          <CardContent className="flex items-center gap-4 p-4">
            <Crown className="h-8 w-8 text-primary" />
            <div className="flex-1">
              <p className="font-semibold">Estatísticas Premium</p>
              <p className="text-sm text-muted-foreground">
                Veja valorização e mais estatísticas avançadas
              </p>
            </div>
            <Button size="sm">Upgrade</Button>
          </CardContent>
        </Card>
      )}

      {/* Filters */}
      <div className="flex flex-col md:flex-row gap-4">
        <div className="flex-1">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input
              placeholder="Buscar cartas..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10"
            />
          </div>
        </div>
        
        <Select value={sortBy} onValueChange={(v) => setSortBy(v as SortBy)}>
          <SelectTrigger className="w-[180px]">
            <SelectValue placeholder="Ordenar por" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="name">Nome</SelectItem>
            <SelectItem value="price">Preço</SelectItem>
            <SelectItem value="rarity">Raridade</SelectItem>
            <SelectItem value="date">Data Adição</SelectItem>
          </SelectContent>
        </Select>
        
        <div className="flex gap-1 border rounded-md p-1">
          <Button
            variant={viewMode === 'grid' ? 'secondary' : 'ghost'}
            size="icon"
            onClick={() => setViewMode('grid')}
          >
            <LayoutGrid className="h-4 w-4" />
          </Button>
          <Button
            variant={viewMode === 'list' ? 'secondary' : 'ghost'}
            size="icon"
            onClick={() => setViewMode('list')}
          >
            <List className="h-4 w-4" />
          </Button>
        </div>
      </div>

      {/* Cards Grid */}
      {viewMode === 'grid' ? (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
          {sortedCards.map((card) => (
            <Link key={card.id} to={`/cards/${card.id}`}>
              <Card className="group hover:border-primary transition-all overflow-hidden">
                <div className="aspect-[2.5/3.5] relative">
                  <img
                    src={card.imageUrl}
                    alt={card.name}
                    className="w-full h-full object-cover"
                  />
                  {card.isFoil && (
                    <div className="absolute top-2 right-2">
                      <Sparkles className="h-4 w-4 text-primary" />
                    </div>
                  )}
                  <div className="absolute inset-0 bg-gradient-to-t from-background/90 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                    <div className="absolute bottom-2 left-2 right-2">
                      <p className="text-sm font-medium truncate">{card.name}</p>
                      <p className="text-xs text-muted-foreground">
                        ${card.prices.usd?.toFixed(2) || 'N/A'}
                      </p>
                    </div>
                  </div>
                </div>
              </Card>
            </Link>
          ))}
        </div>
      ) : (
        <div className="space-y-2">
          {sortedCards.map((card) => (
            <Link key={card.id} to={`/cards/${card.id}`}>
              <Card className="hover:border-primary transition-all">
                <CardContent className="flex items-center gap-4 p-3">
                  <div className="h-16 w-12 rounded overflow-hidden bg-muted flex-shrink-0">
                    <img
                      src={card.imageUrl}
                      alt={card.name}
                      className="w-full h-full object-cover"
                    />
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2">
                      <p className="font-medium truncate">{card.name}</p>
                      {card.isFoil && <Sparkles className="h-4 w-4 text-primary" />}
                    </div>
                    <p className="text-sm text-muted-foreground">
                      {card.setName} • #{card.number}
                    </p>
                  </div>
                  <div className="text-right">
                    <Badge variant="outline">{card.rarity}</Badge>
                    <p className="text-sm font-medium mt-1">
                      ${card.prices.usd?.toFixed(2) || 'N/A'}
                    </p>
                  </div>
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}

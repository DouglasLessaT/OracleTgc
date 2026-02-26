import { useState } from 'react';
import { mockListings, mockCards, mockUsers } from '@/data/mockData';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { 
  Search, 
  ShoppingCart,
  Package,
  Crown,
  Star,
  Sparkles,
  TrendingUp
} from 'lucide-react';
import { Link } from 'react-router-dom';

type ListingTab = 'cards' | 'collections' | 'sellers';
type SortBy = 'recent' | 'price-low' | 'price-high';

const conditionLabels: Record<string, string> = {
  'mint': 'Mint',
  'near-mint': 'Near Mint',
  'excellent': 'Excelente',
  'good': 'Bom',
  'played': 'Jogado',
};

export default function Marketplace() {
  const [activeTab, setActiveTab] = useState<ListingTab>('cards');
  const [searchQuery, setSearchQuery] = useState('');
  const [sortBy, setSortBy] = useState<SortBy>('recent');
  const [selectedGame, setSelectedGame] = useState<'all' | 'mtg' | 'pokemon'>('all');

  const filteredListings = mockListings.filter(listing => {
    const matchesSearch = listing.card.name.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesGame = selectedGame === 'all' || listing.card.game === selectedGame;
    return matchesSearch && matchesGame;
  });

  const sortedListings = [...filteredListings].sort((a, b) => {
    switch (sortBy) {
      case 'price-low':
        return a.price - b.price;
      case 'price-high':
        return b.price - a.price;
      default:
        return new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime();
    }
  });

  return (
    <div className="min-h-0 p-4 sm:p-6 space-y-4 sm:space-y-6 pb-8">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold flex items-center gap-2">
            <ShoppingCart className="h-6 w-6" />
            Marketplace
          </h1>
          <p className="text-muted-foreground">
            Compre e venda cartas com outros colecionadores
          </p>
        </div>
        
        <Button>
          <Package className="h-4 w-4 mr-2" />
          Vender Carta
        </Button>
      </div>

      {/* Featured Sellers */}
      <Card>
        <CardHeader>
          <CardTitle className="text-lg flex items-center gap-2">
            <Crown className="h-5 w-5 text-primary" />
            Vendedores em Destaque
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex gap-4 overflow-x-auto pb-2">
            {mockUsers.map((seller) => (
              <div 
                key={seller.id}
                className="flex-shrink-0 flex items-center gap-3 p-3 rounded-lg border border-border hover:border-primary transition-all cursor-pointer"
              >
                <Avatar>
                  <AvatarImage src={seller.avatarUrl} />
                  <AvatarFallback>{seller.username[0]}</AvatarFallback>
                </Avatar>
                <div>
                  <div className="flex items-center gap-1">
                    <p className="font-medium">{seller.username}</p>
                    {seller.plan === 'premium' && (
                      <Crown className="h-3 w-3 text-primary" />
                    )}
                  </div>
                  <p className="text-xs text-muted-foreground">
                    {seller.cardsCount} cartas
                  </p>
                </div>
                <div className="flex items-center gap-1 text-yellow-500">
                  <Star className="h-4 w-4 fill-current" />
                  <span className="text-sm">4.9</span>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Tabs */}
      <Tabs value={activeTab} onValueChange={(v) => setActiveTab(v as ListingTab)}>
        <TabsList>
          <TabsTrigger value="cards">Cartas</TabsTrigger>
          <TabsTrigger value="collections">Coleções</TabsTrigger>
          <TabsTrigger value="sellers">Vendedores</TabsTrigger>
        </TabsList>
        
        <TabsContent value="cards" className="mt-4 space-y-4">
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
            
            <Select value={selectedGame} onValueChange={(v) => setSelectedGame(v as typeof selectedGame)}>
              <SelectTrigger className="w-[150px]">
                <SelectValue placeholder="Jogo" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Todos</SelectItem>
                <SelectItem value="mtg">MTG</SelectItem>
                <SelectItem value="pokemon">Pokémon</SelectItem>
              </SelectContent>
            </Select>
            
            <Select value={sortBy} onValueChange={(v) => setSortBy(v as SortBy)}>
              <SelectTrigger className="w-[150px]">
                <SelectValue placeholder="Ordenar" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="recent">Mais Recentes</SelectItem>
                <SelectItem value="price-low">Menor Preço</SelectItem>
                <SelectItem value="price-high">Maior Preço</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {/* Listings Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {sortedListings.map((listing) => (
              <Card key={listing.id} className="hover:border-primary transition-all">
                <CardContent className="p-0">
                  <div className="aspect-[2.5/3] relative overflow-hidden rounded-t-lg bg-muted">
                    <img
                      src={listing.card.imageUrl}
                      alt={listing.card.name}
                      className="w-full h-full object-contain"
                    />
                    {listing.card.isFoil && (
                      <div className="absolute top-2 right-2">
                        <Badge variant="secondary" className="gap-1">
                          <Sparkles className="h-3 w-3" />
                          Foil
                        </Badge>
                      </div>
                    )}
                    <div className="absolute bottom-2 left-2">
                      <Badge variant={listing.card.game === 'mtg' ? 'default' : 'secondary'}>
                        {listing.card.game === 'mtg' ? 'MTG' : 'PKM'}
                      </Badge>
                    </div>
                  </div>
                  
                  <div className="p-4 space-y-3">
                    <div>
                      <h3 className="font-semibold truncate">{listing.card.name}</h3>
                      <p className="text-sm text-muted-foreground">
                        {listing.card.setName}
                      </p>
                    </div>
                    
                    <div className="flex items-center justify-between">
                      <Badge variant="outline">
                        {conditionLabels[listing.condition]}
                      </Badge>
                      <span className="text-sm text-muted-foreground">
                        Qtd: {listing.quantity}
                      </span>
                    </div>
                    
                    <div className="flex items-center gap-2">
                      <Avatar className="h-6 w-6">
                        <AvatarImage src={listing.seller.avatarUrl} />
                        <AvatarFallback>{listing.seller.username[0]}</AvatarFallback>
                      </Avatar>
                      <span className="text-sm">{listing.seller.username}</span>
                    </div>
                    
                    <div className="flex items-center justify-between pt-2 border-t">
                      <div>
                        <p className="text-2xl font-bold">
                          ${listing.price.toLocaleString('en-US')}
                        </p>
                        {listing.card.prices.usd && listing.price < listing.card.prices.usd && (
                          <p className="text-xs text-primary flex items-center gap-1">
                            <TrendingUp className="h-3 w-3" />
                            Abaixo do mercado
                          </p>
                        )}
                      </div>
                      <Button size="sm">Comprar</Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
          
          {sortedListings.length === 0 && (
            <Card>
              <CardContent className="py-12 text-center">
                <ShoppingCart className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                <h3 className="font-semibold text-lg">Nenhum anúncio encontrado</h3>
                <p className="text-muted-foreground">
                  Tente buscar por outros termos
                </p>
              </CardContent>
            </Card>
          )}
        </TabsContent>
        
        <TabsContent value="collections" className="mt-4">
          <Card>
            <CardContent className="py-12 text-center">
              <Package className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
              <h3 className="font-semibold text-lg">Coleções à Venda</h3>
              <p className="text-muted-foreground">
                Em breve você poderá comprar e vender coleções completas
              </p>
            </CardContent>
          </Card>
        </TabsContent>
        
        <TabsContent value="sellers" className="mt-4">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {mockUsers.map((seller) => (
              <Card key={seller.id} className="hover:border-primary transition-all">
                <CardContent className="p-6">
                  <div className="flex items-center gap-4 mb-4">
                    <Avatar className="h-16 w-16">
                      <AvatarImage src={seller.avatarUrl} />
                      <AvatarFallback>{seller.username[0]}</AvatarFallback>
                    </Avatar>
                    <div>
                      <div className="flex items-center gap-2">
                        <h3 className="font-semibold">{seller.username}</h3>
                        {seller.plan === 'premium' && (
                          <Crown className="h-4 w-4 text-primary" />
                        )}
                      </div>
                      <div className="flex items-center gap-1 text-yellow-500">
                        <Star className="h-4 w-4 fill-current" />
                        <Star className="h-4 w-4 fill-current" />
                        <Star className="h-4 w-4 fill-current" />
                        <Star className="h-4 w-4 fill-current" />
                        <Star className="h-4 w-4 fill-current" />
                        <span className="text-sm text-muted-foreground ml-1">5.0</span>
                      </div>
                    </div>
                  </div>
                  
                  <div className="grid grid-cols-3 gap-2 text-center mb-4">
                    <div>
                      <p className="font-bold">{seller.cardsCount}</p>
                      <p className="text-xs text-muted-foreground">Cartas</p>
                    </div>
                    <div>
                      <p className="font-bold">{seller.collectionsCount}</p>
                      <p className="text-xs text-muted-foreground">Coleções</p>
                    </div>
                    <div>
                      <p className="font-bold">{seller.followersCount}</p>
                      <p className="text-xs text-muted-foreground">Seguidores</p>
                    </div>
                  </div>
                  
                  <Button variant="outline" className="w-full">
                    Ver Perfil
                  </Button>
                </CardContent>
              </Card>
            ))}
          </div>
        </TabsContent>
      </Tabs>
    </div>
  );
}

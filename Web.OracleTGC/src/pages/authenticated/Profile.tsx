import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useAuth } from '@/contexts/AuthContext';
import { getCollections } from '@/services/collectionsApiService';
import { getInventory, getInventoryStatistics } from '@/services/inventoryApiService';
import type { ApiCollection } from '@/services/inventoryApiService';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Skeleton } from '@/components/ui/skeleton';
import {
  Settings,
  Crown,
  Users,
  FolderOpen,
  Sparkles,
  TrendingUp,
  Share2,
} from 'lucide-react';
import { Link } from 'react-router-dom';

export default function Profile() {
  const { user } = useAuth();
  const [activeTab, setActiveTab] = useState('collections');

  const { data: collections = [], isLoading: loadingCollections } = useQuery({
    queryKey: ['collections'],
    queryFn: getCollections,
  });

  const { data: inventory, isLoading: loadingInventory } = useQuery({
    queryKey: ['inventory'],
    queryFn: getInventory,
  });

  const { data: stats } = useQuery({
    queryKey: ['inventory', 'statistics'],
    queryFn: getInventoryStatistics,
  });

  const isPremium = user?.plan === 'premium';
  const isLoading = loadingCollections || loadingInventory;
  const totalCards = stats?.totalCards ?? user?.cardsCount ?? 0;
  const totalValue = inventory?.items?.reduce((acc, item) => acc + (item.totalValue ?? 0), 0) ?? 0;
  const decksCount = collections.filter((c) => c.isGameDeck).length;
  const gameLabel = (game: string) =>
    game === 'mtg' ? 'MTG' : game === 'pokemon' ? 'PKM' : game === 'onepiece' ? 'OP' : game;

  return (
    <div className="min-h-0 p-4 sm:p-6 space-y-4 sm:space-y-6 pb-8">
      {/* Profile Header */}
      <Card>
        <CardContent className="p-6">
          <div className="flex flex-col md:flex-row items-center gap-6">
            <Avatar className="h-24 w-24">
              <AvatarImage src={user?.avatarUrl} />
              <AvatarFallback className="text-2xl">
                {user?.username?.[0] || 'U'}
              </AvatarFallback>
            </Avatar>
            
            <div className="flex-1 text-center md:text-left">
              <div className="flex flex-col md:flex-row md:items-center gap-2">
                <h1 className="text-2xl font-bold">{user?.username}</h1>
                <Badge variant={isPremium ? 'default' : 'secondary'} className="w-fit mx-auto md:mx-0">
                  {isPremium && <Crown className="h-3 w-3 mr-1" />}
                  {isPremium ? 'Premium' : 'Free'}
                </Badge>
              </div>
              <p className="text-muted-foreground">{user?.email}</p>
              <p className="text-sm text-muted-foreground">
                Membro desde {new Date(user?.createdAt || '').toLocaleDateString('pt-BR')}
              </p>
            </div>
            
            <div className="flex gap-2">
              <Button variant="outline" size="icon" asChild>
                <Link to="/settings">
                  <Settings className="h-4 w-4" />
                </Link>
              </Button>
              {isPremium && (
                <Button variant="outline">
                  <Share2 className="h-4 w-4 mr-2" />
                  Compartilhar Perfil
                </Button>
              )}
            </div>
          </div>
          
          {/* Stats */}
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t">
            <div className="text-center">
              <p className="text-2xl font-bold">{user?.followersCount ?? 0}</p>
              <p className="text-sm text-muted-foreground">Seguidores</p>
            </div>
            <div className="text-center">
              <p className="text-2xl font-bold">{user?.followingCount ?? 0}</p>
              <p className="text-sm text-muted-foreground">Seguindo</p>
            </div>
            <div className="text-center">
              {isLoading ? (
                <Skeleton className="h-8 w-12 mx-auto mb-1" />
              ) : (
                <p className="text-2xl font-bold">{collections.length}</p>
              )}
              <p className="text-sm text-muted-foreground">Coleções</p>
            </div>
            <div className="text-center">
              {isLoading ? (
                <Skeleton className="h-8 w-12 mx-auto mb-1" />
              ) : (
                <p className="text-2xl font-bold">{totalCards}</p>
              )}
              <p className="text-sm text-muted-foreground">Cartas</p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Premium Upgrade Banner */}
      {!isPremium && (
        <Card className="bg-gradient-to-r from-primary/10 to-primary/5 border-primary/20">
          <CardContent className="flex items-center gap-4 p-4">
            <Crown className="h-8 w-8 text-primary" />
            <div className="flex-1">
              <p className="font-semibold">Upgrade para Premium</p>
              <p className="text-sm text-muted-foreground">
                Compartilhe seu perfil, crie coleções ilimitadas e mais!
              </p>
            </div>
            <Button>Fazer Upgrade</Button>
          </CardContent>
        </Card>
      )}

      {/* Portfolio Overview (Premium) */}
      {isPremium && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                <TrendingUp className="h-4 w-4" />
                Valor Total
              </CardTitle>
            </CardHeader>
            <CardContent>
              {isLoading ? (
                <Skeleton className="h-8 w-24 mb-2" />
              ) : (
                <p className="text-2xl font-bold">${totalValue.toLocaleString('en-US', { minimumFractionDigits: 2 })}</p>
              )}
              <p className="text-sm text-muted-foreground">valor de mercado</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                <Sparkles className="h-4 w-4" />
                Cartas Únicas
              </CardTitle>
            </CardHeader>
            <CardContent>
              {isLoading ? (
                <Skeleton className="h-8 w-16 mb-2" />
              ) : (
                <p className="text-2xl font-bold">{stats?.uniqueCards ?? '—'}</p>
              )}
              <p className="text-sm text-muted-foreground">no inventário</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                <FolderOpen className="h-4 w-4" />
                Decks
              </CardTitle>
            </CardHeader>
            <CardContent>
              {isLoading ? (
                <Skeleton className="h-8 w-16 mb-2" />
              ) : (
                <p className="text-2xl font-bold">{decksCount}</p>
              )}
              <p className="text-sm text-muted-foreground">marcados como deck</p>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Tabs */}
      <Tabs value={activeTab} onValueChange={setActiveTab}>
        <TabsList>
          <TabsTrigger value="collections">
            <FolderOpen className="h-4 w-4 mr-2" />
            Coleções
          </TabsTrigger>
          <TabsTrigger value="followers">
            <Users className="h-4 w-4 mr-2" />
            Seguidores
          </TabsTrigger>
        </TabsList>
        
        <TabsContent value="collections" className="mt-4">
          {isLoading ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {[1, 2, 3, 4, 5, 6].map((i) => (
                <Card key={i}>
                  <CardContent className="p-0">
                    <Skeleton className="aspect-video rounded-t-lg" />
                    <div className="p-4 space-y-2">
                      <Skeleton className="h-5 w-3/4" />
                      <Skeleton className="h-4 w-1/2" />
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          ) : collections.length === 0 ? (
            <Card>
              <CardContent className="py-12 text-center">
                <FolderOpen className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                <h3 className="font-semibold text-lg">Nenhuma coleção</h3>
                <p className="text-muted-foreground">
                  Suas coleções aparecerão aqui. Crie uma em Minhas Coleções.
                </p>
                <Button variant="outline" className="mt-4" asChild>
                  <Link to="/collections">Ir para Coleções</Link>
                </Button>
              </CardContent>
            </Card>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {collections.map((collection) => (
                <Link key={collection.id} to={`/collections/${collection.id}`}>
                  <Card className="hover:border-primary transition-all h-full">
                    <CardContent className="p-0">
                      <div className="aspect-video relative overflow-hidden rounded-t-lg bg-muted">
                        {collection.cards?.[0]?.imageUrl ? (
                          <img
                            src={collection.cards[0].imageUrl}
                            alt=""
                            className="w-full h-full object-cover"
                          />
                        ) : null}
                        <div className="absolute bottom-2 left-2">
                          <Badge variant={collection.game === 'mtg' ? 'default' : 'secondary'}>
                            {gameLabel(collection.game)}
                          </Badge>
                        </div>
                      </div>
                      <div className="p-4">
                        <h3 className="font-semibold truncate">{collection.name}</h3>
                        <p className="text-sm text-muted-foreground">
                          {collection.cardCount} cartas
                        </p>
                      </div>
                    </CardContent>
                  </Card>
                </Link>
              ))}
            </div>
          )}
        </TabsContent>
        
        <TabsContent value="followers" className="mt-4">
          <Card>
            <CardContent className="py-12 text-center">
              <Users className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
              <h3 className="font-semibold text-lg">Seguidores</h3>
              <p className="text-muted-foreground">
                {isPremium 
                  ? 'Seus seguidores aparecerão aqui'
                  : 'Faça upgrade para ver e gerenciar seguidores'
                }
              </p>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}

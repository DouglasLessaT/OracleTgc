import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useAuth } from '@/contexts/AuthContext';
import {
  getCollections,
  createCollection,
  type ApiCollection,
  type CreateCollectionPayload,
} from '@/services/collectionsApiService';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { useToast } from '@/hooks/use-toast';
import {
  Plus,
  Search,
  FolderOpen,
  Crown,
  LayoutGrid,
  List,
  Loader2,
} from 'lucide-react';

type ViewMode = 'grid' | 'list';

export default function Collections() {
  const { user } = useAuth();
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const [searchQuery, setSearchQuery] = useState('');
  const [viewMode, setViewMode] = useState<ViewMode>('grid');
  const [selectedGame, setSelectedGame] = useState<'all' | 'mtg' | 'pokemon' | 'onepiece'>('all');
  const [createOpen, setCreateOpen] = useState(false);
  const [newName, setNewName] = useState('');
  const [newDescription, setNewDescription] = useState('');
  const [newGame, setNewGame] = useState<string>('mtg');

  const isPremium = user?.plan === 'premium';

  const { data: collections = [], isLoading } = useQuery({
    queryKey: ['collections'],
    queryFn: getCollections,
  });

  const createMutation = useMutation({
    mutationFn: (payload: CreateCollectionPayload) => createCollection(payload),
    onSuccess: (data) => {
      if (data) {
        queryClient.invalidateQueries({ queryKey: ['collections'] });
        setCreateOpen(false);
        setNewName('');
        setNewDescription('');
        setNewGame('mtg');
        toast({ title: 'Coleção criada', description: data.name });
      } else {
        toast({ variant: 'destructive', title: 'Erro', description: 'Não foi possível criar a coleção.' });
      }
    },
    onError: () => {
      toast({ variant: 'destructive', title: 'Erro', description: 'Não foi possível criar a coleção.' });
    },
  });

  const filteredCollections = collections.filter((collection) => {
    const matchesSearch = collection.name.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesGame = selectedGame === 'all' || collection.game === selectedGame;
    return matchesSearch && matchesGame;
  });

  const handleCreate = () => {
    const name = newName.trim();
    if (!name) {
      toast({ variant: 'destructive', title: 'Nome obrigatório', description: 'Informe o nome da coleção.' });
      return;
    }
    createMutation.mutate({
      name,
      game: newGame,
      ...(newDescription.trim() && { description: newDescription.trim() }),
    });
  };

  const gameLabel = (game: string) =>
    game === 'mtg' ? 'MTG' : game === 'pokemon' ? 'Pokémon' : game === 'onepiece' ? 'One Piece' : game;

  const CollectionCard = ({ collection }: { collection: ApiCollection }) => (
    <Link to={`/collections/${collection.id}`}>
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
            <div className="absolute top-2 right-2 flex gap-1">
              {collection.isGameDeck && (
                <Badge variant="secondary" className="text-xs">
                  Deck
                </Badge>
              )}
            </div>
            <div className="absolute bottom-2 left-2">
              <Badge variant={collection.game === 'mtg' ? 'default' : 'secondary'}>
                {gameLabel(collection.game)}
              </Badge>
            </div>
          </div>
          <div className="p-4 space-y-2">
            <h3 className="font-semibold truncate">{collection.name}</h3>
            {collection.description && (
              <p className="text-sm text-muted-foreground line-clamp-2">{collection.description}</p>
            )}
            <div className="flex items-center justify-between text-sm text-muted-foreground">
              <span>{collection.cardCount} cartas</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </Link>
  );

  const CollectionListItem = ({ collection }: { collection: ApiCollection }) => (
    <Link to={`/collections/${collection.id}`}>
      <Card className="hover:border-primary transition-all">
        <CardContent className="flex items-center gap-4 p-4">
          <div className="h-16 w-16 rounded-lg overflow-hidden bg-muted flex-shrink-0">
            {collection.cards?.[0]?.imageUrl ? (
              <img
                src={collection.cards[0].imageUrl}
                alt=""
                className="w-full h-full object-cover"
              />
            ) : null}
          </div>
          <div className="flex-1 min-w-0">
            <div className="flex items-center gap-2">
              <h3 className="font-semibold truncate">{collection.name}</h3>
              {collection.isGameDeck && (
                <Badge variant="secondary" className="text-xs">
                  Deck
                </Badge>
              )}
            </div>
            <p className="text-sm text-muted-foreground">{collection.cardCount} cartas</p>
          </div>
          <Badge variant={collection.game === 'mtg' ? 'default' : 'secondary'} className="shrink-0">
            {gameLabel(collection.game)}
          </Badge>
        </CardContent>
      </Card>
    </Link>
  );

  return (
    <div className="min-h-0 p-4 sm:p-6 space-y-4 sm:space-y-6 pb-8">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold">Minhas Coleções</h1>
          <p className="text-muted-foreground">
            Gerencie suas cartas e decks
          </p>
        </div>
        
        {isPremium ? (
          <Dialog open={createOpen} onOpenChange={setCreateOpen}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="h-4 w-4 mr-2" />
                Nova Coleção
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Criar Nova Coleção</DialogTitle>
                <DialogDescription>
                  Crie uma coleção personalizada para organizar suas cartas
                </DialogDescription>
              </DialogHeader>
              <div className="space-y-4 py-4">
                <div className="space-y-2">
                  <Label>Nome da Coleção</Label>
                  <Input
                    placeholder="Ex: Deck Modern Burn"
                    value={newName}
                    onChange={(e) => setNewName(e.target.value)}
                  />
                </div>
                <div className="space-y-2">
                  <Label>Jogo</Label>
                  <Select value={newGame} onValueChange={setNewGame}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="mtg">Magic: The Gathering</SelectItem>
                      <SelectItem value="pokemon">Pokémon TCG</SelectItem>
                      <SelectItem value="onepiece">One Piece</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label>Descrição (opcional)</Label>
                  <Input
                    placeholder="Descrição da coleção..."
                    value={newDescription}
                    onChange={(e) => setNewDescription(e.target.value)}
                  />
                </div>
              </div>
              <DialogFooter>
                <Button onClick={handleCreate} disabled={createMutation.isPending}>
                  {createMutation.isPending && <Loader2 className="h-4 w-4 animate-spin mr-2" />}
                  Criar Coleção
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>
        ) : (
          <Card className="bg-gradient-to-r from-primary/10 to-primary/5 border-primary/20">
            <CardContent className="flex items-center gap-3 p-3">
              <Crown className="h-5 w-5 text-primary" />
              <div className="text-sm">
                <p className="font-medium">Coleções Limitadas</p>
                <p className="text-muted-foreground">
                  Upgrade para criar coleções personalizadas
                </p>
              </div>
            </CardContent>
          </Card>
        )}
      </div>

      {/* Filters */}
      <div className="flex flex-col md:flex-row gap-4">
        <div className="flex-1">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input
              placeholder="Buscar coleções..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10"
            />
          </div>
        </div>
        
        <Tabs value={selectedGame} onValueChange={(v) => setSelectedGame(v as typeof selectedGame)}>
          <TabsList>
            <TabsTrigger value="all">Todos</TabsTrigger>
            <TabsTrigger value="mtg">MTG</TabsTrigger>
            <TabsTrigger value="pokemon">Pokémon</TabsTrigger>
            <TabsTrigger value="onepiece">One Piece</TabsTrigger>
          </TabsList>
        </Tabs>
        
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

      {/* Collections */}
      {isLoading ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {[1, 2, 3, 4, 5, 6].map((i) => (
            <Card key={i}>
              <CardContent className="p-0">
                <Skeleton className="aspect-video rounded-t-lg" />
                <div className="p-4 space-y-2">
                  <Skeleton className="h-5 w-3/4" />
                  <Skeleton className="h-4 w-full" />
                  <Skeleton className="h-4 w-1/2" />
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      ) : filteredCollections.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center justify-center py-12">
            <FolderOpen className="h-12 w-12 text-muted-foreground mb-4" />
            <h3 className="font-semibold text-lg">Nenhuma coleção encontrada</h3>
            <p className="text-muted-foreground text-center max-w-md">
              {searchQuery
                ? 'Tente buscar por outro termo ou altere o filtro de jogo.'
                : isPremium
                  ? 'Crie sua primeira coleção com o botão acima.'
                  : 'Upgrade para Premium para criar coleções personalizadas.'}
            </p>
          </CardContent>
        </Card>
      ) : viewMode === 'grid' ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {filteredCollections.map((collection) => (
            <CollectionCard key={collection.id} collection={collection} />
          ))}
        </div>
      ) : (
        <div className="space-y-3">
          {filteredCollections.map((collection) => (
            <CollectionListItem key={collection.id} collection={collection} />
          ))}
        </div>
      )}
    </div>
  );
}

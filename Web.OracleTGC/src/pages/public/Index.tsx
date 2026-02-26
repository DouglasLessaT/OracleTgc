import { useState, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { Camera, Search, TrendingUp, Sparkles } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Header } from '@/components/layout/Header';
import { CameraCapture } from '@/components/scanner/CameraCapture';
import { ImageUpload } from '@/components/scanner/ImageUpload';
import { ScanProgress } from '@/components/scanner/ScanProgress';
import { CardSearchResults } from '@/components/scanner/CardSearchResults';
import { CardResult } from '@/components/scanner/CardResult';
import { GameSelector } from '@/components/scanner/GameSelector';
import { CardInfo, CardGame, ExchangeRates } from '@/types/card';
import { extractTextFromImage, parseCardInfo } from '@/services/ocrService';
import { searchCard } from '@/services/cardApiService';
import { getExchangeRates } from '@/services/currencyService';
import { useToast } from '@/hooks/use-toast';

type AppState = 'home' | 'camera' | 'scanning' | 'results' | 'detail';
type ScanStage = 'ocr' | 'search' | 'prices';

const features = [
  { icon: Camera, title: 'Escaneie', desc: 'Use a câmera para capturar' },
  { icon: Search, title: 'Identifique', desc: 'OCR + APIs públicas' },
  { icon: TrendingUp, title: 'Preços', desc: 'USD, BRL e BTC' },
];

export default function Index() {
  const [appState, setAppState] = useState<AppState>('home');
  const [scanStage, setScanStage] = useState<ScanStage>('ocr');
  const [scanProgress, setScanProgress] = useState(0);
  const [capturedImage, setCapturedImage] = useState<string>('');
  const [foundCards, setFoundCards] = useState<CardInfo[]>([]);
  const [selectedCardIndex, setSelectedCardIndex] = useState<number>(0);
  const [exchangeRates, setExchangeRates] = useState<ExchangeRates>({ brl: 5, btc: 0.00001 });
  const [extractedText, setExtractedText] = useState('');
  const [manualSearch, setManualSearch] = useState('');
  const [selectedGame, setSelectedGame] = useState<CardGame | null>(null);
  const { toast } = useToast();

  const processImage = useCallback(async (imageData: string) => {
    setCapturedImage(imageData);
    setAppState('scanning');
    setScanStage('ocr');
    setScanProgress(0);

    try {
      // Stage 1: OCR
      const ocrResult = await extractTextFromImage(imageData, (progress) => {
        setScanProgress(progress * 0.5);
      });
      setExtractedText(ocrResult.text);

      // Stage 2: Search
      setScanStage('search');
      setScanProgress(50);

      const { possibleName } = parseCardInfo(ocrResult.text);
      
      if (!possibleName) {
        toast({
          title: 'Texto não identificado',
          description: 'Não foi possível extrair texto da imagem.',
          variant: 'destructive',
        });
        setAppState('home');
        return;
      }

      const cards = await searchCard(possibleName, selectedGame || undefined);
      setFoundCards(cards);
      setScanProgress(80);

      // Stage 3: Get exchange rates
      setScanStage('prices');
      const rates = await getExchangeRates();
      setExchangeRates(rates);
      setScanProgress(100);

      setAppState('results');
    } catch (error) {
      console.error('Scan error:', error);
      toast({
        title: 'Erro ao processar',
        description: 'Ocorreu um erro. Tente novamente.',
        variant: 'destructive',
      });
      setAppState('home');
    }
  }, [toast]);

  const handleManualSearch = async () => {
    if (!manualSearch.trim()) return;

    setAppState('scanning');
    setScanStage('search');
    setScanProgress(30);

    try {
      const cards = await searchCard(manualSearch, selectedGame || undefined);
      setFoundCards(cards);
      setScanProgress(70);

      setScanStage('prices');
      const rates = await getExchangeRates();
      setExchangeRates(rates);
      setScanProgress(100);

      setAppState('results');
    } catch (error) {
      toast({
        title: 'Erro na busca',
        description: 'Não foi possível buscar a carta.',
        variant: 'destructive',
      });
      setAppState('home');
    }
  };

  const handleSelectCard = (card: CardInfo) => {
    const index = foundCards.findIndex(c => c.id === card.id);
    setSelectedCardIndex(index >= 0 ? index : 0);
    setAppState('detail');
  };

  const handleCardChange = (index: number) => {
    setSelectedCardIndex(index);
  };

  const resetScan = () => {
    setAppState('home');
    setCapturedImage('');
    setFoundCards([]);
    setSelectedCardIndex(0);
    setExtractedText('');
    setManualSearch('');
    setSelectedGame(null);
  };

  // Camera View
  if (appState === 'camera') {
    return (
      <CameraCapture
        onCapture={processImage}
        onClose={() => setAppState('home')}
      />
    );
  }

  return (
    <div className="min-h-screen bg-background flex flex-col">
      <Header />

      <main className="flex-1 flex flex-col">
        {/* Home State */}
        {appState === 'home' && (
          <div className="flex-1 flex flex-col p-4 space-y-6 max-w-2xl mx-auto w-full">
            {/* Hero */}
            <div className="text-center py-6 space-y-3">
              <Link to="/login">
                <div className="inline-flex items-center gap-1 px-3 py-1 bg-accent rounded-full text-accent-foreground text-sm hover:bg-accent/80 transition-colors cursor-pointer">
                  <Sparkles className="h-4 w-4" />
                  <span>Login</span>
                </div>
              </Link>
              <h2 className="text-2xl font-bold text-foreground">
                Escaneie suas cartas
              </h2>
              <p className="text-muted-foreground">
                Capture fotos de cartas Magic e Pokémon para identificar e ver preços
              </p>
            </div>

            {/* Game Selector */}
            <div className="space-y-2">
              <p className="text-sm text-muted-foreground text-center">
                Selecione o tipo de carta (opcional)
              </p>
              <GameSelector selected={selectedGame} onSelect={setSelectedGame} />
            </div>

            {/* Feature Cards */}
            <div className="grid grid-cols-3 gap-3 max-w-md mx-auto w-full">
              {features.map(({ icon: Icon, title, desc }) => (
                <div
                  key={title}
                  className="bg-card p-3 rounded-xl border border-border text-center space-y-2"
                >
                  <div className="w-10 h-10 mx-auto rounded-lg bg-primary/10 flex items-center justify-center">
                    <Icon className="h-5 w-5 text-primary" />
                  </div>
                  <p className="font-medium text-sm text-foreground">{title}</p>
                  <p className="text-xs text-muted-foreground">{desc}</p>
                </div>
              ))}
            </div>

            {/* Action Buttons */}
            <div className="space-y-3 max-w-md mx-auto w-full">
              <Button
                size="lg"
                className="w-full h-14 gap-3 text-base"
                onClick={() => setAppState('camera')}
              >
                <Camera className="h-5 w-5" />
                Abrir Câmera
              </Button>

              <div className="flex gap-2">
                <ImageUpload onImageSelect={processImage} />
              </div>
            </div>

            {/* Manual Search */}
            <div className="space-y-2 max-w-md mx-auto w-full">
              <p className="text-sm text-muted-foreground text-center">
                ou busque manualmente
              </p>
              <div className="flex gap-2">
                <Input
                  placeholder="Nome da carta..."
                  value={manualSearch}
                  onChange={(e) => setManualSearch(e.target.value)}
                  onKeyDown={(e) => e.key === 'Enter' && handleManualSearch()}
                />
                <Button
                  variant="secondary"
                  onClick={handleManualSearch}
                  disabled={!manualSearch.trim()}
                >
                  <Search className="h-4 w-4" />
                </Button>
              </div>
            </div>

            {/* Info Footer */}
            <div className="mt-auto pt-4 border-t border-border">
              <p className="text-xs text-center text-muted-foreground">
                APIs: Scryfall (MTG) • Pokemontcg (TGC) • CoinGecko • ExchangeRate
              </p>
            </div>
          </div>
        )}

        {/* Scanning State */}
        {appState === 'scanning' && (
          <div className="flex-1 flex items-center justify-center">
            <ScanProgress
              stage={scanStage}
              progress={scanProgress}
              imagePreview={capturedImage || '/placeholder.svg'}
            />
          </div>
        )}

        {/* Results State */}
        {appState === 'results' && (
          <div className="flex-1 flex flex-col">
            <CardSearchResults
              cards={foundCards}
              onSelect={handleSelectCard}
              extractedText={extractedText}
            />
            <div className="p-4 border-t border-border">
              <Button variant="outline" className="w-full" onClick={resetScan}>
                Nova Busca
              </Button>
            </div>
          </div>
        )}

        {/* Detail State */}
        {appState === 'detail' && foundCards.length > 0 && (
          <CardResult
            cards={foundCards}
            selectedIndex={selectedCardIndex}
            rates={exchangeRates}
            onScanAgain={resetScan}
            onCardChange={handleCardChange}
          />
        )}
      </main>
    </div>
  );
}

import { createContext, useContext, useState, ReactNode } from 'react';

type Language = 'pt' | 'en';

interface Translations {
  [key: string]: {
    pt: string;
    en: string;
  };
}

export const translations: Translations = {
  // Header
  appName: { pt: 'OracleTgc', en: 'OracleTgc' },
  appSubtitle: { pt: 'MTG & Pokémon', en: 'MTG & Pokémon' },
  mvp: { pt: 'MVP', en: 'MVP' },
  
  // Home
  poc: { pt: 'Prova de Conceito', en: 'Proof of Concept' },
  scanYourCards: { pt: 'Escaneie suas cartas', en: 'Scan your cards' },
  capturePhotos: { pt: 'Capture fotos de cartas Magic e Pokémon para identificar e ver preços', en: 'Capture photos of Magic and Pokémon cards to identify and see prices' },
  
  // Features
  scan: { pt: 'Escaneie', en: 'Scan' },
  scanDesc: { pt: 'Use a câmera para capturar', en: 'Use the camera to capture' },
  identify: { pt: 'Identifique', en: 'Identify' },
  identifyDesc: { pt: 'OCR + APIs públicas', en: 'OCR + Public APIs' },
  prices: { pt: 'Preços', en: 'Prices' },
  pricesDesc: { pt: 'USD, BRL e BTC', en: 'USD, BRL and BTC' },
  
  // Game selector
  selectCardType: { pt: 'Selecione o tipo de carta (opcional)', en: 'Select card type (optional)' },
  
  // Buttons
  openCamera: { pt: 'Abrir Câmera', en: 'Open Camera' },
  orSearchManually: { pt: 'ou busque manualmente', en: 'or search manually' },
  cardName: { pt: 'Nome da carta...', en: 'Card name...' },
  newSearch: { pt: 'Nova Busca', en: 'New Search' },
  scanAnother: { pt: 'Escanear Outra Carta', en: 'Scan Another Card' },
  
  // Results
  cardsFound: { pt: 'Cartas Encontradas', en: 'Cards Found' },
  selectCorrectCard: { pt: 'Selecione a carta correta:', en: 'Select the correct card:' },
  noCardFound: { pt: 'Nenhuma carta encontrada', en: 'No card found' },
  noCardFoundDesc: { pt: 'Não conseguimos identificar a carta. Tente tirar outra foto com melhor iluminação.', en: 'We couldn\'t identify the card. Try taking another photo with better lighting.' },
  extractedText: { pt: 'Texto extraído:', en: 'Extracted text:' },
  
  // Card details
  edition: { pt: 'Edição', en: 'Edition' },
  number: { pt: 'Número', en: 'Number' },
  foilVersion: { pt: 'Versão Foil', en: 'Foil Version' },
  marketPrice: { pt: 'Preço de Mercado', en: 'Market Price' },
  illustration: { pt: 'Ilustração', en: 'Illustration' },
  
  // Scan progress
  extractingText: { pt: 'Extraindo texto...', en: 'Extracting text...' },
  searchingCard: { pt: 'Buscando carta...', en: 'Searching card...' },
  loadingPrices: { pt: 'Carregando preços...', en: 'Loading prices...' },
  
  // Footer
  apis: { pt: 'APIs: Scryfall (MTG) • Pokémon TCG API • CoinGecko • ExchangeRate', en: 'APIs: Scryfall (MTG) • Pokémon TCG API • CoinGecko • ExchangeRate' },
};

interface LanguageContextType {
  language: Language;
  setLanguage: (lang: Language) => void;
  t: (key: string) => string;
}

const LanguageContext = createContext<LanguageContextType | undefined>(undefined);

export function LanguageProvider({ children }: { children: ReactNode }) {
  const [language, setLanguage] = useState<Language>('pt');

  const t = (key: string): string => {
    return translations[key]?.[language] || key;
  };

  return (
    <LanguageContext.Provider value={{ language, setLanguage, t }}>
      {children}
    </LanguageContext.Provider>
  );
}

export function useLanguage() {
  const context = useContext(LanguageContext);
  if (!context) {
    throw new Error('useLanguage must be used within a LanguageProvider');
  }
  return context;
}

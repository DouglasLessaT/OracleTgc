import { CardGame } from '@/types/card';
import { cn } from '@/lib/utils';
import magicLogo from '@/assets/magic-logo.png';
import pokemonLogo from '@/assets/pokemon-logo.png';

interface GameSelectorProps {
  selected: CardGame | null;
  onSelect: (game: CardGame | null) => void;
}

export function GameSelector({ selected, onSelect }: GameSelectorProps) {
  return (
    <div className="flex justify-center gap-4">
      <button
        onClick={() => onSelect(selected === 'mtg' ? null : 'mtg')}
        className={cn(
          'flex flex-col items-center justify-center w-28 h-28 rounded-2xl border-2 transition-all duration-300',
          selected === 'mtg'
            ? 'border-primary bg-primary/10 shadow-lg shadow-primary/20 scale-105'
            : 'border-border bg-card hover:border-primary/50 hover:bg-primary/5'
        )}
      >
        <img 
          src={magicLogo} 
          alt="Magic: The Gathering" 
          className={cn(
            'w-12 h-12 object-contain rounded-lg transition-all duration-300',
            selected === 'mtg' ? 'scale-110' : 'opacity-70'
          )}
        />
        <p className={cn(
          'font-semibold text-xs mt-2 transition-colors',
          selected === 'mtg' ? 'text-primary' : 'text-muted-foreground'
        )}>Magic</p>
      </button>


      <button
        onClick={() => onSelect(selected === 'pokemon' ? null : 'pokemon')}
        className={cn(
          'flex flex-col items-center justify-center w-28 h-28 rounded-2xl border-2 transition-all duration-300',
          selected === 'pokemon'
            ? 'border-primary bg-primary/10 shadow-lg shadow-primary/20 scale-105'
            : 'border-border bg-card hover:border-primary/50 hover:bg-primary/5'
        )}
      >
        <img 
          src={pokemonLogo} 
          alt="Pokémon TCG" 
          className={cn(
            'w-12 h-12 object-contain rounded-lg transition-all duration-300',
            selected === 'pokemon' ? 'scale-110' : 'opacity-70'
          )}
        />
        <p className={cn(
          'font-semibold text-xs mt-2 transition-colors',
          selected === 'pokemon' ? 'text-primary' : 'text-muted-foreground'
        )}>Pokémon</p>
      </button>
    </div>
  );
}

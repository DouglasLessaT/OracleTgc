import { Sparkles, Moon, Sun, Globe } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTheme } from '@/hooks/useTheme';
import { useLanguage } from '@/contexts/LanguageContext';
import logoSvg from '@/assets/logo.svg';

export function Header() {
  const { theme, toggleTheme } = useTheme();
  const { language, setLanguage, t } = useLanguage();

  return (
    <header className="sticky top-0 z-40 bg-card/80 backdrop-blur-md border-b border-border">
      <div className="flex items-center justify-between px-4 py-3">
        {/* Left: Theme & Language */}
        <div className="flex items-center gap-1">
          <Button
            variant="ghost"
            size="icon"
            onClick={toggleTheme}
            className="h-9 w-9"
            aria-label="Toggle theme"
          >
            {theme === 'dark' ? (
              <Sun className="h-4 w-4" />
            ) : (
              <Moon className="h-4 w-4" />
            )}
          </Button>

          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="ghost" size="icon" className="h-9 w-9">
                <Globe className="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start">
              <DropdownMenuItem 
                onClick={() => setLanguage('pt')}
                className={language === 'pt' ? 'bg-accent' : ''}
              >
                🇧🇷 Português
              </DropdownMenuItem>
              <DropdownMenuItem 
                onClick={() => setLanguage('en')}
                className={language === 'en' ? 'bg-accent' : ''}
              >
                🇺🇸 English
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>

        {/* Center: Logo */}
        <div className="flex items-center gap-2">
          <img src={logoSvg} alt="OracleTgc Logo" className="w-10 h-10" />
          <div>
            <h1 className="font-bold text-foreground">{t('appName')}</h1>
            <p className="text-xs text-muted-foreground">{t('appSubtitle')}</p>
          </div>
        </div>

        {/* Right: Badge */}
        <div className="flex items-center gap-1 px-2 py-1 rounded-full bg-accent text-accent-foreground">
          <Sparkles className="h-3 w-3" />
          <span className="text-xs font-medium">{t('mvp')}</span>
        </div>
      </div>
    </header>
  );
}

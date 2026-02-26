import { Progress } from '@/components/ui/progress';
import { Loader2, FileSearch, Search, DollarSign } from 'lucide-react';

interface ScanProgressProps {
  stage: 'ocr' | 'search' | 'prices';
  progress: number;
  imagePreview: string;
}

const stageInfo = {
  ocr: {
    icon: FileSearch,
    label: 'Extraindo texto da imagem...',
  },
  search: {
    icon: Search,
    label: 'Buscando carta nas APIs...',
  },
  prices: {
    icon: DollarSign,
    label: 'Obtendo cotações...',
  },
};

export function ScanProgress({ stage, progress, imagePreview }: ScanProgressProps) {
  const { icon: Icon, label } = stageInfo[stage];

  return (
    <div className="flex flex-col items-center justify-center p-6 space-y-6">
      {/* Image Preview */}
      <div className="w-48 h-64 rounded-lg overflow-hidden shadow-lg border border-border">
        <img
          src={imagePreview}
          alt="Carta capturada"
          className="w-full h-full object-cover"
        />
      </div>

      {/* Progress Info */}
      <div className="w-full max-w-xs space-y-4">
        <div className="flex items-center justify-center gap-3 text-primary">
          <Icon className="h-5 w-5 animate-pulse" />
          <span className="text-sm font-medium">{label}</span>
        </div>

        <Progress value={progress} className="h-2" />

        <p className="text-center text-xs text-muted-foreground">
          {Math.round(progress)}% concluído
        </p>
      </div>
    </div>
  );
}

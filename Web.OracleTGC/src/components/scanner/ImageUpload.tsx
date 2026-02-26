import { useRef } from 'react';
import { Upload, Image as ImageIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface ImageUploadProps {
  onImageSelect: (imageData: string) => void;
}

export function ImageUpload({ onImageSelect }: ImageUploadProps) {
  const inputRef = useRef<HTMLInputElement>(null);

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
      const result = event.target?.result;
      if (typeof result === 'string') {
        onImageSelect(result);
      }
    };
    reader.readAsDataURL(file);
  };

  return (
    <>
      <input
        ref={inputRef}
        type="file"
        accept="image/*"
        onChange={handleFileSelect}
        className="hidden"
      />
      <Button
        size="lg"
        onClick={() => inputRef.current?.click()}
        className="w-full h-14 gap-3 text-base"
      >
        <ImageIcon className="h-5 w-5 sm:h-8 sm:w-8" />
        <span className="text-sm sm:text-base">Galeria</span>
      </Button>
    </>
  );
}

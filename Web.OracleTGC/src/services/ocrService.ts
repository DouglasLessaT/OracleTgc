import Tesseract from 'tesseract.js';

export interface OCRResult {
  text: string;
  confidence: number;
}

/**
 * Recorta a imagem para focar apenas na área do título (parte superior da carta)
 */
async function cropTitleArea(imageSource: string | File): Promise<string> {
  return new Promise((resolve, reject) => {
    const img = new Image();
    
    const loadImage = () => {
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');
      
      if (!ctx) {
        reject(new Error('Could not get canvas context'));
        return;
      }

      // Calcular área do título (primeiros 25% da altura, largura total)
      const cropHeight = Math.floor(img.height * 0.25);
      const cropWidth = img.width;
      
      // Configurar canvas para o tamanho recortado
      canvas.width = cropWidth;
      canvas.height = cropHeight;
      
      // Desenhar apenas a parte superior da imagem
      ctx.drawImage(
        img,
        0, 0, cropWidth, cropHeight,  // Source: parte superior
        0, 0, cropWidth, cropHeight   // Destination: canvas completo
      );
      
      // Converter para data URL
      const croppedImageData = canvas.toDataURL('image/jpeg', 0.9);
      resolve(croppedImageData);
    };

    if (typeof imageSource === 'string') {
      img.src = imageSource;
      img.onload = loadImage;
      img.onerror = () => reject(new Error('Failed to load image'));
    } else {
      const reader = new FileReader();
      reader.onload = (e) => {
        img.src = e.target?.result as string;
        img.onload = loadImage;
        img.onerror = () => reject(new Error('Failed to load image'));
      };
      reader.onerror = () => reject(new Error('Failed to read file'));
      reader.readAsDataURL(imageSource);
    }
  });
}

export async function extractTextFromImage(
  imageSource: string | File,
  onProgress?: (progress: number) => void
): Promise<OCRResult> {
  try {
    // Recortar apenas a área do título antes de processar
    const croppedImage = await cropTitleArea(imageSource);
    
    const result = await Tesseract.recognize(croppedImage, 'eng', {
      logger: (m) => {
        if (m.status === 'recognizing text' && onProgress) {
          onProgress(m.progress * 100);
        }
      },
    });

    return {
      text: result.data.text,
      confidence: result.data.confidence,
    };
  } catch (error) {
    console.error('Error in OCR:', error);
    // Fallback: tentar processar a imagem completa se o recorte falhar
    const result = await Tesseract.recognize(imageSource, 'eng', {
      logger: (m) => {
        if (m.status === 'recognizing text' && onProgress) {
          onProgress(m.progress * 100);
        }
      },
    });

    return {
      text: result.data.text,
      confidence: result.data.confidence,
    };
  }
}

export function parseCardInfo(text: string): {
  possibleName: string;
  possibleSet: string;
  possibleNumber: string;
} {
  const lines = text.split('\n').filter((line) => line.trim().length > 0);
  
  // First non-empty line is usually the card name
  const possibleName = lines[0]?.trim() || '';
  
  // Look for set code pattern (e.g., "XY12", "SV1", "NEO")
  const setPattern = /\b([A-Z]{2,4}\d{0,3})\b/;
  const setMatch = text.match(setPattern);
  const possibleSet = setMatch ? setMatch[1] : '';
  
  // Look for card number pattern (e.g., "123/200", "45/102")
  const numberPattern = /(\d{1,3})\s*[\/\\]\s*(\d{1,3})/;
  const numberMatch = text.match(numberPattern);
  const possibleNumber = numberMatch ? numberMatch[1] : '';
  
  return {
    possibleName: cleanCardName(possibleName),
    possibleSet,
    possibleNumber,
  };
}

function cleanCardName(name: string): string {
  // Remove common OCR artifacts and clean up the name
  return name
    .replace(/[^\w\s'-]/g, '')
    .replace(/\s+/g, ' ')
    .trim();
}

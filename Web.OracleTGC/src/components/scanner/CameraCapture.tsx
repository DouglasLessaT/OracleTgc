import { useRef, useState, useCallback } from 'react';
import { Camera, X, SwitchCamera, FlipHorizontal } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface CameraCaptureProps {
  onCapture: (imageData: string) => void;
  onClose: () => void;
}

export function CameraCapture({ onCapture, onClose }: CameraCaptureProps) {
  const videoRef = useRef<HTMLVideoElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [stream, setStream] = useState<MediaStream | null>(null);
  const [isReady, setIsReady] = useState(false);
  const [facingMode, setFacingMode] = useState<'environment' | 'user'>('environment');
  const [error, setError] = useState<string | null>(null);

  const startCamera = useCallback(async () => {
    try {
      setError(null);
      
      if (stream) {
        stream.getTracks().forEach((track) => track.stop());
      }
      
      const mediaStream = await navigator.mediaDevices.getUserMedia({
        video: {
          facingMode,
          width: { ideal: 1920 },
          height: { ideal: 1080 },
        },
      });
      
      setStream(mediaStream);
      
      if (videoRef.current) {
        videoRef.current.srcObject = mediaStream;
        videoRef.current.onloadedmetadata = () => {
          setIsReady(true);
        };
      }
    } catch (err) {
      console.error('Camera error:', err);
      setError('Não foi possível acessar a câmera. Verifique as permissões.');
    }
  }, [facingMode, stream]);

  const stopCamera = useCallback(() => {
    if (stream) {
      stream.getTracks().forEach((track) => track.stop());
      setStream(null);
    }
    setIsReady(false);
  }, [stream]);

  const toggleCamera = () => {
    stopCamera();
    setFacingMode((prev) => (prev === 'environment' ? 'user' : 'environment'));
  };

  const capturePhoto = () => {
    if (!videoRef.current || !canvasRef.current) return;
    
    const video = videoRef.current;
    const canvas = canvasRef.current;
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    const ctx = canvas.getContext('2d');
    if (ctx) {
      ctx.drawImage(video, 0, 0);
      const imageData = canvas.toDataURL('image/jpeg', 0.9);
      onCapture(imageData);
      stopCamera();
    }
  };

  // Start camera on mount
  useState(() => {
    startCamera();
    return () => stopCamera();
  });

  return (
    <div className="fixed inset-0 z-50 bg-foreground flex flex-col">
      {/* Header */}
      <div className="flex items-center justify-between p-4 bg-card/90 backdrop-blur-sm">
        <Button
          variant="ghost"
          size="icon"
          onClick={() => {
            stopCamera();
            onClose();
          }}
          className="text-card-foreground"
        >
          <X className="h-6 w-6" />
        </Button>
        <span className="text-card-foreground font-medium">Capturar Carta</span>
        <Button
          variant="ghost"
          size="icon"
          onClick={toggleCamera}
          className="text-card-foreground"
        >
          <SwitchCamera className="h-6 w-6" />
        </Button>
      </div>

      {/* Camera View */}
      <div className="flex-1 relative flex items-center justify-center overflow-hidden">
        {error ? (
          <div className="text-center p-8">
            <p className="text-destructive mb-4">{error}</p>
            <Button onClick={startCamera}>Tentar Novamente</Button>
          </div>
        ) : (
          <>
            <video
              ref={videoRef}
              autoPlay
              playsInline
              muted
              className="w-full h-full object-cover"
            />
            
            {/* Card Frame Overlay */}
            <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div className="w-[280px] h-[400px] border-2 border-primary rounded-lg opacity-70">
                <div className="absolute -top-6 left-1/2 -translate-x-1/2 bg-primary/80 px-3 py-1 rounded-full text-primary-foreground text-xs">
                  Posicione a carta aqui
                </div>
              </div>
            </div>
          </>
        )}
      </div>

      {/* Capture Button */}
      <div className="p-6 bg-card/90 backdrop-blur-sm flex justify-center">
        <Button
          size="lg"
          onClick={capturePhoto}
          disabled={!isReady}
          className="w-20 h-20 rounded-full"
        >
          <Camera className="h-8 w-8" />
        </Button>
      </div>

      {/* Hidden canvas for capture */}
      <canvas ref={canvasRef} className="hidden" />
    </div>
  );
}

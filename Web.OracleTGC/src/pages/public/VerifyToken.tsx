import { useState } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { InputOTP, InputOTPGroup, InputOTPSlot } from '@/components/ui/input-otp';
import { Loader2, ShieldCheck } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';
import logo from '@/assets/logo.svg';

export default function VerifyToken() {
  const [token, setToken] = useState('');
  const { verifyToken, resendCode, isLoading } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const { toast } = useToast();
  const state = location.state as { type?: string; email?: string } | undefined;
  const isRegistering = state?.type === 'register';
  const email = state?.email ?? '';

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    const result = await verifyToken(token);

    if (result.success) {
      toast({
        title: 'Código verificado!',
        description: isRegistering ? 'Escolha seu plano e finalize o cadastro.' : 'Autenticação confirmada.',
      });
      if (isRegistering) {
        navigate('/complete-profile', { state: { email } });
      } else {
        navigate('/dashboard');
      }
    } else {
      toast({
        title: 'Código inválido',
        description: result.errorMessage || 'O código informado está incorreto.',
        variant: 'destructive',
      });
    }
  };

  return (
    <div className="min-h-screen bg-background flex items-center justify-center p-4">
      <Card className="w-full max-w-md">
        <CardHeader className="text-center space-y-4">
          <div className="flex justify-center">
            <img src={logo} alt="OracleTgc" className="h-16 w-16" />
          </div>
          <CardTitle className="text-2xl">Verificação</CardTitle>
          <CardDescription>
            Digite o código de 6 dígitos enviado para seu email
          </CardDescription>
        </CardHeader>
        
        <form onSubmit={handleSubmit}>
          <CardContent className="flex justify-center">
            <InputOTP
              maxLength={6}
              value={token}
              onChange={(value) => setToken(value)}
            >
              <InputOTPGroup>
                <InputOTPSlot index={0} />
                <InputOTPSlot index={1} />
                <InputOTPSlot index={2} />
                <InputOTPSlot index={3} />
                <InputOTPSlot index={4} />
                <InputOTPSlot index={5} />
              </InputOTPGroup>
            </InputOTP>
          </CardContent>
          
          <CardFooter className="flex flex-col gap-4">
            <Button 
              type="submit" 
              className="w-full" 
              disabled={isLoading || token.length !== 6}
            >
              {isLoading ? (
                <Loader2 className="h-4 w-4 animate-spin" />
              ) : (
                <>
                  <ShieldCheck className="h-4 w-4" />
                  Verificar
                </>
              )}
            </Button>
            
            <Button
              type="button"
              variant="ghost"
              className="text-sm"
              disabled={!email}
              onClick={async () => {
                if (!email) return;
                const result = await resendCode(email);
                if (result.success) {
                  toast({ title: 'Código reenviado', description: 'Verifique seu email.' });
                } else {
                  const isEmailNotFound = /não encontrado|not found/i.test(result.errorMessage || '');
                  toast({
                    title: 'Não foi possível reenviar',
                    description: isEmailNotFound
                      ? 'Este e-mail ainda não está cadastrado. Crie sua conta na tela de registro.'
                      : result.errorMessage || 'Tente novamente.',
                    variant: 'destructive',
                  });
                }
              }}
            >
              Reenviar código
            </Button>
          </CardFooter>
        </form>
      </Card>
    </div>
  );
}

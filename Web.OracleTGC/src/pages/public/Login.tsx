import { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Loader2, LogIn } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';
import logo from '@/assets/logo.svg';

export default function Login() {
  const location = useLocation();
  const stateEmail = (location.state as { email?: string })?.email;
  const [email, setEmail] = useState(stateEmail ?? '');
  const [password, setPassword] = useState('');
  const { login, isLoading } = useAuth();
  const navigate = useNavigate();
  const { toast } = useToast();

  useEffect(() => {
    if (stateEmail) setEmail(stateEmail);
  }, [stateEmail]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const result = await login(email, password);
    if (result.success) {
      toast({
        title: 'Login realizado!',
        description: 'Bem-vindo de volta!',
      });
      navigate('/dashboard');
    } else {
      toast({
        title: 'Erro no login',
        description: result.errorMessage || 'Email ou senha inválidos.',
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
          <CardTitle className="text-2xl">Entrar no OracleTgc</CardTitle>
          <CardDescription>
            Digite suas credenciais para acessar sua conta
          </CardDescription>
        </CardHeader>
        
        <form onSubmit={handleSubmit}>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>
              <Input
                id="email"
                type="email"
                placeholder="seu@email.com"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
              />
            </div>
            
            <div className="space-y-2">
              <Label htmlFor="password">Senha</Label>
              <Input
                id="password"
                type="password"
                placeholder="••••••••"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
            </div>
          </CardContent>
          
          <CardFooter className="flex flex-col gap-4">
            <Button type="submit" className="w-full" disabled={isLoading}>
              {isLoading ? (
                <Loader2 className="h-4 w-4 animate-spin" />
              ) : (
                <>
                  <LogIn className="h-4 w-4" />
                  Entrar
                </>
              )}
            </Button>
            
            <p className="text-sm text-muted-foreground text-center">
              Não tem uma conta?{' '}
              <Link to="/register" className="text-primary hover:underline">
                Criar conta
              </Link>
            </p>
            
            <Link to="/" className="text-sm text-muted-foreground hover:text-foreground">
              ← Voltar para o scanner
            </Link>
          </CardFooter>
        </form>
      </Card>
    </div>
  );
}

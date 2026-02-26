import { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '@/contexts/AuthContext';
import { UserPlan } from '@/types/user';
import { authDebug } from '@/lib/authDebug';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Loader2, User, Crown, Check } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';
import logo from '@/assets/logo.svg';

const plans: { id: UserPlan; name: string; price: string; features: string[] }[] = [
  {
    id: 'free',
    name: 'Grátis',
    price: 'R$ 0',
    features: [
      'Escanear cartas ilimitadas',
      '1 coleção por jogo',
      'Ver preços de mercado',
      'Visualização básica',
    ],
  },
  {
    id: 'premium',
    name: 'Premium',
    price: 'R$ 19,90/mês',
    features: [
      'Tudo do plano Grátis',
      'Coleções ilimitadas',
      'Criar decks de jogo',
      'Compartilhar coleções',
      'Overview de preços',
      'Estatísticas avançadas',
    ],
  },
];

export default function CompleteProfile() {
  const location = useLocation();
  const state = location.state as { email?: string } | undefined;
  const emailFromState = state?.email ?? '';

  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [selectedPlan, setSelectedPlan] = useState<UserPlan>('free');
  const { user, completeRegistration, login, isLoading } = useAuth();
  const navigate = useNavigate();
  const { toast } = useToast();

  const isCompletingRegistration = !!emailFromState;
  /** Só fazer login antes se estiver completando registro e ainda não estiver logado (ex.: refresh perdeu o token) */
  const needLoginFirst = isCompletingRegistration && !user;

  useEffect(() => {
    if (!emailFromState && !user) {
      navigate('/login', { replace: true });
    }
  }, [emailFromState, user, navigate]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    authDebug('CompleteProfile submit', {
      needLoginFirst,
      hasUser: !!user,
      emailFromState: emailFromState || undefined,
      willCallLogin: needLoginFirst,
      hint: needLoginFirst
        ? 'Login será chamado porque não há token (verify-email não retornou token ou página foi atualizada).'
        : 'Token presente: só PATCH /users/me será chamado.',
    });

    if (!username.trim()) {
      toast({
        title: 'Nome de usuário obrigatório',
        description: 'Por favor, escolha um nome de usuário.',
        variant: 'destructive',
      });
      return;
    }

    if (isCompletingRegistration && !password.trim()) {
      toast({
        title: 'Senha obrigatória',
        description: 'Defina uma senha para acessar sua conta.',
        variant: 'destructive',
      });
      return;
    }

    if (isCompletingRegistration && password.trim().length < 6) {
      toast({
        title: 'Senha inválida',
        description: 'A senha deve ter no mínimo 6 caracteres.',
        variant: 'destructive',
      });
      return;
    }

    if (needLoginFirst) {
      const loginResult = await login(emailFromState, password);
      if (!loginResult.success) {
        toast({
          title: 'Erro no login',
          description: loginResult.errorMessage || 'Verifique sua senha e tente novamente.',
          variant: 'destructive',
        });
        return;
      }
    }

    const result = await completeRegistration(username, selectedPlan, isCompletingRegistration ? password : undefined);

    if (result.success) {
      toast({
        title: 'Conta criada!',
        description: 'Bem-vindo ao OracleTgc!',
      });
      navigate('/dashboard');
    } else {
      toast({
        title: 'Erro ao criar conta',
        description: result.errorMessage || 'Tente novamente.',
        variant: 'destructive',
      });
    }
  };

  return (
    <div className="min-h-screen bg-background flex items-center justify-center p-4">
      <Card className="w-full max-w-2xl">
        <CardHeader className="text-center space-y-4">
          <div className="flex justify-center">
            <img src={logo} alt="OracleTgc" className="h-16 w-16" />
          </div>
          <CardTitle className="text-2xl">Complete seu Perfil</CardTitle>
          <CardDescription>
            {isCompletingRegistration
              ? 'Seleção de plano Free/Premium e criação da conta (conforme fluxo de registro).'
              : 'Escolha seu nome de usuário e plano'}
          </CardDescription>
        </CardHeader>

        <form onSubmit={handleSubmit}>
          <CardContent className="space-y-6">
            {isCompletingRegistration && (
              <div className="space-y-2">
                <Label>E-mail</Label>
                <Input type="email" value={emailFromState || user?.email} readOnly disabled className="bg-muted" />
              </div>
            )}
            {isCompletingRegistration && (
              <div className="space-y-2">
                <Label htmlFor="password">Senha</Label>
                <Input
                  id="password"
                  type="password"
                  placeholder="Defina uma senha para acessar sua conta (mín. 6 caracteres)"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  minLength={6}
                  required
                />
                <p className="text-xs text-muted-foreground">
                  Use no mínimo 6 caracteres. Você usará esta senha para fazer login.
                </p>
              </div>
            )}
            <div className="space-y-2">
              <Label htmlFor="username">Nome de Usuário</Label>
              <Input
                id="username"
                placeholder="Seu nome no OracleTgc"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                required
              />
            </div>

            <div className="space-y-3">
              <Label>Escolha seu Plano</Label>
              <div className="grid md:grid-cols-2 gap-4">
                {plans.map((plan) => (
                  <div
                    key={plan.id}
                    onClick={() => setSelectedPlan(plan.id)}
                    className={`relative cursor-pointer rounded-xl border-2 p-4 transition-all ${
                      selectedPlan === plan.id
                        ? 'border-primary bg-primary/5'
                        : 'border-border hover:border-muted-foreground'
                    }`}
                  >
                    {plan.id === 'premium' && (
                      <div className="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span className="bg-primary text-primary-foreground text-xs px-2 py-1 rounded-full flex items-center gap-1">
                          <Crown className="h-3 w-3" />
                          Recomendado
                        </span>
                      </div>
                    )}
                    
                    <div className="flex items-center gap-2 mb-2">
                      {plan.id === 'free' ? (
                        <User className="h-5 w-5 text-muted-foreground" />
                      ) : (
                        <Crown className="h-5 w-5 text-primary" />
                      )}
                      <h3 className="font-semibold">{plan.name}</h3>
                    </div>
                    
                    <p className="text-2xl font-bold text-foreground mb-4">
                      {plan.price}
                    </p>
                    
                    <ul className="space-y-2">
                      {plan.features.map((feature, i) => (
                        <li key={i} className="flex items-center gap-2 text-sm text-muted-foreground">
                          <Check className="h-4 w-4 text-primary" />
                          {feature}
                        </li>
                      ))}
                    </ul>
                  </div>
                ))}
              </div>
            </div>
          </CardContent>
          
          <CardFooter>
            <Button type="submit" className="w-full" disabled={isLoading}>
              {isLoading ? (
                <Loader2 className="h-4 w-4 animate-spin" />
              ) : (
                'Criar Conta'
              )}
            </Button>
          </CardFooter>
        </form>
      </Card>
    </div>
  );
}

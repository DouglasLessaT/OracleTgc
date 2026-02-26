import { useState } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';
import { sendSettingsVerificationCode } from '@/services/authApiService';
import {
  Loader2,
  Mail,
  User,
  CheckCircle,
  Crown,
  FileText,
  ShoppingBag,
  Globe,
  Shield,
  AlertTriangle,
} from 'lucide-react';

type PendingField = 'name' | 'email' | 'password' | null;

export default function Settings() {
  const { user, updateProfile } = useAuth();
  const { language, setLanguage } = useLanguage();
  const { toast } = useToast();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [pendingField, setPendingField] = useState<PendingField>(null);
  const [pendingValue, setPendingValue] = useState<{ name?: string; email?: string; password?: string }>({});
  const [code, setCode] = useState('');
  const [sendingCode, setSendingCode] = useState(false);
  const [confirming, setConfirming] = useState(false);

  const openConfirmDialog = (field: PendingField, value: { name?: string; email?: string; password?: string }) => {
    setPendingField(field);
    setPendingValue(value);
    setCode('');
    setDialogOpen(true);
  };

  const handleSendCode = async () => {
    setSendingCode(true);
    try {
      const res = await sendSettingsVerificationCode();
      if (res.success) {
        toast({ title: 'Código enviado', description: 'Verifique seu e-mail.' });
      } else {
        toast({ variant: 'destructive', title: 'Erro', description: (res as { message?: string }).message });
      }
    } catch {
      toast({ variant: 'destructive', title: 'Erro', description: 'Não foi possível enviar o código.' });
    } finally {
      setSendingCode(false);
    }
  };

  const handleConfirm = async () => {
    if (!code || code.length !== 6) {
      toast({ variant: 'destructive', title: 'Código inválido', description: 'Digite o código de 6 dígitos.' });
      return;
    }
    setConfirming(true);
    try {
      const res = await updateProfile(pendingValue);
      if (res.success) {
        toast({ title: 'Alteração confirmada' });
        setDialogOpen(false);
        setPendingField(null);
        setPendingValue({});
        setCode('');
      } else {
        toast({ variant: 'destructive', title: 'Erro', description: res.errorMessage });
      }
    } catch {
      toast({ variant: 'destructive', title: 'Erro', description: 'Não foi possível confirmar.' });
    } finally {
      setConfirming(false);
    }
  };

  const dialogTitle =
    pendingField === 'name'
      ? 'Confirmar alteração do nome'
      : pendingField === 'email'
        ? 'Confirmar alteração do e-mail'
        : 'Confirmar alteração da senha';

  const email = pendingField === 'email' ? pendingValue.email : user?.email;

  return (
    <div className="min-h-0 p-4 sm:p-6 space-y-6 pb-8 max-w-2xl">
      <div>
        <h1 className="text-2xl font-bold">Configurações</h1>
        <p className="text-muted-foreground text-sm">Gerencie sua conta</p>
      </div>

      {/* 1. Plano e assinatura */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Crown className="h-5 w-5" />
            Plano e assinatura
          </CardTitle>
          <CardDescription>Seu plano atual e benefícios.</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex items-center justify-between">
            <span className="font-medium">Plano atual</span>
            <Badge variant={user?.plan === 'premium' ? 'default' : 'secondary'}>
              {user?.plan === 'premium' ? 'Premium' : 'Free'}
            </Badge>
          </div>
          <p className="text-sm text-muted-foreground mt-2">
            {user?.plan === 'premium'
              ? 'Você tem acesso a coleções ilimitadas, decks e mais.'
              : 'Faça upgrade para desbloquear coleções e recursos premium.'}
          </p>
        </CardContent>
      </Card>

      {/* 2. Faturamento */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <FileText className="h-5 w-5" />
            Faturamento
          </CardTitle>
          <CardDescription>Faturas e formas de pagamento.</CardDescription>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">
            Suas faturas e histórico de pagamento aparecerão aqui.
          </p>
        </CardContent>
      </Card>

      {/* 3. Compras */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <ShoppingBag className="h-5 w-5" />
            Compras
          </CardTitle>
          <CardDescription>Histórico de compras e assinaturas.</CardDescription>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">
            Histórico de compras e upgrades será exibido aqui.
          </p>
        </CardContent>
      </Card>

      {/* 4. Dados da conta */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <User className="h-5 w-5" />
            Dados da conta
          </CardTitle>
          <CardDescription>
            Altere nome, e-mail ou senha. Será necessário confirmar por código enviado ao e-mail.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label>Nome</Label>
            <Input defaultValue={user?.username} id="name" />
            <Button
              variant="outline"
              size="sm"
              onClick={() => {
                const name = (document.getElementById('name') as HTMLInputElement)?.value;
                if (name) openConfirmDialog('name', { name });
              }}
            >
              Salvar nome
            </Button>
          </div>
          <div className="space-y-2">
            <Label>E-mail</Label>
            <Input type="email" defaultValue={user?.email} id="email" />
            <Button
              variant="outline"
              size="sm"
              onClick={() => {
                const email = (document.getElementById('email') as HTMLInputElement)?.value;
                if (email) openConfirmDialog('email', { email });
              }}
            >
              Salvar e-mail
            </Button>
          </div>
          <div className="space-y-2">
            <Label>Nova senha</Label>
            <Input type="password" placeholder="••••••••" id="newPassword" />
            <Button
              variant="outline"
              size="sm"
              onClick={() => {
                const password = (document.getElementById('newPassword') as HTMLInputElement)?.value;
                if (password) openConfirmDialog('password', { password });
              }}
            >
              Salvar senha
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* 5. Preferências */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Globe className="h-5 w-5" />
            Preferências
          </CardTitle>
          <CardDescription>Idioma e notificações do app.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label>Idioma</Label>
            <Select value={language} onValueChange={(v) => setLanguage(v as 'pt' | 'en')}>
              <SelectTrigger className="w-full sm:w-[180px]">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="pt">Português</SelectItem>
                <SelectItem value="en">English</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <p className="text-sm text-muted-foreground">
            Opções de notificações por e-mail em breve.
          </p>
        </CardContent>
      </Card>

      {/* 6. Segurança */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Shield className="h-5 w-5" />
            Segurança
          </CardTitle>
          <CardDescription>Sessões ativas e histórico de login.</CardDescription>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">
            Em breve você poderá ver os dispositivos conectados e encerrar sessões remotamente.
          </p>
        </CardContent>
      </Card>

      {/* 7. Excluir conta */}
      <Card className="border-destructive/50">
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-destructive">
            <AlertTriangle className="h-5 w-5" />
            Excluir conta
          </CardTitle>
          <CardDescription>
            Solicitar exclusão permanente dos seus dados. Esta ação não pode ser desfeita.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground mb-4">
            Ao excluir sua conta, todos os dados (coleções, cartas, perfil) serão removidos de forma irreversível.
          </p>
          <Button variant="destructive" size="sm" disabled>
            Solicitar exclusão da conta
          </Button>
          <p className="text-xs text-muted-foreground mt-2">
            Em breve: fluxo de confirmação por e-mail e prazo de desistência.
          </p>
        </CardContent>
      </Card>

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <CheckCircle className="h-5 w-5 text-primary" />
              {dialogTitle}
            </DialogTitle>
            <DialogDescription>
              Enviamos um código de 6 dígitos para {email}. Digite-o abaixo para confirmar a alteração.
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4 py-2">
            <Button
              type="button"
              variant="outline"
              className="w-full"
              onClick={handleSendCode}
              disabled={sendingCode}
            >
              {sendingCode ? <Loader2 className="h-4 w-4 animate-spin" /> : <Mail className="h-4 w-4" />}
              {sendingCode ? 'Enviando...' : 'Enviar código'}
            </Button>
            <div className="space-y-2">
              <Label htmlFor="code">Código (6 dígitos)</Label>
              <Input
                id="code"
                type="text"
                inputMode="numeric"
                maxLength={6}
                placeholder="000000"
                value={code}
                onChange={(e) => setCode(e.target.value.replace(/\D/g, '').slice(0, 6))}
              />
            </div>
          </div>
          <DialogFooter className="mt-4 sm:mt-6">
            <Button onClick={handleConfirm} disabled={confirming || code.length !== 6}>
              {confirming ? <Loader2 className="h-4 w-4 animate-spin" /> : null}
              Confirmar alteração
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}

import { ReactNode } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '@/contexts/AuthContext';
import { AppSidebar } from '@/components/layout/AppSidebar';
import { SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import { Loader2 } from 'lucide-react';

interface AuthenticatedLayoutProps {
  children: ReactNode;
}

export function AuthenticatedLayout({ children }: AuthenticatedLayoutProps) {
  const { isAuthenticated, isLoading } = useAuth();
  const location = useLocation();

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  return (
    <SidebarProvider>
      <div className="min-h-svh min-h-dvh flex w-full flex-col sm:flex-row">
        <AppSidebar />
        <main className="flex-1 flex min-h-0 flex-col">
          <header className="flex shrink-0 items-center h-14 border-b border-border px-4 bg-background">
            <SidebarTrigger />
          </header>
          <div className="flex-1 min-h-0 overflow-auto overflow-x-hidden">
            {children}
          </div>
        </main>
      </div>
    </SidebarProvider>
  );
}

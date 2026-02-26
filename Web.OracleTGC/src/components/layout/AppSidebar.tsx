import { 
  LayoutDashboard, 
  Scan, 
  FolderOpen, 
  User, 
  Settings,
  ShoppingCart,
  LogOut,
  Crown,
  Sun,
  Moon
} from 'lucide-react';
import { useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '@/contexts/AuthContext';
import { useTheme } from '@/hooks/useTheme';
import { NavLink } from '@/components/layout/NavLink';
import logo from '@/assets/logo.svg';

import {
  Sidebar,
  SidebarContent,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarFooter,
  SidebarHeader,
  useSidebar,
} from '@/components/ui/sidebar';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

const menuItems = [
  { title: 'Dashboard', url: '/dashboard', icon: LayoutDashboard },
  { title: 'Scanner', url: '/scanner', icon: Scan },
  { title: 'Coleções', url: '/collections', icon: FolderOpen },
  { title: 'Perfil', url: '/profile', icon: User },
  { title: 'Configurações', url: '/settings', icon: Settings },
  { title: 'Marketplace', url: '/marketplace', icon: ShoppingCart },
];

export function AppSidebar() {
  const { state } = useSidebar();
  const collapsed = state === 'collapsed';
  const location = useLocation();
  const navigate = useNavigate();
  const { user, logout } = useAuth();
  const { theme, toggleTheme } = useTheme();

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  const avatarInitial = (() => {
    if (!user) return 'U';
    const name = user.username || user.email || '';
    const first = name.trim()[0]?.toUpperCase();
    if (first && /[A-Z0-9]/i.test(first)) return first;
    const next = name.trim()[1]?.toUpperCase();
    if (next && /[A-Z0-9]/i.test(next)) return next;
    const fromEmail = user.email?.[0]?.toUpperCase();
    return fromEmail && /[A-Z0-9]/i.test(fromEmail) ? fromEmail : 'U';
  })();

  return (
    <Sidebar collapsible="icon">
      <SidebarHeader className="border-b border-border">
        <div className="flex items-center gap-3 p-2">
          <img src={logo} alt="OracleTgc" className="h-8 w-8" />
          {!collapsed && (
            <span className="font-bold text-lg">OracleTgc</span>
          )}
        </div>
      </SidebarHeader>

      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupLabel>Menu</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              {menuItems.map((item) => (
                <SidebarMenuItem key={item.title}>
                  <SidebarMenuButton 
                    asChild
                    isActive={location.pathname === item.url}
                  >
                    <NavLink 
                      to={item.url} 
                      className="flex items-center gap-3"
                      activeClassName="bg-accent text-accent-foreground"
                    >
                      <item.icon className="h-4 w-4" />
                      {!collapsed && <span>{item.title}</span>}
                    </NavLink>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              ))}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>

      <SidebarFooter className="border-t border-border p-2">
        {user && (
          <div className="space-y-2">
            <div
              className={`flex items-center gap-3 p-2 rounded-xl bg-sidebar-accent/50 ${collapsed ? 'justify-center' : ''}`}
            >
              <Avatar className="h-8 w-8 shrink-0 rounded-full border-2 border-sidebar-border">
                <AvatarImage src={user.avatarUrl} alt={user.username} />
                <AvatarFallback className="bg-primary/10 text-primary text-sm font-semibold">
                  {avatarInitial}
                </AvatarFallback>
              </Avatar>
              {!collapsed && (
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium truncate">{user.username}</p>
                  <Badge 
                    variant={user.plan === 'premium' ? 'default' : 'secondary'}
                    className="text-xs"
                  >
                    {user.plan === 'premium' && <Crown className="h-3 w-3 mr-1" />}
                    {user.plan === 'premium' ? 'Premium' : 'Free'}
                  </Badge>
                </div>
              )}
            </div>

            <Button
              variant="ghost"
              size={collapsed ? 'icon' : 'default'}
              className="w-full justify-start"
              onClick={toggleTheme}
              aria-label={theme === 'dark' ? 'Usar tema claro' : 'Usar tema escuro'}
            >
              {theme === 'dark' ? (
                <Sun className="h-4 w-4" />
              ) : (
                <Moon className="h-4 w-4" />
              )}
              {!collapsed && (
                <span className="ml-2">{theme === 'dark' ? 'Tema claro' : 'Tema escuro'}</span>
              )}
            </Button>
            
            <Button 
              variant="ghost" 
              size={collapsed ? 'icon' : 'default'}
              className="w-full justify-start"
              onClick={handleLogout}
            >
              <LogOut className="h-4 w-4" />
              {!collapsed && <span className="ml-2">Sair</span>}
            </Button>
          </div>
        )}
      </SidebarFooter>
    </Sidebar>
  );
}

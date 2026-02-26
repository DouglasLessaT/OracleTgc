import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { LanguageProvider } from "@/contexts/LanguageContext";
import { AuthProvider } from "@/contexts/AuthContext";
import { AuthenticatedLayout } from "@/components/layout/AuthenticatedLayout";

// Public Pages (acessíveis sem login)
import Index from "./pages/public/Index";
import Login from "./pages/public/Login";
import Register from "./pages/public/Register";
import VerifyToken from "./pages/public/VerifyToken";
import CompleteProfile from "./pages/public/CompleteProfile";
import NotFound from "./pages/public/NotFound";

// Authenticated Pages (exigem login via AuthenticatedLayout)
import Dashboard from "./pages/authenticated/Dashboard";
import Scanner from "./pages/authenticated/Scanner";
import Collections from "./pages/authenticated/Collections";
import CollectionDetail from "./pages/authenticated/CollectionDetail";
import CardDetail from "./pages/authenticated/CardDetail";
import Profile from "./pages/authenticated/Profile";
import Settings from "./pages/authenticated/Settings";
import Marketplace from "./pages/authenticated/Marketplace";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <AuthProvider>
      <LanguageProvider>
        <TooltipProvider>
          <Toaster />
          <Sonner />
          <BrowserRouter>
            <Routes>
              {/* Public Routes */}
              <Route path="/" element={<Index />} />
              <Route path="/login" element={<Login />} />
              <Route path="/register" element={<Register />} />
              <Route path="/verify-token" element={<VerifyToken />} />
              <Route path="/complete-profile" element={<CompleteProfile />} />
              
              {/* Authenticated Routes */}
              <Route path="/dashboard" element={
                <AuthenticatedLayout>
                  <Dashboard />
                </AuthenticatedLayout>
              } />
              <Route path="/scanner" element={
                <AuthenticatedLayout>
                  <Scanner />
                </AuthenticatedLayout>
              } />
              <Route path="/collections" element={
                <AuthenticatedLayout>
                  <Collections />
                </AuthenticatedLayout>
              } />
              <Route path="/collections/:id" element={
                <AuthenticatedLayout>
                  <CollectionDetail />
                </AuthenticatedLayout>
              } />
              <Route path="/cards/:id" element={
                <AuthenticatedLayout>
                  <CardDetail />
                </AuthenticatedLayout>
              } />
              <Route path="/profile" element={
                <AuthenticatedLayout>
                  <Profile />
                </AuthenticatedLayout>
              } />
              <Route path="/settings" element={
                <AuthenticatedLayout>
                  <Settings />
                </AuthenticatedLayout>
              } />
              <Route path="/marketplace" element={
                <AuthenticatedLayout>
                  <Marketplace />
                </AuthenticatedLayout>
              } />
              
              {/* Catch-all */}
              <Route path="*" element={<NotFound />} />
            </Routes>
          </BrowserRouter>
        </TooltipProvider>
      </LanguageProvider>
    </AuthProvider>
  </QueryClientProvider>
);

export default App;

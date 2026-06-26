import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { AdminDashboard } from './AdminDashboard';
import { IntegraUPTApp } from './pages/Usuario/Inicio/InicioPage';
import { ReservasPage } from './pages/Usuario/Reservas/ReservasPage';
import { IncidenciasPage } from "./pages/Usuario/Incidencia/IncidenciasPage";
import { PsicologiaPage } from './pages/Usuario/Psicologia/PsicologiaPage';
import { OlimpiadasPage } from './pages/Usuario/Olimpiadas/OlimpiadasPage';
import { PoliclinicoPage } from './pages/Usuario/Policlinico/PoliclinicoPage';
import { AulaVirtualPage } from './pages/Usuario/AulaVirtual/AulaVirtualPage';
import { ServiciosPage } from './pages/Usuario/Servicios/ServiciosPage';
import { BurraPage } from './pages/Usuario/Burra/BurraPage';
import { PromedioPage } from './pages/Usuario/Promedio/PromedioPage';
import { GimnasioPage } from './pages/Usuario/Gimnasio/GimnasioPage';
import { CafeteriaPage } from './pages/Usuario/Cafeteria/CafeteriaPage';
import { CanalesPage } from './pages/Usuario/Canales/CanalesPage';
import { requestBackendLogout } from '../utils/logout';
import { isBackendLoginType } from '../utils/apiConfig';
import { PerfilPage } from './pages/Usuario/Perfil/PerfilPage';


interface User {
  id: string;
  email: string;
  sessionToken?: string;
  user_metadata: {
    name: string;
    avatar_url: string;
    role?: string;
    login_type?: string;
    codigo?: string;
    escuelaId?: number;
    escuelaNombre?: string;
  };
}

interface DashboardProps {
  user: User;
}

const ACTIVE_VIEW_STORAGE_KEY = 'dashboard_active_view';

export const Dashboard: React.FC<DashboardProps> = ({ user }) => {
  const isAdministrative = user.user_metadata.login_type === 'administrative';

  const [activeView, setActiveView] = useState<
     'inicio' | 'servicios' | 'reservas' | 'incidencias' | 'psicologia' | 'olimpiadas' | 'policlinico' | 'burra' | 'promedio' | 'gimnasio' | 'aulavirtual' | 'cafeteria' | 'canales' | 'perfil'
   >(() => {
     if (typeof window === 'undefined') {
       return 'inicio';
     }

     try {
       const storedView = localStorage.getItem(ACTIVE_VIEW_STORAGE_KEY);

       if (
         storedView === 'inicio' ||
         storedView === 'servicios' ||
         storedView === 'reservas' ||
         storedView === 'incidencias' ||
         storedView === 'psicologia' ||
         storedView === 'olimpiadas' ||
         storedView === 'perfil' ||
         storedView === 'burra' ||
         storedView === 'promedio' ||
         storedView === 'gimnasio' ||
         storedView === 'policlinico' ||
         storedView === 'aulavirtual' ||
         storedView === 'cafeteria' ||
         storedView === 'canales'
       ) {
         return storedView;
       }
     } catch {}

     return 'inicio';
   });

   const [isLoggingOut, setIsLoggingOut] = useState(false);

   const shouldNotifyBackend = useMemo(
     () => isBackendLoginType(user.user_metadata.login_type),
     [user.user_metadata.login_type]
   );

    const handleLogout = useCallback(async () => {
       if (isLoggingOut) {
         return;
       }

       setIsLoggingOut(true);

       try {
         if (shouldNotifyBackend) {
           await requestBackendLogout(user.id, user.sessionToken);
         }
       } finally {
         try {
           localStorage.removeItem('admin_session');
           localStorage.removeItem(ACTIVE_VIEW_STORAGE_KEY);
         } catch {
           // Ignorar si no existe almacenamiento
         }

         window.location.reload();
       }
     }, [isLoggingOut, shouldNotifyBackend, user.id, user.sessionToken]);

     const handleNavigateToServicios = useCallback(() => {
       setActiveView('servicios');
     }, []);

  const handleNavigateToReservas = useCallback(() => {
    setActiveView('reservas');
  }, []);

  const handleNavigateToIncidencias = useCallback(() => {
    setActiveView('incidencias');
  }, []);

  const handleNavigateToPsicologia = useCallback(() => {
    setActiveView('psicologia');
  }, []);

  const handleNavigateToBurra = useCallback(() => {
    setActiveView('burra');
  }, []);

  const handleNavigateToPromedio = useCallback(() => {
    setActiveView('promedio');
  }, []);

  const handleNavigateToGimnasio = useCallback(() => {
    setActiveView('gimnasio');
  }, []);

  const handleNavigateToCanales = useCallback(() => {
    setActiveView('canales');
  }, []);

  const handleNavigateToOlimpiadas = useCallback(() => {
    setActiveView('olimpiadas');
  }, []);

  const handleNavigateToPoliclinico = useCallback(() => {
    setActiveView('policlinico');
  }, []);

  const handleNavigateToAulaVirtual = useCallback(() => {
    setActiveView('aulavirtual');
  }, []);

  const handleNavigateToCafeteria = useCallback(() => {
    setActiveView('cafeteria');
  }, []);

  const handleNavigateToPerfil = useCallback(() => {
    setActiveView('perfil');
  }, []);

  const handleNavigateToInicio = useCallback(() => {
    setActiveView('inicio');
  }, []);

  useEffect(() => {
    if (typeof window === 'undefined') return;

    try {
      localStorage.setItem(ACTIVE_VIEW_STORAGE_KEY, activeView);
    } catch {}
  }, [activeView]);

  // Si el usuario es administrativo → mostrar panel de administrador
  if (isAdministrative) {
    return <AdminDashboard user={user} />;
  }

  if (activeView === 'servicios') {
    return (
      <ServiciosPage
        user={user}
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onNavigateToReservas={handleNavigateToReservas}
        onNavigateToIncidencias={handleNavigateToIncidencias}
        onNavigateToPsicologia={handleNavigateToPsicologia}
        onNavigateToBurra={handleNavigateToBurra}
        onNavigateToOlimpiadas={handleNavigateToOlimpiadas}
        onNavigateToGimnasio={handleNavigateToGimnasio}
        onNavigateToPoliclinico={handleNavigateToPoliclinico}
        onNavigateToPromedio={handleNavigateToPromedio}
        onNavigateToAulaVirtual={handleNavigateToAulaVirtual}
        onNavigateToCafeteria={handleNavigateToCafeteria}
        onNavigateToCanales={handleNavigateToCanales}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  if (activeView === 'canales') {
    return (
      <CanalesPage
        user={user}
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  if (activeView === 'reservas') {
    return (
      <ReservasPage 
        user={user} 
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  if (activeView === 'incidencias') {
    return (
      <IncidenciasPage 
        user={user} 
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  if (activeView === 'psicologia') {
    return (
      <PsicologiaPage
        user={user}
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  if (activeView === 'olimpiadas') {
    return (
      <OlimpiadasPage
        user={user}
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  if (activeView === 'policlinico') {
    return (
      <PoliclinicoPage
        user={user}
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  if (activeView === 'aulavirtual') {
    return (
      <AulaVirtualPage
        user={user}
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  if (activeView === 'cafeteria') {
    return (
      <CafeteriaPage
        user={user}
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  if (activeView === 'perfil') {
    return (
      <PerfilPage 
        user={user} 
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  if (activeView === 'burra') {
    return (
      <BurraPage 
        user={user} 
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  if (activeView === 'promedio') {
    return (
      <PromedioPage
        user={user}
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  if (activeView === 'gimnasio') {
    return (
      <GimnasioPage 
        user={user} 
        onNavigateToInicio={handleNavigateToInicio}
        onNavigateToServicios={handleNavigateToServicios}
        onNavigateToPerfil={handleNavigateToPerfil}
        onLogout={handleLogout}
        isLoggingOut={isLoggingOut}
      />
    );
  }

  return (
    <IntegraUPTApp
      user={user}
      onNavigateToServicios={handleNavigateToServicios}
      onNavigateToReservas={handleNavigateToReservas}
      onNavigateToIncidencias={handleNavigateToIncidencias}
      onNavigateToPerfil={handleNavigateToPerfil}
      onLogout={handleLogout}
      isLoggingOut={isLoggingOut}
    />
  );
};
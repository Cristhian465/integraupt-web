import { useCallback, useEffect, useMemo, useState } from 'react';
import { LoginScreen} from './components/pages/Autenticacion/LoginScreen';
  import type { BackendPerfil,  BackendSession } from './components/pages/Autenticacion/types';
import { Dashboard } from './components/Dashboard';
import { QrReservaVerificationPage } from './components/pages/QR/QrReservaVerificationPage';
import { MoodleSsoCallbackPage } from './components/pages/Usuario/AulaVirtual/MoodleSsoCallbackPage';
import { getLoginApiUrl } from './utils/apiConfig';
import './styles/App.css';

const SESSION_STORAGE_KEY = 'admin_session';

interface BackendValidationResponse {
  success: boolean;
  message: string;
  perfil: BackendPerfil | null;
  token?: string | null;
}

interface QrRouteMatch {
  token: string;
  source: 'path' | 'query';
}

const parseQrRoute = (): QrRouteMatch | null => {
  if (typeof window === 'undefined') {
    return null;
  }

  try {
    const currentUrl = new URL(window.location.href);
    const normalizedPath = decodeURIComponent(currentUrl.pathname);
    const pathMatch = normalizedPath.match(/\/qr\/reservas\/([^/]+)\/?$/i);

    if (pathMatch?.[1]) {
      const token = pathMatch[1].trim();
      if (token) return { token, source: 'path' };
    }

    const searchToken =
      currentUrl.searchParams.get('qrToken') ??
      currentUrl.searchParams.get('qr_token');

    if (searchToken) {
      const token = searchToken.trim();
      if (token) return { token, source: 'query' };
    }
  } catch {}

  return null;
};

const isMoodleSsoCallbackRoute = (): boolean => {
  if (typeof window === 'undefined') {
    return false;
  }

  return window.location.pathname.replace(/\/+$/, '').toLowerCase() === '/aulavirtual/sso-callback';
};

function App() {
  const qrRoute = useMemo(() => parseQrRoute(), []);
  const isSsoCallback = useMemo(() => isMoodleSsoCallbackRoute(), []);
  const [session, setSession] = useState<BackendSession | null>(null);
  const [isInitializing, setIsInitializing] = useState(() => (qrRoute || isSsoCallback ? false : true));

  // Restaurar sesión si no es QR ni el callback de SSO de Moodle
  useEffect(() => {
    if (qrRoute || isSsoCallback) return;

    let isMounted = true;

    const restoreSession = async () => {
      let storedSession: BackendSession | null = null;

      try {
        const raw = localStorage.getItem(SESSION_STORAGE_KEY);
        if (raw) {
          const parsed = JSON.parse(raw) as Partial<BackendSession> | null;
          if (parsed && typeof parsed.token === 'string' && parsed.perfil) {
            storedSession = { token: parsed.token, perfil: parsed.perfil };
          }
        }
      } catch {
        localStorage.removeItem(SESSION_STORAGE_KEY);
      }

      // Sin sesión guardada
      if (!storedSession) {
        if (isMounted) setIsInitializing(false);
        return;
      }

      try {
        const response = await fetch(getLoginApiUrl('/api/auth/validate'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ token: storedSession.token }),
        });

        const data: BackendValidationResponse = await response.json();

        if (!response.ok || !data?.success || !data.perfil || !data.token) {
          throw new Error('Sesión inválida');
        }

        const normalizedSession: BackendSession = {
          token: data.token,
          perfil: data.perfil,
        };

        if (!isMounted) return;

        setSession(normalizedSession);
        localStorage.setItem(SESSION_STORAGE_KEY, JSON.stringify(normalizedSession));
      } catch {
        if (!isMounted) return;
        setSession(null);
        localStorage.removeItem(SESSION_STORAGE_KEY);
      } finally {
        if (isMounted) setIsInitializing(false);
      }
    };

    void restoreSession();

    return () => {
      isMounted = false;
    };
  }, [qrRoute]);

  const handleLoginSuccess = useCallback(
    (newSession: BackendSession) => {
      if (qrRoute) return;

      setSession(newSession);
      try {
        localStorage.setItem(SESSION_STORAGE_KEY, JSON.stringify(newSession));
      } catch {}
    },
    [qrRoute]
  );

  const user = useMemo(() => {
    if (qrRoute || !session) return null;

    const perfil = session.perfil;
    const fullName = [perfil.nombres, perfil.apellidos].filter(Boolean).join(' ').trim();

    return {
      id: perfil.id,
      email: perfil.email ?? '',
      sessionToken: session.token,
      user_metadata: {
        name: fullName || perfil.codigo || 'Usuario',
        avatar_url: perfil.avatarUrl ?? '',
        role: perfil.rol ?? undefined,
        login_type: perfil.tipoLogin ?? undefined,
        codigo: perfil.codigo ?? undefined,
        escuelaId: perfil.escuelaId ?? undefined,
        escuelaNombre: perfil.escuelaNombre ?? undefined,
      },
    };
  }, [qrRoute, session]);

  if (isSsoCallback) {
    return <MoodleSsoCallbackPage callbackUrl={window.location.href} />;
  }

  if (qrRoute) {
    return <QrReservaVerificationPage token={qrRoute.token} />;
  }

  if (isInitializing) {
    return (
      <div className="app-loading-container">
        <div className="app-loading-spinner" />
      </div>
    );
  }

  return (
    <>
      {user ? (
        <Dashboard user={user} />
      ) : (
        <LoginScreen onLoginSuccess={handleLoginSuccess} />
      )}
    </>
  );
}

export default App;

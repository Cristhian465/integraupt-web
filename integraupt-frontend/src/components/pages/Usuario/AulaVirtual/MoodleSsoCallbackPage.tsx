import { useEffect, useState } from 'react';
import { CheckCircle2, AlertTriangle, Loader2 } from 'lucide-react';
import '../../../../styles/AulaVirtualScreen.css';
import { confirmarSso } from './services/aulaVirtualService';
import type { MoodleSsoPending } from './types';

const PENDING_STORAGE_KEY = 'moodle_sso_pending';

interface MoodleSsoCallbackPageProps {
  callbackUrl: string;
}

type Status = 'procesando' | 'exito' | 'error';

const extraerToken = (callbackUrl: string): { siteid: string; token: string; privateToken: string | null } | null => {
  try {
    const params = new URL(callbackUrl).searchParams;
    const redirectedUrl = params.get('url');
    if (!redirectedUrl) {
      return null;
    }

    const tokenMatch = redirectedUrl.match(/token=([^&]+)/);
    if (!tokenMatch) {
      return null;
    }

    const decoded = atob(tokenMatch[1]);
    const [siteid, token, privateToken] = decoded.split(':::');

    if (!siteid || !token) {
      return null;
    }

    return { siteid, token, privateToken: privateToken || null };
  } catch {
    return null;
  }
};

export const MoodleSsoCallbackPage: React.FC<MoodleSsoCallbackPageProps> = ({ callbackUrl }) => {
  const [status, setStatus] = useState<Status>('procesando');
  const [mensaje, setMensaje] = useState('Confirmando tu conexión con el Aula Virtual…');

  useEffect(() => {
    const procesar = async () => {
      const datos = extraerToken(callbackUrl);

      if (!datos) {
        setStatus('error');
        setMensaje('No se pudo leer la información enviada por el Aula Virtual. Cierra esta pestaña e inténtalo de nuevo.');
        return;
      }

      let pending: MoodleSsoPending | null = null;
      try {
        const raw = localStorage.getItem(PENDING_STORAGE_KEY);
        pending = raw ? (JSON.parse(raw) as MoodleSsoPending) : null;
      } catch {
        pending = null;
      }

      if (!pending) {
        setStatus('error');
        setMensaje('No se encontró una solicitud de conexión pendiente. Vuelve a la pestaña de IntegraUPT e inténtalo de nuevo.');
        return;
      }

      try {
        await confirmarSso({
          usuarioId: pending.usuarioId,
          passport: pending.passport,
          siteid: datos.siteid,
          token: datos.token,
          privateToken: datos.privateToken,
        });

        localStorage.removeItem(PENDING_STORAGE_KEY);
        setStatus('exito');
        setMensaje('Tu cuenta del Aula Virtual se conectó correctamente. Puedes cerrar esta pestaña.');
      } catch (error) {
        const message = error instanceof Error ? error.message : 'No fue posible confirmar la conexión con el Aula Virtual.';
        setStatus('error');
        setMensaje(message);
      }
    };

    void procesar();
  }, [callbackUrl]);

  return (
    <div className="aulavirtual-container">
      <main className="aulavirtual-main">
        <section className="aulavirtual-card" style={{ maxWidth: '32rem', margin: '4rem auto' }}>
          <div className="aulavirtual-card-header">
            {status === 'procesando' && <Loader2 className="aulavirtual-card-icon aulavirtual-loading-icon" aria-hidden="true" />}
            {status === 'exito' && <CheckCircle2 className="aulavirtual-card-icon" aria-hidden="true" />}
            {status === 'error' && <AlertTriangle className="aulavirtual-card-icon" aria-hidden="true" />}
            <div>
              <h2>Aula Virtual UPT</h2>
              <p>{mensaje}</p>
            </div>
          </div>
        </section>
      </main>
    </div>
  );
};

import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  AlertTriangle,
  Dumbbell,
  CalendarHeart,
  CheckCircle2,
  Clock,
  Loader2,
  PlayCircle,
  StopCircle
} from 'lucide-react';
import '../../../../styles/PsicologiaScreen.css';
import { Navbar } from '../Navbar';
import {
  obtenerEstadoSesion,
  registrarIngreso,
  registrarSalida,
  obtenerAsistencias
} from './services/gimnasioService';
import type { AsistenciaGimnasioResponse, EstadoSesionResponse } from './types';

interface GimnasioPageProps {
  user: {
    id: string;
    email: string;
    user_metadata: {
      name: string;
      avatar_url: string;
      role?: string;
      login_type?: string;
      codigo?: string;
    };
  };
  onNavigateToInicio: () => void;
  onNavigateToServicios: () => void;
  onNavigateToPerfil: () => void;
  onLogout?: () => void;
  isLoggingOut?: boolean;
}

const getUserDisplayName = (name?: string, codigo?: string): string => {
  const trimmedName = name?.trim();
  if (trimmedName) return trimmedName;
  const trimmedCodigo = codigo?.trim();
  if (trimmedCodigo) return trimmedCodigo;
  return 'Usuario';
};

export const GimnasioPage: React.FC<GimnasioPageProps> = ({
  user,
  onNavigateToInicio,
  onNavigateToServicios,
  onNavigateToPerfil,
  onLogout,
  isLoggingOut = false,
}) => {
  const [estado, setEstado] = useState<EstadoSesionResponse | null>(null);
  const [estadoLoading, setEstadoLoading] = useState(false);
  const [asistencias, setAsistencias] = useState<AsistenciaGimnasioResponse[]>([]);
  const [asistenciasLoading, setAsistenciasLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [feedback, setFeedback] = useState<{type: 'success'|'error', message: string} | null>(null);

  const userId = useMemo(() => {
    const parsed = Number.parseInt(user.id, 10);
    return Number.isNaN(parsed) ? null : parsed;
  }, [user.id]);

  const displayName = useMemo(
    () => getUserDisplayName(user.user_metadata.name, user.user_metadata.codigo),
    [user.user_metadata.codigo, user.user_metadata.name],
  );

  const cargarEstado = useCallback((signal?: AbortSignal) => {
    if (userId == null) return;
    setEstadoLoading(true);
    obtenerEstadoSesion(userId, signal)
      .then(setEstado)
      .catch(e => {
        if (!(e instanceof DOMException && e.name === 'AbortError')) {
          setError(e.message);
        }
      })
      .finally(() => setEstadoLoading(false));
  }, [userId]);

  const cargarAsistencias = useCallback((signal?: AbortSignal) => {
    setAsistenciasLoading(true);
    obtenerAsistencias(signal)
      .then(data => {
        const filtered = data.filter(a => a.id_usuario === userId);
        setAsistencias(filtered);
      })
      .catch(e => {
        if (!(e instanceof DOMException && e.name === 'AbortError')) {
          setError(e.message);
        }
      })
      .finally(() => setAsistenciasLoading(false));
  }, [userId]);

  useEffect(() => {
    const abortController = new AbortController();
    cargarEstado(abortController.signal);
    cargarAsistencias(abortController.signal);
    return () => abortController.abort();
  }, [cargarEstado, cargarAsistencias]);

  const handleIngreso = async () => {
    if (userId == null) return;
    setEstadoLoading(true);
    setFeedback(null);
    try {
      await registrarIngreso(userId);
      setFeedback({ type: 'success', message: 'Ingreso registrado correctamente.' });
      cargarEstado();
      cargarAsistencias();
    } catch (e: any) {
      setFeedback({ type: 'error', message: e.message || 'Error al registrar ingreso' });
    } finally {
      setEstadoLoading(false);
    }
  };

  const handleSalida = async () => {
    if (userId == null) return;
    setEstadoLoading(true);
    setFeedback(null);
    try {
      await registrarSalida(userId);
      setFeedback({ type: 'success', message: 'Salida registrada correctamente.' });
      cargarEstado();
      cargarAsistencias();
    } catch (e: any) {
      setFeedback({ type: 'error', message: e.message || 'Error al registrar salida' });
    } finally {
      setEstadoLoading(false);
    }
  };

  return (
    <div className="psicologia-container">
      <Navbar
        displayName={displayName}
        userCode={user.user_metadata.codigo}
        currentPage="servicios"
        onNavigateToInicio={onNavigateToInicio}
        onNavigateToServicios={onNavigateToServicios}
        onNavigateToPerfil={onNavigateToPerfil}
        onLogout={onLogout}
        isLoggingOut={isLoggingOut}
      />
      <main className="psicologia-main">
        <section className="home-welcome-card">
          <div>
            <p className="home-welcome-date">Deporte y Salud</p>
            <h1 className="home-title">Gimnasio UPT</h1>
            <p className="home-subtitle">
              {displayName}, registra tu asistencia al gimnasio y visualiza tu historial.
            </p>
          </div>
          <div className="home-welcome-avatar" aria-hidden="true">
            <Dumbbell size={44} />
          </div>
        </section>

        <div className="psicologia-grid">
          <section className="psicologia-card">
            <div className="psicologia-card-header">
              <Clock className="psicologia-card-icon" aria-hidden="true" />
              <div>
                <h2>Control de Asistencia</h2>
                <p>Registra tu entrada o salida del gimnasio.</p>
              </div>
            </div>

            {feedback && (
              <div className={`psicologia-feedback psicologia-feedback-${feedback.type}`} role="alert">
                {feedback.type === 'error' ? <AlertTriangle className="psicologia-feedback-icon" /> : <CheckCircle2 className="psicologia-feedback-icon" />}
                <p>{feedback.message}</p>
              </div>
            )}

            <div className="psicologia-form" style={{ display: 'flex', flexDirection: 'column', gap: '1rem', marginTop: '1rem' }}>
              {estadoLoading ? (
                 <div className="psicologia-empty"><Loader2 className="psicologia-loading-icon" /><p>Cargando estado...</p></div>
              ) : estado?.activa ? (
                <>
                  <div className="psicologia-feedback psicologia-feedback-success" style={{ backgroundColor: '#e6fffa', color: '#047857' }}>
                    <CheckCircle2 className="psicologia-feedback-icon" />
                    <p>Tienes una sesión activa desde las {estado.sesion?.hora_ingreso}.</p>
                  </div>
                  <button type="button" className="psicologia-primary-button" onClick={handleSalida} style={{ backgroundColor: '#ef4444' }}>
                    <StopCircle size={20} style={{ marginRight: '8px', verticalAlign: 'middle' }} /> Registrar Salida
                  </button>
                </>
              ) : (
                <>
                  <div className="psicologia-feedback" style={{ backgroundColor: '#f3f4f6', color: '#4b5563' }}>
                    <Clock className="psicologia-feedback-icon" />
                    <p>No tienes sesiones activas en este momento.</p>
                  </div>
                  <button type="button" className="psicologia-primary-button" onClick={handleIngreso}>
                    <PlayCircle size={20} style={{ marginRight: '8px', verticalAlign: 'middle' }} /> Registrar Ingreso
                  </button>
                </>
              )}
            </div>
          </section>

          <section className="psicologia-card">
            <div className="psicologia-card-header">
              <CalendarHeart className="psicologia-card-icon" aria-hidden="true" />
              <div>
                <h2>Historial de Asistencias</h2>
                <p>Revisa tus registros anteriores.</p>
              </div>
            </div>

            {asistenciasLoading ? (
              <div className="psicologia-empty"><Loader2 className="psicologia-loading-icon" /><p>Cargando historial...</p></div>
            ) : asistencias.length === 0 ? (
              <div className="psicologia-empty"><p>No tienes registros de asistencia.</p></div>
            ) : (
              <ul className="psicologia-list">
                {asistencias.map(a => (
                  <li key={a.id_asistencia} className="psicologia-list-item">
                    <div className="psicologia-list-header">
                      <span className="psicologia-list-title">{a.fecha}</span>
                      <span className={`psicologia-status ${a.hora_salida ? 'psicologia-status-finalizada' : 'psicologia-status-pendiente'}`} style={{
                        backgroundColor: a.hora_salida ? '#d1fae5' : '#fef3c7',
                        color: a.hora_salida ? '#065f46' : '#92400e'
                      }}>
                        {a.hora_salida ? 'Finalizada' : 'En curso'}
                      </span>
                    </div>
                    <div className="psicologia-list-body">
                      <span>Ingreso: {a.hora_ingreso}</span>
                      <span>Salida: {a.hora_salida || '—'}</span>
                    </div>
                    {a.duracion_calculada != null && (
                      <p className="psicologia-list-motivo">Duración: {a.duracion_calculada} minutos</p>
                    )}
                  </li>
                ))}
              </ul>
            )}
          </section>
        </div>
      </main>
    </div>
  );
};

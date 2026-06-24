import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
  AlertTriangle,
  CalendarClock,
  CheckCircle2,
  ChevronDown,
  ChevronUp,
  GraduationCap,
  Link2,
  Loader2,
  Unlink,
  XCircle,
} from 'lucide-react';
import '../../../../styles/AulaVirtualScreen.css';
import {
  desconectarCuenta,
  iniciarSso,
  confirmarSso,
  obtenerCursos,
  obtenerEstado,
  obtenerEventos,
  obtenerNotas,
} from './services/aulaVirtualService';
import type {
  CursoMoodleResponse,
  EstadoMoodleResponse,
  EventoMoodleResponse,
  MoodleSsoPending,
  NotaMoodleResponse,
} from './types';
import { Navbar } from '../Navbar';

const PENDING_STORAGE_KEY = 'moodle_sso_pending';
const SSO_URL_SCHEME = 'web+integraupt';
const SSO_POLL_INTERVAL_MS = 3000;
const SSO_MAX_POLL_ATTEMPTS = 60;

interface AulaVirtualPageProps {
  user: {
    id: string;
    email: string;
    user_metadata: {
      name: string;
      avatar_url: string;
      role?: string;
      login_type?: string;
      codigo?: string;
      escuelaId?: number;
      escuelaNombre?: string;
    };
  };
  onNavigateToInicio: () => void;
  onNavigateToServicios: () => void;
  onNavigateToPerfil: () => void;
  onLogout?: () => void;
  isLoggingOut?: boolean;
}

type Feedback = {
  type: 'success' | 'error' | 'info';
  message: string;
};

const FeedbackIcon = ({ type }: { type: Feedback['type'] }) => {
  if (type === 'success') {
    return <CheckCircle2 className="aulavirtual-feedback-icon" aria-hidden="true" />;
  }

  if (type === 'error') {
    return <AlertTriangle className="aulavirtual-feedback-icon" aria-hidden="true" />;
  }

  return <CalendarClock className="aulavirtual-feedback-icon" aria-hidden="true" />;
};

const getUserDisplayName = (name?: string, codigo?: string): string => {
  const trimmedName = name?.trim();
  if (trimmedName) {
    return trimmedName;
  }

  const trimmedCodigo = codigo?.trim();
  if (trimmedCodigo) {
    return trimmedCodigo;
  }

  return 'Usuario';
};

const formatDateTime = (value?: string | null): string => {
  if (!value) {
    return 'No disponible';
  }

  const date = new Date(value.replace(' ', 'T'));
  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat('es-PE', { dateStyle: 'medium', timeStyle: 'short' }).format(date);
};

export const AulaVirtualPage: React.FC<AulaVirtualPageProps> = ({
  user,
  onNavigateToInicio,
  onNavigateToServicios,
  onNavigateToPerfil,
  onLogout,
  isLoggingOut = false,
}) => {
  const [estado, setEstado] = useState<EstadoMoodleResponse | null>(null);
  const [estadoLoading, setEstadoLoading] = useState(true);

  const [conectando, setConectando] = useState(false);
  const [esperandoSso, setEsperandoSso] = useState(false);
  const [conexionFeedback, setConexionFeedback] = useState<Feedback | null>(null);
  const [desconectando, setDesconectando] = useState(false);
  const [manualTokenInput, setManualTokenInput] = useState('');
  const [procesandoManualToken, setProcesandoManualToken] = useState(false);
  const pollRef = useRef<{ intervalId: number; intentos: number } | null>(null);

  const [cursos, setCursos] = useState<CursoMoodleResponse[]>([]);
  const [cursosLoading, setCursosLoading] = useState(false);
  const [cursosError, setCursosError] = useState<string | null>(null);

  const [expandedCursoId, setExpandedCursoId] = useState<number | null>(null);
  const [notas, setNotas] = useState<NotaMoodleResponse[]>([]);
  const [notasLoading, setNotasLoading] = useState(false);
  const [notasError, setNotasError] = useState<string | null>(null);

  const [eventos, setEventos] = useState<EventoMoodleResponse[]>([]);
  const [eventosLoading, setEventosLoading] = useState(false);
  const [eventosError, setEventosError] = useState<string | null>(null);

  const userId = useMemo(() => {
    const parsed = Number.parseInt(user.id, 10);
    return Number.isNaN(parsed) ? null : parsed;
  }, [user.id]);

  const displayName = useMemo(
    () => getUserDisplayName(user.user_metadata.name, user.user_metadata.codigo),
    [user.user_metadata.codigo, user.user_metadata.name],
  );

  const cargarEstado = useCallback(
    (signal?: AbortSignal) => {
      if (userId == null) {
        setEstadoLoading(false);
        return;
      }

      setEstadoLoading(true);

      obtenerEstado(userId, signal)
        .then((data) => setEstado(data))
        .catch((error) => {
          if (error instanceof DOMException && error.name === 'AbortError') {
            return;
          }
          setEstado({ conectado: false });
        })
        .finally(() => setEstadoLoading(false));
    },
    [userId],
  );

  useEffect(() => {
    const abortController = new AbortController();
    cargarEstado(abortController.signal);
    return () => abortController.abort();
  }, [cargarEstado]);

  const cargarCursos = useCallback(
    (signal?: AbortSignal) => {
      if (userId == null) {
        return;
      }

      setCursosLoading(true);
      setCursosError(null);

      obtenerCursos(userId, signal)
        .then((data) => setCursos(Array.isArray(data) ? data : []))
        .catch((error) => {
          if (error instanceof DOMException && error.name === 'AbortError') {
            return;
          }
          const message = error instanceof Error ? error.message : 'No fue posible cargar tus cursos.';
          setCursosError(message);
          setCursos([]);
        })
        .finally(() => setCursosLoading(false));
    },
    [userId],
  );

  const cargarEventos = useCallback(
    (signal?: AbortSignal) => {
      if (userId == null) {
        return;
      }

      setEventosLoading(true);
      setEventosError(null);

      obtenerEventos(userId, signal)
        .then((data) => setEventos(Array.isArray(data) ? data : []))
        .catch((error) => {
          if (error instanceof DOMException && error.name === 'AbortError') {
            return;
          }
          const message = error instanceof Error ? error.message : 'No fue posible cargar tus próximos eventos.';
          setEventosError(message);
          setEventos([]);
        })
        .finally(() => setEventosLoading(false));
    },
    [userId],
  );

  useEffect(() => {
    if (!estado?.conectado) {
      return;
    }

    const abortController = new AbortController();
    cargarCursos(abortController.signal);
    cargarEventos(abortController.signal);
    return () => abortController.abort();
  }, [estado?.conectado, cargarCursos, cargarEventos]);

  const detenerPolling = useCallback(() => {
    if (pollRef.current) {
      window.clearInterval(pollRef.current.intervalId);
      pollRef.current = null;
    }
    setEsperandoSso(false);
  }, []);

  useEffect(() => () => detenerPolling(), [detenerPolling]);

  const iniciarPolling = useCallback(() => {
    if (userId == null) {
      return;
    }

    const intervalId = window.setInterval(() => {
      if (!pollRef.current) {
        return;
      }

      pollRef.current.intentos += 1;

      obtenerEstado(userId)
        .then((data) => {
          if (data.conectado) {
            detenerPolling();
            setEstado(data);
            setConexionFeedback({ type: 'success', message: 'Tu cuenta del Aula Virtual se conectó correctamente.' });
            return;
          }

          if (pollRef.current && pollRef.current.intentos >= SSO_MAX_POLL_ATTEMPTS) {
            detenerPolling();
            setConexionFeedback({
              type: 'error',
              message: 'No detectamos la conexión a tiempo. Si ya iniciaste sesión en la otra pestaña, espera unos segundos o inténtalo de nuevo.',
            });
          }
        })
        .catch(() => {});
    }, SSO_POLL_INTERVAL_MS);

    pollRef.current = { intervalId, intentos: 0 };
    setEsperandoSso(true);
  }, [detenerPolling, userId]);

  const handleIniciarSso = useCallback(async () => {
    if (userId == null) {
      setConexionFeedback({ type: 'error', message: 'No fue posible identificar al usuario actual.' });
      return;
    }

    if (typeof navigator.registerProtocolHandler !== 'function') {
      setConexionFeedback({
        type: 'error',
        message: 'Tu navegador no soporta este método de conexión. Usa Chrome, Edge o Firefox actualizados.',
      });
      return;
    }

    setConectando(true);
    setConexionFeedback(null);

    try {
      const { launchUrl, passport } = await iniciarSso(userId);

      try {
        navigator.registerProtocolHandler(
          SSO_URL_SCHEME,
          `${window.location.origin}/aulavirtual/sso-callback?url=%s`,
        );
      } catch {
        // El usuario pudo haber rechazado el permiso o ya estaba registrado; continuamos.
      }

      const pending: MoodleSsoPending = { usuarioId: userId, passport, ts: Date.now() };
      localStorage.setItem(PENDING_STORAGE_KEY, JSON.stringify(pending));

      window.open(launchUrl, '_blank', 'noopener');
      iniciarPolling();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No fue posible iniciar la conexión con el Aula Virtual.';
      setConexionFeedback({ type: 'error', message });
    } finally {
      setConectando(false);
    }
  }, [iniciarPolling, userId]);

  const handleCancelarSso = useCallback(() => {
    detenerPolling();
    try {
      localStorage.removeItem(PENDING_STORAGE_KEY);
    } catch {}
    setManualTokenInput('');
  }, [detenerPolling]);

  const handleManualTokenSubmit = useCallback(async () => {
    if (!manualTokenInput.trim()) return;

    setProcesandoManualToken(true);
    setConexionFeedback(null);

    try {
      // Intentar extraer el token base64 del texto ingresado
      const tokenMatch = manualTokenInput.match(/token=([A-Za-z0-9+/=]+)/);
      if (!tokenMatch) {
        throw new Error('No se pudo encontrar el token en el texto ingresado. Asegúrate de copiar todo el mensaje de error o el enlace completo.');
      }

      const decoded = atob(tokenMatch[1]);
      const [siteid, token, privateToken] = decoded.split(':::');

      if (!siteid || !token) {
        throw new Error('El formato del token decodificado no es válido.');
      }

      let pending: MoodleSsoPending | null = null;
      try {
        const raw = localStorage.getItem(PENDING_STORAGE_KEY);
        pending = raw ? (JSON.parse(raw) as MoodleSsoPending) : null;
      } catch {
        pending = null;
      }

      if (!pending) {
        throw new Error('No se encontró una solicitud pendiente. Haz clic en "Conectar mi Aula Virtual" de nuevo e inténtalo otra vez.');
      }

      await confirmarSso({
        usuarioId: pending.usuarioId,
        passport: pending.passport,
        siteid,
        token,
        privateToken: privateToken || null,
      });

      // Éxito
      localStorage.removeItem(PENDING_STORAGE_KEY);
      detenerPolling();
      setConexionFeedback({ type: 'success', message: 'Tu cuenta del Aula Virtual se conectó correctamente.' });
      setManualTokenInput('');
      
      // Forzar actualización del estado
      obtenerEstado(pending.usuarioId).then(setEstado).catch(() => {});

    } catch (error) {
      const message = error instanceof Error ? error.message : 'Error al procesar el token manual.';
      setConexionFeedback({ type: 'error', message });
    } finally {
      setProcesandoManualToken(false);
    }
  }, [manualTokenInput, detenerPolling]);

  const handleDesconectar = useCallback(async () => {
    if (userId == null) {
      return;
    }

    setDesconectando(true);

    try {
      await desconectarCuenta(userId);
      setEstado({ conectado: false });
      setCursos([]);
      setEventos([]);
      setExpandedCursoId(null);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No fue posible desconectar tu cuenta.';
      setConexionFeedback({ type: 'error', message });
    } finally {
      setDesconectando(false);
    }
  }, [userId]);

  const handleExpandCurso = useCallback(
    (curso: CursoMoodleResponse) => {
      if (expandedCursoId === curso.id) {
        setExpandedCursoId(null);
        return;
      }

      if (userId == null) {
        return;
      }

      setExpandedCursoId(curso.id);
      setNotas([]);
      setNotasError(null);
      setNotasLoading(true);

      obtenerNotas(userId, curso.id)
        .then((data) => setNotas(Array.isArray(data) ? data : []))
        .catch((error) => {
          const message = error instanceof Error ? error.message : 'No fue posible cargar las notas de este curso.';
          setNotasError(message);
        })
        .finally(() => setNotasLoading(false));
    },
    [expandedCursoId, userId],
  );

  return (
    <div className="aulavirtual-container">
      <Navbar
        displayName={displayName}
        userCode={user.user_metadata.codigo}
        currentPage="aulavirtual"
        onNavigateToInicio={onNavigateToInicio}
        onNavigateToServicios={onNavigateToServicios}
        onNavigateToPerfil={onNavigateToPerfil}
        onLogout={onLogout}
        isLoggingOut={isLoggingOut}
      />
      <main className="aulavirtual-main">
        <section className="home-welcome-card">
          <div>
            <p className="home-welcome-date">Aula Virtual UPT</p>
            <h1 className="home-title">Aula Virtual</h1>
            <p className="home-subtitle">
              {displayName}, conecta tu cuenta del Aula Virtual para ver tus cursos, notas y próximos eventos sin salir de IntegraUPT.
            </p>
          </div>
          <div className="home-welcome-avatar" aria-hidden="true">
            <GraduationCap size={44} />
          </div>
        </section>

        {estadoLoading ? (
          <div className="aulavirtual-empty" role="status">
            <Loader2 className="aulavirtual-loading-icon" aria-hidden="true" />
            <p>Verificando el estado de tu conexión…</p>
          </div>
        ) : !estado?.conectado ? (
          <section className="aulavirtual-card" aria-labelledby="conectar-title">
            <div className="aulavirtual-card-header">
              <Link2 className="aulavirtual-card-icon" aria-hidden="true" />
              <div>
                <h2 id="conectar-title">Conecta tu cuenta del Aula Virtual</h2>
                <p>Inicia sesión igual que en aulavirtual.upt.edu.pe, en una pestaña segura.</p>
              </div>
            </div>

            <p className="aulavirtual-privacy-note">
              Nunca te pedimos tu contraseña: se abrirá una pestaña con la página real del Aula Virtual para que
              inicies sesión ahí (con Google u otro método). Solo guardamos el acceso (token) que resulta de ese
              inicio de sesión.
            </p>

            {esperandoSso ? (
              <div className="aulavirtual-empty" role="status" style={{ display: 'flex', flexDirection: 'column', gap: '1rem', alignItems: 'center' }}>
                <Loader2 className="aulavirtual-loading-icon" aria-hidden="true" />
                <p>Esperando que termines de iniciar sesión en la otra pestaña…</p>
                
                <div className="aulavirtual-manual-token-container" style={{ width: '100%', maxWidth: '450px', marginTop: '1rem', padding: '1rem', backgroundColor: 'var(--surface-sunken, #f8f9fa)', borderRadius: '8px', border: '1px solid var(--border-subtle, #e9ecef)' }}>
                  <p style={{ fontSize: '0.85rem', marginBottom: '0.5rem', color: 'var(--text-secondary)', textAlign: 'left' }}>
                    <strong>¿Se quedó la pantalla en blanco?</strong> Abre la Consola (presiona <strong>F12</strong>), busca el error rojo que empieza con <em>"Failed to launch 'moodlemobile..."</em>, haz clic derecho, selecciona <strong>"Copy message"</strong> y pégalo aquí:
                  </p>
                  <input
                    type="text"
                    value={manualTokenInput}
                    onChange={(e) => setManualTokenInput(e.target.value)}
                    placeholder="Pega el mensaje de error o el enlace moodlemobile:// aquí..."
                    className="aulavirtual-input"
                    style={{ width: '100%', padding: '0.6rem', borderRadius: '6px', border: '1px solid var(--border-strong, #ced4da)', marginBottom: '0.8rem', fontSize: '0.9rem', outline: 'none' }}
                  />
                  <div style={{ display: 'flex', gap: '0.5rem' }}>
                    <button type="button" className="aulavirtual-primary-button" onClick={handleManualTokenSubmit} disabled={!manualTokenInput.trim() || procesandoManualToken} style={{ flex: 1, padding: '0.6rem', fontSize: '0.9rem', justifyContent: 'center' }}>
                      {procesandoManualToken ? <Loader2 className="aulavirtual-button-icon aulavirtual-button-spinner" /> : 'Validar Token Manual'}
                    </button>
                    <button type="button" className="aulavirtual-disconnect-button" onClick={handleCancelarSso} style={{ padding: '0.6rem', fontSize: '0.9rem', justifyContent: 'center' }}>
                      <XCircle className="aulavirtual-button-icon" aria-hidden="true" />
                      Cancelar
                    </button>
                  </div>
                </div>
              </div>
            ) : (
              <button
                type="button"
                className="aulavirtual-primary-button"
                onClick={handleIniciarSso}
                disabled={conectando}
              >
                {conectando ? (
                  <>
                    <Loader2 className="aulavirtual-button-icon aulavirtual-button-spinner" />
                    Abriendo Aula Virtual
                  </>
                ) : (
                  <>
                    <Link2 className="aulavirtual-button-icon" aria-hidden="true" />
                    Conectar mi Aula Virtual
                  </>
                )}
              </button>
            )}

            {conexionFeedback && (
              <div
                className={`aulavirtual-feedback aulavirtual-feedback-${conexionFeedback.type}`}
                role={conexionFeedback.type === 'error' ? 'alert' : 'status'}
              >
                <FeedbackIcon type={conexionFeedback.type} />
                <p>{conexionFeedback.message}</p>
              </div>
            )}
          </section>
        ) : (
          <>
            <section className="aulavirtual-connected-banner">
              <span>
                Conectado como <strong>{estado.moodleUsername}</strong>
              </span>
              <button
                type="button"
                className="aulavirtual-disconnect-button"
                onClick={handleDesconectar}
                disabled={desconectando}
              >
                {desconectando ? (
                  <Loader2 className="aulavirtual-button-icon aulavirtual-button-spinner" />
                ) : (
                  <Unlink className="aulavirtual-button-icon" aria-hidden="true" />
                )}
                Desconectar
              </button>
            </section>

            {conexionFeedback && (
              <div
                className={`aulavirtual-feedback aulavirtual-feedback-${conexionFeedback.type}`}
                role={conexionFeedback.type === 'error' ? 'alert' : 'status'}
              >
                <FeedbackIcon type={conexionFeedback.type} />
                <p>{conexionFeedback.message}</p>
              </div>
            )}

            <div className="aulavirtual-grid">
              <section className="aulavirtual-card" aria-labelledby="cursos-title">
                <div className="aulavirtual-card-header">
                  <GraduationCap className="aulavirtual-card-icon" aria-hidden="true" />
                  <div>
                    <h2 id="cursos-title">Mis cursos</h2>
                    <p>Selecciona un curso para ver tus calificaciones.</p>
                  </div>
                </div>

                {cursosLoading ? (
                  <div className="aulavirtual-empty" role="status">
                    <Loader2 className="aulavirtual-loading-icon" aria-hidden="true" />
                    <p>Cargando tus cursos…</p>
                  </div>
                ) : cursosError ? (
                  <div className="aulavirtual-feedback aulavirtual-feedback-error" role="alert">
                    <AlertTriangle className="aulavirtual-feedback-icon" aria-hidden="true" />
                    <p>{cursosError}</p>
                  </div>
                ) : cursos.length === 0 ? (
                  <div className="aulavirtual-empty" role="status">
                    <p>No se encontraron cursos matriculados.</p>
                  </div>
                ) : (
                  <ul className="aulavirtual-list">
                    {cursos.map((curso) => {
                      const isExpanded = expandedCursoId === curso.id;
                      return (
                        <li key={curso.id} className="aulavirtual-list-item">
                          <button
                            type="button"
                            className="aulavirtual-curso-header"
                            onClick={() => handleExpandCurso(curso)}
                            aria-expanded={isExpanded}
                          >
                            <span className="aulavirtual-curso-nombre">{curso.nombre}</span>
                            {isExpanded ? <ChevronUp size={18} /> : <ChevronDown size={18} />}
                          </button>

                          {isExpanded && (
                            <div className="aulavirtual-curso-body">
                              {notasLoading ? (
                                <div className="aulavirtual-empty" role="status">
                                  <Loader2 className="aulavirtual-loading-icon" aria-hidden="true" />
                                  <p>Cargando notas…</p>
                                </div>
                              ) : notasError ? (
                                <div className="aulavirtual-feedback aulavirtual-feedback-error" role="alert">
                                  <AlertTriangle className="aulavirtual-feedback-icon" aria-hidden="true" />
                                  <p>{notasError}</p>
                                </div>
                              ) : notas.length === 0 ? (
                                <p className="aulavirtual-curso-empty">Sin calificaciones registradas todavía.</p>
                              ) : (
                                <ul className="aulavirtual-notas-list">
                                  {notas.map((nota, index) => (
                                    <li key={`${curso.id}-${index}`} className="aulavirtual-nota-item">
                                      <span className="aulavirtual-nota-nombre">{nota.nombre}</span>
                                      <span className="aulavirtual-nota-valor">{nota.calificacion ?? '—'}</span>
                                    </li>
                                  ))}
                                </ul>
                              )}
                            </div>
                          )}
                        </li>
                      );
                    })}
                  </ul>
                )}
              </section>

              <section className="aulavirtual-card" aria-labelledby="eventos-title">
                <div className="aulavirtual-card-header">
                  <CalendarClock className="aulavirtual-card-icon" aria-hidden="true" />
                  <div>
                    <h2 id="eventos-title">Próximos eventos</h2>
                    <p>Tareas y actividades próximas a vencer en tus cursos.</p>
                  </div>
                </div>

                {eventosLoading ? (
                  <div className="aulavirtual-empty" role="status">
                    <Loader2 className="aulavirtual-loading-icon" aria-hidden="true" />
                    <p>Cargando próximos eventos…</p>
                  </div>
                ) : eventosError ? (
                  <div className="aulavirtual-feedback aulavirtual-feedback-error" role="alert">
                    <AlertTriangle className="aulavirtual-feedback-icon" aria-hidden="true" />
                    <p>{eventosError}</p>
                  </div>
                ) : eventos.length === 0 ? (
                  <div className="aulavirtual-empty" role="status">
                    <p>No tienes eventos próximos registrados.</p>
                  </div>
                ) : (
                  <ul className="aulavirtual-list">
                    {eventos.map((eventoItem) => (
                      <li key={eventoItem.id} className="aulavirtual-list-item">
                        <span className="aulavirtual-evento-nombre">{eventoItem.nombre}</span>
                        {eventoItem.curso && <span className="aulavirtual-evento-curso">{eventoItem.curso}</span>}
                        <span className="aulavirtual-evento-fecha">{formatDateTime(eventoItem.fecha)}</span>
                      </li>
                    ))}
                  </ul>
                )}
              </section>
            </div>
          </>
        )}
      </main>
    </div>
  );
};

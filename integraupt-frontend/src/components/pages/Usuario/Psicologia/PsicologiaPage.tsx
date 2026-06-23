import type { FormEvent } from 'react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  AlertTriangle,
  Brain,
  CalendarHeart,
  CheckCircle2,
  Clock,
  Loader2,
  XCircle,
} from 'lucide-react';
import '../../../../styles/PsicologiaScreen.css';
import {
  cancelarCita,
  obtenerBloquesDisponibles,
  obtenerCitasPorUsuario,
  obtenerPsicologos,
  registrarCita,
} from './services/psicologiaService';
import type {
  BloqueDisponibleResponse,
  CitaPsicologiaResponse,
  PsicologoResponse,
} from './types';
import { Navbar } from '../Navbar';

interface PsicologiaPageProps {
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
    return <CheckCircle2 className="psicologia-feedback-icon" aria-hidden="true" />;
  }

  if (type === 'error') {
    return <AlertTriangle className="psicologia-feedback-icon" aria-hidden="true" />;
  }

  return <Clock className="psicologia-feedback-icon" aria-hidden="true" />;
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

const getTodayValue = (): string => {
  const now = new Date();
  const offset = now.getTimezoneOffset();
  const local = new Date(now.getTime() - offset * 60000);
  return local.toISOString().slice(0, 10);
};

const formatDateValue = (value?: string | null): string => {
  if (!value) {
    return 'Fecha no disponible';
  }

  const date = new Date(`${value}T00:00:00`);
  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat('es-PE', { dateStyle: 'medium' }).format(date);
};

const formatTimeValue = (value?: string | null): string => {
  if (!value) {
    return '—';
  }

  const date = new Date(`1970-01-01T${value}`);
  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat('es-PE', { hour: '2-digit', minute: '2-digit' }).format(date);
};

const formatDateTime = (value?: string | null): string => {
  if (!value) {
    return 'No disponible';
  }

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat('es-PE', { dateStyle: 'medium', timeStyle: 'short' }).format(date);
};

export const PsicologiaPage: React.FC<PsicologiaPageProps> = ({
  user,
  onNavigateToInicio,
  onNavigateToServicios,
  onNavigateToPerfil,
  onLogout,
  isLoggingOut = false,
}) => {
  const [psicologos, setPsicologos] = useState<PsicologoResponse[]>([]);
  const [psicologosLoading, setPsicologosLoading] = useState(false);
  const [psicologosError, setPsicologosError] = useState<string | null>(null);
  const [selectedPsicologoId, setSelectedPsicologoId] = useState<number | null>(null);
  const [fecha, setFecha] = useState(getTodayValue());
  const [motivo, setMotivo] = useState('');

  const [bloques, setBloques] = useState<BloqueDisponibleResponse[]>([]);
  const [bloquesLoading, setBloquesLoading] = useState(false);
  const [bloquesError, setBloquesError] = useState<string | null>(null);
  const [selectedBloqueId, setSelectedBloqueId] = useState<number | null>(null);

  const [citas, setCitas] = useState<CitaPsicologiaResponse[]>([]);
  const [citasLoading, setCitasLoading] = useState(false);
  const [citasError, setCitasError] = useState<string | null>(null);
  const [cancelandoId, setCancelandoId] = useState<number | null>(null);

  const [registroFeedback, setRegistroFeedback] = useState<Feedback | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const userId = useMemo(() => {
    const parsed = Number.parseInt(user.id, 10);
    return Number.isNaN(parsed) ? null : parsed;
  }, [user.id]);

  const displayName = useMemo(
    () => getUserDisplayName(user.user_metadata.name, user.user_metadata.codigo),
    [user.user_metadata.codigo, user.user_metadata.name],
  );

  const cargarCitas = useCallback(
    (signal?: AbortSignal) => {
      if (userId == null) {
        setCitas([]);
        setCitasError('No fue posible identificar al usuario actual.');
        return;
      }

      setCitasLoading(true);
      setCitasError(null);

      obtenerCitasPorUsuario(userId, signal)
        .then((data) => setCitas(Array.isArray(data) ? data : []))
        .catch((error) => {
          if (error instanceof DOMException && error.name === 'AbortError') {
            return;
          }
          const message = error instanceof Error ? error.message : 'No fue posible cargar tus citas.';
          setCitasError(message);
          setCitas([]);
        })
        .finally(() => setCitasLoading(false));
    },
    [userId],
  );

  useEffect(() => {
    const abortController = new AbortController();
    setPsicologosLoading(true);
    setPsicologosError(null);

    obtenerPsicologos(abortController.signal)
      .then((data) => {
        const lista = Array.isArray(data) ? data : [];
        setPsicologos(lista);
        if (lista.length > 0) {
          setSelectedPsicologoId((current) => current ?? lista[0].id);
        }
      })
      .catch((error) => {
        if (error instanceof DOMException && error.name === 'AbortError') {
          return;
        }
        const message = error instanceof Error ? error.message : 'No fue posible cargar la lista de psicólogos.';
        setPsicologosError(message);
        setPsicologos([]);
      })
      .finally(() => setPsicologosLoading(false));

    return () => abortController.abort();
  }, []);

  useEffect(() => {
    const abortController = new AbortController();
    cargarCitas(abortController.signal);
    return () => abortController.abort();
  }, [cargarCitas]);

  useEffect(() => {
    if (!selectedPsicologoId || !fecha) {
      setBloques([]);
      setSelectedBloqueId(null);
      return;
    }

    const abortController = new AbortController();
    setBloquesLoading(true);
    setBloquesError(null);
    setSelectedBloqueId(null);

    obtenerBloquesDisponibles(selectedPsicologoId, fecha, abortController.signal)
      .then((data) => setBloques(Array.isArray(data) ? data : []))
      .catch((error) => {
        if (error instanceof DOMException && error.name === 'AbortError') {
          return;
        }
        const message = error instanceof Error ? error.message : 'No fue posible cargar los horarios disponibles.';
        setBloquesError(message);
        setBloques([]);
      })
      .finally(() => setBloquesLoading(false));

    return () => abortController.abort();
  }, [selectedPsicologoId, fecha]);

  const psicologoSeleccionado = useMemo(
    () => psicologos.find((psicologo) => psicologo.id === selectedPsicologoId) ?? null,
    [psicologos, selectedPsicologoId],
  );

  const handleRegistrarCita = useCallback(
    async (event: FormEvent<HTMLFormElement>) => {
      event.preventDefault();

      if (userId == null) {
        setRegistroFeedback({ type: 'error', message: 'No fue posible identificar al usuario actual.' });
        return;
      }

      if (!selectedPsicologoId || !fecha || !selectedBloqueId) {
        setRegistroFeedback({
          type: 'error',
          message: 'Selecciona psicólogo, fecha y horario para agendar la cita.',
        });
        return;
      }

      setIsSubmitting(true);
      setRegistroFeedback(null);

      try {
        await registrarCita({
          usuarioId: userId,
          psicologoId: selectedPsicologoId,
          bloqueId: selectedBloqueId,
          fecha,
          motivo: motivo.trim() || undefined,
        });

        setRegistroFeedback({
          type: 'success',
          message: `Cita agendada correctamente con ${psicologoSeleccionado?.nombre ?? 'el psicólogo seleccionado'}.`,
        });
        setMotivo('');
        setSelectedBloqueId(null);
        cargarCitas();
        obtenerBloquesDisponibles(selectedPsicologoId, fecha)
          .then((data) => setBloques(Array.isArray(data) ? data : []))
          .catch(() => undefined);
      } catch (error) {
        const message = error instanceof Error ? error.message : 'No fue posible agendar la cita en este momento.';
        setRegistroFeedback({ type: 'error', message });
      } finally {
        setIsSubmitting(false);
      }
    },
    [cargarCitas, fecha, motivo, psicologoSeleccionado, selectedBloqueId, selectedPsicologoId, userId],
  );

  const handleCancelarCita = useCallback(
    async (cita: CitaPsicologiaResponse) => {
      if (userId == null) {
        return;
      }

      setCancelandoId(cita.id);

      try {
        await cancelarCita(cita.id, userId);
        cargarCitas();
      } catch (error) {
        const message = error instanceof Error ? error.message : 'No fue posible cancelar la cita.';
        setCitasError(message);
      } finally {
        setCancelandoId(null);
      }
    },
    [cargarCitas, userId],
  );

  return (
    <div className="psicologia-container">
      <Navbar
        displayName={displayName}
        userCode={user.user_metadata.codigo}
        currentPage="psicologia"
        onNavigateToInicio={onNavigateToInicio}
        onNavigateToServicios={onNavigateToServicios}
        onNavigateToPerfil={onNavigateToPerfil}
        onLogout={onLogout}
        isLoggingOut={isLoggingOut}
      />
      <main className="psicologia-main">
        <section className="home-welcome-card">
          <div>
            <p className="home-welcome-date">Bienestar Estudiantil</p>
            <h1 className="home-title">Citas de Psicología</h1>
            <p className="home-subtitle">
              {displayName}, agenda una cita con el área de psicología y da seguimiento a tus citas
              programadas.
            </p>
          </div>
          <div className="home-welcome-avatar" aria-hidden="true">
            <Brain size={44} />
          </div>
        </section>

        <div className="psicologia-grid">
          <section className="psicologia-card" aria-labelledby="agendar-cita-title">
            <div className="psicologia-card-header">
              <CalendarHeart className="psicologia-card-icon" aria-hidden="true" />
              <div>
                <h2 id="agendar-cita-title">Agendar una cita</h2>
                <p>Elige al psicólogo, la fecha y el horario disponible para tu sesión.</p>
              </div>
            </div>

            {psicologosError && (
              <div className="psicologia-feedback psicologia-feedback-error" role="alert">
                <AlertTriangle className="psicologia-feedback-icon" aria-hidden="true" />
                <p>{psicologosError}</p>
              </div>
            )}

            <form className="psicologia-form" onSubmit={handleRegistrarCita}>
              <label className="psicologia-label" htmlFor="psicologo-select">
                Psicólogo
              </label>
              <select
                id="psicologo-select"
                className="psicologia-input"
                value={selectedPsicologoId ?? ''}
                onChange={(event) => setSelectedPsicologoId(Number(event.target.value) || null)}
                disabled={psicologosLoading || psicologos.length === 0}
                required
              >
                {psicologosLoading && <option value="">Cargando psicólogos…</option>}
                {!psicologosLoading && psicologos.length === 0 && (
                  <option value="">No hay psicólogos disponibles</option>
                )}
                {psicologos.map((psicologo) => (
                  <option key={psicologo.id} value={psicologo.id}>
                    {psicologo.nombre}
                    {psicologo.especialidad ? ` · ${psicologo.especialidad}` : ''}
                  </option>
                ))}
              </select>

              <label className="psicologia-label" htmlFor="fecha-cita">
                Fecha
              </label>
              <input
                id="fecha-cita"
                type="date"
                className="psicologia-input"
                value={fecha}
                min={getTodayValue()}
                onChange={(event) => setFecha(event.target.value)}
                required
              />

              <label className="psicologia-label">Horario disponible</label>
              {bloquesLoading ? (
                <div className="psicologia-empty" role="status">
                  <Loader2 className="psicologia-loading-icon" aria-hidden="true" />
                  <p>Buscando horarios disponibles…</p>
                </div>
              ) : bloquesError ? (
                <div className="psicologia-feedback psicologia-feedback-error" role="alert">
                  <AlertTriangle className="psicologia-feedback-icon" aria-hidden="true" />
                  <p>{bloquesError}</p>
                </div>
              ) : bloques.length === 0 ? (
                <div className="psicologia-empty" role="status">
                  <p>No hay horarios disponibles para la fecha seleccionada.</p>
                </div>
              ) : (
                <div className="psicologia-bloques-grid" role="group" aria-label="Horarios disponibles">
                  {bloques.map((bloque) => {
                    const isSelected = bloque.id === selectedBloqueId;
                    return (
                      <button
                        key={bloque.id}
                        type="button"
                        className={`psicologia-bloque-button${isSelected ? ' psicologia-bloque-button-selected' : ''}`}
                        onClick={() => setSelectedBloqueId(bloque.id)}
                        aria-pressed={isSelected}
                      >
                        {formatTimeValue(bloque.horaInicio)} — {formatTimeValue(bloque.horaFin)}
                      </button>
                    );
                  })}
                </div>
              )}

              <label className="psicologia-label" htmlFor="motivo-cita">
                Motivo de la consulta (opcional)
              </label>
              <textarea
                id="motivo-cita"
                className="psicologia-textarea"
                value={motivo}
                onChange={(event) => setMotivo(event.target.value)}
                placeholder="Cuéntanos brevemente el motivo de tu consulta."
                rows={4}
              />

              <button
                type="submit"
                className="psicologia-primary-button"
                disabled={isSubmitting || !selectedBloqueId}
              >
                {isSubmitting ? (
                  <>
                    <Loader2 className="psicologia-button-icon psicologia-button-spinner" />
                    Agendando cita
                  </>
                ) : (
                  'Agendar cita'
                )}
              </button>
            </form>

            {registroFeedback && (
              <div
                className={`psicologia-feedback psicologia-feedback-${registroFeedback.type}`}
                role={registroFeedback.type === 'error' ? 'alert' : 'status'}
              >
                <FeedbackIcon type={registroFeedback.type} />
                <p>{registroFeedback.message}</p>
              </div>
            )}
          </section>

          <section className="psicologia-card" aria-labelledby="mis-citas-title">
            <div className="psicologia-card-header">
              <Brain className="psicologia-card-icon" aria-hidden="true" />
              <div>
                <h2 id="mis-citas-title">Mis citas</h2>
                <p>Revisa el estado de tus citas programadas y cancélalas si lo necesitas.</p>
              </div>
            </div>

            {citasLoading ? (
              <div className="psicologia-empty" role="status">
                <Loader2 className="psicologia-loading-icon" aria-hidden="true" />
                <p>Cargando tus citas…</p>
              </div>
            ) : citasError ? (
              <div className="psicologia-feedback psicologia-feedback-error" role="alert">
                <AlertTriangle className="psicologia-feedback-icon" aria-hidden="true" />
                <p>{citasError}</p>
              </div>
            ) : citas.length === 0 ? (
              <div className="psicologia-empty" role="status">
                <p>Aún no tienes citas registradas.</p>
              </div>
            ) : (
              <ul className="psicologia-list">
                {citas.map((cita) => {
                  const estadoSlug = cita.estado?.trim().toLowerCase() ?? '';
                  const puedeCancel = estadoSlug === 'pendiente';

                  return (
                    <li key={cita.id} className="psicologia-list-item">
                      <div className="psicologia-list-header">
                        <span className="psicologia-list-title">{cita.psicologoNombre ?? 'Psicólogo'}</span>
                        <span className={`psicologia-status psicologia-status-${estadoSlug}`}>
                          {cita.estado}
                        </span>
                      </div>
                      <div className="psicologia-list-body">
                        <span>{formatDateValue(cita.fecha)}</span>
                        <span>
                          {formatTimeValue(cita.horaInicio)} — {formatTimeValue(cita.horaFin)}
                        </span>
                      </div>
                      {cita.motivo && <p className="psicologia-list-motivo">{cita.motivo}</p>}
                      <div className="psicologia-list-footer">
                        <span className="psicologia-list-date">
                          Solicitada el {formatDateTime(cita.fechaSolicitud)}
                        </span>
                        {puedeCancel && (
                          <button
                            type="button"
                            className="psicologia-cancel-button"
                            onClick={() => handleCancelarCita(cita)}
                            disabled={cancelandoId === cita.id}
                          >
                            {cancelandoId === cita.id ? (
                              <Loader2 className="psicologia-button-icon psicologia-button-spinner" />
                            ) : (
                              <XCircle className="psicologia-button-icon" aria-hidden="true" />
                            )}
                            Cancelar
                          </button>
                        )}
                      </div>
                    </li>
                  );
                })}
              </ul>
            )}
          </section>
        </div>
      </main>
    </div>
  );
};

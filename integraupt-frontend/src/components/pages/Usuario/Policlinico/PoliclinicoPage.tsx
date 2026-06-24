import type { FormEvent } from 'react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  AlertTriangle,
  CalendarHeart,
  CheckCircle2,
  Clock,
  HeartPulse,
  Loader2,
  XCircle,
} from 'lucide-react';
import '../../../../styles/PoliclinicoScreen.css';
import {
  cancelarCita,
  obtenerBloquesDisponibles,
  obtenerCitasPorUsuario,
  obtenerMedicosPorTipoAtencion,
  obtenerTiposAtencion,
  registrarCita,
} from './services/policlinicoService';
import type {
  BloqueDisponibleResponse,
  CitaPoliclinicoResponse,
  MedicoResponse,
  TipoAtencionResponse,
} from './types';
import { Navbar } from '../Navbar';

interface PoliclinicoPageProps {
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
    return <CheckCircle2 className="policlinico-feedback-icon" aria-hidden="true" />;
  }

  if (type === 'error') {
    return <AlertTriangle className="policlinico-feedback-icon" aria-hidden="true" />;
  }

  return <Clock className="policlinico-feedback-icon" aria-hidden="true" />;
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

export const PoliclinicoPage: React.FC<PoliclinicoPageProps> = ({
  user,
  onNavigateToInicio,
  onNavigateToServicios,
  onNavigateToPerfil,
  onLogout,
  isLoggingOut = false,
}) => {
  const [tiposAtencion, setTiposAtencion] = useState<TipoAtencionResponse[]>([]);
  const [tiposLoading, setTiposLoading] = useState(false);
  const [tiposError, setTiposError] = useState<string | null>(null);
  const [selectedTipoAtencionId, setSelectedTipoAtencionId] = useState<number | null>(null);

  const [medicos, setMedicos] = useState<MedicoResponse[]>([]);
  const [medicosLoading, setMedicosLoading] = useState(false);
  const [medicosError, setMedicosError] = useState<string | null>(null);
  const [selectedMedicoId, setSelectedMedicoId] = useState<number | null>(null);

  const [fecha, setFecha] = useState(getTodayValue());
  const [motivo, setMotivo] = useState('');

  const [bloques, setBloques] = useState<BloqueDisponibleResponse[]>([]);
  const [bloquesLoading, setBloquesLoading] = useState(false);
  const [bloquesError, setBloquesError] = useState<string | null>(null);
  const [selectedBloqueId, setSelectedBloqueId] = useState<number | null>(null);

  const [citas, setCitas] = useState<CitaPoliclinicoResponse[]>([]);
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
    setTiposLoading(true);
    setTiposError(null);

    obtenerTiposAtencion(abortController.signal)
      .then((data) => {
        const lista = Array.isArray(data) ? data : [];
        setTiposAtencion(lista);
        if (lista.length > 0) {
          setSelectedTipoAtencionId((current) => current ?? lista[0].id);
        }
      })
      .catch((error) => {
        if (error instanceof DOMException && error.name === 'AbortError') {
          return;
        }
        const message = error instanceof Error ? error.message : 'No fue posible cargar los tipos de atención.';
        setTiposError(message);
        setTiposAtencion([]);
      })
      .finally(() => setTiposLoading(false));

    return () => abortController.abort();
  }, []);

  useEffect(() => {
    const abortController = new AbortController();
    cargarCitas(abortController.signal);
    return () => abortController.abort();
  }, [cargarCitas]);

  useEffect(() => {
    if (!selectedTipoAtencionId) {
      setMedicos([]);
      setSelectedMedicoId(null);
      return;
    }

    const abortController = new AbortController();
    setMedicosLoading(true);
    setMedicosError(null);
    setSelectedMedicoId(null);

    obtenerMedicosPorTipoAtencion(selectedTipoAtencionId, abortController.signal)
      .then((data) => {
        const lista = Array.isArray(data) ? data : [];
        setMedicos(lista);
        if (lista.length > 0) {
          setSelectedMedicoId(lista[0].id);
        }
      })
      .catch((error) => {
        if (error instanceof DOMException && error.name === 'AbortError') {
          return;
        }
        const message = error instanceof Error ? error.message : 'No fue posible cargar la lista de médicos.';
        setMedicosError(message);
        setMedicos([]);
      })
      .finally(() => setMedicosLoading(false));

    return () => abortController.abort();
  }, [selectedTipoAtencionId]);

  useEffect(() => {
    if (!selectedMedicoId || !fecha) {
      setBloques([]);
      setSelectedBloqueId(null);
      return;
    }

    const abortController = new AbortController();
    setBloquesLoading(true);
    setBloquesError(null);
    setSelectedBloqueId(null);

    obtenerBloquesDisponibles(selectedMedicoId, fecha, abortController.signal)
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
  }, [selectedMedicoId, fecha]);

  const medicoSeleccionado = useMemo(
    () => medicos.find((medico) => medico.id === selectedMedicoId) ?? null,
    [medicos, selectedMedicoId],
  );

  const handleRegistrarCita = useCallback(
    async (event: FormEvent<HTMLFormElement>) => {
      event.preventDefault();

      if (userId == null) {
        setRegistroFeedback({ type: 'error', message: 'No fue posible identificar al usuario actual.' });
        return;
      }

      if (!selectedTipoAtencionId || !selectedMedicoId || !fecha || !selectedBloqueId) {
        setRegistroFeedback({
          type: 'error',
          message: 'Selecciona tipo de atención, médico, fecha y horario para agendar la cita.',
        });
        return;
      }

      setIsSubmitting(true);
      setRegistroFeedback(null);

      try {
        await registrarCita({
          usuarioId: userId,
          medicoId: selectedMedicoId,
          tipoAtencionId: selectedTipoAtencionId,
          bloqueId: selectedBloqueId,
          fecha,
          motivo: motivo.trim() || undefined,
        });

        setRegistroFeedback({
          type: 'success',
          message: `Cita agendada correctamente con ${medicoSeleccionado?.nombre ?? 'el médico seleccionado'}.`,
        });
        setMotivo('');
        setSelectedBloqueId(null);
        cargarCitas();
        obtenerBloquesDisponibles(selectedMedicoId, fecha)
          .then((data) => setBloques(Array.isArray(data) ? data : []))
          .catch(() => undefined);
      } catch (error) {
        const message = error instanceof Error ? error.message : 'No fue posible agendar la cita en este momento.';
        setRegistroFeedback({ type: 'error', message });
      } finally {
        setIsSubmitting(false);
      }
    },
    [cargarCitas, fecha, medicoSeleccionado, motivo, selectedBloqueId, selectedMedicoId, selectedTipoAtencionId, userId],
  );

  const handleCancelarCita = useCallback(
    async (cita: CitaPoliclinicoResponse) => {
      if (userId == null) {
        return;
      }

      setCancelandoId(cita.id);

      try {
        await cancelarCita(cita.id, userId);
        cargarCitas();
      } catch (error) {
        const message = error instanceof Error ? error.message : 'No se pudo cancelar la cita.';
        setCitasError(message);
      } finally {
        setCancelandoId(null);
      }
    },
    [cargarCitas, userId],
  );

  return (
    <div className="policlinico-container">
      <Navbar
        displayName={displayName}
        userCode={user.user_metadata.codigo}
        currentPage="policlinico"
        onNavigateToInicio={onNavigateToInicio}
        onNavigateToServicios={onNavigateToServicios}
        onNavigateToPerfil={onNavigateToPerfil}
        onLogout={onLogout}
        isLoggingOut={isLoggingOut}
      />
      <main className="policlinico-main">
        <section className="home-welcome-card">
          <div>
            <p className="home-welcome-date">Bienestar Universitario</p>
            <h1 className="home-title">Policlínico UPT</h1>
            <p className="home-subtitle">
              {displayName}, agenda una cita en el policlínico y da seguimiento a tus citas programadas.
            </p>
          </div>
          <div className="home-welcome-avatar" aria-hidden="true">
            <HeartPulse size={44} />
          </div>
        </section>

        <div className="policlinico-grid">
          <section className="policlinico-card" aria-labelledby="agendar-cita-title">
            <div className="policlinico-card-header">
              <CalendarHeart className="policlinico-card-icon" aria-hidden="true" />
              <div>
                <h2 id="agendar-cita-title">Agendar una cita</h2>
                <p>Elige el tipo de atención, médico, fecha y horario disponible.</p>
              </div>
            </div>

            {tiposError && (
              <div className="policlinico-feedback policlinico-feedback-error" role="alert">
                <AlertTriangle className="policlinico-feedback-icon" aria-hidden="true" />
                <p>{tiposError}</p>
              </div>
            )}

            <form className="policlinico-form" onSubmit={handleRegistrarCita}>
              <label className="policlinico-label" htmlFor="tipo-atencion-select">
                Tipo de atención
              </label>
              <select
                id="tipo-atencion-select"
                className="policlinico-input"
                value={selectedTipoAtencionId ?? ''}
                onChange={(event) => setSelectedTipoAtencionId(Number(event.target.value) || null)}
                disabled={tiposLoading || tiposAtencion.length === 0}
                required
              >
                {tiposLoading && <option value="">Cargando tipos de atención…</option>}
                {!tiposLoading && tiposAtencion.length === 0 && (
                  <option value="">No hay tipos de atención disponibles</option>
                )}
                {tiposAtencion.map((tipo) => (
                  <option key={tipo.id} value={tipo.id}>{tipo.nombre}</option>
                ))}
              </select>

              <label className="policlinico-label" htmlFor="medico-select">
                Médico
              </label>
              {medicosError ? (
                <div className="policlinico-feedback policlinico-feedback-error" role="alert">
                  <AlertTriangle className="policlinico-feedback-icon" aria-hidden="true" />
                  <p>{medicosError}</p>
                </div>
              ) : (
                <select
                  id="medico-select"
                  className="policlinico-input"
                  value={selectedMedicoId ?? ''}
                  onChange={(event) => setSelectedMedicoId(Number(event.target.value) || null)}
                  disabled={medicosLoading || medicos.length === 0}
                  required
                >
                  {medicosLoading && <option value="">Cargando médicos…</option>}
                  {!medicosLoading && medicos.length === 0 && (
                    <option value="">No hay médicos disponibles para este tipo de atención</option>
                  )}
                  {medicos.map((medico) => (
                    <option key={medico.id} value={medico.id}>{medico.nombre}</option>
                  ))}
                </select>
              )}

              <label className="policlinico-label" htmlFor="fecha-cita">
                Fecha
              </label>
              <input
                id="fecha-cita"
                type="date"
                className="policlinico-input"
                value={fecha}
                min={getTodayValue()}
                onChange={(event) => setFecha(event.target.value)}
                required
              />

              <label className="policlinico-label">Horario disponible</label>
              {bloquesLoading ? (
                <div className="policlinico-empty" role="status">
                  <Loader2 className="policlinico-loading-icon" aria-hidden="true" />
                  <p>Buscando horarios disponibles…</p>
                </div>
              ) : bloquesError ? (
                <div className="policlinico-feedback policlinico-feedback-error" role="alert">
                  <AlertTriangle className="policlinico-feedback-icon" aria-hidden="true" />
                  <p>{bloquesError}</p>
                </div>
              ) : bloques.length === 0 ? (
                <div className="policlinico-empty" role="status">
                  <p>No hay horarios disponibles para la fecha seleccionada.</p>
                </div>
              ) : (
                <div className="policlinico-bloques-grid" role="group" aria-label="Horarios disponibles">
                  {bloques.map((bloque) => {
                    const isSelected = bloque.id === selectedBloqueId;
                    return (
                      <button
                        key={bloque.id}
                        type="button"
                        className={`policlinico-bloque-button${isSelected ? ' policlinico-bloque-button-selected' : ''}`}
                        onClick={() => setSelectedBloqueId(bloque.id)}
                        aria-pressed={isSelected}
                      >
                        {formatTimeValue(bloque.horaInicio)} — {formatTimeValue(bloque.horaFin)}
                      </button>
                    );
                  })}
                </div>
              )}

              <label className="policlinico-label" htmlFor="motivo-cita">
                Motivo de la consulta (opcional)
              </label>
              <textarea
                id="motivo-cita"
                className="policlinico-textarea"
                value={motivo}
                onChange={(event) => setMotivo(event.target.value)}
                placeholder="Cuéntanos brevemente el motivo de tu consulta."
                rows={4}
              />

              <button
                type="submit"
                className="policlinico-primary-button"
                disabled={isSubmitting || !selectedBloqueId}
              >
                {isSubmitting ? (
                  <>
                    <Loader2 className="policlinico-button-icon policlinico-button-spinner" />
                    Agendando cita
                  </>
                ) : (
                  'Agendar cita'
                )}
              </button>
            </form>

            {registroFeedback && (
              <div
                className={`policlinico-feedback policlinico-feedback-${registroFeedback.type}`}
                role={registroFeedback.type === 'error' ? 'alert' : 'status'}
              >
                <FeedbackIcon type={registroFeedback.type} />
                <p>{registroFeedback.message}</p>
              </div>
            )}
          </section>

          <section className="policlinico-card" aria-labelledby="mis-citas-title">
            <div className="policlinico-card-header">
              <HeartPulse className="policlinico-card-icon" aria-hidden="true" />
              <div>
                <h2 id="mis-citas-title">Mis citas</h2>
                <p>Revisa el estado de tus citas programadas y cancélalas si lo necesitas.</p>
              </div>
            </div>

            {citasLoading ? (
              <div className="policlinico-empty" role="status">
                <Loader2 className="policlinico-loading-icon" aria-hidden="true" />
                <p>Cargando tus citas…</p>
              </div>
            ) : citasError ? (
              <div className="policlinico-feedback policlinico-feedback-error" role="alert">
                <AlertTriangle className="policlinico-feedback-icon" aria-hidden="true" />
                <p>{citasError}</p>
              </div>
            ) : citas.length === 0 ? (
              <div className="policlinico-empty" role="status">
                <p>Aún no tienes citas registradas.</p>
              </div>
            ) : (
              <ul className="policlinico-list">
                {citas.map((cita) => {
                  const estadoSlug = cita.estado?.trim().toLowerCase() ?? '';
                  const puedeCancel = estadoSlug === 'pendiente';

                  return (
                    <li key={cita.id} className="policlinico-list-item">
                      <div className="policlinico-list-header">
                        <span className="policlinico-list-title">
                          {cita.medicoNombre ?? 'Médico'} · {cita.tipoAtencionNombre ?? 'Atención'}
                        </span>
                        <span className={`policlinico-status policlinico-status-${estadoSlug}`}>
                          {cita.estado}
                        </span>
                      </div>
                      <div className="policlinico-list-body">
                        <span>{formatDateValue(cita.fecha)}</span>
                        <span>
                          {formatTimeValue(cita.horaInicio)} — {formatTimeValue(cita.horaFin)}
                        </span>
                      </div>
                      {cita.motivo && <p className="policlinico-list-motivo">{cita.motivo}</p>}
                      <div className="policlinico-list-footer">
                        <span className="policlinico-list-date">
                          Solicitada el {formatDateTime(cita.fechaSolicitud)}
                        </span>
                        {puedeCancel && (
                          <button
                            type="button"
                            className="policlinico-cancel-button"
                            onClick={() => handleCancelarCita(cita)}
                            disabled={cancelandoId === cita.id}
                          >
                            {cancelandoId === cita.id ? (
                              <Loader2 className="policlinico-button-icon policlinico-button-spinner" />
                            ) : (
                              <XCircle className="policlinico-button-icon" aria-hidden="true" />
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

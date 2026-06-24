import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  AlertTriangle,
  CheckCircle2,
  Loader2,
  Medal,
  Swords,
  Trophy,
  XCircle,
} from 'lucide-react';
import '../../../../styles/OlimpiadasScreen.css';
import { Navbar } from '../Navbar';
import {
  cancelarInscripcion,
  inscribirse,
  obtenerDisciplinasDeEdicion,
  obtenerEdicionActual,
  obtenerEdiciones,
  obtenerFixture,
  obtenerMisInscripciones,
  obtenerTablaPosiciones,
} from './services/olimpiadasService';
import type {
  EdicionDisciplinaResponse,
  EdicionResponse,
  InscripcionMiaResponse,
  ResultadoResponse,
  TablaPosicionResponse,
} from './types';

interface OlimpiadasPageProps {
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

type Feedback = { type: 'success' | 'error'; message: string };

const ESTADO_EDICION_LABEL: Record<string, string> = {
  planificada: 'Planificada',
  inscripcion_abierta: 'Inscripción abierta',
  inscripcion_cerrada: 'Inscripción cerrada',
  en_curso: 'En curso',
  finalizada: 'Finalizada',
  cancelada: 'Cancelada',
};

const getUserDisplayName = (name?: string, codigo?: string): string => {
  const trimmedName = name?.trim();
  if (trimmedName) return trimmedName;
  const trimmedCodigo = codigo?.trim();
  if (trimmedCodigo) return trimmedCodigo;
  return 'Usuario';
};

const formatPeriodo = (edicion: EdicionResponse): string =>
  `${edicion.anioInicio}-${edicion.semestreInicio} a ${edicion.anioFin ?? '—'}-${edicion.semestreFin ?? '—'}`;

const formatFecha = (value?: string | null): string => {
  if (!value) return '—';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return new Intl.DateTimeFormat('es-PE', { dateStyle: 'medium', timeStyle: 'short' }).format(date);
};

export const OlimpiadasPage: React.FC<OlimpiadasPageProps> = ({
  user,
  onNavigateToInicio,
  onNavigateToServicios,
  onNavigateToPerfil,
  onLogout,
  isLoggingOut = false,
}) => {
  const userId = useMemo(() => {
    const parsed = Number.parseInt(user.id, 10);
    return Number.isNaN(parsed) ? null : parsed;
  }, [user.id]);

  const displayName = useMemo(
    () => getUserDisplayName(user.user_metadata.name, user.user_metadata.codigo),
    [user.user_metadata.codigo, user.user_metadata.name],
  );

  const [ediciones, setEdiciones] = useState<EdicionResponse[]>([]);
  const [edicionActualId, setEdicionActualId] = useState<number | null>(null);
  const [selectedEdicionId, setSelectedEdicionId] = useState<number | null>(null);
  const [edicionesLoading, setEdicionesLoading] = useState(false);
  const [edicionesError, setEdicionesError] = useState<string | null>(null);

  const [disciplinas, setDisciplinas] = useState<EdicionDisciplinaResponse[]>([]);
  const [disciplinasLoading, setDisciplinasLoading] = useState(false);
  const [disciplinasError, setDisciplinasError] = useState<string | null>(null);
  const [selectedDisciplinaVinculoId, setSelectedDisciplinaVinculoId] = useState<number | null>(null);

  const [fixture, setFixture] = useState<ResultadoResponse[]>([]);
  const [tabla, setTabla] = useState<TablaPosicionResponse[]>([]);
  const [resultadosLoading, setResultadosLoading] = useState(false);

  const [misInscripciones, setMisInscripciones] = useState<InscripcionMiaResponse[]>([]);
  const [inscripcionesLoading, setInscripcionesLoading] = useState(false);
  const [feedback, setFeedback] = useState<Feedback | null>(null);
  const [inscribiendoId, setInscribiendoId] = useState<number | null>(null);
  const [cancelandoId, setCancelandoId] = useState<number | null>(null);

  const selectedEdicion = useMemo(
    () => ediciones.find((e) => e.id === selectedEdicionId) ?? null,
    [ediciones, selectedEdicionId],
  );

  const cargarMisInscripciones = useCallback((signal?: AbortSignal) => {
    if (userId == null) return;
    setInscripcionesLoading(true);
    obtenerMisInscripciones(userId, signal)
      .then((data) => setMisInscripciones(Array.isArray(data) ? data : []))
      .catch((error) => {
        if (error instanceof DOMException && error.name === 'AbortError') return;
      })
      .finally(() => setInscripcionesLoading(false));
  }, [userId]);

  useEffect(() => {
    const abortController = new AbortController();
    setEdicionesLoading(true);
    setEdicionesError(null);

    Promise.all([
      obtenerEdiciones(abortController.signal),
      obtenerEdicionActual(abortController.signal),
    ])
      .then(([listado, actual]) => {
        const lista = Array.isArray(listado) ? listado : [];
        setEdiciones(lista);
        const actualId = actual?.id ?? lista[0]?.id ?? null;
        setEdicionActualId(actual?.id ?? null);
        setSelectedEdicionId(actualId);
      })
      .catch((error) => {
        if (error instanceof DOMException && error.name === 'AbortError') return;
        const message = error instanceof Error ? error.message : 'No fue posible cargar las ediciones de Olimpiadas.';
        setEdicionesError(message);
      })
      .finally(() => setEdicionesLoading(false));

    return () => abortController.abort();
  }, []);

  useEffect(() => {
    const abortController = new AbortController();
    cargarMisInscripciones(abortController.signal);
    return () => abortController.abort();
  }, [cargarMisInscripciones]);

  useEffect(() => {
    if (selectedEdicionId == null) {
      setDisciplinas([]);
      return;
    }

    const abortController = new AbortController();
    setDisciplinasLoading(true);
    setDisciplinasError(null);
    setSelectedDisciplinaVinculoId(null);

    obtenerDisciplinasDeEdicion(selectedEdicionId, abortController.signal)
      .then((data) => {
        const lista = Array.isArray(data) ? data : [];
        setDisciplinas(lista);
        if (lista.length > 0) {
          setSelectedDisciplinaVinculoId(lista[0].id);
        }
      })
      .catch((error) => {
        if (error instanceof DOMException && error.name === 'AbortError') return;
        const message = error instanceof Error ? error.message : 'No fue posible cargar las disciplinas de esta edición.';
        setDisciplinasError(message);
        setDisciplinas([]);
      })
      .finally(() => setDisciplinasLoading(false));

    return () => abortController.abort();
  }, [selectedEdicionId]);

  useEffect(() => {
    if (selectedDisciplinaVinculoId == null) {
      setFixture([]);
      setTabla([]);
      return;
    }

    const abortController = new AbortController();
    setResultadosLoading(true);

    Promise.all([
      obtenerFixture(selectedDisciplinaVinculoId, abortController.signal),
      obtenerTablaPosiciones(selectedDisciplinaVinculoId, abortController.signal),
    ])
      .then(([fixtureData, tablaData]) => {
        setFixture(Array.isArray(fixtureData) ? fixtureData : []);
        setTabla(Array.isArray(tablaData) ? tablaData : []);
      })
      .catch((error) => {
        if (error instanceof DOMException && error.name === 'AbortError') return;
      })
      .finally(() => setResultadosLoading(false));

    return () => abortController.abort();
  }, [selectedDisciplinaVinculoId]);

  const notify = useCallback((value: Feedback) => {
    setFeedback(value);
    window.setTimeout(() => setFeedback((current) => (current === value ? null : current)), 4500);
  }, []);

  const handleInscribirse = useCallback(
    async (vinculo: EdicionDisciplinaResponse) => {
      if (userId == null) {
        notify({ type: 'error', message: 'No fue posible identificar al usuario actual.' });
        return;
      }

      setInscribiendoId(vinculo.id);
      try {
        await inscribirse({ edicionDisciplinaId: vinculo.id, usuarioId: userId });
        notify({ type: 'success', message: `Te inscribiste en ${vinculo.disciplinaNombre ?? 'la disciplina'}.` });
        cargarMisInscripciones();
        if (selectedEdicionId != null) {
          obtenerDisciplinasDeEdicion(selectedEdicionId).then((data) => setDisciplinas(Array.isArray(data) ? data : []));
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : 'No fue posible completar la inscripción.';
        notify({ type: 'error', message });
      } finally {
        setInscribiendoId(null);
      }
    },
    [userId, notify, cargarMisInscripciones, selectedEdicionId],
  );

  const handleCancelar = useCallback(
    async (inscripcion: InscripcionMiaResponse) => {
      if (userId == null) return;
      setCancelandoId(inscripcion.id);
      try {
        await cancelarInscripcion(inscripcion.id, userId);
        notify({ type: 'success', message: 'Inscripción cancelada.' });
        cargarMisInscripciones();
      } catch (error) {
        const message = error instanceof Error ? error.message : 'No fue posible cancelar la inscripción.';
        notify({ type: 'error', message });
      } finally {
        setCancelandoId(null);
      }
    },
    [userId, notify, cargarMisInscripciones],
  );

  const disciplinasInscritas = useMemo(
    () => new Set(misInscripciones.filter((i) => i.estado === 'inscrito').map((i) => i.edicionDisciplinaId)),
    [misInscripciones],
  );

  return (
    <div className="olimpiadas-container">
      <Navbar
        displayName={displayName}
        userCode={user.user_metadata.codigo}
        currentPage="olimpiadas"
        onNavigateToInicio={onNavigateToInicio}
        onNavigateToServicios={onNavigateToServicios}
        onNavigateToPerfil={onNavigateToPerfil}
        onLogout={onLogout}
        isLoggingOut={isLoggingOut}
      />
      <main className="olimpiadas-main">
        <section className="home-welcome-card olimpiadas-welcome">
          <div>
            <p className="home-welcome-date">Competencia Interfacultades</p>
            <h1 className="home-title">Olimpiadas Interfacultades</h1>
            <p className="home-subtitle">
              {displayName}, sigue las disciplinas, los resultados y el estado de inscripción de tu facultad.
            </p>
          </div>
          <div className="home-welcome-avatar" aria-hidden="true">
            <Trophy size={44} />
          </div>
        </section>

        {feedback && (
          <div className={`olimpiadas-feedback olimpiadas-feedback-${feedback.type}`} role="status">
            {feedback.type === 'success' ? (
              <CheckCircle2 className="olimpiadas-feedback-icon" aria-hidden="true" />
            ) : (
              <AlertTriangle className="olimpiadas-feedback-icon" aria-hidden="true" />
            )}
            <p>{feedback.message}</p>
          </div>
        )}

        <section className="olimpiadas-card">
          <div className="olimpiadas-card-header">
            <Medal className="olimpiadas-card-icon" aria-hidden="true" />
            <div>
              <h2>Edición</h2>
              <p>Selecciona una edición actual o histórica para revisar sus disciplinas y resultados.</p>
            </div>
          </div>

          {edicionesLoading ? (
            <div className="olimpiadas-empty" role="status">
              <Loader2 className="olimpiadas-loading-icon" aria-hidden="true" />
              <p>Cargando ediciones…</p>
            </div>
          ) : edicionesError ? (
            <div className="olimpiadas-feedback olimpiadas-feedback-error" role="alert">
              <AlertTriangle className="olimpiadas-feedback-icon" aria-hidden="true" />
              <p>{edicionesError}</p>
            </div>
          ) : ediciones.length === 0 ? (
            <div className="olimpiadas-empty" role="status">
              <p>Aún no se ha registrado ninguna edición de las Olimpiadas.</p>
            </div>
          ) : (
            <>
              <select
                className="olimpiadas-input"
                value={selectedEdicionId ?? ''}
                onChange={(event) => setSelectedEdicionId(Number(event.target.value) || null)}
              >
                {ediciones.map((edicion) => (
                  <option key={edicion.id} value={edicion.id}>
                    {edicion.nombre} ({formatPeriodo(edicion)}){edicion.id === edicionActualId ? ' · actual' : ''}
                  </option>
                ))}
              </select>

              {selectedEdicion && (
                <div className="olimpiadas-edicion-info">
                  <span className={`olimpiadas-status olimpiadas-status-${selectedEdicion.estado}`}>
                    {ESTADO_EDICION_LABEL[selectedEdicion.estado] ?? selectedEdicion.estado}
                  </span>
                  {selectedEdicion.inscripcionAbierta ? (
                    <span className="olimpiadas-inscripcion-abierta">
                      Inscripciones abiertas
                      {selectedEdicion.fechaCierreInscripcion
                        ? ` hasta ${formatFecha(selectedEdicion.fechaCierreInscripcion)}`
                        : ''}
                    </span>
                  ) : (
                    <span className="olimpiadas-inscripcion-cerrada">Inscripciones no disponibles</span>
                  )}
                </div>
              )}
            </>
          )}
        </section>

        <section className="olimpiadas-card" aria-labelledby="disciplinas-title">
          <div className="olimpiadas-card-header">
            <Swords className="olimpiadas-card-icon" aria-hidden="true" />
            <div>
              <h2 id="disciplinas-title">Disciplinas</h2>
              <p>Disciplinas habilitadas para la edición seleccionada.</p>
            </div>
          </div>

          {disciplinasLoading ? (
            <div className="olimpiadas-empty" role="status">
              <Loader2 className="olimpiadas-loading-icon" aria-hidden="true" />
              <p>Cargando disciplinas…</p>
            </div>
          ) : disciplinasError ? (
            <div className="olimpiadas-feedback olimpiadas-feedback-error" role="alert">
              <AlertTriangle className="olimpiadas-feedback-icon" aria-hidden="true" />
              <p>{disciplinasError}</p>
            </div>
          ) : disciplinas.length === 0 ? (
            <div className="olimpiadas-empty" role="status">
              <p>Esta edición todavía no tiene disciplinas configuradas.</p>
            </div>
          ) : (
            <div className="olimpiadas-disciplinas-grid">
              {disciplinas.map((vinculo) => {
                const yaInscrito = disciplinasInscritas.has(vinculo.id);
                const puedeInscribirse =
                  selectedEdicion?.inscripcionAbierta && vinculo.estado === 'activa' && !yaInscrito;

                return (
                  <button
                    key={vinculo.id}
                    type="button"
                    className={`olimpiadas-disciplina-card${
                      vinculo.id === selectedDisciplinaVinculoId ? ' olimpiadas-disciplina-card-selected' : ''
                    }`}
                    onClick={() => setSelectedDisciplinaVinculoId(vinculo.id)}
                  >
                    <span className="olimpiadas-disciplina-nombre">{vinculo.disciplinaNombre}</span>
                    <span className="olimpiadas-disciplina-meta">
                      {vinculo.tipoParticipacion === 'individual' ? 'Individual' : 'Por equipos'} ·{' '}
                      {vinculo.inscritosActivos} inscritos
                      {vinculo.cupoMaximoPorFacultad ? ` / cupo ${vinculo.cupoMaximoPorFacultad}` : ''}
                    </span>
                    <span
                      className="olimpiadas-disciplina-action"
                      onClick={(event) => {
                        event.stopPropagation();
                        if (puedeInscribirse) {
                          handleInscribirse(vinculo);
                        }
                      }}
                    >
                      {yaInscrito ? (
                        'Ya estás inscrito'
                      ) : inscribiendoId === vinculo.id ? (
                        <Loader2 className="olimpiadas-button-icon olimpiadas-button-spinner" />
                      ) : puedeInscribirse ? (
                        'Inscribirme'
                      ) : (
                        'Inscripción no disponible'
                      )}
                    </span>
                  </button>
                );
              })}
            </div>
          )}
        </section>

        <div className="olimpiadas-grid">
          <section className="olimpiadas-card" aria-labelledby="fixture-title">
            <div className="olimpiadas-card-header">
              <Swords className="olimpiadas-card-icon" aria-hidden="true" />
              <div>
                <h2 id="fixture-title">Fixture de enfrentamientos</h2>
                <p>Sorteos y resultados de la disciplina seleccionada.</p>
              </div>
            </div>

            {resultadosLoading ? (
              <div className="olimpiadas-empty" role="status">
                <Loader2 className="olimpiadas-loading-icon" aria-hidden="true" />
                <p>Cargando fixture…</p>
              </div>
            ) : fixture.length === 0 ? (
              <div className="olimpiadas-empty" role="status">
                <p>Aún no hay enfrentamientos programados para esta disciplina.</p>
              </div>
            ) : (
              <ul className="olimpiadas-list">
                {fixture.map((resultado) => (
                  <li key={resultado.id} className="olimpiadas-list-item">
                    <div className="olimpiadas-list-header">
                      <span className="olimpiadas-list-title">
                        {resultado.facultadLocalNombre ?? 'Por definir'}
                        {resultado.facultadVisitanteNombre ? ` vs ${resultado.facultadVisitanteNombre}` : ''}
                      </span>
                      <span className={`olimpiadas-status olimpiadas-status-${resultado.estado}`}>
                        {resultado.estado}
                      </span>
                    </div>
                    <div className="olimpiadas-list-body">
                      <span>{resultado.fase}{resultado.grupo ? ` · ${resultado.grupo}` : ''}</span>
                      <span>{formatFecha(resultado.fechaPartido)}</span>
                      {resultado.puntajeLocal !== null && resultado.puntajeVisitante !== null && (
                        <span className="olimpiadas-marcador">
                          {resultado.puntajeLocal} - {resultado.puntajeVisitante}
                        </span>
                      )}
                    </div>
                  </li>
                ))}
              </ul>
            )}
          </section>

          <section className="olimpiadas-card" aria-labelledby="tabla-title">
            <div className="olimpiadas-card-header">
              <Trophy className="olimpiadas-card-icon" aria-hidden="true" />
              <div>
                <h2 id="tabla-title">Tabla de posiciones</h2>
                <p>Resultado acumulado por facultad en la disciplina seleccionada.</p>
              </div>
            </div>

            {tabla.length === 0 ? (
              <div className="olimpiadas-empty" role="status">
                <p>Todavía no hay resultados registrados para calcular la tabla.</p>
              </div>
            ) : (
              <table className="olimpiadas-tabla">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Facultad</th>
                    <th>PJ</th>
                    <th>PG</th>
                    <th>PE</th>
                    <th>PP</th>
                    <th>Pts</th>
                  </tr>
                </thead>
                <tbody>
                  {tabla.map((fila) => (
                    <tr key={fila.facultadId}>
                      <td>{fila.posicion ?? '—'}</td>
                      <td>{fila.facultadAbreviatura ?? fila.facultadNombre}</td>
                      <td>{fila.partidosJugados}</td>
                      <td>{fila.partidosGanados}</td>
                      <td>{fila.partidosEmpatados}</td>
                      <td>{fila.partidosPerdidos}</td>
                      <td><strong>{fila.puntos}</strong></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </section>
        </div>

        <section className="olimpiadas-card" aria-labelledby="mis-inscripciones-title">
          <div className="olimpiadas-card-header">
            <Medal className="olimpiadas-card-icon" aria-hidden="true" />
            <div>
              <h2 id="mis-inscripciones-title">Mis inscripciones</h2>
              <p>Disciplinas en las que te encuentras inscrito.</p>
            </div>
          </div>

          {inscripcionesLoading ? (
            <div className="olimpiadas-empty" role="status">
              <Loader2 className="olimpiadas-loading-icon" aria-hidden="true" />
              <p>Cargando tus inscripciones…</p>
            </div>
          ) : misInscripciones.length === 0 ? (
            <div className="olimpiadas-empty" role="status">
              <p>Aún no te has inscrito en ninguna disciplina.</p>
            </div>
          ) : (
            <ul className="olimpiadas-list">
              {misInscripciones.map((inscripcion) => (
                <li key={inscripcion.id} className="olimpiadas-list-item">
                  <div className="olimpiadas-list-header">
                    <span className="olimpiadas-list-title">
                      {inscripcion.disciplinaNombre} · {inscripcion.edicionNombre}
                    </span>
                    <span className={`olimpiadas-status olimpiadas-status-${inscripcion.estado}`}>
                      {inscripcion.estado}
                    </span>
                  </div>
                  <div className="olimpiadas-list-body">
                    <span>Representas a {inscripcion.facultadNombre}</span>
                    <span>Inscrito el {formatFecha(inscripcion.fechaInscripcion)}</span>
                  </div>
                  {inscripcion.estado === 'inscrito' && (
                    <div className="olimpiadas-list-footer">
                      <button
                        type="button"
                        className="olimpiadas-cancel-button"
                        onClick={() => handleCancelar(inscripcion)}
                        disabled={cancelandoId === inscripcion.id}
                      >
                        {cancelandoId === inscripcion.id ? (
                          <Loader2 className="olimpiadas-button-icon olimpiadas-button-spinner" />
                        ) : (
                          <XCircle className="olimpiadas-button-icon" aria-hidden="true" />
                        )}
                        Cancelar
                      </button>
                    </div>
                  )}
                </li>
              ))}
            </ul>
          )}
        </section>
      </main>
    </div>
  );
};

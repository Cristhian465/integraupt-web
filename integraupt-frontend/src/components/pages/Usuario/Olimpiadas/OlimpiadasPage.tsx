import { useCallback, useEffect, useMemo, useState, type CSSProperties } from 'react';
import {
  AlertTriangle,
  CheckCircle2,
  Loader2,
  Medal,
  MessageCircle,
  Newspaper,
  Send,
  Swords,
  Target,
  Trophy,
  XCircle,
} from 'lucide-react';
import '../../../../styles/OlimpiadasScreen.css';
import { Navbar } from '../Navbar';
import { facultadColor } from '../../../../utils/facultadColors';
import {
  cancelarInscripcion,
  comentarPost,
  obtenerAnotadores,
  obtenerComentarios,
  obtenerDisciplinasDeEdicion,
  obtenerEdicionActual,
  obtenerEdiciones,
  obtenerFixture,
  obtenerMedallero,
  obtenerMisInscripciones,
  obtenerPosts,
  obtenerResultadosPosicion,
  obtenerTablaPosiciones,
} from './services/olimpiadasService';
import type {
  AnotadorResponse,
  ComentarioResponse,
  EdicionDisciplinaResponse,
  EdicionResponse,
  InscripcionMiaResponse,
  MedalleroFilaResponse,
  PostResponse,
  ResultadoResponse,
  ResultadoPosicionResponse,
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
  const [resultadosPosicion, setResultadosPosicion] = useState<ResultadoPosicionResponse[]>([]);
  const [tabla, setTabla] = useState<TablaPosicionResponse[]>([]);
  const [resultadosLoading, setResultadosLoading] = useState(false);

  const [misInscripciones, setMisInscripciones] = useState<InscripcionMiaResponse[]>([]);
  const [inscripcionesLoading, setInscripcionesLoading] = useState(false);
  const [feedback, setFeedback] = useState<Feedback | null>(null);
  const [cancelandoId, setCancelandoId] = useState<number | null>(null);

  const [medallero, setMedallero] = useState<MedalleroFilaResponse[]>([]);
  const [medalleroLoading, setMedalleroLoading] = useState(false);

  const [anotadores, setAnotadores] = useState<AnotadorResponse[]>([]);

  const [fixtureFiltro, setFixtureFiltro] = useState<'todos' | 'proximos' | 'anteriores'>('todos');

  const [posts, setPosts] = useState<PostResponse[]>([]);
  const [postsLoading, setPostsLoading] = useState(false);
  const [expandedPostId, setExpandedPostId] = useState<number | null>(null);
  const [comentariosPorPost, setComentariosPorPost] = useState<Record<number, ComentarioResponse[]>>({});
  const [comentarioTexto, setComentarioTexto] = useState('');
  const [comentando, setComentando] = useState(false);

  const selectedEdicion = useMemo(
    () => ediciones.find((e) => e.id === selectedEdicionId) ?? null,
    [ediciones, selectedEdicionId],
  );

  const selectedVinculo = useMemo(
    () => disciplinas.find((d) => d.id === selectedDisciplinaVinculoId) ?? null,
    [disciplinas, selectedDisciplinaVinculoId],
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
      setResultadosPosicion([]);
      setTabla([]);
      return;
    }

    const abortController = new AbortController();
    setResultadosLoading(true);

    const selectedVinculo = disciplinas.find((d) => d.id === selectedDisciplinaVinculoId);
    const isPosiciones = selectedVinculo?.tipoPuntuacion === 'posiciones';

    if (isPosiciones) {
      Promise.all([
        obtenerResultadosPosicion(selectedDisciplinaVinculoId, abortController.signal),
        obtenerTablaPosiciones(selectedDisciplinaVinculoId, abortController.signal),
      ])
        .then(([posicionesData, tablaData]) => {
          setResultadosPosicion(Array.isArray(posicionesData) ? posicionesData : []);
          setFixture([]);
          setTabla(Array.isArray(tablaData) ? tablaData : []);
        })
        .catch((error) => {
          if (error instanceof DOMException && error.name === 'AbortError') return;
        })
        .finally(() => setResultadosLoading(false));
    } else {
      Promise.all([
        obtenerFixture(selectedDisciplinaVinculoId, abortController.signal),
        obtenerTablaPosiciones(selectedDisciplinaVinculoId, abortController.signal),
      ])
        .then(([fixtureData, tablaData]) => {
          setFixture(Array.isArray(fixtureData) ? fixtureData : []);
          setResultadosPosicion([]);
          setTabla(Array.isArray(tablaData) ? tablaData : []);
        })
        .catch((error) => {
          if (error instanceof DOMException && error.name === 'AbortError') return;
        })
        .finally(() => setResultadosLoading(false));
    }

    return () => abortController.abort();
  }, [selectedDisciplinaVinculoId, disciplinas]);

  useEffect(() => {
    const vinculo = disciplinas.find((d) => d.id === selectedDisciplinaVinculoId);
    if (selectedDisciplinaVinculoId == null || vinculo?.tipoPuntuacion !== 'partido') {
      setAnotadores([]);
      return;
    }
    const abortController = new AbortController();
    obtenerAnotadores(selectedDisciplinaVinculoId, abortController.signal)
      .then((data) => setAnotadores(Array.isArray(data) ? data : []))
      .catch((error) => {
        if (error instanceof DOMException && error.name === 'AbortError') return;
      });
    return () => abortController.abort();
  }, [selectedDisciplinaVinculoId, disciplinas]);

  useEffect(() => {
    if (selectedEdicionId == null) {
      setMedallero([]);
      return;
    }
    const abortController = new AbortController();
    setMedalleroLoading(true);
    obtenerMedallero(selectedEdicionId, abortController.signal)
      .then((data) => setMedallero(Array.isArray(data) ? data : []))
      .catch((error) => {
        if (error instanceof DOMException && error.name === 'AbortError') return;
      })
      .finally(() => setMedalleroLoading(false));
    return () => abortController.abort();
  }, [selectedEdicionId]);

  useEffect(() => {
    if (selectedEdicionId == null) {
      setPosts([]);
      return;
    }
    const abortController = new AbortController();
    setPostsLoading(true);
    obtenerPosts(selectedEdicionId, abortController.signal)
      .then((data) => setPosts(Array.isArray(data) ? data : []))
      .catch((error) => {
        if (error instanceof DOMException && error.name === 'AbortError') return;
      })
      .finally(() => setPostsLoading(false));
    return () => abortController.abort();
  }, [selectedEdicionId]);

  const handleExpandPost = useCallback((post: PostResponse) => {
    if (expandedPostId === post.id) {
      setExpandedPostId(null);
      return;
    }
    setExpandedPostId(post.id);
    setComentarioTexto('');
    if (!comentariosPorPost[post.id]) {
      obtenerComentarios(post.id)
        .then((data) => setComentariosPorPost((prev) => ({ ...prev, [post.id]: Array.isArray(data) ? data : [] })))
        .catch(() => undefined);
    }
  }, [expandedPostId, comentariosPorPost]);

  const handleComentar = useCallback(
    async (postId: number) => {
      if (userId == null || !comentarioTexto.trim()) return;
      setComentando(true);
      try {
        await comentarPost(postId, { usuarioId: userId, contenido: comentarioTexto.trim() });
        setComentarioTexto('');
        const data = await obtenerComentarios(postId);
        setComentariosPorPost((prev) => ({ ...prev, [postId]: Array.isArray(data) ? data : [] }));
        setPosts((prev) => prev.map((p) => (p.id === postId ? { ...p, totalComentarios: p.totalComentarios + 1 } : p)));
      } catch (error) {
        const message = error instanceof Error ? error.message : 'No fue posible publicar el comentario.';
        setFeedback({ type: 'error', message });
      } finally {
        setComentando(false);
      }
    },
    [userId, comentarioTexto],
  );

  const notify = useCallback((value: Feedback) => {
    setFeedback(value);
    window.setTimeout(() => setFeedback((current) => (current === value ? null : current)), 4500);
  }, []);

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

  const fixtureFiltrado = useMemo(() => {
    if (fixtureFiltro === 'todos') return fixture;
    if (fixtureFiltro === 'proximos') {
      return fixture.filter((r) => r.estado === 'programado' || r.estado === 'en_curso' || r.estado === 'suspendido');
    }
    return fixture.filter((r) => r.estado === 'finalizado' || r.estado === 'cancelado');
  }, [fixture, fixtureFiltro]);

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

        <section className="olimpiadas-card olimpiadas-medallero-card" aria-labelledby="medallero-title">
          <div className="olimpiadas-card-header">
            <Trophy className="olimpiadas-card-icon olimpiadas-card-icon-gold" aria-hidden="true" />
            <div>
              <h2 id="medallero-title">Medallero general</h2>
              <p>Suma de puntos de todas las disciplinas: así va la pelea por el título de campeón interfacultades.</p>
            </div>
          </div>

          {medalleroLoading ? (
            <div className="olimpiadas-empty" role="status">
              <Loader2 className="olimpiadas-loading-icon" aria-hidden="true" />
              <p>Cargando medallero…</p>
            </div>
          ) : medallero.length === 0 ? (
            <div className="olimpiadas-empty" role="status">
              <p>Todavía no hay resultados registrados en esta edición.</p>
            </div>
          ) : (
            <ol className="olimpiadas-medallero-list">
              {medallero.map((fila) => (
                <li
                  key={fila.facultadId}
                  className={`olimpiadas-medallero-row${fila.posicion === 1 ? ' olimpiadas-medallero-row-lider' : ''}`}
                  style={{ '--dot-color': facultadColor(fila.facultadAbreviatura ?? fila.facultadNombre) } as CSSProperties}
                >
                  <span className="olimpiadas-medallero-posicion">
                    {fila.posicion === 1 ? '🏆' : `#${fila.posicion}`}
                  </span>
                  <span className="olimpiadas-medallero-barra" aria-hidden="true" />
                  <span className="olimpiadas-medallero-facultad">{fila.facultadNombre}</span>
                  <span className="olimpiadas-medallero-medallas">
                    <span title="Oros">🥇 {fila.oros}</span>
                    <span title="Platas">🥈 {fila.platas}</span>
                    <span title="Bronces">🥉 {fila.bronces}</span>
                  </span>
                  <span className="olimpiadas-medallero-puntos">{fila.puntosTotales} pts</span>
                </li>
              ))}
            </ol>
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
                    {yaInscrito && (
                      <span className="olimpiadas-disciplina-action olimpiadas-disciplina-action-inscrito">
                        Ya estás inscrito
                      </span>
                    )}
                  </button>
                );
              })}
            </div>
          )}
          <p className="olimpiadas-hint">
            La inscripción de participantes la gestiona el área administrativa de Bienestar Universitario.
          </p>
        </section>

        <div className="olimpiadas-grid">
          <section className="olimpiadas-card" aria-labelledby="fixture-title">
            <div className="olimpiadas-card-header">
              <Swords className="olimpiadas-card-icon" aria-hidden="true" />
              <div>
                <h2 id="fixture-title">
                  {selectedVinculo?.tipoPuntuacion === 'posiciones'
                    ? 'Resultados por posición'
                    : 'Fixture de enfrentamientos'}
                </h2>
                <p>
                  {selectedVinculo?.tipoPuntuacion === 'posiciones'
                    ? 'Resultados registrados por prueba en esta edición.'
                    : 'Sorteos y resultados de la disciplina seleccionada.'}
                </p>
              </div>
            </div>

            {selectedVinculo?.tipoPuntuacion !== 'posiciones' && fixture.length > 0 && (
              <div className="olimpiadas-fixture-filtros">
                {(['todos', 'proximos', 'anteriores'] as const).map((f) => (
                  <button
                    key={f}
                    type="button"
                    className={fixtureFiltro === f ? 'active' : ''}
                    onClick={() => setFixtureFiltro(f)}
                  >
                    {f === 'todos' ? 'Todos' : f === 'proximos' ? 'Próximos' : 'Anteriores'}
                  </button>
                ))}
              </div>
            )}

            {resultadosLoading ? (
              <div className="olimpiadas-empty" role="status">
                <Loader2 className="olimpiadas-loading-icon" aria-hidden="true" />
                <p>Cargando fixture…</p>
              </div>
            ) : selectedVinculo?.tipoPuntuacion === 'posiciones' ? (
              resultadosPosicion.length === 0 ? (
                <div className="olimpiadas-empty" role="status">
                  <p>Aún no hay resultados registrados para esta disciplina.</p>
                </div>
              ) : (
                <ul className="olimpiadas-list">
                  {resultadosPosicion.map((res) => (
                    <li key={res.id} className="olimpiadas-list-item">
                      <div className="olimpiadas-list-header">
                        <span
                          className="olimpiadas-list-title olimpiadas-facultad-tag"
                          style={{ '--dot-color': facultadColor(res.facultadAbreviatura ?? res.facultadNombre) } as CSSProperties}
                        >
                          <span className="olimpiadas-facultad-dot" />
                          Puesto {res.posicion}º: {res.facultadNombre}
                        </span>
                        <span className="olimpiadas-status olimpiadas-status-finalizado">
                          {res.puntos} pts
                        </span>
                      </div>
                      <div className="olimpiadas-list-body">
                        <span>{res.prueba ? `Prueba: ${res.prueba}` : 'General'}</span>
                        <span>{formatFecha(res.fecha)}</span>
                        {res.lugar && <span>Sede: {res.lugar}</span>}
                      </div>
                      {res.observaciones && (
                        <div className="olimpiadas-list-body" style={{ marginTop: '0.25rem', fontStyle: 'italic', opacity: 0.8 }}>
                          <span>Obs: {res.observaciones}</span>
                        </div>
                      )}
                    </li>
                  ))}
                </ul>
              )
            ) : fixtureFiltrado.length === 0 ? (
              <div className="olimpiadas-empty" role="status">
                <p>No hay enfrentamientos en este filtro.</p>
              </div>
            ) : (
              <ul className="olimpiadas-matchcard-list">
                {fixtureFiltrado.map((resultado) => {
                  const finalizado = resultado.estado === 'finalizado';
                  return (
                    <li key={resultado.id} className="olimpiadas-matchcard">
                      <div className="olimpiadas-matchcard-meta">
                        <span>{resultado.fase}{resultado.grupo ? ` · Grupo ${resultado.grupo}` : ''}</span>
                        <span className={`olimpiadas-status olimpiadas-status-${resultado.estado}`}>{resultado.estado}</span>
                      </div>
                      <div className="olimpiadas-matchcard-body">
                        <div
                          className="olimpiadas-matchcard-team"
                          style={{ '--dot-color': facultadColor(resultado.facultadLocalNombre) } as CSSProperties}
                        >
                          <span className="olimpiadas-matchcard-shield" />
                          <span className="olimpiadas-matchcard-team-name">{resultado.facultadLocalNombre ?? 'Por definir'}</span>
                        </div>

                        <div className="olimpiadas-matchcard-score">
                          {finalizado && resultado.puntajeLocal !== null && resultado.puntajeVisitante !== null ? (
                            <>
                              <span className={resultado.puntajeLocal > resultado.puntajeVisitante ? 'olimpiadas-matchcard-winner' : ''}>
                                {resultado.puntajeLocal}
                              </span>
                              <span className="olimpiadas-matchcard-dash">-</span>
                              <span className={resultado.puntajeVisitante > resultado.puntajeLocal ? 'olimpiadas-matchcard-winner' : ''}>
                                {resultado.puntajeVisitante}
                              </span>
                            </>
                          ) : (
                            <span className="olimpiadas-matchcard-vs">VS</span>
                          )}
                        </div>

                        <div
                          className="olimpiadas-matchcard-team"
                          style={{ '--dot-color': facultadColor(resultado.facultadVisitanteNombre) } as CSSProperties}
                        >
                          <span className="olimpiadas-matchcard-shield" />
                          <span className="olimpiadas-matchcard-team-name">{resultado.facultadVisitanteNombre ?? 'Por definir'}</span>
                        </div>
                      </div>
                      <div className="olimpiadas-matchcard-footer">
                        <span>{formatFecha(resultado.fechaPartido)}</span>
                        {resultado.lugar && <span>Sede: {resultado.lugar}</span>}
                      </div>
                    </li>
                  );
                })}
              </ul>
            )}

            {anotadores.length > 0 && (
              <div className="olimpiadas-goleadores">
                <h3><Target size={16} /> Goleadores / Anotadores</h3>
                <ol>
                  {anotadores.map((a) => (
                    <li key={a.id}>
                      <span
                        className="olimpiadas-facultad-tag"
                        style={{ '--dot-color': facultadColor(a.facultadAbreviatura ?? a.facultadNombre) } as CSSProperties}
                      >
                        <span className="olimpiadas-facultad-dot" />
                        {a.nombreJugador} ({a.facultadAbreviatura ?? a.facultadNombre})
                      </span>
                      <strong>{a.cantidad}</strong>
                    </li>
                  ))}
                </ol>
              </div>
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
                      <td>
                        <span
                          className="olimpiadas-facultad-tag"
                          style={{ '--dot-color': facultadColor(fila.facultadAbreviatura ?? fila.facultadNombre) } as CSSProperties}
                        >
                          <span className="olimpiadas-facultad-dot" />
                          {fila.facultadAbreviatura ?? fila.facultadNombre}
                        </span>
                      </td>
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
                    <span
                      className="olimpiadas-facultad-tag"
                      style={{ '--dot-color': facultadColor(inscripcion.facultadNombre) } as CSSProperties}
                    >
                      <span className="olimpiadas-facultad-dot" />
                      Representas a {inscripcion.facultadNombre}
                    </span>
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

        <section className="olimpiadas-card olimpiadas-noticias-card" aria-labelledby="noticias-title">
          <div className="olimpiadas-card-header">
            <Newspaper className="olimpiadas-card-icon" aria-hidden="true" />
            <div>
              <h2 id="noticias-title">Noticias y comunidad</h2>
              <p>Novedades publicadas por el comité organizador. ¡Comenta y anima a tu facultad!</p>
            </div>
          </div>

          {postsLoading ? (
            <div className="olimpiadas-empty" role="status">
              <Loader2 className="olimpiadas-loading-icon" aria-hidden="true" />
              <p>Cargando noticias…</p>
            </div>
          ) : posts.length === 0 ? (
            <div className="olimpiadas-empty" role="status">
              <p>Aún no hay publicaciones para esta edición.</p>
            </div>
          ) : (
            <ul className="olimpiadas-posts-list">
              {posts.map((post) => (
                <li key={post.id} className="olimpiadas-post-card">
                  {post.imagenUrl && <img src={post.imagenUrl} alt={post.titulo} className="olimpiadas-post-img" />}
                  <div className="olimpiadas-post-content">
                    <h3>{post.titulo}</h3>
                    <p>{post.contenido}</p>
                    <span className="olimpiadas-post-meta">
                      {post.autor ?? 'Comité Olímpico UPT'} · {formatFecha(post.fechaPublicacion)}
                    </span>

                    <button type="button" className="olimpiadas-post-comments-toggle" onClick={() => handleExpandPost(post)}>
                      <MessageCircle size={16} /> {post.totalComentarios} comentario{post.totalComentarios === 1 ? '' : 's'}
                    </button>

                    {expandedPostId === post.id && (
                      <div className="olimpiadas-post-comments">
                        <ul>
                          {(comentariosPorPost[post.id] ?? []).map((c) => (
                            <li key={c.id}>
                              <strong>{c.usuarioNombre ?? 'Estudiante'}</strong>
                              <span>{c.contenido}</span>
                            </li>
                          ))}
                          {(comentariosPorPost[post.id] ?? []).length === 0 && (
                            <li className="olimpiadas-post-comments-empty">Sé el primero en comentar.</li>
                          )}
                        </ul>
                        <div className="olimpiadas-post-comment-form">
                          <input
                            className="olimpiadas-input"
                            placeholder="Escribe un comentario de aliento…"
                            value={comentarioTexto}
                            onChange={(e) => setComentarioTexto(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && handleComentar(post.id)}
                          />
                          <button type="button" onClick={() => handleComentar(post.id)} disabled={comentando}>
                            <Send size={16} />
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                </li>
              ))}
            </ul>
          )}
        </section>
      </main>
    </div>
  );
};

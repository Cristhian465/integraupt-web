import React, { useCallback, useEffect, useMemo, useState } from "react";
import { AlertTriangle, CalendarDays, CheckCircle2, MapPin, Sparkles, Users } from "lucide-react";
import "../../../../styles/EventosScreen.css";
import { Navbar } from "../Navbar";
import {
  cancelarInscripcion,
  fetchEventosPublicados,
  fetchMiFacultad,
  fetchMisInscripciones,
  inscribirme,
} from "./eventosService";
import type { Evento, MiInscripcion } from "./types";

interface EventosPageProps {
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

type Feedback = { type: "success" | "error"; message: string };

const TIPO_LABEL: Record<string, string> = {
  charla: "Charla",
  taller: "Taller",
  cultural: "Cultural",
  academico: "Académico",
};

const ESTADO_LABEL: Record<string, string> = {
  inscrito: "Inscrito",
  asistio: "Asististe",
  no_asistio: "No asististe",
  cancelado: "Cancelado",
};

const formatFecha = (iso?: string | null): string => {
  if (!iso) return "Sin fecha";
  try {
    return new Date(iso).toLocaleString("es-PE", { dateStyle: "medium", timeStyle: "short" });
  } catch {
    return iso;
  }
};

const getDisplayName = (name?: string, codigo?: string): string => {
  const trimmedName = name?.trim();
  if (trimmedName) return trimmedName;
  const trimmedCodigo = codigo?.trim();
  if (trimmedCodigo) return trimmedCodigo;
  return "Usuario";
};

export const EventosPage: React.FC<EventosPageProps> = ({
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
    () => getDisplayName(user.user_metadata.name, user.user_metadata.codigo),
    [user.user_metadata.codigo, user.user_metadata.name]
  );

  const [tab, setTab] = useState<"disponibles" | "mis-inscripciones">("disponibles");
  const [eventos, setEventos] = useState<Evento[]>([]);
  const [misInscripciones, setMisInscripciones] = useState<MiInscripcion[]>([]);
  const [escuelaId, setEscuelaId] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [feedback, setFeedback] = useState<Feedback | null>(null);
  const [procesandoId, setProcesandoId] = useState<number | null>(null);

  const cargarTodo = useCallback(async () => {
    if (!userId) return;
    setLoading(true);
    setError(null);
    try {
      const miFacultad = await fetchMiFacultad(userId);
      setEscuelaId(miFacultad.escuelaId);

      const [eventosData, inscripcionesData] = await Promise.all([
        miFacultad.facultadId ? fetchEventosPublicados(miFacultad.facultadId) : Promise.resolve([]),
        fetchMisInscripciones(userId),
      ]);

      setEventos(eventosData);
      setMisInscripciones(inscripcionesData);
    } catch (loadError) {
      setError(loadError instanceof Error ? loadError.message : "No se pudieron cargar los eventos.");
    } finally {
      setLoading(false);
    }
  }, [userId]);

  useEffect(() => {
    void cargarTodo();
  }, [cargarTodo]);

  const eventosVisibles = useMemo(
    () => eventos.filter((evento) => evento.alcance === "facultad" || evento.escuelaId === escuelaId),
    [eventos, escuelaId]
  );

  const inscripcionPorEvento = useMemo(() => {
    const map = new Map<number, MiInscripcion>();
    misInscripciones.forEach((inscripcion) => {
      if (inscripcion.estado !== "cancelado") {
        map.set(inscripcion.eventoId, inscripcion);
      }
    });
    return map;
  }, [misInscripciones]);

  const handleInscribirme = async (evento: Evento) => {
    if (!userId) return;
    setProcesandoId(evento.id);
    setFeedback(null);
    try {
      await inscribirme(evento.id, userId);
      setFeedback({ type: "success", message: `Te inscribiste a "${evento.titulo}".` });
      await cargarTodo();
    } catch (inscribirError) {
      setFeedback({
        type: "error",
        message: inscribirError instanceof Error ? inscribirError.message : "No se pudo completar la inscripcion.",
      });
    } finally {
      setProcesandoId(null);
    }
  };

  const handleCancelar = async (inscripcion: MiInscripcion) => {
    setProcesandoId(inscripcion.eventoId);
    try {
      await cancelarInscripcion(inscripcion.eventoId, inscripcion.id);
      await cargarTodo();
    } catch (cancelError) {
      setFeedback({
        type: "error",
        message: cancelError instanceof Error ? cancelError.message : "No se pudo cancelar la inscripcion.",
      });
    } finally {
      setProcesandoId(null);
    }
  };

  return (
    <div className="eventos-container">
      <Navbar
        displayName={displayName}
        userCode={user.user_metadata.codigo}
        currentPage="eventos"
        onNavigateToInicio={onNavigateToInicio}
        onNavigateToServicios={onNavigateToServicios}
        onNavigateToPerfil={onNavigateToPerfil}
        onLogout={onLogout}
        isLoggingOut={isLoggingOut}
      />

      <main className="eventos-main">
        <div className="eventos-header">
          <h1 className="eventos-title">Eventos de tu facultad</h1>
          <p className="eventos-subtitle">
            {displayName}, descubre charlas, talleres y actividades e inscribete cuando quieras participar.
          </p>
        </div>

        {feedback && (
          <div className={`eventos-feedback eventos-feedback-${feedback.type}`} role="status">
            {feedback.type === "success" ? <CheckCircle2 size={18} /> : <AlertTriangle size={18} />}
            <p>{feedback.message}</p>
          </div>
        )}

        {error && (
          <div className="eventos-feedback eventos-feedback-error" role="status">
            <AlertTriangle size={18} />
            <p>{error}</p>
          </div>
        )}

        <div className="eventos-tabs">
          <button type="button" className={tab === "disponibles" ? "active" : ""} onClick={() => setTab("disponibles")}>
            Disponibles
          </button>
          <button
            type="button"
            className={tab === "mis-inscripciones" ? "active" : ""}
            onClick={() => setTab("mis-inscripciones")}
          >
            Mis inscripciones {misInscripciones.length > 0 && `(${misInscripciones.length})`}
          </button>
        </div>

        {loading && <p className="eventos-loading">Cargando...</p>}

        {!loading && tab === "disponibles" && (
          <div className="eventos-list">
            {eventosVisibles.length === 0 && (
              <p className="eventos-loading">No hay eventos publicados para tu facultad por ahora.</p>
            )}
            {eventosVisibles.map((evento) => {
              const inscripcion = inscripcionPorEvento.get(evento.id);
              return (
                <div key={evento.id} className="eventos-card">
                  <div className="eventos-card-content">
                    <div className="eventos-card-layout">
                      <div className="eventos-card-info">
                        <div className="eventos-card-header">
                          <h3 className="eventos-card-title">{evento.titulo}</h3>
                          <span className="eventos-categoria">{TIPO_LABEL[evento.tipoEvento] ?? evento.tipoEvento}</span>
                        </div>
                        {evento.descripcion && <p className="eventos-descripcion">{evento.descripcion}</p>}
                        <div className="eventos-details-grid">
                          <div className="eventos-detail">
                            <CalendarDays className="eventos-detail-icon" />
                            {formatFecha(evento.fechaInicio)}
                          </div>
                          {evento.espacioNombre && (
                            <div className="eventos-detail">
                              <MapPin className="eventos-detail-icon" />
                              {evento.espacioNombre}
                            </div>
                          )}
                          <div className="eventos-detail">
                            <Sparkles className="eventos-detail-icon" />
                            {evento.alcance === "facultad" ? "Toda la facultad" : evento.escuelaNombre ?? "Tu escuela"}
                          </div>
                        </div>
                        {evento.aforoMaximo && (
                          <div className="eventos-participantes">
                            <Users className="eventos-participantes-icon" />
                            <span className="eventos-participantes-text">Aforo maximo: {evento.aforoMaximo}</span>
                          </div>
                        )}
                      </div>
                      <div className="eventos-actions">
                        {inscripcion ? (
                          <span className={`eventos-status-badge eventos-status-${inscripcion.estado}`}>
                            {ESTADO_LABEL[inscripcion.estado] ?? inscripcion.estado}
                          </span>
                        ) : evento.requiereInscripcion ? (
                          <button
                            type="button"
                            className="eventos-btn eventos-btn-primary"
                            disabled={procesandoId === evento.id}
                            onClick={() => handleInscribirme(evento)}
                          >
                            {procesandoId === evento.id ? "Inscribiendo..." : "Inscribirme"}
                          </button>
                        ) : (
                          <span className="eventos-status-badge">Libre, sin inscripcion</span>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}

        {!loading && tab === "mis-inscripciones" && (
          <div className="eventos-mis-eventos">
            <h3 className="eventos-mis-eventos-title">Mis inscripciones</h3>
            {misInscripciones.length === 0 && <p className="eventos-loading">Aun no te has inscrito a ningun evento.</p>}
            <div className="eventos-mis-eventos-grid">
              {misInscripciones.map((inscripcion) => (
                <div key={inscripcion.id} className="eventos-mis-evento-card">
                  <p className="eventos-mis-evento-title">{inscripcion.eventoTitulo ?? "Evento"}</p>
                  <div className="eventos-mis-evento-fecha">
                    <CalendarDays className="eventos-mis-evento-icon" />
                    {formatFecha(inscripcion.eventoFechaInicio)}
                  </div>
                  <div className="eventos-mis-evento-status">
                    <span className={`eventos-status-badge eventos-status-${inscripcion.estado}`}>
                      {ESTADO_LABEL[inscripcion.estado] ?? inscripcion.estado}
                    </span>
                  </div>
                  {inscripcion.estado === "inscrito" && (
                    <p className="eventos-pedido-qr">
                      Codigo QR de ingreso: <code>{inscripcion.codigoQr}</code>
                    </p>
                  )}
                  {inscripcion.estado === "inscrito" && (
                    <button
                      type="button"
                      className="eventos-cancelar-button"
                      disabled={procesandoId === inscripcion.eventoId}
                      onClick={() => handleCancelar(inscripcion)}
                    >
                      Cancelar inscripcion
                    </button>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}
      </main>
    </div>
  );
};

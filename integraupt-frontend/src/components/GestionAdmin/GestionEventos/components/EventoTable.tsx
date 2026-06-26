import React from "react";
import { Pencil, Users, Send, Ban, CheckCircle2 } from "lucide-react";
import type { Evento, EstadoEvento } from "../types";

interface EventoTableProps {
  eventos: Evento[];
  loading: boolean;
  onEdit: (evento: Evento) => void;
  onVerInscripciones: (evento: Evento) => void;
  onCambiarEstado: (evento: Evento, estado: EstadoEvento) => void;
}

const ESTADO_LABEL: Record<EstadoEvento, string> = {
  borrador: "Borrador",
  publicado: "Publicado",
  en_curso: "En curso",
  finalizado: "Finalizado",
  cancelado: "Cancelado"
};

const formatFecha = (iso: string): string => {
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return iso;
  return date.toLocaleString("es-PE", { dateStyle: "medium", timeStyle: "short" });
};

export const EventoTable: React.FC<EventoTableProps> = ({
  eventos,
  loading,
  onEdit,
  onVerInscripciones,
  onCambiarEstado
}) => {
  if (loading && eventos.length === 0) {
    return <div className="evento-table-empty">Cargando eventos...</div>;
  }

  if (!loading && eventos.length === 0) {
    return <div className="evento-table-empty">No hay eventos registrados.</div>;
  }

  return (
    <div className="evento-table-wrapper">
      <table className="evento-table">
        <thead>
          <tr>
            <th>Evento</th>
            <th>Alcance</th>
            <th>Fecha</th>
            <th>Aforo</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {eventos.map((evento) => (
            <tr key={evento.id}>
              <td>
                <div className="evento-table-name">
                  <p className="evento-table-title">{evento.titulo}</p>
                  <span className="evento-table-subtitle">{evento.tipoEvento}</span>
                </div>
              </td>
              <td>
                {evento.alcance === "escuela"
                  ? evento.escuelaNombre ?? `Escuela #${evento.escuelaId}`
                  : `${evento.facultadNombre ?? `Facultad #${evento.facultadId}`} (toda la facultad)`}
              </td>
              <td>{formatFecha(evento.fechaInicio)}</td>
              <td className="evento-cupos-cell">
                {evento.aforoMaximo == null ? (
                  "Sin limite"
                ) : (
                  <span className={evento.cuposDisponibles === 0 ? "evento-cupos-llenos" : undefined}>
                    {evento.inscritos ?? 0}/{evento.aforoMaximo}
                    {evento.cuposDisponibles === 0 ? " (lleno)" : ""}
                  </span>
                )}
              </td>
              <td>
                <span className={`evento-status-badge ${evento.estado}`}>{ESTADO_LABEL[evento.estado]}</span>
              </td>
              <td>
                <div className="evento-table-actions">
                  <button type="button" className="evento-action-button" onClick={() => onEdit(evento)} title="Editar">
                    <Pencil size={16} />
                  </button>
                  <button
                    type="button"
                    className="evento-action-button"
                    onClick={() => onVerInscripciones(evento)}
                    title="Ver inscripciones"
                  >
                    <Users size={16} />
                  </button>
                  {evento.estado === "borrador" && (
                    <button
                      type="button"
                      className="evento-action-button publish"
                      onClick={() => onCambiarEstado(evento, "publicado")}
                      title="Publicar"
                    >
                      <Send size={16} />
                    </button>
                  )}
                  {evento.estado === "publicado" && (
                    <button
                      type="button"
                      className="evento-action-button finish"
                      onClick={() => onCambiarEstado(evento, "finalizado")}
                      title="Marcar como finalizado"
                    >
                      <CheckCircle2 size={16} />
                    </button>
                  )}
                  {evento.estado !== "cancelado" && evento.estado !== "finalizado" && (
                    <button
                      type="button"
                      className="evento-action-button cancel"
                      onClick={() => onCambiarEstado(evento, "cancelado")}
                      title="Cancelar evento"
                    >
                      <Ban size={16} />
                    </button>
                  )}
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

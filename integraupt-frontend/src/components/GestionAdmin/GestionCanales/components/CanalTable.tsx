import React from "react";
import { Archive, ArchiveRestore, MessageCircle, Pencil, Users } from "lucide-react";
import type { Canal } from "../types";

interface CanalTableProps {
  canales: Canal[];
  loading: boolean;
  onEdit: (canal: Canal) => void;
  onMiembros: (canal: Canal) => void;
  onChat: (canal: Canal) => void;
  onCambiarEstado: (canal: Canal) => void;
}

const formatFecha = (iso?: string | null): string => {
  if (!iso) return "-";
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return iso;
  return date.toLocaleString("es-PE", { dateStyle: "medium", timeStyle: "short" });
};

export const CanalTable: React.FC<CanalTableProps> = ({
  canales,
  loading,
  onEdit,
  onMiembros,
  onChat,
  onCambiarEstado
}) => {
  if (loading && canales.length === 0) {
    return <div className="canal-table-empty">Cargando canales...</div>;
  }

  if (!loading && canales.length === 0) {
    return <div className="canal-table-empty">No hay canales registrados.</div>;
  }

  return (
    <div className="canal-table-wrapper">
      <table className="canal-table">
        <thead>
          <tr>
            <th>Canal</th>
            <th>Creador</th>
            <th>Miembros</th>
            <th>Creado</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {canales.map((canal) => (
            <tr key={canal.id}>
              <td>
                <div className="canal-table-name">
                  <p className="canal-table-title">{canal.nombre}</p>
                  {canal.descripcion && <span className="canal-table-subtitle">{canal.descripcion}</span>}
                </div>
              </td>
              <td>
                {canal.creadorNombre ?? `Usuario #${canal.creadorId}`}
                <span className="canal-table-subtitle"> ({canal.tipoCreador})</span>
              </td>
              <td>{canal.miembros.length}</td>
              <td>{formatFecha(canal.fechaCreacion)}</td>
              <td>
                <span className={`canal-status-badge ${canal.estado}`}>
                  {canal.estado === "activo" ? "Activo" : "Archivado"}
                </span>
              </td>
              <td>
                <div className="canal-table-actions">
                  <button type="button" className="canal-action-button" onClick={() => onChat(canal)} title="Abrir chat">
                    <MessageCircle size={16} />
                  </button>
                  <button type="button" className="canal-action-button" onClick={() => onEdit(canal)} title="Editar">
                    <Pencil size={16} />
                  </button>
                  <button type="button" className="canal-action-button" onClick={() => onMiembros(canal)} title="Miembros">
                    <Users size={16} />
                  </button>
                  {canal.estado === "activo" ? (
                    <button
                      type="button"
                      className="canal-action-button archive"
                      onClick={() => onCambiarEstado(canal)}
                      title="Archivar canal"
                    >
                      <Archive size={16} />
                    </button>
                  ) : (
                    <button
                      type="button"
                      className="canal-action-button restore"
                      onClick={() => onCambiarEstado(canal)}
                      title="Reactivar canal"
                    >
                      <ArchiveRestore size={16} />
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

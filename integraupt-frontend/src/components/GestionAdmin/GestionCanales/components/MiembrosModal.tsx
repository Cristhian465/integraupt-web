import React, { useState } from "react";
import { X, UserMinus } from "lucide-react";
import { UsuarioPicker } from "./UsuarioPicker";
import type { Canal, UsuarioBusqueda } from "../types";

interface MiembrosModalProps {
  canal: Canal | null;
  onClose: () => void;
  onAddMiembros: (idCanal: number, miembros: number[]) => Promise<void>;
  onRemoveMiembro: (idCanal: number, idUsuario: number) => Promise<void>;
}

export const MiembrosModal: React.FC<MiembrosModalProps> = ({
  canal,
  onClose,
  onAddMiembros,
  onRemoveMiembro
}) => {
  const [pendientes, setPendientes] = useState<UsuarioBusqueda[]>([]);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  if (!canal) {
    return null;
  }

  const handleAdd = (usuario: UsuarioBusqueda) => {
    setPendientes((prev) => (prev.some((u) => u.id === usuario.id) ? prev : [...prev, usuario]));
  };

  const handleRemovePendiente = (idUsuario: number) => {
    setPendientes((prev) => prev.filter((u) => u.id !== idUsuario));
  };

  const handleGuardar = async () => {
    if (pendientes.length === 0) return;

    setSubmitting(true);
    setError(null);
    try {
      await onAddMiembros(canal.id, pendientes.map((u) => u.id));
      setPendientes([]);
    } catch (saveError) {
      setError(saveError instanceof Error ? saveError.message : "No se pudieron agregar los miembros.");
    } finally {
      setSubmitting(false);
    }
  };

  const handleQuitar = async (idUsuario: number) => {
    setError(null);
    try {
      await onRemoveMiembro(canal.id, idUsuario);
    } catch (removeError) {
      setError(removeError instanceof Error ? removeError.message : "No se pudo quitar al miembro.");
    }
  };

  return (
    <div className="canal-modal-backdrop" role="dialog" aria-modal="true">
      <div className="canal-modal">
        <div className="canal-modal-header">
          <h3>Miembros de "{canal.nombre}"</h3>
          <button type="button" className="canal-modal-close" aria-label="Cerrar" onClick={onClose}>
            <X size={18} />
          </button>
        </div>

        <ul className="canal-miembros-list">
          {canal.miembros.map((miembro) => (
            <li key={miembro.idUsuario}>
              <span>
                {miembro.nombre ?? `Usuario #${miembro.idUsuario}`}
                {miembro.rol === "creador" && <span className="canal-table-subtitle"> (creador)</span>}
              </span>
              {miembro.rol !== "creador" && (
                <button
                  type="button"
                  className="canal-action-button"
                  onClick={() => handleQuitar(miembro.idUsuario)}
                  title="Quitar del canal"
                >
                  <UserMinus size={14} />
                </button>
              )}
            </li>
          ))}
        </ul>

        <div className="canal-form-group">
          <label className="canal-form-label">Agregar nuevos miembros</label>
          <UsuarioPicker selected={pendientes} onAdd={handleAdd} onRemove={handleRemovePendiente} />
        </div>

        {error && <div className="canal-form-errors">{error}</div>}

        <div className="canal-form-actions">
          <button type="button" className="gestion-canales-btn secondary" onClick={onClose}>
            Cerrar
          </button>
          <button
            type="button"
            className="gestion-canales-btn primary"
            onClick={handleGuardar}
            disabled={submitting || pendientes.length === 0}
          >
            Agregar miembros
          </button>
        </div>
      </div>
    </div>
  );
};

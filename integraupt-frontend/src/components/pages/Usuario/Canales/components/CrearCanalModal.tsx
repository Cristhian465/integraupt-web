import React, { useState } from "react";
import { X } from "lucide-react";
import { UsuarioPicker } from "./UsuarioPicker";
import { createCanal } from "../services/canalesService";
import type { Canal, UsuarioBusqueda } from "../types";

interface CrearCanalModalProps {
  open: boolean;
  creadorId: number;
  onClose: () => void;
  onCreated: (canal: Canal) => void;
}

export const CrearCanalModal: React.FC<CrearCanalModalProps> = ({ open, creadorId, onClose, onCreated }) => {
  const [nombre, setNombre] = useState("");
  const [descripcion, setDescripcion] = useState("");
  const [miembros, setMiembros] = useState<UsuarioBusqueda[]>([]);
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  if (!open) {
    return null;
  }

  const handleAdd = (usuario: UsuarioBusqueda) => {
    setMiembros((prev) => (prev.some((u) => u.id === usuario.id) ? prev : [...prev, usuario]));
  };

  const handleRemove = (idUsuario: number) => {
    setMiembros((prev) => prev.filter((u) => u.id !== idUsuario));
  };

  const handleSubmit = async () => {
    if (nombre.trim() === "") {
      setError("El nombre del canal es obligatorio.");
      return;
    }

    setSubmitting(true);
    setError(null);
    try {
      const canal = await createCanal(nombre.trim(), descripcion.trim() || undefined, creadorId, miembros.map((m) => m.id));
      setNombre("");
      setDescripcion("");
      setMiembros([]);
      onCreated(canal);
    } catch (createError) {
      setError(createError instanceof Error ? createError.message : "No se pudo crear el canal.");
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="canal-modal-backdrop" role="dialog" aria-modal="true">
      <div className="canal-modal">
        <div className="canal-modal-header">
          <h3>Crear nuevo canal</h3>
          <button type="button" className="canal-modal-close" aria-label="Cerrar" onClick={onClose}>
            <X size={18} />
          </button>
        </div>

        <div className="canal-form-group">
          <label className="canal-form-label" htmlFor="nuevo-canal-nombre">
            Nombre del canal
          </label>
          <input
            id="nuevo-canal-nombre"
            className="canal-form-input"
            type="text"
            value={nombre}
            onChange={(event) => setNombre(event.target.value)}
            placeholder="Ej. Sección A - Curso de Redes"
          />
        </div>

        <div className="canal-form-group">
          <label className="canal-form-label" htmlFor="nuevo-canal-descripcion">
            Descripción (opcional)
          </label>
          <textarea
            id="nuevo-canal-descripcion"
            className="canal-form-textarea"
            value={descripcion}
            onChange={(event) => setDescripcion(event.target.value)}
          />
        </div>

        <div className="canal-form-group">
          <label className="canal-form-label">Agregar miembros (opcional)</label>
          <UsuarioPicker selected={miembros} onAdd={handleAdd} onRemove={handleRemove} />
        </div>

        {error && <div className="canal-form-errors">{error}</div>}

        <div className="canal-form-actions">
          <button type="button" className="canal-btn secondary" onClick={onClose} disabled={submitting}>
            Cancelar
          </button>
          <button type="button" className="canal-btn primary" onClick={handleSubmit} disabled={submitting}>
            Crear canal
          </button>
        </div>
      </div>
    </div>
  );
};

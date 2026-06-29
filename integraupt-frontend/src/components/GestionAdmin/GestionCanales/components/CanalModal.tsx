import React, { useRef, useState } from "react";
import { ImageIcon, X } from "lucide-react";
import { UsuarioPicker } from "./UsuarioPicker";
import { uploadArchivo } from "../canalesService";
import type { UsuarioBusqueda } from "../types";

const COLORS = ["#4f46e5", "#0ea5e9", "#10b981", "#f59e0b", "#ef4444", "#8b5cf6", "#ec4899", "#14b8a6", "#f97316", "#64748b"];

export interface CanalFormValues {
  nombre: string;
  descripcion: string;
  color: string;
  fotoUrl: string;
}

interface CanalModalProps {
  open: boolean;
  mode: "create" | "edit";
  values: CanalFormValues;
  errors: string[];
  submitting: boolean;
  onClose: () => void;
  onChange: (field: keyof CanalFormValues, value: string) => void;
  onSubmit: (miembros: number[]) => void;
}

export const CanalModal: React.FC<CanalModalProps> = ({
  open, mode, values, errors, submitting, onClose, onChange, onSubmit
}) => {
  const [miembros, setMiembros] = useState<UsuarioBusqueda[]>([]);
  const [uploadingFoto, setUploadingFoto] = useState(false);
  const fotoRef = useRef<HTMLInputElement>(null);

  if (!open) return null;

  const handleFotoChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    setUploadingFoto(true);
    try {
      const { url } = await uploadArchivo(file);
      onChange("fotoUrl", url);
    } catch {} finally {
      setUploadingFoto(false);
    }
  };

  const canalColor = values.color || "#4f46e5";

  return (
    <div className="canal-modal-backdrop" role="dialog" aria-modal="true">
      <div className="canal-modal">
        <div className="canal-modal-header">
          <h3>{mode === "create" ? "Crear nuevo canal" : "Editar canal"}</h3>
          <button type="button" className="canal-modal-close" onClick={onClose}><X size={18} /></button>
        </div>

        {/* Avatar preview + foto */}
        <div className="canal-form-group" style={{ display: "flex", alignItems: "center", gap: 16 }}>
          <div
            className="canal-avatar-preview"
            style={{ background: canalColor, cursor: "pointer" }}
            onClick={() => fotoRef.current?.click()}
            title="Cambiar foto"
          >
            {values.fotoUrl
              ? <img src={values.fotoUrl} alt="foto canal" style={{ width: "100%", height: "100%", objectFit: "cover", borderRadius: "50%" }} />
              : <ImageIcon size={24} color="#fff" />}
            {uploadingFoto && <div className="canal-avatar-overlay">...</div>}
          </div>
          <input ref={fotoRef} type="file" accept="image/*" style={{ display: "none" }} onChange={handleFotoChange} />
          <div style={{ flex: 1 }}>
            <p className="canal-form-label" style={{ marginBottom: 6 }}>Color del canal</p>
            <div className="canal-color-palette">
              {COLORS.map((c) => (
                <button
                  key={c} type="button"
                  className={`canal-color-swatch ${values.color === c ? "selected" : ""}`}
                  style={{ background: c }}
                  onClick={() => onChange("color", c)}
                  title={c}
                />
              ))}
            </div>
          </div>
        </div>

        <div className="canal-form-group">
          <label className="canal-form-label" htmlFor="canal-nombre">Nombre del canal</label>
          <input id="canal-nombre" className="canal-form-input" type="text" value={values.nombre}
            onChange={(e) => onChange("nombre", e.target.value)}
            placeholder="Ej. Anuncios Facultad de Ingeniería" />
        </div>

        <div className="canal-form-group">
          <label className="canal-form-label" htmlFor="canal-desc">Descripción (opcional)</label>
          <textarea id="canal-desc" className="canal-form-textarea" value={values.descripcion}
            onChange={(e) => onChange("descripcion", e.target.value)}
            placeholder="¿Para qué se usará este canal?" />
        </div>

        {mode === "create" && (
          <div className="canal-form-group">
            <label className="canal-form-label">Agregar miembros (opcional)</label>
            <UsuarioPicker
              selected={miembros}
              onAdd={(u) => setMiembros((p) => p.some((x) => x.id === u.id) ? p : [...p, u])}
              onRemove={(id) => setMiembros((p) => p.filter((u) => u.id !== id))}
            />
          </div>
        )}

        {errors.length > 0 && (
          <div className="canal-form-errors">
            <ul>{errors.map((e) => <li key={e}>{e}</li>)}</ul>
          </div>
        )}

        <div className="canal-form-actions">
          <button type="button" className="gestion-canales-btn secondary" onClick={onClose} disabled={submitting}>Cancelar</button>
          <button type="button" className="gestion-canales-btn primary"
            onClick={() => onSubmit(miembros.map((u) => u.id))} disabled={submitting}>
            {mode === "create" ? "Crear canal" : "Guardar cambios"}
          </button>
        </div>
      </div>
    </div>
  );
};

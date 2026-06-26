import React from "react";
import { X } from "lucide-react";
import type { ProductoFormValues } from "../types";

interface ProductoModalProps {
  open: boolean;
  mode: "create" | "edit";
  values: ProductoFormValues;
  errors: string[];
  submitting: boolean;
  onClose: () => void;
  onChange: (field: keyof ProductoFormValues, value: string | boolean) => void;
  onSubmit: () => void;
}

export const ProductoModal: React.FC<ProductoModalProps> = ({
  open,
  mode,
  values,
  errors,
  submitting,
  onClose,
  onChange,
  onSubmit
}) => {
  if (!open) return null;

  const handleSubmit: React.FormEventHandler<HTMLFormElement> = (event) => {
    event.preventDefault();
    onSubmit();
  };

  return (
    <div className="cafeteria-modal-backdrop" role="dialog" aria-modal="true">
      <div className="cafeteria-modal">
        <div className="cafeteria-modal-header">
          <h3>{mode === "create" ? "Nuevo producto" : "Editar producto"}</h3>
          <button type="button" className="cafeteria-modal-close" onClick={onClose} aria-label="Cerrar">
            <X size={18} />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="cafeteria-form">
          <div className="cafeteria-form-grid">
            <div className="cafeteria-form-group cafeteria-form-group-full">
              <label className="cafeteria-form-label" htmlFor="nombreProducto">
                Nombre
              </label>
              <input
                id="nombreProducto"
                className="cafeteria-form-input"
                maxLength={150}
                value={values.nombre}
                onChange={(event) => onChange("nombre", event.target.value)}
                required
              />
            </div>

            <div className="cafeteria-form-group">
              <label className="cafeteria-form-label" htmlFor="precio">
                Precio (S/.)
              </label>
              <input
                id="precio"
                type="text"
                inputMode="decimal"
                className="cafeteria-form-input"
                value={values.precio}
                onChange={(event) => {
                  if (/^\d{0,4}(\.\d{0,2})?$/.test(event.target.value)) {
                    onChange("precio", event.target.value);
                  }
                }}
                required
              />
            </div>

            <div className="cafeteria-form-group">
              <label className="cafeteria-form-label" htmlFor="stock">
                Stock disponible
              </label>
              <input
                id="stock"
                type="text"
                inputMode="numeric"
                className="cafeteria-form-input"
                value={values.stock}
                onChange={(event) => {
                  if (/^\d{0,5}$/.test(event.target.value)) {
                    onChange("stock", event.target.value);
                  }
                }}
                required
              />
            </div>

            <div className="cafeteria-form-group cafeteria-form-checkbox">
              <label className="cafeteria-form-label" htmlFor="estadoProducto">
                <input
                  id="estadoProducto"
                  type="checkbox"
                  checked={values.estado}
                  onChange={(event) => onChange("estado", event.target.checked)}
                />
                Disponible para la venta
              </label>
            </div>
          </div>

          <div className="cafeteria-form-group cafeteria-form-group-full">
            <label className="cafeteria-form-label" htmlFor="descripcionProducto">
              Descripcion (opcional)
            </label>
            <textarea
              id="descripcionProducto"
              className="cafeteria-form-textarea"
              rows={2}
              maxLength={1000}
              value={values.descripcion}
              onChange={(event) => onChange("descripcion", event.target.value)}
            />
          </div>

          {errors.length > 0 && (
            <div className="cafeteria-form-errors">
              <ul>
                {errors.map((error) => (
                  <li key={error}>{error}</li>
                ))}
              </ul>
            </div>
          )}

          <div className="cafeteria-form-actions">
            <button type="button" className="gestion-cafeteria-btn ghost" onClick={onClose} disabled={submitting}>
              Cancelar
            </button>
            <button type="submit" className="gestion-cafeteria-btn primary" disabled={submitting}>
              {submitting ? "Guardando..." : mode === "create" ? "Registrar" : "Actualizar"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

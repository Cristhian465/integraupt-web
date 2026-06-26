import React from "react";
import { X } from "lucide-react";
import type { Escuela, EspacioCatalogo, EventoFormMode, EventoFormValues, Facultad } from "../types";
import { EventoForm } from "./EventoForm";

interface EventoModalProps {
  open: boolean;
  mode: EventoFormMode;
  values: EventoFormValues;
  errors: string[];
  submitting: boolean;
  facultades: Facultad[];
  escuelas: Escuela[];
  espacios: EspacioCatalogo[];
  catalogosLoading: boolean;
  catalogosError: string | null;
  onClose: () => void;
  onChange: (field: keyof EventoFormValues, value: string | boolean) => void;
  onImageChange: (file: File | null) => void;
  onSubmit: () => void;
}

export const EventoModal: React.FC<EventoModalProps> = ({
  open,
  mode,
  values,
  errors,
  submitting,
  facultades,
  escuelas,
  espacios,
  catalogosLoading,
  catalogosError,
  onClose,
  onChange,
  onImageChange,
  onSubmit
}) => {
  if (!open) {
    return null;
  }

  return (
    <div className="evento-modal-backdrop" role="dialog" aria-modal="true">
      <div className="evento-modal">
        <div className="evento-modal-header">
          <h3>{mode === "create" ? "Registrar nuevo evento" : "Actualizar evento"}</h3>
          <button type="button" className="evento-modal-close" aria-label="Cerrar formulario" onClick={onClose}>
            <X size={18} />
          </button>
        </div>

        {catalogosError && (
          <div className="evento-form-errors">
            <p>{catalogosError}</p>
          </div>
        )}

        <EventoForm
          values={values}
          errors={errors}
          mode={mode}
          submitting={submitting}
          facultades={facultades}
          escuelas={escuelas}
          espacios={espacios}
          catalogosLoading={catalogosLoading}
          onChange={onChange}
          onImageChange={onImageChange}
          onSubmit={onSubmit}
          onCancel={onClose}
        />
      </div>
    </div>
  );
};

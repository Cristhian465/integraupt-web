import React, { useMemo } from "react";
import type { Escuela, EspacioCatalogo, EventoFormMode, EventoFormValues, Facultad } from "../types";

interface EventoFormProps {
  values: EventoFormValues;
  errors: string[];
  mode: EventoFormMode;
  submitting: boolean;
  facultades: Facultad[];
  escuelas: Escuela[];
  espacios: EspacioCatalogo[];
  catalogosLoading: boolean;
  onChange: (field: keyof EventoFormValues, value: string | boolean) => void;
  onSubmit: () => void;
  onCancel: () => void;
}

const TIPOS_EVENTO: Array<{ value: EventoFormValues["tipoEvento"]; label: string }> = [
  { value: "charla", label: "Charla" },
  { value: "taller", label: "Taller" },
  { value: "cultural", label: "Cultural" },
  { value: "academico", label: "Academico" }
];

export const EventoForm: React.FC<EventoFormProps> = ({
  values,
  errors,
  mode,
  submitting,
  facultades,
  escuelas,
  espacios,
  catalogosLoading,
  onChange,
  onSubmit,
  onCancel
}) => {
  const handleSubmit: React.FormEventHandler<HTMLFormElement> = (event) => {
    event.preventDefault();
    onSubmit();
  };

  const escuelasFiltradas = useMemo(() => {
    if (!values.facultadId) return escuelas;
    return escuelas.filter((escuela) => `${escuela.facultadId}` === values.facultadId);
  }, [escuelas, values.facultadId]);

  const espaciosFiltrados = useMemo(() => {
    if (!values.escuelaId) return espacios;
    return espacios.filter((espacio) => `${espacio.escuelaId}` === values.escuelaId);
  }, [espacios, values.escuelaId]);

  return (
    <form className="evento-form" onSubmit={handleSubmit}>
      <div className="evento-form-grid">
        <div className="evento-form-group evento-form-group-full">
          <label className="evento-form-label" htmlFor="titulo">
            Titulo
          </label>
          <input
            id="titulo"
            className="evento-form-input"
            maxLength={200}
            placeholder="Ej. Charla de Inteligencia Artificial"
            value={values.titulo}
            onChange={(event) => onChange("titulo", event.target.value)}
            required
          />
        </div>

        <div className="evento-form-group">
          <label className="evento-form-label" htmlFor="tipoEvento">
            Tipo de evento
          </label>
          <select
            id="tipoEvento"
            className="evento-form-input"
            value={values.tipoEvento}
            onChange={(event) => onChange("tipoEvento", event.target.value)}
          >
            {TIPOS_EVENTO.map((tipo) => (
              <option key={tipo.value} value={tipo.value}>
                {tipo.label}
              </option>
            ))}
          </select>
        </div>

        <div className="evento-form-group">
          <label className="evento-form-label" htmlFor="alcance">
            Alcance
          </label>
          <select
            id="alcance"
            className="evento-form-input"
            value={values.alcance}
            onChange={(event) => {
              onChange("alcance", event.target.value);
              if (event.target.value === "facultad") {
                onChange("escuelaId", "");
              }
            }}
          >
            <option value="facultad">Toda la facultad</option>
            <option value="escuela">Una escuela especifica</option>
          </select>
        </div>

        <div className="evento-form-group">
          <label className="evento-form-label" htmlFor="facultadId">
            Facultad
          </label>
          <select
            id="facultadId"
            className="evento-form-input"
            value={values.facultadId}
            onChange={(event) => {
              onChange("facultadId", event.target.value);
              onChange("escuelaId", "");
            }}
            disabled={catalogosLoading}
            required
          >
            <option value="" disabled>
              {catalogosLoading ? "Cargando..." : "Selecciona una facultad"}
            </option>
            {facultades.map((facultad) => (
              <option key={facultad.id} value={`${facultad.id}`}>
                {facultad.nombre}
              </option>
            ))}
          </select>
        </div>

        {values.alcance === "escuela" && (
          <div className="evento-form-group">
            <label className="evento-form-label" htmlFor="escuelaId">
              Escuela
            </label>
            <select
              id="escuelaId"
              className="evento-form-input"
              value={values.escuelaId}
              onChange={(event) => onChange("escuelaId", event.target.value)}
              disabled={catalogosLoading || !values.facultadId}
              required
            >
              <option value="" disabled>
                Selecciona una escuela
              </option>
              {escuelasFiltradas.map((escuela) => (
                <option key={escuela.id} value={`${escuela.id}`}>
                  {escuela.nombre}
                </option>
              ))}
            </select>
          </div>
        )}

        <div className="evento-form-group">
          <label className="evento-form-label" htmlFor="espacioId">
            Espacio (opcional)
          </label>
          <select
            id="espacioId"
            className="evento-form-input"
            value={values.espacioId}
            onChange={(event) => onChange("espacioId", event.target.value)}
            disabled={catalogosLoading}
          >
            <option value="">Sin espacio asignado / virtual</option>
            {espaciosFiltrados.map((espacio) => (
              <option key={espacio.id} value={`${espacio.id}`}>
                {espacio.nombre} ({espacio.codigo})
              </option>
            ))}
          </select>
        </div>

        <div className="evento-form-group">
          <label className="evento-form-label" htmlFor="fechaInicio">
            Fecha y hora de inicio
          </label>
          <input
            id="fechaInicio"
            type="datetime-local"
            className="evento-form-input"
            value={values.fechaInicio}
            onChange={(event) => onChange("fechaInicio", event.target.value)}
            required
          />
        </div>

        <div className="evento-form-group">
          <label className="evento-form-label" htmlFor="fechaFin">
            Fecha y hora de fin
          </label>
          <input
            id="fechaFin"
            type="datetime-local"
            className="evento-form-input"
            value={values.fechaFin}
            onChange={(event) => onChange("fechaFin", event.target.value)}
            required
          />
        </div>

        <div className="evento-form-group">
          <label className="evento-form-label" htmlFor="aforoMaximo">
            Aforo maximo (opcional)
          </label>
          <input
            id="aforoMaximo"
            type="text"
            inputMode="numeric"
            className="evento-form-input"
            placeholder="Sin limite"
            value={values.aforoMaximo}
            onChange={(event) => {
              const value = event.target.value;
              if (/^\d{0,5}$/.test(value)) {
                onChange("aforoMaximo", value);
              }
            }}
          />
        </div>

        <div className="evento-form-group">
          <label className="evento-form-label" htmlFor="responsableId">
            ID de usuario responsable
          </label>
          <input
            id="responsableId"
            type="text"
            inputMode="numeric"
            className="evento-form-input"
            placeholder="Ej. 1"
            value={values.responsableId}
            onChange={(event) => {
              const value = event.target.value;
              if (/^\d{0,10}$/.test(value)) {
                onChange("responsableId", value);
              }
            }}
            required
          />
        </div>

        <div className="evento-form-group evento-form-checkbox">
          <label className="evento-form-label" htmlFor="requiereInscripcion">
            <input
              id="requiereInscripcion"
              type="checkbox"
              checked={values.requiereInscripcion}
              onChange={(event) => onChange("requiereInscripcion", event.target.checked)}
            />
            Requiere inscripcion previa
          </label>
        </div>
      </div>

      <div className="evento-form-group evento-form-group-full">
        <label className="evento-form-label" htmlFor="descripcion">
          Descripcion
        </label>
        <textarea
          id="descripcion"
          className="evento-form-textarea"
          maxLength={1000}
          rows={3}
          placeholder="Detalle del evento..."
          value={values.descripcion}
          onChange={(event) => onChange("descripcion", event.target.value)}
        />
      </div>

      {errors.length > 0 && (
        <div className="evento-form-errors">
          <p>Revisa los siguientes puntos:</p>
          <ul>
            {errors.map((error) => (
              <li key={error}>{error}</li>
            ))}
          </ul>
        </div>
      )}

      <div className="evento-form-actions">
        <button type="button" className="gestion-eventos-btn ghost" onClick={onCancel} disabled={submitting}>
          Cancelar
        </button>
        <button type="submit" className="gestion-eventos-btn primary" disabled={submitting}>
          {submitting ? "Guardando..." : mode === "create" ? "Registrar" : "Actualizar"}
        </button>
      </div>
    </form>
  );
};

import type { EventoFormValues } from "./types";

export const validateEventoValues = (values: EventoFormValues): string[] => {
  const errors: string[] = [];

  if (!values.titulo.trim()) {
    errors.push("El titulo es obligatorio.");
  }

  if (!values.facultadId) {
    errors.push("La facultad es obligatoria.");
  }

  if (values.alcance === "escuela" && !values.escuelaId) {
    errors.push("Debes indicar la escuela cuando el alcance es por escuela.");
  }

  if (!values.fechaInicio) {
    errors.push("La fecha de inicio es obligatoria.");
  }

  if (!values.fechaFin) {
    errors.push("La fecha de fin es obligatoria.");
  }

  if (values.fechaInicio && values.fechaFin && values.fechaFin <= values.fechaInicio) {
    errors.push("La fecha de fin debe ser posterior a la fecha de inicio.");
  }

  if (values.aforoMaximo) {
    const aforo = Number(values.aforoMaximo);
    if (!Number.isFinite(aforo) || aforo <= 0) {
      errors.push("El aforo maximo debe ser un numero mayor a cero.");
    }
  }

  if (!values.responsableId) {
    errors.push("El responsable es obligatorio.");
  }

  return errors;
};

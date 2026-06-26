import type { Evento, EventoFormValues, EventoPayload } from "./types";

export const createEmptyFormValues = (): EventoFormValues => ({
  titulo: "",
  descripcion: "",
  tipoEvento: "charla",
  alcance: "facultad",
  facultadId: "",
  escuelaId: "",
  espacioId: "",
  fechaInicio: "",
  fechaFin: "",
  aforoMaximo: "",
  requiereInscripcion: true,
  responsableId: ""
});

const toDateTimeLocalInput = (isoDate: string): string => {
  const date = new Date(isoDate);
  if (Number.isNaN(date.getTime())) {
    return "";
  }
  const pad = (n: number) => `${n}`.padStart(2, "0");
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

export const mapEventoToFormValues = (evento: Evento): EventoFormValues => ({
  titulo: evento.titulo ?? "",
  descripcion: evento.descripcion ?? "",
  tipoEvento: evento.tipoEvento,
  alcance: evento.alcance,
  facultadId: `${evento.facultadId ?? ""}`,
  escuelaId: evento.escuelaId ? `${evento.escuelaId}` : "",
  espacioId: evento.espacioId ? `${evento.espacioId}` : "",
  fechaInicio: toDateTimeLocalInput(evento.fechaInicio),
  fechaFin: toDateTimeLocalInput(evento.fechaFin),
  aforoMaximo: evento.aforoMaximo ? `${evento.aforoMaximo}` : "",
  requiereInscripcion: evento.requiereInscripcion,
  responsableId: `${evento.responsableId ?? ""}`
});

export const buildPayloadFromValues = (values: EventoFormValues): EventoPayload => {
  const descripcion = values.descripcion.trim();
  const aforoMaximo = values.aforoMaximo.trim();

  return {
    titulo: values.titulo.trim(),
    descripcion: descripcion || undefined,
    tipoEvento: values.tipoEvento,
    alcance: values.alcance,
    facultadId: Number(values.facultadId),
    escuelaId: values.alcance === "escuela" ? Number(values.escuelaId) : undefined,
    espacioId: values.espacioId ? Number(values.espacioId) : undefined,
    fechaInicio: values.fechaInicio.replace("T", " "),
    fechaFin: values.fechaFin.replace("T", " "),
    aforoMaximo: aforoMaximo ? Number(aforoMaximo) : undefined,
    requiereInscripcion: values.requiereInscripcion,
    responsableId: Number(values.responsableId)
  };
};

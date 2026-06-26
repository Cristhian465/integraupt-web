export type TipoEvento = "charla" | "taller" | "cultural" | "academico";
export type AlcanceEvento = "facultad" | "escuela";
export type EstadoEvento = "borrador" | "publicado" | "en_curso" | "finalizado" | "cancelado";
export type TipoUsuarioInscripcion = "estudiante" | "docente";
export type EstadoInscripcion = "inscrito" | "asistio" | "no_asistio" | "cancelado";

export interface Facultad {
  id: number;
  nombre: string;
  abreviatura?: string | null;
}

export interface Escuela {
  id: number;
  nombre: string;
  facultadId: number;
  facultadNombre?: string | null;
}

export interface EspacioCatalogo {
  id: number;
  codigo: string;
  nombre: string;
  capacidad: number;
  escuelaId: number;
}

export interface Evento {
  id: number;
  titulo: string;
  descripcion?: string | null;
  tipoEvento: TipoEvento;
  alcance: AlcanceEvento;
  facultadId: number;
  facultadNombre?: string | null;
  escuelaId?: number | null;
  escuelaNombre?: string | null;
  espacioId?: number | null;
  espacioNombre?: string | null;
  fechaInicio: string;
  fechaFin: string;
  aforoMaximo?: number | null;
  requiereInscripcion: boolean;
  estado: EstadoEvento;
  responsableId: number;
  responsableNombre?: string | null;
}

export type EventoPayload = {
  titulo: string;
  descripcion?: string;
  tipoEvento: TipoEvento;
  alcance: AlcanceEvento;
  facultadId: number;
  escuelaId?: number;
  espacioId?: number;
  fechaInicio: string;
  fechaFin: string;
  aforoMaximo?: number;
  requiereInscripcion: boolean;
  responsableId: number;
};

export interface EventoFormValues {
  titulo: string;
  descripcion: string;
  tipoEvento: TipoEvento;
  alcance: AlcanceEvento;
  facultadId: string;
  escuelaId: string;
  espacioId: string;
  fechaInicio: string;
  fechaFin: string;
  aforoMaximo: string;
  requiereInscripcion: boolean;
  responsableId: string;
}

export type EventoFormMode = "create" | "edit";

export interface EventoInscripcion {
  id: number;
  eventoId: number;
  usuarioId: number;
  usuarioNombre?: string | null;
  tipoUsuario: TipoUsuarioInscripcion;
  estado: EstadoInscripcion;
  codigoQr: string;
  fechaInscripcion?: string | null;
}

export interface ReporteAsistencia {
  evento: string;
  aforoMaximo: number | null;
  inscritos: number;
  asistieron: number;
  noAsistieron: number;
  cancelados: number;
}

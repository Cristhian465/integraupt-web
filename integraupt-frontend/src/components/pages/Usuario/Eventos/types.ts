export type TipoEvento = "charla" | "taller" | "cultural" | "academico";
export type AlcanceEvento = "facultad" | "escuela";
export type EstadoEvento = "borrador" | "publicado" | "en_curso" | "finalizado" | "cancelado";
export type EstadoInscripcion = "inscrito" | "asistio" | "no_asistio" | "cancelado";

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

export interface MiInscripcion {
  id: number;
  eventoId: number;
  usuarioId: number;
  tipoUsuario: "estudiante" | "docente";
  estado: EstadoInscripcion;
  codigoQr: string;
  fechaInscripcion?: string | null;
  eventoTitulo?: string | null;
  eventoTipoEvento?: TipoEvento | null;
  eventoFechaInicio?: string | null;
  eventoFechaFin?: string | null;
  eventoEstado?: EstadoEvento | null;
}

export interface MiFacultad {
  facultadId: number | null;
  escuelaId: number | null;
}

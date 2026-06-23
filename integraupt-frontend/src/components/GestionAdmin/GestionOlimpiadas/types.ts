export interface FacultadOption {
  id: number;
  nombre: string;
  abreviatura: string | null;
}

export interface DisciplinaCatalogo {
  id: number;
  nombre: string;
  descripcion: string | null;
  tipoParticipacion: 'individual' | 'equipo';
  reglas: string | null;
  cupoMaximoDefault: number | null;
  estado: 'activa' | 'inactiva';
}

export interface DisciplinaFormValues {
  nombre: string;
  descripcion: string;
  tipoParticipacion: 'individual' | 'equipo';
  reglas: string;
  cupoMaximoDefault: string;
}

export interface Edicion {
  id: number;
  nombre: string;
  anioInicio: number;
  semestreInicio: number;
  anioFin: number | null;
  semestreFin: number | null;
  estado: string;
  fechaAperturaInscripcion: string | null;
  fechaCierreInscripcion: string | null;
  fechaInicioJuegos: string | null;
  fechaFinJuegos: string | null;
  observaciones: string | null;
  inscripcionAbierta: boolean;
}

export interface EdicionFormValues {
  nombre: string;
  anioInicio: string;
  semestreInicio: '1' | '2';
  fechaInicioJuegos: string;
  fechaFinJuegos: string;
  observaciones: string;
}

export interface EdicionDisciplina {
  id: number;
  edicionId: number;
  disciplinaId: number;
  disciplinaNombre: string | null;
  tipoParticipacion: string | null;
  cupoMaximoPorFacultad: number | null;
  reglasEspecificas: string | null;
  estado: 'activa' | 'inactiva';
  inscritosActivos: number;
}

export interface InscripcionAdmin {
  inscripcionId: number;
  usuarioId: number;
  usuarioNombre: string | null;
  facultadId: number;
  facultadNombre: string | null;
}

export interface Resultado {
  id: number;
  edicionDisciplinaId: number;
  facultadLocalId: number;
  facultadLocalNombre: string | null;
  facultadVisitanteId: number | null;
  facultadVisitanteNombre: string | null;
  fase: string;
  grupo: string | null;
  fechaPartido: string | null;
  puntajeLocal: number | null;
  puntajeVisitante: number | null;
  facultadGanadoraId: number | null;
  facultadGanadoraNombre: string | null;
  estado: string;
  observaciones: string | null;
}

export interface TablaPosicion {
  facultadId: number;
  facultadNombre: string | null;
  facultadAbreviatura: string | null;
  partidosJugados: number;
  partidosGanados: number;
  partidosEmpatados: number;
  partidosPerdidos: number;
  puntosAFavor: number;
  puntosEnContra: number;
  puntos: number;
  posicion: number | null;
}

export interface ResultadoFormValues {
  facultadLocalId: string;
  facultadVisitanteId: string;
  fase: string;
  grupo: string;
  fechaPartido: string;
  puntajeLocal: string;
  puntajeVisitante: string;
  estado: 'programado' | 'en_curso' | 'finalizado' | 'cancelado' | 'suspendido';
  observaciones: string;
}

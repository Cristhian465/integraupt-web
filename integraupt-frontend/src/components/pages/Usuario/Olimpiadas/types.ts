export interface EdicionResponse {
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

export interface EdicionDisciplinaResponse {
  id: number;
  edicionId: number;
  disciplinaId: number;
  disciplinaNombre: string | null;
  tipoParticipacion: string | null;
  cupoMaximoPorFacultad: number | null;
  reglasEspecificas: string | null;
  estado: string;
  inscritosActivos: number;
}

export interface InscripcionMiaResponse {
  id: number;
  edicionDisciplinaId: number;
  disciplinaNombre: string | null;
  edicionNombre: string | null;
  facultadId: number;
  facultadNombre: string | null;
  estado: string;
  fechaInscripcion: string | null;
}

export interface ResultadoResponse {
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

export interface TablaPosicionResponse {
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

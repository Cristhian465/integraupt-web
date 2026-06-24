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
  categoria: 'general' | 'varones' | 'damas' | 'mixto';
  tipoParticipacion: string | null;
  tipoPuntuacion: string;
  cupoMaximoPorFacultad: number | null;
  reglasEspecificas: string | null;
  lugar: string | null;
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
  lugar: string | null;
  puntajeLocal: number | null;
  puntajeVisitante: number | null;
  facultadGanadoraId: number | null;
  facultadGanadoraNombre: string | null;
  estado: string;
  observaciones: string | null;
}

export interface ResultadoPosicionResponse {
  id: number;
  edicionDisciplinaId: number;
  facultadId: number;
  facultadNombre: string | null;
  facultadAbreviatura: string | null;
  posicion: number;
  puntos: number;
  prueba: string | null;
  fecha: string | null;
  lugar: string | null;
  observaciones: string | null;
  estado: string;
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

export interface MedalleroFilaResponse {
  facultadId: number;
  facultadNombre: string | null;
  facultadAbreviatura: string | null;
  oros: number;
  platas: number;
  bronces: number;
  disciplinas: number;
  puntosTotales: number;
  posicion: number;
}

export interface AnotadorResponse {
  id: number;
  edicionDisciplinaId: number;
  facultadId: number;
  facultadNombre: string | null;
  facultadAbreviatura: string | null;
  nombreJugador: string;
  cantidad: number;
  observaciones: string | null;
}

export interface PostResponse {
  id: number;
  edicionId: number;
  titulo: string;
  contenido: string;
  imagenUrl: string | null;
  autor: string | null;
  fechaPublicacion: string | null;
  totalComentarios: number;
}

export interface ComentarioResponse {
  id: number;
  postId: number;
  usuarioId: number;
  usuarioNombre: string | null;
  contenido: string;
  fechaComentario: string | null;
}

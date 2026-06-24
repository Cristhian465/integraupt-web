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
  tipoPuntuacion: 'partido' | 'posiciones';
  reglas: string | null;
  cupoMaximoDefault: number | null;
  estado: 'activa' | 'inactiva';
}

export interface DisciplinaFormValues {
  nombre: string;
  descripcion: string;
  tipoParticipacion: 'individual' | 'equipo';
  tipoPuntuacion: 'partido' | 'posiciones';
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
  categoria: 'general' | 'varones' | 'damas' | 'mixto';
  tipoParticipacion: string | null;
  tipoPuntuacion: 'partido' | 'posiciones';
  cupoMaximoPorFacultad: number | null;
  reglasEspecificas: string | null;
  lugar: string | null;
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

export interface EstudianteBusqueda {
  usuarioId: number;
  nombreCompleto: string;
  codigo: string;
  facultadId: number | null;
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

export interface ResultadoPosicion {
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

export interface ResultadoPosicionFormValues {
  facultadId: string;
  posicion: string;
  puntos: string;
  prueba: string;
  fecha: string;
  lugar: string;
  observaciones: string;
  estado: 'registrado' | 'cancelado';
}

export interface MedalleroFila {
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

export interface Anotador {
  id: number;
  edicionDisciplinaId: number;
  facultadId: number;
  facultadNombre: string | null;
  facultadAbreviatura: string | null;
  nombreJugador: string;
  cantidad: number;
  observaciones: string | null;
}

export interface AnotadorFormValues {
  facultadId: string;
  nombreJugador: string;
  cantidad: string;
  observaciones: string;
}

export interface Post {
  id: number;
  edicionId: number;
  titulo: string;
  contenido: string;
  imagenUrl: string | null;
  autor: string | null;
  fechaPublicacion: string | null;
  totalComentarios: number;
}

export interface PostFormValues {
  titulo: string;
  contenido: string;
  imagenUrl: string;
  autor: string;
}

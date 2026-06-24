export interface EstadoMoodleResponse {
  conectado: boolean;
  moodleUsername?: string | null;
  fechaConexion?: string | null;
}

export interface ConectarMoodlePayload {
  usuarioId: number;
  username: string;
  password: string;
}

export interface ConectarMoodleResponse {
  conectado: boolean;
  nombreCompleto?: string | null;
  sitio?: string | null;
}

export interface IniciarSsoResponse {
  passport: string;
  launchUrl: string;
}

export interface ConfirmarSsoPayload {
  usuarioId: number;
  passport: string;
  siteid: string;
  token: string;
  privateToken?: string | null;
}

export interface MoodleSsoPending {
  usuarioId: number;
  passport: string;
  ts: number;
}

export interface CursoMoodleResponse {
  id: number;
  nombre: string;
  progreso?: number | null;
  fechaInicio?: string | null;
}

export interface NotaMoodleResponse {
  nombre: string;
  calificacion?: string | null;
  porcentaje?: string | null;
}

export interface EventoMoodleResponse {
  id: number;
  nombre: string;
  curso?: string | null;
  fecha?: string | null;
}

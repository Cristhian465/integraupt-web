export interface TipoAtencionResponse {
  id: number;
  nombre: string;
  estado: boolean;
}

export interface MedicoResponse {
  id: number;
  nombre: string;
  estado: boolean;
}

export interface BloqueDisponibleResponse {
  id: number;
  nombre: string;
  horaInicio: string;
  horaFin: string;
}

export interface CitaPoliclinicoResponse {
  id: number;
  usuarioId: number;
  medicoId: number;
  medicoNombre?: string | null;
  tipoAtencionId: number;
  tipoAtencionNombre?: string | null;
  bloqueId: number;
  horaInicio?: string | null;
  horaFin?: string | null;
  fecha: string;
  motivo?: string | null;
  estado: string;
  fechaSolicitud: string;
}

export interface RegistrarCitaPayload {
  usuarioId: number;
  medicoId: number;
  tipoAtencionId: number;
  bloqueId: number;
  fecha: string;
  motivo?: string;
}

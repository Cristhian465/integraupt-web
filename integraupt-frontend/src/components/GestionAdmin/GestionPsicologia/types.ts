export interface CitaAdminResponse {
  id: number;
  usuarioId: number;
  estudianteNombre?: string | null;
  psicologoId: number;
  psicologoNombre?: string | null;
  bloqueId: number;
  horaInicio?: string | null;
  horaFin?: string | null;
  fecha: string;
  motivo?: string | null;
  estado: string;
  fechaSolicitud: string;
}

export interface PsicologoAdminResponse {
  id: number;
  nombre: string;
  especialidad?: string | null;
  estado: boolean;
}

export interface CitaFiltros {
  estado?: string;
  fecha?: string;
  psicologoId?: number;
}

export interface PsicologoFormValues {
  nombre: string;
  especialidad: string;
}

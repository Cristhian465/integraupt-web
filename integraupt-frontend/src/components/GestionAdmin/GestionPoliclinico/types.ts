export interface CitaAdminResponse {
  id: number;
  usuarioId: number;
  estudianteNombre?: string | null;
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

export interface TipoAtencionAdminResponse {
  id: number;
  nombre: string;
  estado: boolean;
}

export interface MedicoAdminResponse {
  id: number;
  nombre: string;
  estado: boolean;
  tiposAtencion: TipoAtencionAdminResponse[];
}

export interface CitaFiltros {
  estado?: string;
  fecha?: string;
  medicoId?: number;
  tipoAtencionId?: number;
}

export interface MedicoFormValues {
  nombre: string;
  tiposAtencionIds: number[];
}

export interface TipoAtencionFormValues {
  nombre: string;
}

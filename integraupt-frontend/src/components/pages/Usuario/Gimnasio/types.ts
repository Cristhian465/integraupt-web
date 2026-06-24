export interface AsistenciaGimnasioResponse {
  id_asistencia: number;
  id_usuario: number;
  usuario_nombre?: string | null;
  fecha: string;
  hora_ingreso: string;
  hora_salida?: string | null;
  duracion_calculada?: number | null;
}

export interface EstadoSesionResponse {
  activa: boolean;
  sesion: AsistenciaGimnasioResponse | null;
}

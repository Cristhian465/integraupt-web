export type EstadoCanal = "activo" | "archivado";
export type TipoCreadorCanal = "admin" | "docente";
export type RolMiembroCanal = "creador" | "miembro";

export interface MiembroCanal {
  idUsuario: number;
  nombre?: string | null;
  rol: RolMiembroCanal;
}

export interface Tema {
  id: number;
  canalId: number;
  nombre: string;
  descripcion?: string | null;
  orden: number;
  fechaCreacion?: string | null;
}

export interface Canal {
  id: number;
  nombre: string;
  descripcion?: string | null;
  creadorId: number;
  creadorNombre?: string | null;
  tipoCreador: TipoCreadorCanal;
  estado: EstadoCanal;
  color?: string | null;
  fotoUrl?: string | null;
  fechaCreacion?: string | null;
  miembros: MiembroCanal[];
}

export interface CanalPayload {
  nombre: string;
  descripcion?: string;
  creadorId: number;
  miembros?: number[];
  color?: string;
  fotoUrl?: string;
}

export interface Reaccion {
  emoji: string;
  cantidad: number;
  usuarios: number[];
}

export interface RespuestaResumen {
  id: number;
  usuarioNombre?: string | null;
  contenido?: string | null;
  imagenUrl?: string | null;
}

export interface Mensaje {
  id: number;
  canalId: number;
  temaId?: number | null;
  usuarioId: number;
  usuarioNombre?: string | null;
  contenido: string;
  imagenUrl?: string | null;
  idMensajeRespuesta?: number | null;
  respuestaA?: RespuestaResumen | null;
  reacciones: Reaccion[];
  fechaEnvio?: string | null;
}

export interface LinkPreview {
  title?: string | null;
  description?: string | null;
  image?: string | null;
  url: string;
  siteName?: string | null;
}

export interface UsuarioBusqueda {
  id: number;
  nombre: string;
  numDoc?: string | null;
  esAdmin: boolean;
  esDocente: boolean;
}

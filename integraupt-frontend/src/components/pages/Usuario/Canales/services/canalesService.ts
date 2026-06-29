import axios, { type AxiosInstance, type AxiosResponse } from "axios";
import { CANALES_API_BASE_URL } from "../../../../../utils/apiConfig";
import type { Canal, LinkPreview, Mensaje, Reaccion, Tema, TipoArchivoAdjunto, UsuarioBusqueda } from "../types";

const canalesClient: AxiosInstance = axios.create({
  baseURL: CANALES_API_BASE_URL,
  headers: { "Content-Type": "application/json" },
  timeout: 15000
});

const normalizeError = (error: unknown): Error => {
  if (axios.isAxiosError(error)) {
    const responseData = error.response?.data;
    if (typeof responseData === "string") return new Error(responseData);
    if (responseData && typeof responseData === "object" && "message" in responseData) {
      const msg = (responseData as { message?: string }).message;
      if (msg) return new Error(msg);
    }
    return new Error(error.message || "No se pudo completar la solicitud.");
  }
  return error instanceof Error ? error : new Error("No se pudo completar la solicitud.");
};

const unwrap = async <T>(promise: Promise<AxiosResponse<T>>): Promise<T> => {
  try {
    const { data } = await promise;
    return data;
  } catch (error) {
    throw normalizeError(error);
  }
};

// Canales
export const fetchMisCanales = (usuarioId: number): Promise<Canal[]> =>
  unwrap(canalesClient.get<{ data: Canal[] }>("/api/canales", { params: { usuarioId, esAdmin: 0 } }))
    .then((r) => r.data);

export const createCanal = (
  nombre: string,
  descripcion: string | undefined,
  creadorId: number,
  miembros: number[],
  color?: string,
  fotoUrl?: string
): Promise<Canal> =>
  unwrap(canalesClient.post<Canal>("/api/canales", { nombre, descripcion, creadorId, miembros, color, fotoUrl }));

// Temas
export const fetchTemas = (canalId: number): Promise<Tema[]> =>
  unwrap(canalesClient.get<{ data: Tema[] }>(`/api/canales/${canalId}/temas`)).then((r) => r.data);

export const createTema = (canalId: number, nombre: string, descripcion?: string): Promise<Tema> =>
  unwrap(canalesClient.post<Tema>(`/api/canales/${canalId}/temas`, { nombre, descripcion }));

export const deleteTema = async (canalId: number, temaId: number): Promise<void> => {
  try {
    await canalesClient.delete(`/api/canales/${canalId}/temas/${temaId}`);
  } catch (error) {
    throw normalizeError(error);
  }
};

// Mensajes
export const fetchMensajes = (idCanal: number, temaId?: number | null): Promise<Mensaje[]> =>
  unwrap(
    canalesClient.get<{ data: Mensaje[] }>(`/api/canales/${idCanal}/mensajes`, {
      params: temaId != null ? { temaId } : {}
    })
  ).then((r) => r.data);

export const enviarMensaje = (
  idCanal: number,
  usuarioId: number,
  contenido: string,
  options?: {
    temaId?: number | null;
    idMensajeRespuesta?: number | null;
    archivoUrl?: string | null;
    archivoTipo?: TipoArchivoAdjunto | null;
    archivoNombre?: string | null;
  }
): Promise<Mensaje> =>
  unwrap(
    canalesClient.post<Mensaje>(`/api/canales/${idCanal}/mensajes`, {
      usuarioId,
      contenido,
      temaId: options?.temaId ?? null,
      idMensajeRespuesta: options?.idMensajeRespuesta ?? null,
      archivoUrl: options?.archivoUrl ?? null,
      archivoTipo: options?.archivoTipo ?? null,
      archivoNombre: options?.archivoNombre ?? null,
    })
  );

export const eliminarMensaje = async (idCanal: number, idMensaje: number, usuarioId: number): Promise<void> => {
  try {
    await canalesClient.delete(`/api/canales/${idCanal}/mensajes/${idMensaje}`, {
      params: { usuarioId, esAdmin: 0 }
    });
  } catch (error) {
    throw normalizeError(error);
  }
};

export const editarMensaje = (idCanal: number, idMensaje: number, usuarioId: number, contenido: string): Promise<Mensaje> =>
  unwrap(canalesClient.put<Mensaje>(`/api/canales/${idCanal}/mensajes/${idMensaje}`, { usuarioId, contenido }));

// Indicador de "escribiendo"
export const marcarEscribiendo = async (idCanal: number, usuarioId: number, temaId?: number | null): Promise<void> => {
  try {
    await canalesClient.post(`/api/canales/${idCanal}/escribiendo`, { usuarioId, temaId: temaId ?? null });
  } catch {
    // best-effort, no es crítico si falla un ping
  }
};

export const fetchEscribiendo = (idCanal: number, usuarioId: number, temaId?: number | null): Promise<string[]> =>
  unwrap(
    canalesClient.get<{ usuarios: string[] }>(`/api/canales/${idCanal}/escribiendo`, {
      params: { usuarioId, temaId: temaId ?? undefined }
    })
  ).then((r) => r.usuarios);

// Reacciones
export const toggleReaccion = (
  idCanal: number,
  idMensaje: number,
  usuarioId: number,
  emoji: string
): Promise<{ accion: string; reacciones: Reaccion[] }> =>
  unwrap(
    canalesClient.post(`/api/canales/${idCanal}/mensajes/${idMensaje}/reacciones`, { usuarioId, emoji })
  );

// Subida de archivos adjuntos (imagen, video, audio, documentos, comprimidos)
export interface ArchivoSubido {
  url: string;
  tipo: TipoArchivoAdjunto;
  nombre: string;
  mime: string;
}

export const uploadArchivo = async (file: File): Promise<ArchivoSubido> => {
  const formData = new FormData();
  formData.append("file", file);
  const { data } = await canalesClient.post<ArchivoSubido>("/api/upload", formData, {
    headers: { "Content-Type": "multipart/form-data" },
  });
  return data;
};

// Link preview
export const fetchLinkPreview = (url: string): Promise<LinkPreview> =>
  unwrap(canalesClient.get<LinkPreview>("/api/preview", { params: { url } }));

// Búsqueda de usuarios
export const buscarUsuarios = (search: string): Promise<UsuarioBusqueda[]> =>
  unwrap(canalesClient.get<{ data: UsuarioBusqueda[] }>("/api/catalogos/usuarios", { params: { search } }))
    .then((r) => r.data);

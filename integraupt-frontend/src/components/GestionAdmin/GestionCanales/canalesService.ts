import axios, { type AxiosInstance, type AxiosResponse } from "axios";
import { CANALES_API_BASE_URL } from "../../../utils/apiConfig";
import type { Canal, CanalPayload, EstadoCanal, LinkPreview, Mensaje, Reaccion, Tema, UsuarioBusqueda } from "./types";

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
export const fetchCanales = (usuarioId: number, esAdmin: boolean): Promise<Canal[]> =>
  unwrap(canalesClient.get<{ data: Canal[] }>("/api/canales", { params: { usuarioId, esAdmin: esAdmin ? 1 : 0 } }))
    .then((r) => r.data);

export const createCanal = (payload: CanalPayload): Promise<Canal> =>
  unwrap(canalesClient.post<Canal>("/api/canales", {
    nombre: payload.nombre,
    descripcion: payload.descripcion,
    creadorId: payload.creadorId,
    miembros: payload.miembros ?? [],
    color: payload.color,
    fotoUrl: payload.fotoUrl,
  }));

export const updateCanal = (id: number, nombre: string, descripcion?: string, color?: string, fotoUrl?: string): Promise<Canal> =>
  unwrap(canalesClient.put<Canal>(`/api/canales/${id}`, { nombre, descripcion, color, fotoUrl }));

export const cambiarEstadoCanal = (id: number, estado: EstadoCanal): Promise<Canal> =>
  unwrap(canalesClient.patch<Canal>(`/api/canales/${id}/estado`, { estado }));

export const agregarMiembros = (id: number, miembros: number[]): Promise<Canal> =>
  unwrap(canalesClient.post<Canal>(`/api/canales/${id}/miembros`, { miembros }));

export const quitarMiembro = async (id: number, idUsuario: number): Promise<void> => {
  try {
    await canalesClient.delete(`/api/canales/${id}/miembros/${idUsuario}`);
  } catch (error) {
    throw normalizeError(error);
  }
};

// Temas
export const fetchTemas = (canalId: number): Promise<Tema[]> =>
  unwrap(canalesClient.get<{ data: Tema[] }>(`/api/canales/${canalId}/temas`)).then((r) => r.data);

export const createTema = (canalId: number, nombre: string, descripcion?: string): Promise<Tema> =>
  unwrap(canalesClient.post<Tema>(`/api/canales/${canalId}/temas`, { nombre, descripcion }));

export const updateTema = (canalId: number, temaId: number, nombre: string, descripcion?: string): Promise<Tema> =>
  unwrap(canalesClient.put<Tema>(`/api/canales/${canalId}/temas/${temaId}`, { nombre, descripcion }));

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
  options?: { temaId?: number | null; idMensajeRespuesta?: number | null; imagenUrl?: string | null }
): Promise<Mensaje> =>
  unwrap(
    canalesClient.post<Mensaje>(`/api/canales/${idCanal}/mensajes`, {
      usuarioId,
      contenido,
      temaId: options?.temaId ?? null,
      idMensajeRespuesta: options?.idMensajeRespuesta ?? null,
      imagenUrl: options?.imagenUrl ?? null,
    })
  );

export const eliminarMensaje = async (idCanal: number, idMensaje: number, usuarioId: number, esAdmin: boolean): Promise<void> => {
  try {
    await canalesClient.delete(`/api/canales/${idCanal}/mensajes/${idMensaje}`, {
      params: { usuarioId, esAdmin: esAdmin ? 1 : 0 }
    });
  } catch (error) {
    throw normalizeError(error);
  }
};

// Reacciones
export const toggleReaccion = (
  idCanal: number,
  idMensaje: number,
  usuarioId: number,
  emoji: string
): Promise<{ accion: string; reacciones: Reaccion[] }> =>
  unwrap(canalesClient.post(`/api/canales/${idCanal}/mensajes/${idMensaje}/reacciones`, { usuarioId, emoji }));

// Subida de imagen
export const uploadImagen = async (file: File): Promise<string> => {
  const formData = new FormData();
  formData.append("file", file);
  const { data } = await canalesClient.post<{ url: string }>("/api/upload", formData, {
    headers: { "Content-Type": "multipart/form-data" },
  });
  return data.url;
};

// Link preview
export const fetchLinkPreview = (url: string): Promise<LinkPreview> =>
  unwrap(canalesClient.get<LinkPreview>("/api/preview", { params: { url } }));

// Búsqueda de usuarios
export const buscarUsuarios = (search: string): Promise<UsuarioBusqueda[]> =>
  unwrap(canalesClient.get<{ data: UsuarioBusqueda[] }>("/api/catalogos/usuarios", { params: { search } }))
    .then((r) => r.data);

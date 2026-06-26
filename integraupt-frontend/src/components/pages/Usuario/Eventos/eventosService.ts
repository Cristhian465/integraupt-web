import axios, { type AxiosInstance, type AxiosResponse } from "axios";
import { getEventosApiUrl } from "../../../../utils/apiConfig";
import type { Evento, MiFacultad, MiInscripcion } from "./types";

const client: AxiosInstance = axios.create({ timeout: 15000 });

const normalizeError = (error: unknown): Error => {
  if (axios.isAxiosError(error)) {
    const responseData = error.response?.data;
    if (responseData && typeof responseData === "object" && "message" in responseData) {
      const maybeMessage = (responseData as { message?: string }).message;
      if (maybeMessage) return new Error(maybeMessage);
    }
    return new Error(error.message || "No se pudo completar la solicitud.");
  }
  if (error instanceof Error) return error;
  return new Error("No se pudo completar la solicitud.");
};

const unwrap = async <T>(promise: Promise<AxiosResponse<T>>): Promise<T> => {
  try {
    const { data } = await promise;
    return data;
  } catch (error) {
    throw normalizeError(error);
  }
};

export const fetchMiFacultad = (usuarioId: number): Promise<MiFacultad> =>
  unwrap(client.get<MiFacultad>(getEventosApiUrl("/api/catalogos/mi-facultad"), { params: { usuarioId } }));

export const fetchEventosPublicados = async (facultadId: number): Promise<Evento[]> => {
  const data = await unwrap(
    client.get<{ data: Evento[] }>(getEventosApiUrl("/api/eventos"), {
      params: { facultadId, estado: "publicado", porPagina: 50 },
    })
  );
  return data.data;
};

export const fetchMisInscripciones = (usuarioId: number): Promise<MiInscripcion[]> =>
  unwrap(client.get<MiInscripcion[]>(getEventosApiUrl("/api/mis-inscripciones"), { params: { usuarioId } }));

export const inscribirme = (eventoId: number, usuarioId: number): Promise<MiInscripcion> =>
  unwrap(
    client.post<MiInscripcion>(getEventosApiUrl(`/api/eventos/${eventoId}/inscripciones`), { usuarioId })
  );

export const cancelarInscripcion = (eventoId: number, inscripcionId: number): Promise<MiInscripcion> =>
  unwrap(
    client.delete<MiInscripcion>(getEventosApiUrl(`/api/eventos/${eventoId}/inscripciones/${inscripcionId}`))
  );

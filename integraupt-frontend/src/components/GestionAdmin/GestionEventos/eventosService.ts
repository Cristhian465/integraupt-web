import axios, { type AxiosInstance, type AxiosResponse } from "axios";
import { EVENTOS_API_BASE_URL } from "../../../utils/apiConfig";
import type {
  Escuela,
  EspacioCatalogo,
  Evento,
  EventoInscripcion,
  EventoPayload,
  EstadoEvento,
  Facultad,
  ReporteAsistencia
} from "./types";

const eventosClient: AxiosInstance = axios.create({
  baseURL: EVENTOS_API_BASE_URL,
  headers: {
    "Content-Type": "application/json"
  },
  timeout: 15000
});

const normalizeError = (error: unknown): Error => {
  if (axios.isAxiosError(error)) {
    const responseData = error.response?.data;
    if (typeof responseData === "string") {
      return new Error(responseData);
    }

    if (responseData && typeof responseData === "object" && "message" in responseData) {
      const maybeMessage = (responseData as { message?: string }).message;
      if (maybeMessage) {
        return new Error(maybeMessage);
      }
    }

    return new Error(error.message || "No se pudo completar la solicitud en eventos-backend.");
  }

  if (error instanceof Error) {
    return error;
  }

  return new Error("No se pudo completar la solicitud en eventos-backend.");
};

const unwrap = async <T>(promise: Promise<AxiosResponse<T>>): Promise<T> => {
  try {
    const { data } = await promise;
    return data;
  } catch (error) {
    throw normalizeError(error);
  }
};

export interface EventoFiltros {
  facultadId?: string;
  escuelaId?: string;
  tipoEvento?: string;
  estado?: string;
}

export const fetchEventos = (filtros: EventoFiltros = {}): Promise<Evento[]> =>
  unwrap(
    eventosClient.get<{ data: Evento[] }>("/api/eventos", { params: filtros })
  ).then((response) => response.data);

export const createEvento = (payload: EventoPayload): Promise<Evento> =>
  unwrap(eventosClient.post<Evento>("/api/eventos", payload));

export const updateEvento = (id: number, payload: EventoPayload): Promise<Evento> =>
  unwrap(eventosClient.put<Evento>(`/api/eventos/${id}`, payload));

export const cambiarEstadoEvento = (id: number, estado: EstadoEvento): Promise<Evento> =>
  unwrap(eventosClient.patch<Evento>(`/api/eventos/${id}/estado`, { estado }));

export const fetchReporteAsistencia = (id: number): Promise<ReporteAsistencia> =>
  unwrap(eventosClient.get<ReporteAsistencia>(`/api/eventos/${id}/reporte`));

export const fetchInscripciones = (idEvento: number): Promise<EventoInscripcion[]> =>
  unwrap(eventosClient.get<EventoInscripcion[]>(`/api/eventos/${idEvento}/inscripciones`));

export const inscribirUsuario = (
  idEvento: number,
  usuarioId: number
): Promise<EventoInscripcion> =>
  unwrap(eventosClient.post<EventoInscripcion>(`/api/eventos/${idEvento}/inscripciones`, { usuarioId }));

export const cancelarInscripcion = async (idEvento: number, idInscripcion: number): Promise<void> => {
  try {
    await eventosClient.delete(`/api/eventos/${idEvento}/inscripciones/${idInscripcion}`);
  } catch (error) {
    throw normalizeError(error);
  }
};

export const checkinPorQr = (idEvento: number, codigoQr: string): Promise<EventoInscripcion> =>
  unwrap(eventosClient.post<EventoInscripcion>(`/api/eventos/${idEvento}/checkin`, { codigoQr }));

export const fetchFacultades = (): Promise<Facultad[]> =>
  unwrap(eventosClient.get<Facultad[]>("/api/catalogos/facultades"));

export const fetchEscuelas = (facultadId?: number): Promise<Escuela[]> =>
  unwrap(
    eventosClient.get<Escuela[]>("/api/catalogos/escuelas", {
      params: facultadId ? { facultadId } : {}
    })
  );

export const fetchEspaciosCatalogo = (escuelaId?: number): Promise<EspacioCatalogo[]> =>
  unwrap(
    eventosClient.get<EspacioCatalogo[]>("/api/catalogos/espacios", {
      params: escuelaId ? { escuelaId } : {}
    })
  );

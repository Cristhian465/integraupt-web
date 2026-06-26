import axios, { type AxiosInstance, type AxiosResponse } from "axios";
import { getCafeteriaApiUrl } from "../../../../utils/apiConfig";
import type { Cafeteria, Pedido } from "./types";

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

export const fetchMiCafeteria = (usuarioId: number): Promise<Cafeteria> =>
  unwrap(client.get<Cafeteria>(getCafeteriaApiUrl("/api/cafeterias/cliente"), { params: { usuarioId } }));

export const fetchMisPedidos = (usuarioId: number): Promise<Pedido[]> =>
  unwrap(client.get<Pedido[]>(getCafeteriaApiUrl("/api/pedidos/mios"), { params: { usuarioId } }));

export const crearPedido = (
  cafeteriaId: number,
  usuarioId: number,
  items: Array<{ productoId: number; cantidad: number }>,
  codigoOperacion: string,
  comprobante: File
): Promise<Pedido> => {
  const formData = new FormData();
  formData.append("usuarioId", String(usuarioId));
  formData.append("codigoOperacion", codigoOperacion);
  formData.append("comprobante", comprobante);
  items.forEach((item, index) => {
    formData.append(`items[${index}][productoId]`, String(item.productoId));
    formData.append(`items[${index}][cantidad]`, String(item.cantidad));
  });

  return unwrap(
    client.post<Pedido>(getCafeteriaApiUrl(`/api/cafeterias/${cafeteriaId}/pedidos`), formData, {
      headers: { "Content-Type": "multipart/form-data" }
    })
  );
};

export const cancelarMiPedido = (idPedido: number, usuarioId: number): Promise<Pedido> =>
  unwrap(client.patch<Pedido>(getCafeteriaApiUrl(`/api/pedidos/${idPedido}/cancelar`), { usuarioId }));

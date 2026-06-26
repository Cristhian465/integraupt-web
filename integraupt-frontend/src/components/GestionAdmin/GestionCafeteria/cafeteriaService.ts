import axios, { type AxiosInstance, type AxiosResponse } from "axios";
import { getCafeteriaApiUrl } from "../../../utils/apiConfig";
import type {
  AdministrativoCatalogo,
  Cafeteria,
  Facultad,
  Pedido,
  Producto,
  ProductoFormValues
} from "./types";

const client: AxiosInstance = axios.create({
  headers: { "Content-Type": "application/json" },
  timeout: 15000
});

const normalizeError = (error: unknown): Error => {
  if (axios.isAxiosError(error)) {
    const responseData = error.response?.data;
    if (responseData && typeof responseData === "object" && "message" in responseData) {
      const maybeMessage = (responseData as { message?: string }).message;
      if (maybeMessage) return new Error(maybeMessage);
    }
    return new Error(error.message || "No se pudo completar la solicitud en cafeteria-backend.");
  }
  if (error instanceof Error) return error;
  return new Error("No se pudo completar la solicitud en cafeteria-backend.");
};

const unwrap = async <T>(promise: Promise<AxiosResponse<T>>): Promise<T> => {
  try {
    const { data } = await promise;
    return data;
  } catch (error) {
    throw normalizeError(error);
  }
};

export const fetchCafeterias = (): Promise<Cafeteria[]> =>
  unwrap(client.get<Cafeteria[]>(getCafeteriaApiUrl("/api/cafeterias")));

export const fetchCafeteria = (id: number): Promise<Cafeteria> =>
  unwrap(client.get<Cafeteria>(getCafeteriaApiUrl(`/api/cafeterias/${id}`)));

export const fetchCafeteriaDeEncargado = (usuarioId: number): Promise<Cafeteria> =>
  unwrap(
    client.get<Cafeteria>(getCafeteriaApiUrl("/api/cafeterias/encargado"), {
      params: { usuarioId }
    })
  );

export const createCafeteria = (nombre: string, facultadId: number): Promise<Cafeteria> =>
  unwrap(client.post<Cafeteria>(getCafeteriaApiUrl("/api/cafeterias"), { nombre, facultadId }));

export const updateCafeteria = (
  id: number,
  payload: { nombre: string; facultadId: number; estado?: boolean; encargadoId?: number | null }
): Promise<Cafeteria> => unwrap(client.put<Cafeteria>(getCafeteriaApiUrl(`/api/cafeterias/${id}`), payload));

export const fetchFacultades = (): Promise<Facultad[]> =>
  unwrap(client.get<Facultad[]>(getCafeteriaApiUrl("/api/catalogos/facultades")));

export const buscarAdministrativos = (busqueda: string): Promise<AdministrativoCatalogo[]> =>
  unwrap(
    client.get<AdministrativoCatalogo[]>(getCafeteriaApiUrl("/api/catalogos/administrativos"), {
      params: { busqueda }
    })
  );

export const fetchProductos = (cafeteriaId: number): Promise<Producto[]> =>
  unwrap(client.get<Producto[]>(getCafeteriaApiUrl(`/api/cafeterias/${cafeteriaId}/productos`)));

const productoPayload = (values: ProductoFormValues) => ({
  nombre: values.nombre.trim(),
  descripcion: values.descripcion.trim() || undefined,
  precio: Number(values.precio),
  stock: Number(values.stock),
  estado: values.estado
});

export const createProducto = (cafeteriaId: number, values: ProductoFormValues): Promise<Producto> =>
  unwrap(
    client.post<Producto>(getCafeteriaApiUrl(`/api/cafeterias/${cafeteriaId}/productos`), productoPayload(values))
  );

export const updateProducto = (
  cafeteriaId: number,
  id: number,
  values: ProductoFormValues
): Promise<Producto> =>
  unwrap(
    client.put<Producto>(
      getCafeteriaApiUrl(`/api/cafeterias/${cafeteriaId}/productos/${id}`),
      productoPayload(values)
    )
  );

export const cambiarEstadoProducto = (
  cafeteriaId: number,
  id: number,
  estado: boolean
): Promise<Producto> =>
  unwrap(
    client.patch<Producto>(getCafeteriaApiUrl(`/api/cafeterias/${cafeteriaId}/productos/${id}/estado`), {
      estado
    })
  );

export const fetchPedidosPorCafeteria = (cafeteriaId: number, estado?: string): Promise<Pedido[]> =>
  unwrap(
    client.get<Pedido[]>(getCafeteriaApiUrl(`/api/cafeterias/${cafeteriaId}/pedidos`), {
      params: estado ? { estado } : {}
    })
  );

export const aprobarPedido = (idPedido: number): Promise<Pedido> =>
  unwrap(client.patch<Pedido>(getCafeteriaApiUrl(`/api/pedidos/${idPedido}/aprobar`)));

export const rechazarPedido = (idPedido: number, motivo: string): Promise<Pedido> =>
  unwrap(client.patch<Pedido>(getCafeteriaApiUrl(`/api/pedidos/${idPedido}/rechazar`), { motivo }));

export const cambiarEstadoPedido = (idPedido: number, estado: "preparando" | "listo"): Promise<Pedido> =>
  unwrap(client.patch<Pedido>(getCafeteriaApiUrl(`/api/pedidos/${idPedido}/estado`), { estado }));

export const checkinPedido = (codigoQr: string): Promise<Pedido> =>
  unwrap(client.post<Pedido>(getCafeteriaApiUrl("/api/pedidos/checkin"), { codigoQr }));

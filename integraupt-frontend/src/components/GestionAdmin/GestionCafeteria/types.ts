export type EstadoPedido =
  | "pendiente_revision"
  | "pagado"
  | "preparando"
  | "listo"
  | "entregado"
  | "rechazado"
  | "cancelado";

export interface Facultad {
  id: number;
  nombre: string;
  abreviatura?: string | null;
}

export interface AdministrativoCatalogo {
  usuarioId: number;
  nombreCompleto: string;
}

export interface Producto {
  id: number;
  cafeteriaId: number;
  nombre: string;
  descripcion?: string | null;
  precio: number;
  stock: number;
  estado: boolean;
}

export interface Cafeteria {
  id: number;
  nombre: string;
  estado: boolean;
  facultadId: number;
  facultadNombre?: string | null;
  encargadoId?: number | null;
  encargadoNombre?: string | null;
  productos?: Producto[];
}

export interface PedidoItem {
  productoId: number;
  productoNombre?: string | null;
  cantidad: number;
  precioUnitario: number;
}

export interface Pedido {
  id: number;
  cafeteriaId: number;
  cafeteriaNombre?: string | null;
  usuarioId: number;
  usuarioNombre?: string | null;
  total: number;
  estado: EstadoPedido;
  codigoQr?: string | null;
  comprobanteUrl?: string | null;
  codigoOperacion?: string | null;
  motivoRechazo?: string | null;
  fechaPedido?: string | null;
  fechaConfirmacionPago?: string | null;
  fechaEntrega?: string | null;
  items: PedidoItem[];
}

export type ProductoFormValues = {
  nombre: string;
  descripcion: string;
  precio: string;
  stock: string;
  estado: boolean;
};

export const createEmptyProductoForm = (): ProductoFormValues => ({
  nombre: "",
  descripcion: "",
  precio: "",
  stock: "",
  estado: true
});

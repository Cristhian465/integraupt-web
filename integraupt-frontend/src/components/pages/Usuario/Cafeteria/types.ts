export interface Producto {
  id: number;
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
  productos: Producto[];
}

export interface PedidoItem {
  productoId: number;
  productoNombre?: string | null;
  cantidad: number;
  precioUnitario: number;
}

export type EstadoPedido =
  | "pendiente_revision"
  | "pagado"
  | "preparando"
  | "listo"
  | "entregado"
  | "rechazado"
  | "cancelado";

export interface Pedido {
  id: number;
  cafeteriaId: number;
  cafeteriaNombre?: string | null;
  total: number;
  estado: EstadoPedido;
  codigoQr?: string | null;
  comprobanteUrl?: string | null;
  motivoRechazo?: string | null;
  fechaPedido?: string | null;
  items: PedidoItem[];
}

import React, { useState } from "react";
import { Check, X, ChefHat, BellRing, Image as ImageIcon } from "lucide-react";
import type { Pedido } from "../types";

interface PedidoCardProps {
  pedido: Pedido;
  onAprobar: (pedido: Pedido) => void;
  onRechazar: (pedido: Pedido, motivo: string) => void;
  onCambiarEstado: (pedido: Pedido, estado: "preparando" | "listo") => void;
}

const ESTADO_LABEL: Record<string, string> = {
  pendiente_revision: "Pendiente de revision",
  pagado: "Pagado",
  preparando: "Preparando",
  listo: "Listo para recoger",
  entregado: "Entregado",
  rechazado: "Rechazado",
  cancelado: "Cancelado"
};

const formatFecha = (iso?: string | null): string => {
  if (!iso) return "-";
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return iso;
  return date.toLocaleString("es-PE", { dateStyle: "medium", timeStyle: "short" });
};

export const PedidoCard: React.FC<PedidoCardProps> = ({ pedido, onAprobar, onRechazar, onCambiarEstado }) => {
  const [motivo, setMotivo] = useState("");
  const [showMotivo, setShowMotivo] = useState(false);

  return (
    <div className="cafeteria-pedido-card">
      <div className="cafeteria-pedido-header">
        <div>
          <p className="cafeteria-pedido-usuario">{pedido.usuarioNombre ?? `Usuario #${pedido.usuarioId}`}</p>
          <span className="cafeteria-pedido-fecha">{formatFecha(pedido.fechaPedido)}</span>
        </div>
        <span className={`cafeteria-status-badge ${pedido.estado}`}>{ESTADO_LABEL[pedido.estado] ?? pedido.estado}</span>
      </div>

      <ul className="cafeteria-pedido-items">
        {pedido.items.map((item) => (
          <li key={item.productoId}>
            {item.cantidad}x {item.productoNombre ?? `Producto #${item.productoId}`} — S/. {(item.precioUnitario * item.cantidad).toFixed(2)}
          </li>
        ))}
      </ul>

      <div className="cafeteria-pedido-footer">
        <strong>Total: S/. {pedido.total.toFixed(2)}</strong>
        {pedido.codigoOperacion && <span>Operacion Yape: {pedido.codigoOperacion}</span>}
      </div>

      {pedido.comprobanteUrl && (
        <a href={pedido.comprobanteUrl} target="_blank" rel="noreferrer" className="cafeteria-comprobante-link">
          <ImageIcon size={14} /> Ver comprobante de pago
        </a>
      )}

      {pedido.estado === "pendiente_revision" && (
        <div className="cafeteria-pedido-actions">
          <button type="button" className="gestion-cafeteria-btn primary small" onClick={() => onAprobar(pedido)}>
            <Check size={14} /> Aprobar pago
          </button>
          {!showMotivo ? (
            <button type="button" className="gestion-cafeteria-btn ghost small" onClick={() => setShowMotivo(true)}>
              <X size={14} /> Rechazar
            </button>
          ) : (
            <div className="cafeteria-rechazo-inline">
              <input
                type="text"
                placeholder="Motivo del rechazo"
                value={motivo}
                onChange={(event) => setMotivo(event.target.value)}
              />
              <button
                type="button"
                className="gestion-cafeteria-btn ghost small"
                disabled={!motivo.trim()}
                onClick={() => {
                  onRechazar(pedido, motivo.trim());
                  setMotivo("");
                  setShowMotivo(false);
                }}
              >
                Confirmar
              </button>
            </div>
          )}
        </div>
      )}

      {pedido.estado === "pagado" && (
        <div className="cafeteria-pedido-actions">
          <button
            type="button"
            className="gestion-cafeteria-btn primary small"
            onClick={() => onCambiarEstado(pedido, "preparando")}
          >
            <ChefHat size={14} /> Empezar a preparar
          </button>
        </div>
      )}

      {pedido.estado === "preparando" && (
        <div className="cafeteria-pedido-actions">
          <button
            type="button"
            className="gestion-cafeteria-btn primary small"
            onClick={() => onCambiarEstado(pedido, "listo")}
          >
            <BellRing size={14} /> Marcar listo para recoger
          </button>
        </div>
      )}

      {pedido.estado === "listo" && pedido.codigoQr && (
        <p className="cafeteria-pedido-qr">Codigo de recojo: <code>{pedido.codigoQr}</code></p>
      )}

      {pedido.estado === "rechazado" && pedido.motivoRechazo && (
        <p className="cafeteria-pedido-motivo">Motivo: {pedido.motivoRechazo}</p>
      )}
    </div>
  );
};

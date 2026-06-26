import React, { useCallback, useEffect, useMemo, useState } from "react";
import { AlertTriangle, CheckCircle2, Coffee, Minus, Plus, ShoppingCart, Upload } from "lucide-react";
import "../../../../styles/CafeteriaScreen.css";
import { Navbar } from "../Navbar";
import { cancelarMiPedido, crearPedido, fetchMiCafeteria, fetchMisPedidos } from "./cafeteriaService";
import type { Cafeteria, Pedido } from "./types";

interface CafeteriaPageProps {
  user: {
    id: string;
    email: string;
    user_metadata: {
      name: string;
      avatar_url: string;
      role?: string;
      login_type?: string;
      codigo?: string;
      escuelaId?: number;
      escuelaNombre?: string;
    };
  };
  onNavigateToInicio: () => void;
  onNavigateToServicios: () => void;
  onNavigateToPerfil: () => void;
  onLogout?: () => void;
  isLoggingOut?: boolean;
}

type Feedback = { type: "success" | "error"; message: string };

const ESTADO_LABEL: Record<string, string> = {
  pendiente_revision: "Pendiente de revision",
  pagado: "Pago confirmado",
  preparando: "Preparando tu pedido",
  listo: "Listo para recoger",
  entregado: "Entregado",
  rechazado: "Pago rechazado",
  cancelado: "Cancelado"
};

const getDisplayName = (name?: string, codigo?: string): string => {
  const trimmedName = name?.trim();
  if (trimmedName) return trimmedName;
  const trimmedCodigo = codigo?.trim();
  if (trimmedCodigo) return trimmedCodigo;
  return "Usuario";
};

export const CafeteriaPage: React.FC<CafeteriaPageProps> = ({
  user,
  onNavigateToInicio,
  onNavigateToServicios,
  onNavigateToPerfil,
  onLogout,
  isLoggingOut = false
}) => {
  const userId = useMemo(() => {
    const parsed = Number.parseInt(user.id, 10);
    return Number.isNaN(parsed) ? null : parsed;
  }, [user.id]);

  const displayName = useMemo(
    () => getDisplayName(user.user_metadata.name, user.user_metadata.codigo),
    [user.user_metadata.codigo, user.user_metadata.name]
  );

  const [tab, setTab] = useState<"menu" | "pedidos">("menu");
  const [cafeteria, setCafeteria] = useState<Cafeteria | null>(null);
  const [pedidos, setPedidos] = useState<Pedido[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [feedback, setFeedback] = useState<Feedback | null>(null);

  const [carrito, setCarrito] = useState<Record<number, number>>({});
  const [codigoOperacion, setCodigoOperacion] = useState("");
  const [comprobante, setComprobante] = useState<File | null>(null);
  const [enviandoPedido, setEnviandoPedido] = useState(false);

  const cargarTodo = useCallback(async () => {
    if (!userId) return;
    setLoading(true);
    setError(null);
    try {
      const [cafeteriaData, pedidosData] = await Promise.all([
        fetchMiCafeteria(userId),
        fetchMisPedidos(userId)
      ]);
      setCafeteria(cafeteriaData);
      setPedidos(pedidosData);
    } catch (loadError) {
      setError(loadError instanceof Error ? loadError.message : "No se pudo cargar la cafeteria.");
    } finally {
      setLoading(false);
    }
  }, [userId]);

  useEffect(() => {
    void cargarTodo();
  }, [cargarTodo]);

  const productosDisponibles = useMemo(
    () => (cafeteria?.productos ?? []).filter((p) => p.estado),
    [cafeteria]
  );

  const itemsCarrito = useMemo(
    () =>
      Object.entries(carrito)
        .filter(([, cantidad]) => cantidad > 0)
        .map(([productoId, cantidad]) => {
          const producto = productosDisponibles.find((p) => p.id === Number(productoId));
          return { productoId: Number(productoId), cantidad, producto };
        }),
    [carrito, productosDisponibles]
  );

  const totalCarrito = useMemo(
    () => itemsCarrito.reduce((sum, item) => sum + (item.producto?.precio ?? 0) * item.cantidad, 0),
    [itemsCarrito]
  );

  const ajustarCantidad = (productoId: number, delta: number, stockMaximo: number) => {
    setCarrito((prev) => {
      const actual = prev[productoId] ?? 0;
      const siguiente = Math.max(0, Math.min(stockMaximo, actual + delta));
      return { ...prev, [productoId]: siguiente };
    });
  };

  const handleConfirmarPedido = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!cafeteria || !userId || itemsCarrito.length === 0 || !comprobante) return;

    setEnviandoPedido(true);
    setFeedback(null);
    try {
      await crearPedido(
        cafeteria.id,
        userId,
        itemsCarrito.map((item) => ({ productoId: item.productoId, cantidad: item.cantidad })),
        codigoOperacion.trim(),
        comprobante
      );
      setCarrito({});
      setCodigoOperacion("");
      setComprobante(null);
      setFeedback({ type: "success", message: "Pedido enviado. Espera la confirmacion del pago." });
      setTab("pedidos");
      await cargarTodo();
    } catch (createError) {
      setFeedback({
        type: "error",
        message: createError instanceof Error ? createError.message : "No se pudo enviar el pedido."
      });
    } finally {
      setEnviandoPedido(false);
    }
  };

  const handleCancelar = async (pedido: Pedido) => {
    if (!userId) return;
    try {
      await cancelarMiPedido(pedido.id, userId);
      await cargarTodo();
    } catch (cancelError) {
      setFeedback({
        type: "error",
        message: cancelError instanceof Error ? cancelError.message : "No se pudo cancelar el pedido."
      });
    }
  };

  return (
    <div className="cafeteria-screen-container">
      <Navbar
        displayName={displayName}
        userCode={user.user_metadata.codigo}
        currentPage="cafeteria"
        onNavigateToInicio={onNavigateToInicio}
        onNavigateToServicios={onNavigateToServicios}
        onNavigateToPerfil={onNavigateToPerfil}
        onLogout={onLogout}
        isLoggingOut={isLoggingOut}
      />

      <main className="cafeteria-screen-main">
        <section className="home-welcome-card cafeteria-welcome">
          <div>
            <p className="home-welcome-date">Cafeteria UPT</p>
            <h1 className="home-title">{cafeteria?.nombre ?? "Cafeteria de tu facultad"}</h1>
            <p className="home-subtitle">
              {displayName}, pide tu comida, paga por Yape y recogela cuando este lista.
            </p>
          </div>
          <div className="home-welcome-avatar" aria-hidden="true">
            <Coffee size={44} />
          </div>
        </section>

        {feedback && (
          <div className={`cafeteria-feedback cafeteria-feedback-${feedback.type}`} role="status">
            {feedback.type === "success" ? <CheckCircle2 size={18} /> : <AlertTriangle size={18} />}
            <p>{feedback.message}</p>
          </div>
        )}

        {error && (
          <div className="cafeteria-feedback cafeteria-feedback-error" role="status">
            <AlertTriangle size={18} />
            <p>{error}</p>
          </div>
        )}

        <div className="cafeteria-tabs">
          <button type="button" className={tab === "menu" ? "active" : ""} onClick={() => setTab("menu")}>
            Menu
          </button>
          <button type="button" className={tab === "pedidos" ? "active" : ""} onClick={() => setTab("pedidos")}>
            Mis pedidos {pedidos.length > 0 && `(${pedidos.length})`}
          </button>
        </div>

        {loading && <p className="cafeteria-loading">Cargando...</p>}

        {!loading && tab === "menu" && (
          <div className="cafeteria-menu-layout">
            <div className="cafeteria-menu-grid">
              {productosDisponibles.map((producto) => (
                <div key={producto.id} className="cafeteria-menu-card">
                  <div>
                    <p className="cafeteria-menu-nombre">{producto.nombre}</p>
                    {producto.descripcion && <p className="cafeteria-menu-desc">{producto.descripcion}</p>}
                    <p className="cafeteria-menu-precio">S/. {producto.precio.toFixed(2)}</p>
                    {producto.stock <= 5 && producto.stock > 0 && (
                      <span className="cafeteria-menu-stock-bajo">Quedan {producto.stock}</span>
                    )}
                    {producto.stock === 0 && <span className="cafeteria-menu-sin-stock">Agotado</span>}
                  </div>
                  <div className="cafeteria-menu-stepper">
                    <button
                      type="button"
                      onClick={() => ajustarCantidad(producto.id, -1, producto.stock)}
                      disabled={!carrito[producto.id]}
                    >
                      <Minus size={14} />
                    </button>
                    <span>{carrito[producto.id] ?? 0}</span>
                    <button
                      type="button"
                      onClick={() => ajustarCantidad(producto.id, 1, producto.stock)}
                      disabled={producto.stock === 0 || (carrito[producto.id] ?? 0) >= producto.stock}
                    >
                      <Plus size={14} />
                    </button>
                  </div>
                </div>
              ))}
              {productosDisponibles.length === 0 && (
                <p className="cafeteria-loading">No hay productos disponibles por el momento.</p>
              )}
            </div>

            <aside className="cafeteria-carrito">
              <h3>
                <ShoppingCart size={18} /> Tu pedido
              </h3>
              {itemsCarrito.length === 0 ? (
                <p className="cafeteria-carrito-vacio">Agrega productos del menu.</p>
              ) : (
                <>
                  <ul className="cafeteria-carrito-items">
                    {itemsCarrito.map((item) => (
                      <li key={item.productoId}>
                        {item.cantidad}x {item.producto?.nombre} — S/. {((item.producto?.precio ?? 0) * item.cantidad).toFixed(2)}
                      </li>
                    ))}
                  </ul>
                  <p className="cafeteria-carrito-total">Total: S/. {totalCarrito.toFixed(2)}</p>

                  <form onSubmit={handleConfirmarPedido} className="cafeteria-checkout-form">
                    <label className="cafeteria-checkout-label">
                      Codigo de operacion Yape (opcional)
                      <input
                        type="text"
                        value={codigoOperacion}
                        onChange={(event) => setCodigoOperacion(event.target.value)}
                        placeholder="Ej. 00123456"
                      />
                    </label>

                    <label className="cafeteria-checkout-upload">
                      <Upload size={16} />
                      {comprobante ? comprobante.name : "Subir captura del pago Yape"}
                      <input
                        type="file"
                        accept="image/*"
                        onChange={(event) => setComprobante(event.target.files?.[0] ?? null)}
                        required
                      />
                    </label>

                    <button type="submit" className="cafeteria-checkout-button" disabled={enviandoPedido || !comprobante}>
                      {enviandoPedido ? "Enviando..." : "Confirmar pedido"}
                    </button>
                  </form>
                </>
              )}
            </aside>
          </div>
        )}

        {!loading && tab === "pedidos" && (
          <div className="cafeteria-mis-pedidos">
            {pedidos.length === 0 && <p className="cafeteria-loading">Aun no tienes pedidos.</p>}
            {pedidos.map((pedido) => (
              <div key={pedido.id} className="cafeteria-pedido-mio">
                <div className="cafeteria-pedido-mio-header">
                  <span className={`cafeteria-status-badge ${pedido.estado}`}>
                    {ESTADO_LABEL[pedido.estado] ?? pedido.estado}
                  </span>
                  <strong>S/. {pedido.total.toFixed(2)}</strong>
                </div>
                <ul>
                  {pedido.items.map((item) => (
                    <li key={item.productoId}>
                      {item.cantidad}x {item.productoNombre}
                    </li>
                  ))}
                </ul>
                {pedido.estado === "listo" && pedido.codigoQr && (
                  <p className="cafeteria-pedido-qr">Muestra este codigo al recoger: <code>{pedido.codigoQr}</code></p>
                )}
                {pedido.estado === "rechazado" && pedido.motivoRechazo && (
                  <p className="cafeteria-pedido-motivo">Motivo: {pedido.motivoRechazo}</p>
                )}
                {pedido.estado === "pendiente_revision" && (
                  <button type="button" className="cafeteria-cancelar-button" onClick={() => handleCancelar(pedido)}>
                    Cancelar pedido
                  </button>
                )}
              </div>
            ))}
          </div>
        )}
      </main>
    </div>
  );
};

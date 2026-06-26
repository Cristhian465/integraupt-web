import React, { useCallback, useEffect, useMemo, useState } from "react";
import { AlertCircle, Plus, RefreshCw, Search, Store } from "lucide-react";
import "../../../styles/GestionCafeteria.css";
import { ProductoModal } from "./components/ProductoModal";
import { PedidoCard } from "./components/PedidoCard";
import type {
  AdministrativoCatalogo,
  Cafeteria,
  Facultad,
  Pedido,
  Producto,
  ProductoFormValues
} from "./types";
import { createEmptyProductoForm } from "./types";
import { validateProductoValues } from "./validators";
import {
  aprobarPedido,
  buscarAdministrativos,
  cambiarEstadoPedido,
  cambiarEstadoProducto,
  createCafeteria,
  createProducto,
  fetchCafeteriaDeEncargado,
  fetchCafeterias,
  fetchFacultades,
  fetchPedidosPorCafeteria,
  fetchProductos,
  rechazarPedido,
  updateCafeteria,
  updateProducto
} from "./cafeteriaService";

interface UserMetadata {
  role?: string;
}

interface GestionCafeteriaProps {
  onAuditLog?: (message: string, detail?: string) => void;
  currentUser?: { id?: string; user_metadata?: UserMetadata };
}

type StatusMessage = { type: "success" | "error"; text: string };

const ESTADOS_PEDIDO_FILTRO = [
  { value: "", label: "Todos" },
  { value: "pendiente_revision", label: "Pendientes de revision" },
  { value: "pagado", label: "Pagados" },
  { value: "preparando", label: "Preparando" },
  { value: "listo", label: "Listos para recoger" },
  { value: "entregado", label: "Entregados" },
  { value: "rechazado", label: "Rechazados" }
];

export const GestionCafeteria: React.FC<GestionCafeteriaProps> = ({ onAuditLog, currentUser }) => {
  const normalizedRole = currentUser?.user_metadata?.role?.trim().toLowerCase() ?? "";
  const isEncargado = normalizedRole === "encargado cafe";
  const usuarioId = currentUser?.id ? Number(currentUser.id) : null;

  const [cafeterias, setCafeterias] = useState<Cafeteria[]>([]);
  const [facultades, setFacultades] = useState<Facultad[]>([]);
  const [selectedCafeteriaId, setSelectedCafeteriaId] = useState<number | null>(null);
  const [cafeteria, setCafeteria] = useState<Cafeteria | null>(null);
  const [productos, setProductos] = useState<Producto[]>([]);
  const [pedidos, setPedidos] = useState<Pedido[]>([]);
  const [estadoFiltro, setEstadoFiltro] = useState("");
  const [loading, setLoading] = useState(false);
  const [statusMessage, setStatusMessage] = useState<StatusMessage | null>(null);
  const [sinAsignar, setSinAsignar] = useState(false);

  const [productoModalOpen, setProductoModalOpen] = useState(false);
  const [productoMode, setProductoMode] = useState<"create" | "edit">("create");
  const [productoEditId, setProductoEditId] = useState<number | null>(null);
  const [productoValues, setProductoValues] = useState<ProductoFormValues>(createEmptyProductoForm());
  const [productoErrors, setProductoErrors] = useState<string[]>([]);
  const [productoSubmitting, setProductoSubmitting] = useState(false);

  const [nuevaCafeteriaNombre, setNuevaCafeteriaNombre] = useState("");
  const [nuevaCafeteriaFacultad, setNuevaCafeteriaFacultad] = useState("");

  const [busquedaEncargado, setBusquedaEncargado] = useState("");
  const [resultadosEncargado, setResultadosEncargado] = useState<AdministrativoCatalogo[]>([]);

  const notify = (type: StatusMessage["type"], text: string) => setStatusMessage({ type, text });

  const cargarDetalleCafeteria = useCallback(async (idCafeteria: number) => {
    setLoading(true);
    try {
      const [productosData, pedidosData] = await Promise.all([
        fetchProductos(idCafeteria),
        fetchPedidosPorCafeteria(idCafeteria)
      ]);
      setProductos(productosData);
      setPedidos(pedidosData);
    } catch (loadError) {
      notify("error", loadError instanceof Error ? loadError.message : "No se pudo cargar la informacion.");
    } finally {
      setLoading(false);
    }
  }, []);

  const inicializar = useCallback(async () => {
    setLoading(true);
    setSinAsignar(false);
    try {
      if (isEncargado) {
        if (!usuarioId) return;
        const propia = await fetchCafeteriaDeEncargado(usuarioId);
        setCafeteria(propia);
        setSelectedCafeteriaId(propia.id);
        await cargarDetalleCafeteria(propia.id);
      } else {
        const [lista, facultadesData] = await Promise.all([fetchCafeterias(), fetchFacultades()]);
        setCafeterias(lista);
        setFacultades(facultadesData);
        if (lista.length > 0) {
          setSelectedCafeteriaId(lista[0].id);
          setCafeteria(lista[0]);
          await cargarDetalleCafeteria(lista[0].id);
        }
      }
    } catch (initError) {
      if (isEncargado) {
        setSinAsignar(true);
      } else {
        notify("error", initError instanceof Error ? initError.message : "No se pudo cargar la cafeteria.");
      }
    } finally {
      setLoading(false);
    }
  }, [isEncargado, usuarioId, cargarDetalleCafeteria]);

  useEffect(() => {
    void inicializar();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleSelectCafeteria = async (id: number) => {
    setSelectedCafeteriaId(id);
    const encontrada = cafeterias.find((c) => c.id === id) ?? null;
    setCafeteria(encontrada);
    await cargarDetalleCafeteria(id);
  };

  const pedidosFiltrados = useMemo(() => {
    if (!estadoFiltro) return pedidos;
    return pedidos.filter((p) => p.estado === estadoFiltro);
  }, [pedidos, estadoFiltro]);

  const pendientesCount = useMemo(
    () => pedidos.filter((p) => p.estado === "pendiente_revision").length,
    [pedidos]
  );
  const listosCount = useMemo(() => pedidos.filter((p) => p.estado === "listo").length, [pedidos]);

  const handleCrearCafeteria = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!nuevaCafeteriaNombre.trim() || !nuevaCafeteriaFacultad) return;

    try {
      const creada = await createCafeteria(nuevaCafeteriaNombre.trim(), Number(nuevaCafeteriaFacultad));
      setCafeterias((prev) => [...prev, creada]);
      setNuevaCafeteriaNombre("");
      setNuevaCafeteriaFacultad("");
      notify("success", "Cafeteria registrada correctamente.");
      onAuditLog?.("Registro de cafeteria", creada.nombre);
    } catch (createError) {
      notify("error", createError instanceof Error ? createError.message : "No se pudo registrar la cafeteria.");
    }
  };

  const handleBuscarEncargado = async (texto: string) => {
    setBusquedaEncargado(texto);
    if (texto.trim().length < 2) {
      setResultadosEncargado([]);
      return;
    }
    try {
      setResultadosEncargado(await buscarAdministrativos(texto.trim()));
    } catch {
      setResultadosEncargado([]);
    }
  };

  const handleAsignarEncargado = async (administrativo: AdministrativoCatalogo) => {
    if (!cafeteria) return;
    try {
      const actualizada = await updateCafeteria(cafeteria.id, {
        nombre: cafeteria.nombre,
        facultadId: cafeteria.facultadId,
        estado: cafeteria.estado,
        encargadoId: administrativo.usuarioId
      });
      setCafeteria(actualizada);
      setCafeterias((prev) => prev.map((c) => (c.id === actualizada.id ? actualizada : c)));
      setBusquedaEncargado("");
      setResultadosEncargado([]);
      notify("success", `${administrativo.nombreCompleto} ahora es el encargado de esta cafeteria.`);
    } catch (assignError) {
      notify("error", assignError instanceof Error ? assignError.message : "No se pudo asignar el encargado.");
    }
  };

  const openCreateProducto = () => {
    setProductoMode("create");
    setProductoEditId(null);
    setProductoValues(createEmptyProductoForm());
    setProductoErrors([]);
    setProductoModalOpen(true);
  };

  const openEditProducto = (producto: Producto) => {
    setProductoMode("edit");
    setProductoEditId(producto.id);
    setProductoValues({
      nombre: producto.nombre,
      descripcion: producto.descripcion ?? "",
      precio: `${producto.precio}`,
      stock: `${producto.stock}`,
      estado: producto.estado
    });
    setProductoErrors([]);
    setProductoModalOpen(true);
  };

  const handleProductoChange = (field: keyof ProductoFormValues, value: string | boolean) => {
    setProductoValues((prev) => ({ ...prev, [field]: value }));
  };

  const handleProductoSubmit = async () => {
    const validation = validateProductoValues(productoValues);
    if (validation.length > 0) {
      setProductoErrors(validation);
      return;
    }
    if (!selectedCafeteriaId) return;

    setProductoSubmitting(true);
    try {
      const result = productoEditId
        ? await updateProducto(selectedCafeteriaId, productoEditId, productoValues)
        : await createProducto(selectedCafeteriaId, productoValues);

      setProductos((prev) =>
        productoEditId ? prev.map((p) => (p.id === result.id ? result : p)) : [result, ...prev]
      );
      notify("success", productoEditId ? "Producto actualizado." : "Producto registrado.");
      onAuditLog?.(productoEditId ? "Actualizacion de producto" : "Registro de producto", result.nombre);
      setProductoModalOpen(false);
    } catch (saveError) {
      const message = saveError instanceof Error ? saveError.message : "No se pudo guardar el producto.";
      setProductoErrors([message]);
    } finally {
      setProductoSubmitting(false);
    }
  };

  const handleToggleProductoEstado = async (producto: Producto) => {
    if (!selectedCafeteriaId) return;
    try {
      const result = await cambiarEstadoProducto(selectedCafeteriaId, producto.id, !producto.estado);
      setProductos((prev) => prev.map((p) => (p.id === result.id ? result : p)));
    } catch (toggleError) {
      notify("error", toggleError instanceof Error ? toggleError.message : "No se pudo actualizar el producto.");
    }
  };

  const handleAprobar = async (pedido: Pedido) => {
    try {
      const result = await aprobarPedido(pedido.id);
      setPedidos((prev) => prev.map((p) => (p.id === result.id ? result : p)));
      onAuditLog?.("Pago de cafeteria aprobado", `Pedido #${pedido.id}`);
    } catch (err) {
      notify("error", err instanceof Error ? err.message : "No se pudo aprobar el pedido.");
    }
  };

  const handleRechazar = async (pedido: Pedido, motivo: string) => {
    try {
      const result = await rechazarPedido(pedido.id, motivo);
      setPedidos((prev) => prev.map((p) => (p.id === result.id ? result : p)));
      onAuditLog?.("Pago de cafeteria rechazado", `Pedido #${pedido.id}: ${motivo}`);
      if (selectedCafeteriaId) {
        const productosActualizados = await fetchProductos(selectedCafeteriaId);
        setProductos(productosActualizados);
      }
    } catch (err) {
      notify("error", err instanceof Error ? err.message : "No se pudo rechazar el pedido.");
    }
  };

  const handleCambiarEstado = async (pedido: Pedido, estado: "preparando" | "listo") => {
    try {
      const result = await cambiarEstadoPedido(pedido.id, estado);
      setPedidos((prev) => prev.map((p) => (p.id === result.id ? result : p)));
    } catch (err) {
      notify("error", err instanceof Error ? err.message : "No se pudo actualizar el pedido.");
    }
  };

  if (sinAsignar) {
    return (
      <div className="gestion-cafeteria">
        <div className="gestion-cafeteria-alert error">
          <AlertCircle size={16} />
          <span>Aun no tienes una cafeteria asignada. Pide a un administrador que te registre como encargado.</span>
        </div>
      </div>
    );
  }

  return (
    <div className="gestion-cafeteria">
      <div className="gestion-cafeteria-header">
        <div>
          <h2 className="gestion-cafeteria-title">Cafeteria UPT</h2>
          <p className="gestion-cafeteria-subtitle">
            {cafeteria ? `${cafeteria.nombre} — ${cafeteria.facultadNombre}` : "Administra el stock y los pedidos."}
          </p>
        </div>
        <button
          type="button"
          className="gestion-cafeteria-btn secondary"
          onClick={() => selectedCafeteriaId && cargarDetalleCafeteria(selectedCafeteriaId)}
          disabled={loading}
        >
          <RefreshCw size={16} />
          Actualizar
        </button>
      </div>

      {!isEncargado && (
        <div className="cafeteria-admin-toolbar">
          <div className="cafeteria-selector">
            <Store size={16} />
            <select
              value={selectedCafeteriaId ?? ""}
              onChange={(event) => handleSelectCafeteria(Number(event.target.value))}
            >
              <option value="" disabled>
                Selecciona una cafeteria
              </option>
              {cafeterias.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.nombre} ({c.facultadNombre})
                </option>
              ))}
            </select>
          </div>

          <form onSubmit={handleCrearCafeteria} className="cafeteria-crear-form">
            <input
              type="text"
              placeholder="Nombre de la nueva cafeteria"
              value={nuevaCafeteriaNombre}
              onChange={(event) => setNuevaCafeteriaNombre(event.target.value)}
            />
            <select value={nuevaCafeteriaFacultad} onChange={(event) => setNuevaCafeteriaFacultad(event.target.value)}>
              <option value="">Facultad</option>
              {facultades.map((f) => (
                <option key={f.id} value={f.id}>
                  {f.nombre}
                </option>
              ))}
            </select>
            <button type="submit" className="gestion-cafeteria-btn primary small">
              <Plus size={14} /> Crear
            </button>
          </form>
        </div>
      )}

      {!isEncargado && cafeteria && (
        <div className="cafeteria-encargado-box">
          <p>
            Encargado actual: <strong>{cafeteria.encargadoNombre ?? "Sin asignar"}</strong>
          </p>
          <div className="cafeteria-encargado-search">
            <Search size={14} />
            <input
              type="text"
              placeholder="Buscar administrativo por nombre..."
              value={busquedaEncargado}
              onChange={(event) => handleBuscarEncargado(event.target.value)}
            />
          </div>
          {resultadosEncargado.length > 0 && (
            <ul className="cafeteria-encargado-results">
              {resultadosEncargado.map((admin) => (
                <li key={admin.usuarioId}>
                  <span>{admin.nombreCompleto}</span>
                  <button type="button" onClick={() => handleAsignarEncargado(admin)}>
                    Asignar
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>
      )}

      {statusMessage && (
        <div className={`gestion-cafeteria-alert ${statusMessage.type}`}>
          {statusMessage.type === "error" && <AlertCircle size={16} />}
          <span>{statusMessage.text}</span>
        </div>
      )}

      {cafeteria && (
        <>
          <div className="gestion-cafeteria-stats">
            <div className="gestion-cafeteria-stat">
              <p>Productos activos</p>
              <strong>{productos.filter((p) => p.estado).length}</strong>
            </div>
            <div className="gestion-cafeteria-stat">
              <p>Pedidos por revisar</p>
              <strong>{pendientesCount}</strong>
            </div>
            <div className="gestion-cafeteria-stat">
              <p>Listos para recoger</p>
              <strong>{listosCount}</strong>
            </div>
          </div>

          <section className="cafeteria-section">
            <div className="cafeteria-section-header">
              <h3>Menu y stock</h3>
              <button type="button" className="gestion-cafeteria-btn primary small" onClick={openCreateProducto}>
                <Plus size={14} /> Nuevo producto
              </button>
            </div>

            <div className="cafeteria-productos-grid">
              {productos.map((producto) => (
                <div key={producto.id} className={`cafeteria-producto-card ${!producto.estado ? "inactivo" : ""}`}>
                  <div>
                    <p className="cafeteria-producto-nombre">{producto.nombre}</p>
                    {producto.descripcion && <p className="cafeteria-producto-desc">{producto.descripcion}</p>}
                  </div>
                  <div className="cafeteria-producto-meta">
                    <span>S/. {producto.precio.toFixed(2)}</span>
                    <span className={producto.stock === 0 ? "sin-stock" : ""}>Stock: {producto.stock}</span>
                  </div>
                  <div className="cafeteria-producto-actions">
                    <button type="button" onClick={() => openEditProducto(producto)}>
                      Editar
                    </button>
                    <button type="button" onClick={() => handleToggleProductoEstado(producto)}>
                      {producto.estado ? "Desactivar" : "Activar"}
                    </button>
                  </div>
                </div>
              ))}
              {productos.length === 0 && <p className="cafeteria-empty">Aun no hay productos registrados.</p>}
            </div>
          </section>

          <section className="cafeteria-section">
            <div className="cafeteria-section-header">
              <h3>Pedidos</h3>
              <select value={estadoFiltro} onChange={(event) => setEstadoFiltro(event.target.value)}>
                {ESTADOS_PEDIDO_FILTRO.map((opt) => (
                  <option key={opt.value} value={opt.value}>
                    {opt.label}
                  </option>
                ))}
              </select>
            </div>

            <div className="cafeteria-pedidos-grid">
              {pedidosFiltrados.map((pedido) => (
                <PedidoCard
                  key={pedido.id}
                  pedido={pedido}
                  onAprobar={handleAprobar}
                  onRechazar={handleRechazar}
                  onCambiarEstado={handleCambiarEstado}
                />
              ))}
              {pedidosFiltrados.length === 0 && <p className="cafeteria-empty">No hay pedidos en este filtro.</p>}
            </div>
          </section>
        </>
      )}

      <ProductoModal
        open={productoModalOpen}
        mode={productoMode}
        values={productoValues}
        errors={productoErrors}
        submitting={productoSubmitting}
        onClose={() => setProductoModalOpen(false)}
        onChange={handleProductoChange}
        onSubmit={handleProductoSubmit}
      />
    </div>
  );
};

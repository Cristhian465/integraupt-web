import React, { useMemo, useState, useEffect } from "react";
import { AlertCircle, CalendarPlus, Plus, RefreshCw, Sparkles } from "lucide-react";
import "../../../styles/GestionEventos.css";
import { EventoModal } from "./components/EventoModal";
import { EventoTable } from "./components/EventoTable";
import { EventoFilters } from "./components/EventoFilters";
import { InscripcionesModal } from "./components/InscripcionesModal";
import type { Evento, EventoFormMode, EventoFormValues, EstadoEvento } from "./types";
import { buildPayloadFromValues, createEmptyFormValues, mapEventoToFormValues } from "./mappers";
import { validateEventoValues } from "./validators";
import { useEventos } from "./hooks/useEventos";
import { useCatalogosEventos } from "./hooks/useCatalogosEventos";

interface GestionEventosProps {
  onAuditLog?: (message: string, detail?: string) => void;
}

type StatusMessage = {
  type: "success" | "error";
  text: string;
};

export const GestionEventos: React.FC<GestionEventosProps> = ({ onAuditLog }) => {
  const [searchTerm, setSearchTerm] = useState("");
  const [facultadFilter, setFacultadFilter] = useState("");
  const [escuelaFilter, setEscuelaFilter] = useState("");
  const [tipoFilter, setTipoFilter] = useState("");
  const [estadoFilter, setEstadoFilter] = useState("");

  const filtros = useMemo(
    () => ({
      facultadId: facultadFilter || undefined,
      escuelaId: escuelaFilter || undefined,
      tipoEvento: tipoFilter || undefined,
      estado: estadoFilter || undefined
    }),
    [facultadFilter, escuelaFilter, tipoFilter, estadoFilter]
  );

  const { eventos, loading, error, loadEventos, saveEvento, cambiarEstado } = useEventos(filtros);
  const { facultades, escuelas, espacios, loading: catalogosLoading, error: catalogosError } =
    useCatalogosEventos();

  const [modalOpen, setModalOpen] = useState(false);
  const [mode, setMode] = useState<EventoFormMode>("create");
  const [formValues, setFormValues] = useState<EventoFormValues>(createEmptyFormValues());
  const [formErrors, setFormErrors] = useState<string[]>([]);
  const [submitting, setSubmitting] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [statusMessage, setStatusMessage] = useState<StatusMessage | null>(null);
  const [inscripcionesEvento, setInscripcionesEvento] = useState<Evento | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const ITEMS_PER_PAGE = 5;

  const filteredEventos = useMemo(() => {
    const query = searchTerm.trim().toLowerCase();
    if (!query) return eventos;
    return eventos.filter((evento) => evento.titulo.toLowerCase().includes(query));
  }, [eventos, searchTerm]);

  useEffect(() => {
    setCurrentPage(1);
  }, [searchTerm, facultadFilter, escuelaFilter, tipoFilter, estadoFilter]);

  const totalPages = Math.ceil(filteredEventos.length / ITEMS_PER_PAGE) || 1;
  const paginatedEventos = useMemo(() => {
    const start = (currentPage - 1) * ITEMS_PER_PAGE;
    return filteredEventos.slice(start, start + ITEMS_PER_PAGE);
  }, [filteredEventos, currentPage]);

  const totalPublicados = useMemo(
    () => eventos.filter((evento) => evento.estado === "publicado").length,
    [eventos]
  );

  const totalProximos = useMemo(
    () =>
      eventos.filter(
        (evento) =>
          (evento.estado === "publicado" || evento.estado === "borrador") &&
          new Date(evento.fechaInicio).getTime() > Date.now()
      ).length,
    [eventos]
  );

  const openCreateModal = () => {
    setMode("create");
    setEditingId(null);
    setFormValues(createEmptyFormValues());
    setFormErrors([]);
    setModalOpen(true);
  };

  const openEditModal = (evento: Evento) => {
    setMode("edit");
    setEditingId(evento.id);
    setFormValues(mapEventoToFormValues(evento));
    setFormErrors([]);
    setModalOpen(true);
  };

  const closeModal = () => {
    setModalOpen(false);
    setSubmitting(false);
  };

  const handleValueChange = (field: keyof EventoFormValues, value: string | boolean) => {
    setFormValues((prev) => ({ ...prev, [field]: value }));
  };

  const notifyStatus = (type: StatusMessage["type"], text: string) => {
    setStatusMessage({ type, text });
  };

  const handleSubmit = async () => {
    const validation = validateEventoValues(formValues);
    if (validation.length > 0) {
      setFormErrors(validation);
      return;
    }

    setSubmitting(true);
    try {
      const payload = buildPayloadFromValues(formValues);
      const result = await saveEvento(payload, editingId ?? undefined);
      notifyStatus(
        "success",
        mode === "create" ? "Evento registrado correctamente." : "Evento actualizado correctamente."
      );
      onAuditLog?.(
        mode === "create" ? `Registro de evento ${result.titulo}` : `Actualizacion de evento ${result.titulo}`,
        result.tipoEvento
      );
      setModalOpen(false);
    } catch (saveError) {
      const message = saveError instanceof Error ? saveError.message : "No se pudo guardar el evento.";
      setFormErrors([message]);
      notifyStatus("error", message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleCambiarEstado = async (evento: Evento, estado: EstadoEvento) => {
    const confirmMessages: Partial<Record<EstadoEvento, string>> = {
      publicado: `¿Publicar el evento "${evento.titulo}"? Quedara visible para inscripciones.`,
      cancelado: `¿Cancelar el evento "${evento.titulo}"?`,
      finalizado: `¿Marcar como finalizado el evento "${evento.titulo}"?`
    };

    const confirmed = window.confirm(confirmMessages[estado] ?? "¿Confirmas el cambio de estado?");
    if (!confirmed) return;

    try {
      await cambiarEstado(evento.id, estado);
      notifyStatus("success", "Estado del evento actualizado.");
      onAuditLog?.(`Cambio de estado de evento ${evento.titulo}`, estado);
    } catch (estadoError) {
      const message = estadoError instanceof Error ? estadoError.message : "No se pudo cambiar el estado.";
      notifyStatus("error", message);
    }
  };

  const handleReload = async () => {
    try {
      await loadEventos();
    } catch (reloadError) {
      const message = reloadError instanceof Error ? reloadError.message : "No se pudo sincronizar la lista.";
      notifyStatus("error", message);
    }
  };

  return (
    <div className="gestion-eventos">
      <div className="gestion-eventos-header">
        <div>
          <h2 className="gestion-eventos-title">Gestion de Eventos</h2>
          <p className="gestion-eventos-subtitle">
            Organiza charlas, talleres y actividades por facultad o escuela, con inscripcion y check-in por QR.
          </p>
        </div>
        <div className="gestion-eventos-actions">
          <button type="button" className="gestion-eventos-btn secondary" onClick={handleReload} disabled={loading}>
            <RefreshCw size={16} />
            Actualizar
          </button>
          <button type="button" className="gestion-eventos-btn primary" onClick={openCreateModal}>
            <Plus size={16} />
            Nuevo evento
          </button>
        </div>
      </div>

      <div className="gestion-eventos-stats">
        <div className="gestion-eventos-stat">
          <CalendarPlus size={20} />
          <div>
            <p>Total registrados</p>
            <strong>{eventos.length}</strong>
          </div>
        </div>
        <div className="gestion-eventos-stat">
          <span className="gestion-eventos-dot publicado" />
          <div>
            <p>Publicados</p>
            <strong>{totalPublicados}</strong>
          </div>
        </div>
        <div className="gestion-eventos-stat">
          <Sparkles size={20} />
          <div>
            <p>Proximos</p>
            <strong>{totalProximos}</strong>
          </div>
        </div>
      </div>

      <EventoFilters
        search={searchTerm}
        facultadId={facultadFilter}
        escuelaId={escuelaFilter}
        tipoEvento={tipoFilter}
        estado={estadoFilter}
        facultades={facultades}
        escuelas={escuelas}
        onSearchChange={setSearchTerm}
        onFacultadChange={setFacultadFilter}
        onEscuelaChange={setEscuelaFilter}
        onTipoChange={setTipoFilter}
        onEstadoChange={setEstadoFilter}
        onReload={handleReload}
        total={eventos.length}
        filtered={filteredEventos.length}
        loading={loading}
      />

      {statusMessage && (
        <div className={`gestion-eventos-alert ${statusMessage.type === "success" ? "success" : "error"}`}>
          {statusMessage.type === "error" && <AlertCircle size={16} />}
          <span>{statusMessage.text}</span>
        </div>
      )}

      {error && (
        <div className="gestion-eventos-alert error">
          <AlertCircle size={16} />
          <span>{error}</span>
        </div>
      )}

      <EventoTable
        eventos={paginatedEventos}
        loading={loading}
        onEdit={openEditModal}
        onVerInscripciones={setInscripcionesEvento}
        onCambiarEstado={handleCambiarEstado}
      />

      {totalPages > 1 && (
        <div style={{ display: "flex", justifyContent: "center", alignItems: "center", gap: "1rem", marginTop: "1rem", padding: "1rem" }}>
          <button
            type="button"
            disabled={currentPage === 1}
            onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
            style={{ padding: "0.5rem 1rem", borderRadius: "4px", border: "1px solid #e2e8f0", backgroundColor: currentPage === 1 ? "#f8fafc" : "#fff", cursor: currentPage === 1 ? "not-allowed" : "pointer" }}
          >
            Anterior
          </button>
          <span style={{ fontSize: "0.875rem", color: "#64748b" }}>
            Página {currentPage} de {totalPages}
          </span>
          <button
            type="button"
            disabled={currentPage === totalPages}
            onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
            style={{ padding: "0.5rem 1rem", borderRadius: "4px", border: "1px solid #e2e8f0", backgroundColor: currentPage === totalPages ? "#f8fafc" : "#fff", cursor: currentPage === totalPages ? "not-allowed" : "pointer" }}
          >
            Siguiente
          </button>
        </div>
      )}

      <EventoModal
        open={modalOpen}
        mode={mode}
        values={formValues}
        errors={formErrors}
        submitting={submitting}
        facultades={facultades}
        escuelas={escuelas}
        espacios={espacios}
        catalogosLoading={catalogosLoading}
        catalogosError={catalogosError}
        onClose={closeModal}
        onChange={handleValueChange}
        onSubmit={handleSubmit}
      />

      <InscripcionesModal evento={inscripcionesEvento} onClose={() => setInscripcionesEvento(null)} />
    </div>
  );
};

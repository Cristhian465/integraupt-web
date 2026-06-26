import React, { useEffect, useMemo, useState } from "react";
import { AlertCircle, MessageSquarePlus, Plus, RefreshCw } from "lucide-react";
import "../../../styles/GestionCanales.css";
import { CanalModal, type CanalFormValues } from "./components/CanalModal";
import { CanalTable } from "./components/CanalTable";
import { MiembrosModal } from "./components/MiembrosModal";
import { ChatPanel } from "./components/ChatPanel";
import type { Canal, EstadoCanal } from "./types";
import { useCanales } from "./hooks/useCanales";

interface GestionCanalesProps {
  user: {
    id: string;
    user_metadata?: { role?: string };
  };
  onAuditLog?: (message: string, detail?: string) => void;
}

type StatusMessage = { type: "success" | "error"; text: string };

const emptyFormValues: CanalFormValues = { nombre: "", descripcion: "", color: "#4f46e5", fotoUrl: "" };

export const GestionCanales: React.FC<GestionCanalesProps> = ({ user, onAuditLog }) => {
  const usuarioId = useMemo(() => {
    const parsed = Number.parseInt(user.id, 10);
    return Number.isNaN(parsed) ? null : parsed;
  }, [user.id]);

  const { canales, loading, error, loadCanales, saveCanal, cambiarEstado, addMiembros, removeMiembro } =
    useCanales(usuarioId);

  const [searchTerm, setSearchTerm] = useState("");
  const [modalOpen, setModalOpen] = useState(false);
  const [mode, setMode] = useState<"create" | "edit">("create");
  const [formValues, setFormValues] = useState<CanalFormValues>(emptyFormValues);
  const [formErrors, setFormErrors] = useState<string[]>([]);
  const [submitting, setSubmitting] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [statusMessage, setStatusMessage] = useState<StatusMessage | null>(null);
  const [miembrosCanal, setMiembrosCanal] = useState<Canal | null>(null);
  const [chatCanal, setChatCanal] = useState<Canal | null>(null);

  const filteredCanales = useMemo(() => {
    const query = searchTerm.trim().toLowerCase();
    if (!query) return canales;
    return canales.filter((canal) => canal.nombre.toLowerCase().includes(query));
  }, [canales, searchTerm]);

  const totalActivos = useMemo(() => canales.filter((c) => c.estado === "activo").length, [canales]);

  useEffect(() => {
    setMiembrosCanal((prev) => (prev ? canales.find((c) => c.id === prev.id) ?? null : null));
  }, [canales]);

  const notifyStatus = (type: StatusMessage["type"], text: string) => setStatusMessage({ type, text });

  const openCreateModal = () => {
    setMode("create");
    setEditingId(null);
    setFormValues(emptyFormValues);
    setFormErrors([]);
    setModalOpen(true);
  };

  const openEditModal = (canal: Canal) => {
    setMode("edit");
    setEditingId(canal.id);
    setFormValues({ nombre: canal.nombre, descripcion: canal.descripcion ?? "", color: canal.color ?? "#4f46e5", fotoUrl: canal.fotoUrl ?? "" });
    setFormErrors([]);
    setModalOpen(true);
  };

  const closeModal = () => {
    setModalOpen(false);
    setSubmitting(false);
  };

  const handleValueChange = (field: keyof CanalFormValues, value: string) => {
    setFormValues((prev) => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (miembros: number[]) => {
    if (usuarioId == null) {
      setFormErrors(["No se pudo identificar tu usuario."]);
      return;
    }

    if (formValues.nombre.trim() === "") {
      setFormErrors(["El nombre del canal es obligatorio."]);
      return;
    }

    setSubmitting(true);
    try {
      const result = await saveCanal(
        {
          nombre: formValues.nombre.trim(),
          descripcion: formValues.descripcion.trim() || undefined,
          creadorId: usuarioId,
          miembros,
          color: formValues.color || undefined,
          fotoUrl: formValues.fotoUrl || undefined,
        },
        editingId ?? undefined
      );
      notifyStatus("success", mode === "create" ? "Canal creado correctamente." : "Canal actualizado correctamente.");
      onAuditLog?.(mode === "create" ? `Creación de canal ${result.nombre}` : `Actualización de canal ${result.nombre}`);
      setModalOpen(false);
    } catch (saveError) {
      const message = saveError instanceof Error ? saveError.message : "No se pudo guardar el canal.";
      setFormErrors([message]);
      notifyStatus("error", message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleCambiarEstado = async (canal: Canal) => {
    const nuevoEstado: EstadoCanal = canal.estado === "activo" ? "archivado" : "activo";
    const confirmed = window.confirm(
      nuevoEstado === "archivado"
        ? `¿Archivar el canal "${canal.nombre}"? Los miembros no podrán seguir enviando mensajes.`
        : `¿Reactivar el canal "${canal.nombre}"?`
    );
    if (!confirmed) return;

    try {
      await cambiarEstado(canal.id, nuevoEstado);
      notifyStatus("success", "Estado del canal actualizado.");
      onAuditLog?.(`Cambio de estado de canal ${canal.nombre}`, nuevoEstado);
    } catch (estadoError) {
      notifyStatus("error", estadoError instanceof Error ? estadoError.message : "No se pudo cambiar el estado.");
    }
  };

  const handleReload = async () => {
    try {
      await loadCanales();
    } catch (reloadError) {
      notifyStatus("error", reloadError instanceof Error ? reloadError.message : "No se pudo sincronizar la lista.");
    }
  };

  return (
    <div className="gestion-canales">
      <div className="gestion-canales-header">
        <div>
          <h2 className="gestion-canales-title">Gestión de Canales</h2>
          <p className="gestion-canales-subtitle">
            Crea espacios de comunicación para reemplazar los grupos de WhatsApp entre administración, docentes y estudiantes.
          </p>
        </div>
        <div className="gestion-canales-actions">
          <button type="button" className="gestion-canales-btn secondary" onClick={handleReload} disabled={loading}>
            <RefreshCw size={16} />
            Actualizar
          </button>
          <button type="button" className="gestion-canales-btn primary" onClick={openCreateModal}>
            <Plus size={16} />
            Nuevo canal
          </button>
        </div>
      </div>

      <div className="gestion-canales-stats">
        <div className="gestion-canales-stat">
          <MessageSquarePlus size={20} />
          <div>
            <p>Total de canales</p>
            <strong>{canales.length}</strong>
          </div>
        </div>
        <div className="gestion-canales-stat">
          <span className="gestion-canales-dot activo" />
          <div>
            <p>Activos</p>
            <strong>{totalActivos}</strong>
          </div>
        </div>
      </div>

      <div className="canal-search-box canal-search-box-wide">
        <input
          type="text"
          placeholder="Buscar canal por nombre..."
          value={searchTerm}
          onChange={(event) => setSearchTerm(event.target.value)}
        />
      </div>

      {statusMessage && (
        <div className={`gestion-canales-alert ${statusMessage.type === "success" ? "success" : "error"}`}>
          {statusMessage.type === "error" && <AlertCircle size={16} />}
          <span>{statusMessage.text}</span>
        </div>
      )}

      {error && (
        <div className="gestion-canales-alert error">
          <AlertCircle size={16} />
          <span>{error}</span>
        </div>
      )}

      <CanalTable
        canales={filteredCanales}
        loading={loading}
        onEdit={openEditModal}
        onMiembros={setMiembrosCanal}
        onChat={setChatCanal}
        onCambiarEstado={handleCambiarEstado}
      />

      <CanalModal
        open={modalOpen}
        mode={mode}
        values={formValues}
        errors={formErrors}
        submitting={submitting}
        onClose={closeModal}
        onChange={handleValueChange}
        onSubmit={handleSubmit}
      />

      <MiembrosModal
        canal={miembrosCanal}
        onClose={() => setMiembrosCanal(null)}
        onAddMiembros={async (id, miembros) => {
          await addMiembros(id, miembros);
        }}
        onRemoveMiembro={removeMiembro}
      />

      {usuarioId != null && (
        <ChatPanel canal={chatCanal} usuarioId={usuarioId} esAdmin onClose={() => setChatCanal(null)} />
      )}
    </div>
  );
};

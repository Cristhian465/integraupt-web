import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { AlertCircle, CheckCircle2, HeartPulse, Plus, RefreshCw } from 'lucide-react';
import '../../../styles/GestionPoliclinico.css';
import * as api from './policlinicoAdminService';
import type {
  CitaAdminResponse,
  MedicoAdminResponse,
  MedicoFormValues,
  TipoAtencionAdminResponse,
} from './types';

interface GestionPoliclinicoProps {
  onAuditLog?: (message: string, detail?: string) => void;
}

type StatusMessage = { type: 'success' | 'error'; text: string };
type Tab = 'citas' | 'medicos' | 'tipos';

const EMPTY_MEDICO_FORM: MedicoFormValues = {
  nombre: '',
  tiposAtencionIds: [],
};

const ESTADOS_CITA = ['Pendiente', 'Confirmada', 'Atendida', 'Cancelada'];

const formatFecha = (value?: string | null): string => {
  if (!value) return '—';
  const date = new Date(`${value}T00:00:00`);
  if (Number.isNaN(date.getTime())) return value;
  return new Intl.DateTimeFormat('es-PE', { dateStyle: 'medium' }).format(date);
};

const formatHora = (value?: string | null): string => {
  if (!value) return '—';
  const date = new Date(`1970-01-01T${value}`);
  if (Number.isNaN(date.getTime())) return value;
  return new Intl.DateTimeFormat('es-PE', { hour: '2-digit', minute: '2-digit' }).format(date);
};

const formatFechaHora = (value?: string | null): string => {
  if (!value) return '—';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return new Intl.DateTimeFormat('es-PE', { dateStyle: 'medium', timeStyle: 'short' }).format(date);
};

export const GestionPoliclinico: React.FC<GestionPoliclinicoProps> = ({ onAuditLog }) => {
  const [tab, setTab] = useState<Tab>('citas');
  const [status, setStatus] = useState<StatusMessage | null>(null);

  const notify = useCallback((value: StatusMessage) => {
    setStatus(value);
    window.setTimeout(() => setStatus((current) => (current === value ? null : current)), 4500);
  }, []);

  // ===== Citas =====
  const [citas, setCitas] = useState<CitaAdminResponse[]>([]);
  const [citasLoading, setCitasLoading] = useState(false);
  const [filtroEstado, setFiltroEstado] = useState('');
  const [filtroFecha, setFiltroFecha] = useState('');
  const [filtroMedicoId, setFiltroMedicoId] = useState('');
  const [filtroTipoAtencionId, setFiltroTipoAtencionId] = useState('');
  const [actualizandoId, setActualizandoId] = useState<number | null>(null);

  // ===== Médicos =====
  const [medicos, setMedicos] = useState<MedicoAdminResponse[]>([]);
  const [medicosLoading, setMedicosLoading] = useState(false);
  const [medicoForm, setMedicoForm] = useState<MedicoFormValues>(EMPTY_MEDICO_FORM);
  const [medicoFormOpen, setMedicoFormOpen] = useState(false);
  const [editingMedicoId, setEditingMedicoId] = useState<number | null>(null);
  const [submittingMedico, setSubmittingMedico] = useState(false);

  // ===== Tipos de atención =====
  const [tiposAtencion, setTiposAtencion] = useState<TipoAtencionAdminResponse[]>([]);
  const [tiposLoading, setTiposLoading] = useState(false);
  const [nuevoTipoNombre, setNuevoTipoNombre] = useState('');
  const [submittingTipo, setSubmittingTipo] = useState(false);

  const cargarCitas = useCallback(async () => {
    setCitasLoading(true);
    try {
      const data = await api.fetchCitasAdmin({
        estado: filtroEstado || undefined,
        fecha: filtroFecha || undefined,
        medicoId: filtroMedicoId ? Number(filtroMedicoId) : undefined,
        tipoAtencionId: filtroTipoAtencionId ? Number(filtroTipoAtencionId) : undefined,
      });
      setCitas(data);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudieron cargar las citas.';
      notify({ type: 'error', text: message });
    } finally {
      setCitasLoading(false);
    }
  }, [filtroEstado, filtroFecha, filtroMedicoId, filtroTipoAtencionId, notify]);

  const cargarMedicos = useCallback(async () => {
    setMedicosLoading(true);
    try {
      const data = await api.fetchMedicosAdmin();
      setMedicos(data);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo cargar la lista de médicos.';
      notify({ type: 'error', text: message });
    } finally {
      setMedicosLoading(false);
    }
  }, [notify]);

  const cargarTipos = useCallback(async () => {
    setTiposLoading(true);
    try {
      const data = await api.fetchTiposAtencionAdmin();
      setTiposAtencion(data);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo cargar los tipos de atención.';
      notify({ type: 'error', text: message });
    } finally {
      setTiposLoading(false);
    }
  }, [notify]);

  useEffect(() => {
    cargarTipos();
    cargarMedicos();
  }, [cargarTipos, cargarMedicos]);

  useEffect(() => {
    cargarCitas();
  }, [cargarCitas]);

  const handleCambiarEstado = async (cita: CitaAdminResponse, estado: string) => {
    setActualizandoId(cita.id);
    try {
      await api.cambiarEstadoCita(cita.id, estado);
      notify({ type: 'success', text: `Cita #${cita.id} actualizada a "${estado}".` });
      onAuditLog?.(`Cita de policlínico #${cita.id} cambió de estado`, estado);
      cargarCitas();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo actualizar el estado de la cita.';
      notify({ type: 'error', text: message });
    } finally {
      setActualizandoId(null);
    }
  };

  const handleEditarMedico = (medico: MedicoAdminResponse) => {
    setEditingMedicoId(medico.id);
    setMedicoForm({
      nombre: medico.nombre,
      tiposAtencionIds: medico.tiposAtencion.map((t) => t.id),
    });
    setMedicoFormOpen(true);
  };

  const handleToggleTipoEnForm = (tipoId: number) => {
    setMedicoForm((prev) => {
      const yaIncluido = prev.tiposAtencionIds.includes(tipoId);
      return {
        ...prev,
        tiposAtencionIds: yaIncluido
          ? prev.tiposAtencionIds.filter((id) => id !== tipoId)
          : [...prev.tiposAtencionIds, tipoId],
      };
    });
  };

  const handleGuardarMedico = async () => {
    if (!medicoForm.nombre.trim()) {
      notify({ type: 'error', text: 'El nombre del médico es obligatorio.' });
      return;
    }

    setSubmittingMedico(true);
    try {
      if (editingMedicoId != null) {
        await api.actualizarMedico(editingMedicoId, {
          nombre: medicoForm.nombre.trim(),
          tiposAtencionIds: medicoForm.tiposAtencionIds,
        });
        notify({ type: 'success', text: 'Médico actualizado.' });
      } else {
        await api.crearMedico({
          nombre: medicoForm.nombre.trim(),
          tiposAtencionIds: medicoForm.tiposAtencionIds,
        });
        notify({ type: 'success', text: 'Médico registrado.' });
      }
      onAuditLog?.('Médico de policlínico guardado', medicoForm.nombre);
      setMedicoForm(EMPTY_MEDICO_FORM);
      setEditingMedicoId(null);
      setMedicoFormOpen(false);
      cargarMedicos();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo guardar al médico.';
      notify({ type: 'error', text: message });
    } finally {
      setSubmittingMedico(false);
    }
  };

  const handleToggleEstadoMedico = async (medico: MedicoAdminResponse) => {
    try {
      await api.actualizarMedico(medico.id, { estado: !medico.estado });
      notify({
        type: 'success',
        text: `Médico "${medico.nombre}" ${medico.estado ? 'desactivado' : 'activado'}.`,
      });
      cargarMedicos();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo actualizar al médico.';
      notify({ type: 'error', text: message });
    }
  };

  const handleCrearTipo = async () => {
    if (!nuevoTipoNombre.trim()) {
      notify({ type: 'error', text: 'El nombre del tipo de atención es obligatorio.' });
      return;
    }

    setSubmittingTipo(true);
    try {
      await api.crearTipoAtencion(nuevoTipoNombre.trim());
      notify({ type: 'success', text: 'Tipo de atención creado.' });
      setNuevoTipoNombre('');
      cargarTipos();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo crear el tipo de atención.';
      notify({ type: 'error', text: message });
    } finally {
      setSubmittingTipo(false);
    }
  };

  const handleToggleEstadoTipo = async (tipo: TipoAtencionAdminResponse) => {
    try {
      await api.actualizarTipoAtencion(tipo.id, { estado: !tipo.estado });
      notify({
        type: 'success',
        text: `Tipo de atención "${tipo.nombre}" ${tipo.estado ? 'desactivado' : 'activado'}.`,
      });
      cargarTipos();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo actualizar el tipo de atención.';
      notify({ type: 'error', text: message });
    }
  };

  const medicosActivos = useMemo(() => medicos.filter((m) => m.estado), [medicos]);

  return (
    <section className="gestion-policlinico">
      <header className="gestion-policlinico-header">
        <div>
          <h2 className="gestion-policlinico-title">
            <HeartPulse size={22} /> Policlínico UPT
          </h2>
          <p className="gestion-policlinico-subtitle">
            Revisa, confirma y da seguimiento a las citas del policlínico, y administra médicos y tipos de atención.
          </p>
        </div>
        <button
          type="button"
          className="policlinico-admin-btn secondary"
          onClick={() => {
            if (tab === 'citas') cargarCitas();
            else if (tab === 'medicos') cargarMedicos();
            else cargarTipos();
          }}
          disabled={citasLoading || medicosLoading || tiposLoading}
        >
          <RefreshCw size={16} /> Actualizar
        </button>
      </header>

      {status && (
        <div className={`policlinico-admin-status ${status.type}`}>
          {status.type === 'error' ? <AlertCircle size={16} /> : <CheckCircle2 size={16} />}
          <span>{status.text}</span>
        </div>
      )}

      <div className="gestion-policlinico-tabs">
        <button type="button" className={tab === 'citas' ? 'active' : ''} onClick={() => setTab('citas')}>
          Citas
        </button>
        <button type="button" className={tab === 'medicos' ? 'active' : ''} onClick={() => setTab('medicos')}>
          Médicos
        </button>
        <button type="button" className={tab === 'tipos' ? 'active' : ''} onClick={() => setTab('tipos')}>
          Tipos de atención
        </button>
      </div>

      {tab === 'citas' && (
        <div className="gestion-policlinico-panel">
          <div className="policlinico-admin-row">
            <select
              className="policlinico-admin-input"
              value={filtroEstado}
              onChange={(e) => setFiltroEstado(e.target.value)}
            >
              <option value="">Todos los estados</option>
              {ESTADOS_CITA.map((estado) => (
                <option key={estado} value={estado}>{estado}</option>
              ))}
            </select>
            <select
              className="policlinico-admin-input"
              value={filtroTipoAtencionId}
              onChange={(e) => setFiltroTipoAtencionId(e.target.value)}
            >
              <option value="">Todos los tipos de atención</option>
              {tiposAtencion.map((tipo) => (
                <option key={tipo.id} value={tipo.id}>{tipo.nombre}</option>
              ))}
            </select>
            <select
              className="policlinico-admin-input"
              value={filtroMedicoId}
              onChange={(e) => setFiltroMedicoId(e.target.value)}
            >
              <option value="">Todos los médicos</option>
              {medicos.map((medico) => (
                <option key={medico.id} value={medico.id}>{medico.nombre}</option>
              ))}
            </select>
            <input
              className="policlinico-admin-input"
              type="date"
              value={filtroFecha}
              onChange={(e) => setFiltroFecha(e.target.value)}
            />
          </div>

          {citasLoading ? (
            <p className="policlinico-admin-empty">Cargando citas…</p>
          ) : citas.length === 0 ? (
            <p className="policlinico-admin-empty">No hay citas que coincidan con los filtros.</p>
          ) : (
            <table className="policlinico-admin-table">
              <thead>
                <tr>
                  <th>Estudiante</th>
                  <th>Médico</th>
                  <th>Tipo de atención</th>
                  <th>Fecha</th>
                  <th>Horario</th>
                  <th>Motivo</th>
                  <th>Solicitada</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                {citas.map((cita) => (
                  <tr key={cita.id}>
                    <td>{cita.estudianteNombre ?? `Usuario #${cita.usuarioId}`}</td>
                    <td>{cita.medicoNombre ?? `Médico #${cita.medicoId}`}</td>
                    <td>{cita.tipoAtencionNombre ?? '—'}</td>
                    <td>{formatFecha(cita.fecha)}</td>
                    <td>{formatHora(cita.horaInicio)} – {formatHora(cita.horaFin)}</td>
                    <td>{cita.motivo ?? '—'}</td>
                    <td>{formatFechaHora(cita.fechaSolicitud)}</td>
                    <td>
                      <select
                        className="policlinico-admin-input-sm"
                        value={cita.estado}
                        disabled={actualizandoId === cita.id}
                        onChange={(e) => handleCambiarEstado(cita, e.target.value)}
                      >
                        {ESTADOS_CITA.map((estado) => (
                          <option key={estado} value={estado}>{estado}</option>
                        ))}
                      </select>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      )}

      {tab === 'medicos' && (
        <div className="gestion-policlinico-panel">
          <div className="gestion-policlinico-panel-actions">
            <button
              type="button"
              className="policlinico-admin-btn primary"
              onClick={() => {
                setEditingMedicoId(null);
                setMedicoForm(EMPTY_MEDICO_FORM);
                setMedicoFormOpen((v) => !v);
              }}
            >
              <Plus size={16} /> {medicoFormOpen ? 'Cerrar formulario' : 'Nuevo médico'}
            </button>
          </div>

          {medicoFormOpen && (
            <div className="gestion-policlinico-form">
              <input
                className="policlinico-admin-input"
                placeholder="Nombre completo"
                value={medicoForm.nombre}
                onChange={(e) => setMedicoForm((p) => ({ ...p, nombre: e.target.value }))}
              />
              <div className="policlinico-admin-checkbox-group">
                {tiposAtencion.map((tipo) => (
                  <label key={tipo.id} className="policlinico-admin-checkbox">
                    <input
                      type="checkbox"
                      checked={medicoForm.tiposAtencionIds.includes(tipo.id)}
                      onChange={() => handleToggleTipoEnForm(tipo.id)}
                    />
                    {tipo.nombre}
                  </label>
                ))}
              </div>
              <button
                type="button"
                className="policlinico-admin-btn primary"
                onClick={handleGuardarMedico}
                disabled={submittingMedico}
              >
                {editingMedicoId != null ? 'Guardar cambios' : 'Guardar médico'}
              </button>
            </div>
          )}

          {medicosLoading ? (
            <p className="policlinico-admin-empty">Cargando médicos…</p>
          ) : (
            <table className="policlinico-admin-table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Tipos de atención</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {medicos.map((medico) => (
                  <tr key={medico.id}>
                    <td>{medico.nombre}</td>
                    <td>{medico.tiposAtencion.map((t) => t.nombre).join(', ') || '—'}</td>
                    <td>{medico.estado ? 'Activo' : 'Inactivo'}</td>
                    <td className="policlinico-admin-actions">
                      <button type="button" className="policlinico-icon-btn" onClick={() => handleEditarMedico(medico)}>
                        Editar
                      </button>
                      <button
                        type="button"
                        className="policlinico-icon-btn"
                        onClick={() => handleToggleEstadoMedico(medico)}
                      >
                        {medico.estado ? 'Desactivar' : 'Activar'}
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}

          <p className="policlinico-admin-hint">
            {medicosActivos.length} médico(s) activo(s) visible(s) para los estudiantes.
          </p>
        </div>
      )}

      {tab === 'tipos' && (
        <div className="gestion-policlinico-panel">
          <div className="policlinico-admin-row">
            <input
              className="policlinico-admin-input"
              placeholder="Nuevo tipo de atención (ej. Odontología)"
              value={nuevoTipoNombre}
              onChange={(e) => setNuevoTipoNombre(e.target.value)}
            />
            <button
              type="button"
              className="policlinico-admin-btn primary"
              onClick={handleCrearTipo}
              disabled={submittingTipo}
            >
              <Plus size={16} /> Agregar
            </button>
          </div>

          {tiposLoading ? (
            <p className="policlinico-admin-empty">Cargando tipos de atención…</p>
          ) : (
            <table className="policlinico-admin-table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {tiposAtencion.map((tipo) => (
                  <tr key={tipo.id}>
                    <td>{tipo.nombre}</td>
                    <td>{tipo.estado ? 'Activo' : 'Inactivo'}</td>
                    <td className="policlinico-admin-actions">
                      <button
                        type="button"
                        className="policlinico-icon-btn"
                        onClick={() => handleToggleEstadoTipo(tipo)}
                      >
                        {tipo.estado ? 'Desactivar' : 'Activar'}
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      )}
    </section>
  );
};

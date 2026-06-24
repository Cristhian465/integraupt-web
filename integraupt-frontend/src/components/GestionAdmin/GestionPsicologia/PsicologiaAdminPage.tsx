import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { AlertCircle, Brain, CheckCircle2, Plus, RefreshCw } from 'lucide-react';
import '../../../styles/GestionPsicologia.css';
import * as api from './psicologiaAdminService';
import type { CitaAdminResponse, PsicologoAdminResponse, PsicologoFormValues } from './types';

interface GestionPsicologiaProps {
  onAuditLog?: (message: string, detail?: string) => void;
}

type StatusMessage = { type: 'success' | 'error'; text: string };
type Tab = 'citas' | 'psicologos';

const EMPTY_PSICOLOGO_FORM: PsicologoFormValues = {
  nombre: '',
  especialidad: '',
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

export const GestionPsicologia: React.FC<GestionPsicologiaProps> = ({ onAuditLog }) => {
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
  const [filtroPsicologoId, setFiltroPsicologoId] = useState('');
  const [actualizandoId, setActualizandoId] = useState<number | null>(null);

  // ===== Psicólogos =====
  const [psicologos, setPsicologos] = useState<PsicologoAdminResponse[]>([]);
  const [psicologosLoading, setPsicologosLoading] = useState(false);
  const [psicologoForm, setPsicologoForm] = useState<PsicologoFormValues>(EMPTY_PSICOLOGO_FORM);
  const [psicologoFormOpen, setPsicologoFormOpen] = useState(false);
  const [submittingPsicologo, setSubmittingPsicologo] = useState(false);

  const cargarCitas = useCallback(async () => {
    setCitasLoading(true);
    try {
      const data = await api.fetchCitasAdmin({
        estado: filtroEstado || undefined,
        fecha: filtroFecha || undefined,
        psicologoId: filtroPsicologoId ? Number(filtroPsicologoId) : undefined,
      });
      setCitas(data);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudieron cargar las citas.';
      notify({ type: 'error', text: message });
    } finally {
      setCitasLoading(false);
    }
  }, [filtroEstado, filtroFecha, filtroPsicologoId, notify]);

  const cargarPsicologos = useCallback(async () => {
    setPsicologosLoading(true);
    try {
      const data = await api.fetchPsicologosAdmin();
      setPsicologos(data);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo cargar la lista de psicólogos.';
      notify({ type: 'error', text: message });
    } finally {
      setPsicologosLoading(false);
    }
  }, [notify]);

  useEffect(() => {
    cargarPsicologos();
  }, [cargarPsicologos]);

  useEffect(() => {
    cargarCitas();
  }, [cargarCitas]);

  const handleCambiarEstado = async (cita: CitaAdminResponse, estado: string) => {
    setActualizandoId(cita.id);
    try {
      await api.cambiarEstadoCita(cita.id, estado);
      notify({ type: 'success', text: `Cita #${cita.id} actualizada a "${estado}".` });
      onAuditLog?.(`Cita de psicología #${cita.id} cambió de estado`, estado);
      cargarCitas();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo actualizar el estado de la cita.';
      notify({ type: 'error', text: message });
    } finally {
      setActualizandoId(null);
    }
  };

  const handleGuardarPsicologo = async () => {
    if (!psicologoForm.nombre.trim()) {
      notify({ type: 'error', text: 'El nombre del psicólogo es obligatorio.' });
      return;
    }

    setSubmittingPsicologo(true);
    try {
      const creado = await api.crearPsicologo({
        nombre: psicologoForm.nombre.trim(),
        especialidad: psicologoForm.especialidad.trim() || undefined,
      });
      notify({ type: 'success', text: `Psicólogo "${creado.nombre}" registrado.` });
      onAuditLog?.('Psicólogo registrado', creado.nombre);
      setPsicologoForm(EMPTY_PSICOLOGO_FORM);
      setPsicologoFormOpen(false);
      cargarPsicologos();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo registrar al psicólogo.';
      notify({ type: 'error', text: message });
    } finally {
      setSubmittingPsicologo(false);
    }
  };

  const handleToggleEstadoPsicologo = async (psicologo: PsicologoAdminResponse) => {
    try {
      await api.actualizarPsicologo(psicologo.id, { estado: !psicologo.estado });
      notify({
        type: 'success',
        text: `Psicólogo "${psicologo.nombre}" ${psicologo.estado ? 'desactivado' : 'activado'}.`,
      });
      cargarPsicologos();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo actualizar el psicólogo.';
      notify({ type: 'error', text: message });
    }
  };

  const psicologosActivos = useMemo(() => psicologos.filter((p) => p.estado), [psicologos]);

  return (
    <section className="gestion-psicologia">
      <header className="gestion-psicologia-header">
        <div>
          <h2 className="gestion-psicologia-title">
            <Brain size={22} /> Citas de Psicología
          </h2>
          <p className="gestion-psicologia-subtitle">
            Revisa, confirma y da seguimiento a las citas solicitadas por los estudiantes.
          </p>
        </div>
        <button
          type="button"
          className="psicologia-admin-btn secondary"
          onClick={() => (tab === 'citas' ? cargarCitas() : cargarPsicologos())}
          disabled={citasLoading || psicologosLoading}
        >
          <RefreshCw size={16} /> Actualizar
        </button>
      </header>

      {status && (
        <div className={`psicologia-admin-status ${status.type}`}>
          {status.type === 'error' ? <AlertCircle size={16} /> : <CheckCircle2 size={16} />}
          <span>{status.text}</span>
        </div>
      )}

      <div className="gestion-psicologia-tabs">
        <button type="button" className={tab === 'citas' ? 'active' : ''} onClick={() => setTab('citas')}>
          Citas
        </button>
        <button type="button" className={tab === 'psicologos' ? 'active' : ''} onClick={() => setTab('psicologos')}>
          Psicólogos
        </button>
      </div>

      {tab === 'citas' && (
        <div className="gestion-psicologia-panel">
          <div className="psicologia-admin-row">
            <select
              className="psicologia-admin-input"
              value={filtroEstado}
              onChange={(e) => setFiltroEstado(e.target.value)}
            >
              <option value="">Todos los estados</option>
              {ESTADOS_CITA.map((estado) => (
                <option key={estado} value={estado}>{estado}</option>
              ))}
            </select>
            <select
              className="psicologia-admin-input"
              value={filtroPsicologoId}
              onChange={(e) => setFiltroPsicologoId(e.target.value)}
            >
              <option value="">Todos los psicólogos</option>
              {psicologos.map((psicologo) => (
                <option key={psicologo.id} value={psicologo.id}>{psicologo.nombre}</option>
              ))}
            </select>
            <input
              className="psicologia-admin-input"
              type="date"
              value={filtroFecha}
              onChange={(e) => setFiltroFecha(e.target.value)}
            />
          </div>

          {citasLoading ? (
            <p className="psicologia-admin-empty">Cargando citas…</p>
          ) : citas.length === 0 ? (
            <p className="psicologia-admin-empty">No hay citas que coincidan con los filtros.</p>
          ) : (
            <table className="psicologia-admin-table">
              <thead>
                <tr>
                  <th>Estudiante</th>
                  <th>Psicólogo</th>
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
                    <td>{cita.psicologoNombre ?? `Psicólogo #${cita.psicologoId}`}</td>
                    <td>{formatFecha(cita.fecha)}</td>
                    <td>{formatHora(cita.horaInicio)} – {formatHora(cita.horaFin)}</td>
                    <td>{cita.motivo ?? '—'}</td>
                    <td>{formatFechaHora(cita.fechaSolicitud)}</td>
                    <td>
                      <select
                        className="psicologia-admin-input-sm"
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

      {tab === 'psicologos' && (
        <div className="gestion-psicologia-panel">
          <div className="gestion-psicologia-panel-actions">
            <button
              type="button"
              className="psicologia-admin-btn primary"
              onClick={() => setPsicologoFormOpen((v) => !v)}
            >
              <Plus size={16} /> {psicologoFormOpen ? 'Cerrar formulario' : 'Nuevo psicólogo'}
            </button>
          </div>

          {psicologoFormOpen && (
            <div className="gestion-psicologia-form">
              <input
                className="psicologia-admin-input"
                placeholder="Nombre completo"
                value={psicologoForm.nombre}
                onChange={(e) => setPsicologoForm((p) => ({ ...p, nombre: e.target.value }))}
              />
              <input
                className="psicologia-admin-input"
                placeholder="Especialidad (opcional)"
                value={psicologoForm.especialidad}
                onChange={(e) => setPsicologoForm((p) => ({ ...p, especialidad: e.target.value }))}
              />
              <button
                type="button"
                className="psicologia-admin-btn primary"
                onClick={handleGuardarPsicologo}
                disabled={submittingPsicologo}
              >
                Guardar psicólogo
              </button>
            </div>
          )}

          {psicologosLoading ? (
            <p className="psicologia-admin-empty">Cargando psicólogos…</p>
          ) : (
            <table className="psicologia-admin-table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Especialidad</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {psicologos.map((psicologo) => (
                  <tr key={psicologo.id}>
                    <td>{psicologo.nombre}</td>
                    <td>{psicologo.especialidad ?? '—'}</td>
                    <td>{psicologo.estado ? 'Activo' : 'Inactivo'}</td>
                    <td className="psicologia-admin-actions">
                      <button
                        type="button"
                        className="psicologia-icon-btn"
                        onClick={() => handleToggleEstadoPsicologo(psicologo)}
                      >
                        {psicologo.estado ? 'Desactivar' : 'Activar'}
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}

          <p className="psicologia-admin-hint">
            {psicologosActivos.length} psicólogo(s) activo(s) visible(s) para los estudiantes.
          </p>
        </div>
      )}
    </section>
  );
};

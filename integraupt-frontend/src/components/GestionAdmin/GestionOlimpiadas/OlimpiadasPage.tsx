import React, { useCallback, useEffect, useMemo, useState } from 'react';
import {
  AlertCircle,
  CheckCircle2,
  ChevronDown,
  ChevronUp,
  Lock,
  Plus,
  RefreshCw,
  Trophy,
  Unlock,
} from 'lucide-react';
import '../../../styles/GestionOlimpiadas.css';
import * as api from './olimpiadasAdminService';
import type {
  DisciplinaCatalogo,
  DisciplinaFormValues,
  Edicion,
  EdicionDisciplina,
  EdicionFormValues,
  FacultadOption,
  InscripcionAdmin,
  Resultado,
  ResultadoFormValues,
  TablaPosicion,
} from './types';

interface GestionOlimpiadasProps {
  onAuditLog?: (message: string, detail?: string) => void;
}

type StatusMessage = { type: 'success' | 'error'; text: string };
type Tab = 'ediciones' | 'disciplinas' | 'resultados';

const EMPTY_EDICION_FORM: EdicionFormValues = {
  nombre: '',
  anioInicio: '',
  semestreInicio: '1',
  fechaInicioJuegos: '',
  fechaFinJuegos: '',
  observaciones: '',
};

const EMPTY_DISCIPLINA_FORM: DisciplinaFormValues = {
  nombre: '',
  descripcion: '',
  tipoParticipacion: 'equipo',
  reglas: '',
  cupoMaximoDefault: '',
};

const EMPTY_RESULTADO_FORM: ResultadoFormValues = {
  facultadLocalId: '',
  facultadVisitanteId: '',
  fase: 'grupos',
  grupo: '',
  fechaPartido: '',
  puntajeLocal: '',
  puntajeVisitante: '',
  estado: 'programado',
  observaciones: '',
};

const ESTADOS_EDICION = [
  'planificada',
  'inscripcion_abierta',
  'inscripcion_cerrada',
  'en_curso',
  'finalizada',
  'cancelada',
];

const ESTADOS_RESULTADO = ['programado', 'en_curso', 'finalizado', 'cancelado', 'suspendido'];

export const GestionOlimpiadas: React.FC<GestionOlimpiadasProps> = ({ onAuditLog }) => {
  const [tab, setTab] = useState<Tab>('ediciones');
  const [status, setStatus] = useState<StatusMessage | null>(null);

  const notify = useCallback((value: StatusMessage) => {
    setStatus(value);
    window.setTimeout(() => setStatus((current) => (current === value ? null : current)), 4500);
  }, []);

  const [facultades, setFacultades] = useState<FacultadOption[]>([]);
  const [ediciones, setEdiciones] = useState<Edicion[]>([]);
  const [disciplinas, setDisciplinas] = useState<DisciplinaCatalogo[]>([]);
  const [loading, setLoading] = useState(false);

  const cargarTodo = useCallback(async () => {
    setLoading(true);
    try {
      const [facultadesData, edicionesData, disciplinasData] = await Promise.all([
        api.fetchFacultades(),
        api.fetchEdiciones(),
        api.fetchDisciplinas(),
      ]);
      setFacultades(facultadesData);
      setEdiciones(edicionesData);
      setDisciplinas(disciplinasData);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo cargar la información de Olimpiadas.';
      notify({ type: 'error', text: message });
    } finally {
      setLoading(false);
    }
  }, [notify]);

  useEffect(() => {
    cargarTodo();
  }, [cargarTodo]);

  // ===== Ediciones =====
  const [edicionForm, setEdicionForm] = useState<EdicionFormValues>(EMPTY_EDICION_FORM);
  const [edicionFormOpen, setEdicionFormOpen] = useState(false);
  const [submittingEdicion, setSubmittingEdicion] = useState(false);

  const handleCrearEdicion = async () => {
    if (!edicionForm.nombre.trim() || !edicionForm.anioInicio.trim()) {
      notify({ type: 'error', text: 'Nombre y año de inicio son obligatorios.' });
      return;
    }

    setSubmittingEdicion(true);
    try {
      const creada = await api.crearEdicion({
        nombre: edicionForm.nombre.trim(),
        anioInicio: Number(edicionForm.anioInicio),
        semestreInicio: Number(edicionForm.semestreInicio),
        fechaInicioJuegos: edicionForm.fechaInicioJuegos || undefined,
        fechaFinJuegos: edicionForm.fechaFinJuegos || undefined,
        observaciones: edicionForm.observaciones || undefined,
      });
      notify({ type: 'success', text: `Edición "${creada.nombre}" creada.` });
      onAuditLog?.(`Edición de Olimpiadas creada #${creada.id}`, creada.nombre);
      setEdicionForm(EMPTY_EDICION_FORM);
      setEdicionFormOpen(false);
      cargarTodo();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo crear la edición.';
      notify({ type: 'error', text: message });
    } finally {
      setSubmittingEdicion(false);
    }
  };

  const handleCambiarEstadoEdicion = async (edicion: Edicion, estado: string) => {
    try {
      await api.cambiarEstadoEdicion(edicion.id, estado);
      notify({ type: 'success', text: `Estado de "${edicion.nombre}" actualizado.` });
      onAuditLog?.(`Edición #${edicion.id} cambió de estado`, estado);
      cargarTodo();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo actualizar el estado.';
      notify({ type: 'error', text: message });
    }
  };

  const handleAbrirInscripcion = async (edicion: Edicion) => {
    const fechaCierre = window.prompt(
      'Fecha y hora de cierre de inscripción (opcional, formato AAAA-MM-DD HH:mm):',
      '',
    );
    try {
      await api.abrirInscripcionEdicion(edicion.id, fechaCierre || undefined);
      notify({ type: 'success', text: `Inscripción abierta para "${edicion.nombre}".` });
      onAuditLog?.(`Inscripción abierta`, edicion.nombre);
      cargarTodo();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo abrir la inscripción.';
      notify({ type: 'error', text: message });
    }
  };

  const handleCerrarInscripcion = async (edicion: Edicion) => {
    try {
      await api.cerrarInscripcionEdicion(edicion.id);
      notify({ type: 'success', text: `Inscripción cerrada para "${edicion.nombre}".` });
      onAuditLog?.(`Inscripción cerrada`, edicion.nombre);
      cargarTodo();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo cerrar la inscripción.';
      notify({ type: 'error', text: message });
    }
  };

  // ===== Disciplinas (catálogo) =====
  const [disciplinaForm, setDisciplinaForm] = useState<DisciplinaFormValues>(EMPTY_DISCIPLINA_FORM);
  const [disciplinaFormOpen, setDisciplinaFormOpen] = useState(false);
  const [editingDisciplinaId, setEditingDisciplinaId] = useState<number | null>(null);
  const [submittingDisciplina, setSubmittingDisciplina] = useState(false);

  const handleEditarDisciplina = (disciplina: DisciplinaCatalogo) => {
    setEditingDisciplinaId(disciplina.id);
    setDisciplinaForm({
      nombre: disciplina.nombre,
      descripcion: disciplina.descripcion ?? '',
      tipoParticipacion: disciplina.tipoParticipacion,
      reglas: disciplina.reglas ?? '',
      cupoMaximoDefault: disciplina.cupoMaximoDefault?.toString() ?? '',
    });
    setDisciplinaFormOpen(true);
  };

  const handleGuardarDisciplina = async () => {
    if (!disciplinaForm.nombre.trim()) {
      notify({ type: 'error', text: 'El nombre de la disciplina es obligatorio.' });
      return;
    }

    setSubmittingDisciplina(true);
    try {
      const payload = {
        nombre: disciplinaForm.nombre.trim(),
        descripcion: disciplinaForm.descripcion || undefined,
        tipoParticipacion: disciplinaForm.tipoParticipacion,
        reglas: disciplinaForm.reglas || undefined,
        cupoMaximoDefault: disciplinaForm.cupoMaximoDefault ? Number(disciplinaForm.cupoMaximoDefault) : undefined,
      };

      if (editingDisciplinaId != null) {
        await api.actualizarDisciplina(editingDisciplinaId, payload);
        notify({ type: 'success', text: 'Disciplina actualizada.' });
      } else {
        await api.crearDisciplina(payload);
        notify({ type: 'success', text: 'Disciplina creada.' });
      }

      onAuditLog?.('Disciplina de Olimpiadas guardada', disciplinaForm.nombre);
      setDisciplinaForm(EMPTY_DISCIPLINA_FORM);
      setEditingDisciplinaId(null);
      setDisciplinaFormOpen(false);
      cargarTodo();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo guardar la disciplina.';
      notify({ type: 'error', text: message });
    } finally {
      setSubmittingDisciplina(false);
    }
  };

  const handleToggleEstadoDisciplina = async (disciplina: DisciplinaCatalogo) => {
    const nuevoEstado = disciplina.estado === 'activa' ? 'inactiva' : 'activa';
    try {
      await api.cambiarEstadoDisciplina(disciplina.id, nuevoEstado);
      notify({ type: 'success', text: `Disciplina "${disciplina.nombre}" ${nuevoEstado}.` });
      cargarTodo();
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo cambiar el estado.';
      notify({ type: 'error', text: message });
    }
  };

  // ===== Resultados / disciplinas por edición =====
  const [selectedEdicionId, setSelectedEdicionId] = useState<number | null>(null);
  const [edicionDisciplinas, setEdicionDisciplinas] = useState<EdicionDisciplina[]>([]);
  const [expandedVinculoId, setExpandedVinculoId] = useState<number | null>(null);
  const [participantes, setParticipantes] = useState<InscripcionAdmin[]>([]);
  const [fixture, setFixture] = useState<Resultado[]>([]);
  const [tabla, setTabla] = useState<TablaPosicion[]>([]);
  const [resultadoForm, setResultadoForm] = useState<ResultadoFormValues>(EMPTY_RESULTADO_FORM);
  const [editingResultadoId, setEditingResultadoId] = useState<number | null>(null);
  const [vincularDisciplinaId, setVincularDisciplinaId] = useState('');

  useEffect(() => {
    if (ediciones.length > 0 && selectedEdicionId == null) {
      setSelectedEdicionId(ediciones[0].id);
    }
  }, [ediciones, selectedEdicionId]);

  const cargarDisciplinasEdicion = useCallback(async (edicionId: number) => {
    try {
      const data = await api.fetchDisciplinasDeEdicion(edicionId);
      setEdicionDisciplinas(data);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo cargar las disciplinas de la edición.';
      notify({ type: 'error', text: message });
    }
  }, [notify]);

  useEffect(() => {
    if (selectedEdicionId != null) {
      cargarDisciplinasEdicion(selectedEdicionId);
    } else {
      setEdicionDisciplinas([]);
    }
  }, [selectedEdicionId, cargarDisciplinasEdicion]);

  const handleVincularDisciplina = async () => {
    if (selectedEdicionId == null || !vincularDisciplinaId) {
      notify({ type: 'error', text: 'Selecciona una disciplina del catálogo.' });
      return;
    }

    try {
      await api.vincularDisciplinaAEdicion(selectedEdicionId, { disciplinaId: Number(vincularDisciplinaId) });
      notify({ type: 'success', text: 'Disciplina vinculada a la edición.' });
      setVincularDisciplinaId('');
      cargarDisciplinasEdicion(selectedEdicionId);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo vincular la disciplina.';
      notify({ type: 'error', text: message });
    }
  };

  const handleToggleEstadoVinculo = async (vinculo: EdicionDisciplina) => {
    const nuevoEstado = vinculo.estado === 'activa' ? 'inactiva' : 'activa';
    try {
      await api.cambiarEstadoVinculo(vinculo.id, nuevoEstado);
      notify({ type: 'success', text: `Disciplina "${vinculo.disciplinaNombre}" ${nuevoEstado} en la edición.` });
      if (selectedEdicionId != null) cargarDisciplinasEdicion(selectedEdicionId);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo cambiar el estado.';
      notify({ type: 'error', text: message });
    }
  };

  const cargarResultadosVinculo = useCallback(async (vinculoId: number) => {
    try {
      const [participantesData, fixtureData, tablaData] = await Promise.all([
        api.fetchParticipantes(vinculoId),
        api.fetchFixture(vinculoId),
        api.fetchTabla(vinculoId),
      ]);
      setParticipantes(participantesData);
      setFixture(fixtureData);
      setTabla(tablaData);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo cargar la información de la disciplina.';
      notify({ type: 'error', text: message });
    }
  }, [notify]);

  const handleExpandVinculo = (vinculo: EdicionDisciplina) => {
    if (expandedVinculoId === vinculo.id) {
      setExpandedVinculoId(null);
      return;
    }
    setExpandedVinculoId(vinculo.id);
    setEditingResultadoId(null);
    setResultadoForm(EMPTY_RESULTADO_FORM);
    cargarResultadosVinculo(vinculo.id);
  };

  const handleEditarResultado = (resultado: Resultado) => {
    setEditingResultadoId(resultado.id);
    setResultadoForm({
      facultadLocalId: resultado.facultadLocalId.toString(),
      facultadVisitanteId: resultado.facultadVisitanteId?.toString() ?? '',
      fase: resultado.fase,
      grupo: resultado.grupo ?? '',
      fechaPartido: resultado.fechaPartido ?? '',
      puntajeLocal: resultado.puntajeLocal?.toString() ?? '',
      puntajeVisitante: resultado.puntajeVisitante?.toString() ?? '',
      estado: resultado.estado as ResultadoFormValues['estado'],
      observaciones: resultado.observaciones ?? '',
    });
  };

  const handleGuardarResultado = async () => {
    if (expandedVinculoId == null || !resultadoForm.facultadLocalId) {
      notify({ type: 'error', text: 'Selecciona la facultad local para registrar el resultado.' });
      return;
    }

    const payload = {
      edicionDisciplinaId: expandedVinculoId,
      facultadLocalId: Number(resultadoForm.facultadLocalId),
      facultadVisitanteId: resultadoForm.facultadVisitanteId ? Number(resultadoForm.facultadVisitanteId) : undefined,
      fase: resultadoForm.fase || undefined,
      grupo: resultadoForm.grupo || undefined,
      fechaPartido: resultadoForm.fechaPartido || undefined,
      puntajeLocal: resultadoForm.puntajeLocal !== '' ? Number(resultadoForm.puntajeLocal) : undefined,
      puntajeVisitante: resultadoForm.puntajeVisitante !== '' ? Number(resultadoForm.puntajeVisitante) : undefined,
      estado: resultadoForm.estado,
      observaciones: resultadoForm.observaciones || undefined,
    };

    try {
      if (editingResultadoId != null) {
        await api.actualizarResultado(editingResultadoId, payload);
        notify({ type: 'success', text: 'Resultado actualizado.' });
      } else {
        await api.crearResultado(payload);
        notify({ type: 'success', text: 'Resultado registrado.' });
      }
      onAuditLog?.('Resultado de Olimpiadas registrado/corregido');
      setResultadoForm(EMPTY_RESULTADO_FORM);
      setEditingResultadoId(null);
      cargarResultadosVinculo(expandedVinculoId);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo guardar el resultado.';
      notify({ type: 'error', text: message });
    }
  };

  const facultadNombre = useMemo(
    () => (id: number) => facultades.find((f) => f.id === id)?.nombre ?? `Facultad #${id}`,
    [facultades],
  );

  const disciplinasDisponiblesParaVincular = useMemo(
    () => disciplinas.filter((d) => !edicionDisciplinas.some((vinculo) => vinculo.disciplinaId === d.id)),
    [disciplinas, edicionDisciplinas],
  );

  return (
    <section className="gestion-olimpiadas">
      <header className="gestion-olimpiadas-header">
        <div>
          <h2 className="gestion-olimpiadas-title">
            <Trophy size={22} /> Olimpiadas Interfacultades
          </h2>
          <p className="gestion-olimpiadas-subtitle">
            Administra ediciones, disciplinas, inscripciones y resultados de la competencia.
          </p>
        </div>
        <button type="button" className="olimpiadas-btn secondary" onClick={cargarTodo} disabled={loading}>
          <RefreshCw size={16} /> Actualizar
        </button>
      </header>

      {status && (
        <div className={`olimpiadas-status ${status.type}`}>
          {status.type === 'error' ? <AlertCircle size={16} /> : <CheckCircle2 size={16} />}
          <span>{status.text}</span>
        </div>
      )}

      <div className="gestion-olimpiadas-tabs">
        <button type="button" className={tab === 'ediciones' ? 'active' : ''} onClick={() => setTab('ediciones')}>
          Ediciones
        </button>
        <button type="button" className={tab === 'disciplinas' ? 'active' : ''} onClick={() => setTab('disciplinas')}>
          Disciplinas
        </button>
        <button type="button" className={tab === 'resultados' ? 'active' : ''} onClick={() => setTab('resultados')}>
          Inscripciones y resultados
        </button>
      </div>

      {tab === 'ediciones' && (
        <div className="gestion-olimpiadas-panel">
          <div className="gestion-olimpiadas-panel-actions">
            <button type="button" className="olimpiadas-btn primary" onClick={() => setEdicionFormOpen((v) => !v)}>
              <Plus size={16} /> {edicionFormOpen ? 'Cerrar formulario' : 'Nueva edición'}
            </button>
          </div>

          {edicionFormOpen && (
            <div className="gestion-olimpiadas-form">
              <input
                className="olimpiadas-admin-input"
                placeholder="Nombre (ej. Olimpiadas Interfacultades 2026)"
                value={edicionForm.nombre}
                onChange={(e) => setEdicionForm((p) => ({ ...p, nombre: e.target.value }))}
              />
              <div className="olimpiadas-admin-row">
                <input
                  className="olimpiadas-admin-input"
                  type="number"
                  placeholder="Año de inicio"
                  value={edicionForm.anioInicio}
                  onChange={(e) => setEdicionForm((p) => ({ ...p, anioInicio: e.target.value }))}
                />
                <select
                  className="olimpiadas-admin-input"
                  value={edicionForm.semestreInicio}
                  onChange={(e) => setEdicionForm((p) => ({ ...p, semestreInicio: e.target.value as '1' | '2' }))}
                >
                  <option value="1">Semestre 1</option>
                  <option value="2">Semestre 2</option>
                </select>
              </div>
              <div className="olimpiadas-admin-row">
                <input
                  className="olimpiadas-admin-input"
                  type="date"
                  value={edicionForm.fechaInicioJuegos}
                  onChange={(e) => setEdicionForm((p) => ({ ...p, fechaInicioJuegos: e.target.value }))}
                />
                <input
                  className="olimpiadas-admin-input"
                  type="date"
                  value={edicionForm.fechaFinJuegos}
                  onChange={(e) => setEdicionForm((p) => ({ ...p, fechaFinJuegos: e.target.value }))}
                />
              </div>
              <textarea
                className="olimpiadas-admin-input"
                placeholder="Observaciones"
                value={edicionForm.observaciones}
                onChange={(e) => setEdicionForm((p) => ({ ...p, observaciones: e.target.value }))}
              />
              <button type="button" className="olimpiadas-btn primary" onClick={handleCrearEdicion} disabled={submittingEdicion}>
                Guardar edición
              </button>
            </div>
          )}

          <table className="olimpiadas-admin-table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Periodo</th>
                <th>Estado</th>
                <th>Inscripción</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {ediciones.map((edicion) => (
                <tr key={edicion.id}>
                  <td>{edicion.nombre}</td>
                  <td>{edicion.anioInicio}-{edicion.semestreInicio} a {edicion.anioFin}-{edicion.semestreFin}</td>
                  <td>
                    <select
                      className="olimpiadas-admin-input-sm"
                      value={edicion.estado}
                      onChange={(e) => handleCambiarEstadoEdicion(edicion, e.target.value)}
                    >
                      {ESTADOS_EDICION.map((estado) => (
                        <option key={estado} value={estado}>{estado}</option>
                      ))}
                    </select>
                  </td>
                  <td>{edicion.inscripcionAbierta ? 'Abierta' : 'Cerrada'}</td>
                  <td className="olimpiadas-admin-actions">
                    <button type="button" className="olimpiadas-icon-btn" title="Abrir inscripción" onClick={() => handleAbrirInscripcion(edicion)}>
                      <Unlock size={16} />
                    </button>
                    <button type="button" className="olimpiadas-icon-btn" title="Cerrar inscripción" onClick={() => handleCerrarInscripcion(edicion)}>
                      <Lock size={16} />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {tab === 'disciplinas' && (
        <div className="gestion-olimpiadas-panel">
          <div className="gestion-olimpiadas-panel-actions">
            <button
              type="button"
              className="olimpiadas-btn primary"
              onClick={() => {
                setEditingDisciplinaId(null);
                setDisciplinaForm(EMPTY_DISCIPLINA_FORM);
                setDisciplinaFormOpen((v) => !v);
              }}
            >
              <Plus size={16} /> {disciplinaFormOpen ? 'Cerrar formulario' : 'Nueva disciplina'}
            </button>
          </div>

          {disciplinaFormOpen && (
            <div className="gestion-olimpiadas-form">
              <input
                className="olimpiadas-admin-input"
                placeholder="Nombre de la disciplina"
                value={disciplinaForm.nombre}
                onChange={(e) => setDisciplinaForm((p) => ({ ...p, nombre: e.target.value }))}
              />
              <textarea
                className="olimpiadas-admin-input"
                placeholder="Descripción"
                value={disciplinaForm.descripcion}
                onChange={(e) => setDisciplinaForm((p) => ({ ...p, descripcion: e.target.value }))}
              />
              <div className="olimpiadas-admin-row">
                <select
                  className="olimpiadas-admin-input"
                  value={disciplinaForm.tipoParticipacion}
                  onChange={(e) =>
                    setDisciplinaForm((p) => ({ ...p, tipoParticipacion: e.target.value as 'individual' | 'equipo' }))
                  }
                >
                  <option value="equipo">Por equipos</option>
                  <option value="individual">Individual</option>
                </select>
                <input
                  className="olimpiadas-admin-input"
                  type="number"
                  placeholder="Cupo máximo por defecto"
                  value={disciplinaForm.cupoMaximoDefault}
                  onChange={(e) => setDisciplinaForm((p) => ({ ...p, cupoMaximoDefault: e.target.value }))}
                />
              </div>
              <textarea
                className="olimpiadas-admin-input"
                placeholder="Reglas de juego"
                value={disciplinaForm.reglas}
                onChange={(e) => setDisciplinaForm((p) => ({ ...p, reglas: e.target.value }))}
              />
              <button type="button" className="olimpiadas-btn primary" onClick={handleGuardarDisciplina} disabled={submittingDisciplina}>
                Guardar disciplina
              </button>
            </div>
          )}

          <table className="olimpiadas-admin-table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Cupo</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {disciplinas.map((disciplina) => (
                <tr key={disciplina.id}>
                  <td>{disciplina.nombre}</td>
                  <td>{disciplina.tipoParticipacion === 'individual' ? 'Individual' : 'Por equipos'}</td>
                  <td>{disciplina.cupoMaximoDefault ?? '—'}</td>
                  <td>{disciplina.estado}</td>
                  <td className="olimpiadas-admin-actions">
                    <button type="button" className="olimpiadas-icon-btn" onClick={() => handleEditarDisciplina(disciplina)}>
                      Editar
                    </button>
                    <button type="button" className="olimpiadas-icon-btn" onClick={() => handleToggleEstadoDisciplina(disciplina)}>
                      {disciplina.estado === 'activa' ? 'Desactivar' : 'Activar'}
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {tab === 'resultados' && (
        <div className="gestion-olimpiadas-panel">
          <select
            className="olimpiadas-admin-input"
            value={selectedEdicionId ?? ''}
            onChange={(e) => setSelectedEdicionId(Number(e.target.value) || null)}
          >
            {ediciones.map((edicion) => (
              <option key={edicion.id} value={edicion.id}>{edicion.nombre}</option>
            ))}
          </select>

          <div className="olimpiadas-admin-row">
            <select
              className="olimpiadas-admin-input"
              value={vincularDisciplinaId}
              onChange={(e) => setVincularDisciplinaId(e.target.value)}
            >
              <option value="">Vincular disciplina del catálogo…</option>
              {disciplinasDisponiblesParaVincular.map((d) => (
                <option key={d.id} value={d.id}>{d.nombre}</option>
              ))}
            </select>
            <button type="button" className="olimpiadas-btn primary" onClick={handleVincularDisciplina}>
              <Plus size={16} /> Vincular
            </button>
          </div>

          <ul className="olimpiadas-admin-vinculos">
            {edicionDisciplinas.map((vinculo) => (
              <li key={vinculo.id} className="olimpiadas-admin-vinculo">
                <button type="button" className="olimpiadas-admin-vinculo-header" onClick={() => handleExpandVinculo(vinculo)}>
                  <span>
                    {vinculo.disciplinaNombre} · {vinculo.inscritosActivos} inscritos
                    {vinculo.cupoMaximoPorFacultad ? ` (cupo ${vinculo.cupoMaximoPorFacultad})` : ''} · {vinculo.estado}
                  </span>
                  {expandedVinculoId === vinculo.id ? <ChevronUp size={18} /> : <ChevronDown size={18} />}
                </button>

                <div className="olimpiadas-admin-vinculo-toolbar">
                  <button type="button" className="olimpiadas-icon-btn" onClick={() => handleToggleEstadoVinculo(vinculo)}>
                    {vinculo.estado === 'activa' ? 'Desactivar en esta edición' : 'Activar en esta edición'}
                  </button>
                </div>

                {expandedVinculoId === vinculo.id && (
                  <div className="olimpiadas-admin-vinculo-body">
                    <div className="olimpiadas-admin-subsection">
                      <h4>Participantes inscritos</h4>
                      {participantes.length === 0 ? (
                        <p className="olimpiadas-admin-empty">Sin participantes inscritos.</p>
                      ) : (
                        <ul className="olimpiadas-admin-participantes">
                          {participantes.map((p) => (
                            <li key={p.inscripcionId}>
                              {p.usuarioNombre ?? `Usuario #${p.usuarioId}`} — {p.facultadNombre}
                            </li>
                          ))}
                        </ul>
                      )}
                    </div>

                    <div className="olimpiadas-admin-subsection">
                      <h4>{editingResultadoId ? 'Editar resultado' : 'Registrar resultado'}</h4>
                      <div className="olimpiadas-admin-row">
                        <select
                          className="olimpiadas-admin-input"
                          value={resultadoForm.facultadLocalId}
                          onChange={(e) => setResultadoForm((p) => ({ ...p, facultadLocalId: e.target.value }))}
                        >
                          <option value="">Facultad local</option>
                          {facultades.map((f) => (
                            <option key={f.id} value={f.id}>{f.nombre}</option>
                          ))}
                        </select>
                        <select
                          className="olimpiadas-admin-input"
                          value={resultadoForm.facultadVisitanteId}
                          onChange={(e) => setResultadoForm((p) => ({ ...p, facultadVisitanteId: e.target.value }))}
                        >
                          <option value="">Facultad visitante (opcional)</option>
                          {facultades.map((f) => (
                            <option key={f.id} value={f.id}>{f.nombre}</option>
                          ))}
                        </select>
                      </div>
                      <div className="olimpiadas-admin-row">
                        <input
                          className="olimpiadas-admin-input"
                          placeholder="Fase (grupos, semifinal, final…)"
                          value={resultadoForm.fase}
                          onChange={(e) => setResultadoForm((p) => ({ ...p, fase: e.target.value }))}
                        />
                        <input
                          className="olimpiadas-admin-input"
                          placeholder="Grupo (opcional)"
                          value={resultadoForm.grupo}
                          onChange={(e) => setResultadoForm((p) => ({ ...p, grupo: e.target.value }))}
                        />
                      </div>
                      <div className="olimpiadas-admin-row">
                        <input
                          className="olimpiadas-admin-input"
                          type="datetime-local"
                          value={resultadoForm.fechaPartido}
                          onChange={(e) => setResultadoForm((p) => ({ ...p, fechaPartido: e.target.value }))}
                        />
                        <select
                          className="olimpiadas-admin-input"
                          value={resultadoForm.estado}
                          onChange={(e) =>
                            setResultadoForm((p) => ({ ...p, estado: e.target.value as ResultadoFormValues['estado'] }))
                          }
                        >
                          {ESTADOS_RESULTADO.map((estado) => (
                            <option key={estado} value={estado}>{estado}</option>
                          ))}
                        </select>
                      </div>
                      <div className="olimpiadas-admin-row">
                        <input
                          className="olimpiadas-admin-input"
                          type="number"
                          placeholder="Puntaje local"
                          value={resultadoForm.puntajeLocal}
                          onChange={(e) => setResultadoForm((p) => ({ ...p, puntajeLocal: e.target.value }))}
                        />
                        <input
                          className="olimpiadas-admin-input"
                          type="number"
                          placeholder="Puntaje visitante"
                          value={resultadoForm.puntajeVisitante}
                          onChange={(e) => setResultadoForm((p) => ({ ...p, puntajeVisitante: e.target.value }))}
                        />
                      </div>
                      <textarea
                        className="olimpiadas-admin-input"
                        placeholder="Observaciones"
                        value={resultadoForm.observaciones}
                        onChange={(e) => setResultadoForm((p) => ({ ...p, observaciones: e.target.value }))}
                      />
                      <button type="button" className="olimpiadas-btn primary" onClick={handleGuardarResultado}>
                        {editingResultadoId ? 'Guardar corrección' : 'Registrar resultado'}
                      </button>
                    </div>

                    <div className="olimpiadas-admin-subsection">
                      <h4>Fixture</h4>
                      {fixture.length === 0 ? (
                        <p className="olimpiadas-admin-empty">Sin enfrentamientos registrados.</p>
                      ) : (
                        <table className="olimpiadas-admin-table">
                          <thead>
                            <tr>
                              <th>Local</th>
                              <th>Visitante</th>
                              <th>Fase</th>
                              <th>Marcador</th>
                              <th>Estado</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            {fixture.map((resultado) => (
                              <tr key={resultado.id}>
                                <td>{resultado.facultadLocalNombre}</td>
                                <td>{resultado.facultadVisitanteNombre ?? '—'}</td>
                                <td>{resultado.fase}{resultado.grupo ? ` (${resultado.grupo})` : ''}</td>
                                <td>
                                  {resultado.puntajeLocal ?? '—'} - {resultado.puntajeVisitante ?? '—'}
                                </td>
                                <td>{resultado.estado}</td>
                                <td>
                                  <button type="button" className="olimpiadas-icon-btn" onClick={() => handleEditarResultado(resultado)}>
                                    Editar
                                  </button>
                                </td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      )}
                    </div>

                    <div className="olimpiadas-admin-subsection">
                      <h4>Tabla de posiciones</h4>
                      {tabla.length === 0 ? (
                        <p className="olimpiadas-admin-empty">Aún no hay resultados finalizados.</p>
                      ) : (
                        <table className="olimpiadas-admin-table">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th>Facultad</th>
                              <th>PJ</th>
                              <th>PG</th>
                              <th>PE</th>
                              <th>PP</th>
                              <th>Pts</th>
                            </tr>
                          </thead>
                          <tbody>
                            {tabla.map((fila) => (
                              <tr key={fila.facultadId}>
                                <td>{fila.posicion ?? '—'}</td>
                                <td>{fila.facultadNombre ?? facultadNombre(fila.facultadId)}</td>
                                <td>{fila.partidosJugados}</td>
                                <td>{fila.partidosGanados}</td>
                                <td>{fila.partidosEmpatados}</td>
                                <td>{fila.partidosPerdidos}</td>
                                <td><strong>{fila.puntos}</strong></td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      )}
                    </div>
                  </div>
                )}
              </li>
            ))}
          </ul>
        </div>
      )}
    </section>
  );
};

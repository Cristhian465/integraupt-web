import React, { useCallback, useEffect, useMemo, useState } from 'react';
import {
  AlertCircle,
  CheckCircle2,
  ChevronDown,
  ChevronUp,
  ImagePlus,
  Lock,
  Medal,
  MessageCircle,
  Plus,
  RefreshCw,
  Search,
  Target,
  Trophy,
  UserPlus,
  Unlock,
} from 'lucide-react';
import '../../../styles/GestionOlimpiadas.css';
import * as api from './olimpiadasAdminService';
import { facultadColor } from '../../../utils/facultadColors';
import type {
  Anotador,
  AnotadorFormValues,
  DisciplinaCatalogo,
  DisciplinaFormValues,
  Edicion,
  EdicionDisciplina,
  EdicionFormValues,
  EstudianteBusqueda,
  FacultadOption,
  InscripcionAdmin,
  MedalleroFila,
  Post,
  PostFormValues,
  Resultado,
  ResultadoFormValues,
  ResultadoPosicion,
  ResultadoPosicionFormValues,
  TablaPosicion,
} from './types';

interface GestionOlimpiadasProps {
  onAuditLog?: (message: string, detail?: string) => void;
}

type StatusMessage = { type: 'success' | 'error'; text: string };
type Tab = 'ediciones' | 'disciplinas' | 'resultados' | 'medallero' | 'posts';

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
  tipoPuntuacion: 'partido',
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

const EMPTY_RESULTADO_POSICION_FORM: ResultadoPosicionFormValues = {
  facultadId: '',
  posicion: '',
  puntos: '',
  prueba: '',
  fecha: '',
  lugar: '',
  observaciones: '',
  estado: 'registrado',
};

const EMPTY_ANOTADOR_FORM: AnotadorFormValues = {
  facultadId: '',
  nombreJugador: '',
  cantidad: '1',
  observaciones: '',
};

const EMPTY_POST_FORM: PostFormValues = {
  titulo: '',
  contenido: '',
  imagenUrl: '',
  autor: 'Comité Olímpico UPT',
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
      tipoPuntuacion: disciplina.tipoPuntuacion,
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
        tipoPuntuacion: disciplinaForm.tipoPuntuacion,
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
  const [vincularLugar, setVincularLugar] = useState('');
  const [vincularCategoria, setVincularCategoria] = useState<'general' | 'varones' | 'damas' | 'mixto'>('general');
  const [resultadosPosicion, setResultadosPosicion] = useState<ResultadoPosicion[]>([]);
  const [resultadoPosicionForm, setResultadoPosicionForm] = useState<ResultadoPosicionFormValues>(EMPTY_RESULTADO_POSICION_FORM);
  const [editingResultadoPosicionId, setEditingResultadoPosicionId] = useState<number | null>(null);
  const [busquedaEstudiante, setBusquedaEstudiante] = useState('');
  const [resultadosBusquedaEstudiante, setResultadosBusquedaEstudiante] = useState<EstudianteBusqueda[]>([]);
  const [buscandoEstudiante, setBuscandoEstudiante] = useState(false);
  const [inscribiendoEstudianteId, setInscribiendoEstudianteId] = useState<number | null>(null);
  const [anotadores, setAnotadores] = useState<Anotador[]>([]);
  const [anotadorForm, setAnotadorForm] = useState<AnotadorFormValues>(EMPTY_ANOTADOR_FORM);
  const [editingAnotadorId, setEditingAnotadorId] = useState<number | null>(null);

  // ===== Medallero =====
  const [medallero, setMedallero] = useState<MedalleroFila[]>([]);
  const [medalleroLoading, setMedalleroLoading] = useState(false);

  // ===== Posts =====
  const [posts, setPosts] = useState<Post[]>([]);
  const [postForm, setPostForm] = useState<PostFormValues>(EMPTY_POST_FORM);
  const [postFormOpen, setPostFormOpen] = useState(false);
  const [submittingPost, setSubmittingPost] = useState(false);

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
      await api.vincularDisciplinaAEdicion(selectedEdicionId, {
        disciplinaId: Number(vincularDisciplinaId),
        lugar: vincularLugar || undefined,
        categoria: vincularCategoria,
      });
      notify({ type: 'success', text: 'Disciplina vinculada a la edición.' });
      setVincularDisciplinaId('');
      setVincularLugar('');
      setVincularCategoria('general');
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
      const [participantesData, fixtureData, tablaData, posicionesData, anotadoresData] = await Promise.all([
        api.fetchParticipantes(vinculoId),
        api.fetchFixture(vinculoId),
        api.fetchTabla(vinculoId),
        api.fetchResultadosPosicion(vinculoId),
        api.fetchAnotadores(vinculoId),
      ]);
      setParticipantes(participantesData);
      setFixture(fixtureData);
      setTabla(tablaData);
      setResultadosPosicion(posicionesData);
      setAnotadores(anotadoresData);
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
    setEditingResultadoPosicionId(null);
    setResultadoPosicionForm(EMPTY_RESULTADO_POSICION_FORM);
    setEditingAnotadorId(null);
    setAnotadorForm(EMPTY_ANOTADOR_FORM);
    setBusquedaEstudiante('');
    setResultadosBusquedaEstudiante([]);
    cargarResultadosVinculo(vinculo.id);
  };

  const handleEditarAnotador = (anotador: Anotador) => {
    setEditingAnotadorId(anotador.id);
    setAnotadorForm({
      facultadId: anotador.facultadId.toString(),
      nombreJugador: anotador.nombreJugador,
      cantidad: anotador.cantidad.toString(),
      observaciones: anotador.observaciones ?? '',
    });
  };

  const handleGuardarAnotador = async () => {
    if (expandedVinculoId == null || !anotadorForm.facultadId || !anotadorForm.nombreJugador.trim()) {
      notify({ type: 'error', text: 'Selecciona la facultad e ingresa el nombre del jugador.' });
      return;
    }

    const payload = {
      edicionDisciplinaId: expandedVinculoId,
      facultadId: Number(anotadorForm.facultadId),
      nombreJugador: anotadorForm.nombreJugador.trim(),
      cantidad: anotadorForm.cantidad !== '' ? Number(anotadorForm.cantidad) : 1,
      observaciones: anotadorForm.observaciones || undefined,
    };

    try {
      if (editingAnotadorId != null) {
        await api.actualizarAnotador(editingAnotadorId, payload);
        notify({ type: 'success', text: 'Anotador actualizado.' });
      } else {
        await api.crearAnotador(payload);
        notify({ type: 'success', text: 'Anotador registrado.' });
      }
      onAuditLog?.('Anotador de Olimpiadas registrado/corregido');
      setAnotadorForm(EMPTY_ANOTADOR_FORM);
      setEditingAnotadorId(null);
      cargarResultadosVinculo(expandedVinculoId);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo guardar el anotador.';
      notify({ type: 'error', text: message });
    }
  };

  const handleBuscarEstudiante = async () => {
    if (!busquedaEstudiante.trim()) {
      setResultadosBusquedaEstudiante([]);
      return;
    }
    setBuscandoEstudiante(true);
    try {
      const data = await api.buscarEstudiantes(busquedaEstudiante.trim());
      setResultadosBusquedaEstudiante(Array.isArray(data) ? data : []);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo buscar estudiantes.';
      notify({ type: 'error', text: message });
    } finally {
      setBuscandoEstudiante(false);
    }
  };

  const handleInscribirEstudiante = async (estudiante: EstudianteBusqueda) => {
    if (expandedVinculoId == null) return;
    setInscribiendoEstudianteId(estudiante.usuarioId);
    try {
      await api.inscribirParticipante({ edicionDisciplinaId: expandedVinculoId, usuarioId: estudiante.usuarioId });
      notify({ type: 'success', text: `${estudiante.nombreCompleto} fue inscrito.` });
      onAuditLog?.('Participante inscrito en Olimpiadas', estudiante.nombreCompleto);
      setBusquedaEstudiante('');
      setResultadosBusquedaEstudiante([]);
      cargarResultadosVinculo(expandedVinculoId);
      if (selectedEdicionId != null) cargarDisciplinasEdicion(selectedEdicionId);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo inscribir al estudiante.';
      notify({ type: 'error', text: message });
    } finally {
      setInscribiendoEstudianteId(null);
    }
  };

  const handleEditarResultadoPosicion = (posicion: ResultadoPosicion) => {
    setEditingResultadoPosicionId(posicion.id);
    setResultadoPosicionForm({
      facultadId: posicion.facultadId.toString(),
      posicion: posicion.posicion.toString(),
      puntos: posicion.puntos.toString(),
      prueba: posicion.prueba ?? '',
      fecha: posicion.fecha ?? '',
      lugar: posicion.lugar ?? '',
      observaciones: posicion.observaciones ?? '',
      estado: posicion.estado as ResultadoPosicionFormValues['estado'],
    });
  };

  const handleGuardarResultadoPosicion = async () => {
    if (expandedVinculoId == null || !resultadoPosicionForm.facultadId || !resultadoPosicionForm.posicion) {
      notify({ type: 'error', text: 'Selecciona la facultad y la posición obtenida.' });
      return;
    }

    const payload = {
      edicionDisciplinaId: expandedVinculoId,
      facultadId: Number(resultadoPosicionForm.facultadId),
      posicion: Number(resultadoPosicionForm.posicion),
      puntos: resultadoPosicionForm.puntos !== '' ? Number(resultadoPosicionForm.puntos) : 0,
      prueba: resultadoPosicionForm.prueba || undefined,
      fecha: resultadoPosicionForm.fecha || undefined,
      lugar: resultadoPosicionForm.lugar || undefined,
      observaciones: resultadoPosicionForm.observaciones || undefined,
      estado: resultadoPosicionForm.estado,
    };

    try {
      if (editingResultadoPosicionId != null) {
        await api.actualizarResultadoPosicion(editingResultadoPosicionId, payload);
        notify({ type: 'success', text: 'Resultado de posición actualizado.' });
      } else {
        await api.crearResultadoPosicion(payload);
        notify({ type: 'success', text: 'Resultado de posición registrado.' });
      }
      onAuditLog?.('Resultado por posición de Olimpiadas registrado/corregido');
      setResultadoPosicionForm(EMPTY_RESULTADO_POSICION_FORM);
      setEditingResultadoPosicionId(null);
      cargarResultadosVinculo(expandedVinculoId);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo guardar el resultado de posición.';
      notify({ type: 'error', text: message });
    }
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

  const cargarMedallero = useCallback(async (edicionId: number) => {
    setMedalleroLoading(true);
    try {
      const data = await api.fetchMedallero(edicionId);
      setMedallero(Array.isArray(data) ? data : []);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo cargar el medallero general.';
      notify({ type: 'error', text: message });
    } finally {
      setMedalleroLoading(false);
    }
  }, [notify]);

  useEffect(() => {
    if (tab === 'medallero' && selectedEdicionId != null) {
      cargarMedallero(selectedEdicionId);
    }
  }, [tab, selectedEdicionId, cargarMedallero]);

  const cargarPosts = useCallback(async (edicionId: number) => {
    try {
      const data = await api.fetchPosts(edicionId);
      setPosts(Array.isArray(data) ? data : []);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo cargar los posts.';
      notify({ type: 'error', text: message });
    }
  }, [notify]);

  useEffect(() => {
    if (tab === 'posts' && selectedEdicionId != null) {
      cargarPosts(selectedEdicionId);
    }
  }, [tab, selectedEdicionId, cargarPosts]);

  const handleCrearPost = async () => {
    if (selectedEdicionId == null || !postForm.titulo.trim() || !postForm.contenido.trim()) {
      notify({ type: 'error', text: 'Título y contenido son obligatorios.' });
      return;
    }

    setSubmittingPost(true);
    try {
      await api.crearPost({
        edicionId: selectedEdicionId,
        titulo: postForm.titulo.trim(),
        contenido: postForm.contenido.trim(),
        imagenUrl: postForm.imagenUrl || undefined,
        autor: postForm.autor || undefined,
      });
      notify({ type: 'success', text: 'Post publicado.' });
      onAuditLog?.('Post de Olimpiadas publicado', postForm.titulo);
      setPostForm(EMPTY_POST_FORM);
      setPostFormOpen(false);
      cargarPosts(selectedEdicionId);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo publicar el post.';
      notify({ type: 'error', text: message });
    } finally {
      setSubmittingPost(false);
    }
  };

  const handleEliminarPost = async (post: Post) => {
    if (selectedEdicionId == null) return;
    try {
      await api.eliminarPost(post.id);
      notify({ type: 'success', text: 'Post eliminado.' });
      cargarPosts(selectedEdicionId);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'No se pudo eliminar el post.';
      notify({ type: 'error', text: message });
    }
  };

  const disciplinasDisponiblesParaVincular = useMemo(
    () =>
      disciplinas.filter(
        (d) => !edicionDisciplinas.some((vinculo) => vinculo.disciplinaId === d.id && vinculo.categoria === vincularCategoria),
      ),
    [disciplinas, edicionDisciplinas, vincularCategoria],
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
        <button type="button" className={tab === 'medallero' ? 'active' : ''} onClick={() => setTab('medallero')}>
          <Medal size={15} /> Medallero general
        </button>
        <button type="button" className={tab === 'posts' ? 'active' : ''} onClick={() => setTab('posts')}>
          <ImagePlus size={15} /> Noticias
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
                <select
                  className="olimpiadas-admin-input"
                  value={disciplinaForm.tipoPuntuacion}
                  onChange={(e) =>
                    setDisciplinaForm((p) => ({ ...p, tipoPuntuacion: e.target.value as 'partido' | 'posiciones' }))
                  }
                >
                  <option value="partido">Por partidos (cabeza a cabeza)</option>
                  <option value="posiciones">Por posiciones (ranking)</option>
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
            <select
              className="olimpiadas-admin-input"
              value={vincularCategoria}
              onChange={(e) => setVincularCategoria(e.target.value as 'general' | 'varones' | 'damas' | 'mixto')}
            >
              <option value="general">General</option>
              <option value="varones">Varones</option>
              <option value="damas">Damas</option>
              <option value="mixto">Mixto</option>
            </select>
            <input
              className="olimpiadas-admin-input"
              placeholder="Lugar / sede (opcional)"
              value={vincularLugar}
              onChange={(e) => setVincularLugar(e.target.value)}
            />
            <button type="button" className="olimpiadas-btn primary" onClick={handleVincularDisciplina}>
              <Plus size={16} /> Vincular
            </button>
          </div>

          <ul className="olimpiadas-admin-vinculos">
            {edicionDisciplinas.map((vinculo) => (
              <li key={vinculo.id} className="olimpiadas-admin-vinculo">
                <button type="button" className="olimpiadas-admin-vinculo-header" onClick={() => handleExpandVinculo(vinculo)}>
                  <span>
                    {vinculo.disciplinaNombre}
                    {vinculo.categoria !== 'general' ? ` (${vinculo.categoria})` : ''} · {vinculo.inscritosActivos} inscritos
                    {vinculo.cupoMaximoPorFacultad ? ` (cupo ${vinculo.cupoMaximoPorFacultad})` : ''} · {vinculo.estado}
                    {vinculo.lugar ? ` · ${vinculo.lugar}` : ''} · {vinculo.tipoPuntuacion === 'posiciones' ? 'Por posiciones' : 'Por partidos'}
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
                      <h4><UserPlus size={16} /> Inscribir participante</h4>
                      <p className="olimpiadas-admin-hint">
                        Solo el administrador puede inscribir estudiantes. Busca por código, nombre o apellido.
                      </p>
                      <div className="olimpiadas-admin-row">
                        <input
                          className="olimpiadas-admin-input"
                          placeholder="Buscar estudiante por código o nombre…"
                          value={busquedaEstudiante}
                          onChange={(e) => setBusquedaEstudiante(e.target.value)}
                          onKeyDown={(e) => e.key === 'Enter' && handleBuscarEstudiante()}
                        />
                        <button type="button" className="olimpiadas-btn secondary" onClick={handleBuscarEstudiante} disabled={buscandoEstudiante}>
                          <Search size={16} /> Buscar
                        </button>
                      </div>
                      {resultadosBusquedaEstudiante.length > 0 && (
                        <ul className="olimpiadas-admin-busqueda-resultados">
                          {resultadosBusquedaEstudiante.map((est) => (
                            <li key={est.usuarioId}>
                              <span className="olimpiadas-facultad-tag" style={{ '--dot-color': facultadColor(est.facultadNombre) } as React.CSSProperties}>
                                <span className="olimpiadas-facultad-dot" />
                                {est.nombreCompleto} · {est.codigo} · {est.facultadNombre}
                              </span>
                              <button
                                type="button"
                                className="olimpiadas-icon-btn"
                                onClick={() => handleInscribirEstudiante(est)}
                                disabled={inscribiendoEstudianteId === est.usuarioId}
                              >
                                {inscribiendoEstudianteId === est.usuarioId ? 'Inscribiendo…' : 'Inscribir'}
                              </button>
                            </li>
                          ))}
                        </ul>
                      )}
                    </div>

                    <div className="olimpiadas-admin-subsection">
                      <h4>Participantes inscritos</h4>
                      {participantes.length === 0 ? (
                        <p className="olimpiadas-admin-empty">Sin participantes inscritos.</p>
                      ) : (
                        <ul className="olimpiadas-admin-participantes">
                          {participantes.map((p) => (
                            <li key={p.inscripcionId}>
                              <span className="olimpiadas-facultad-tag" style={{ '--dot-color': facultadColor(p.facultadNombre) } as React.CSSProperties}>
                                <span className="olimpiadas-facultad-dot" />
                                {p.usuarioNombre ?? `Usuario #${p.usuarioId}`} — {p.facultadNombre}
                              </span>
                            </li>
                          ))}
                        </ul>
                      )}
                    </div>

                    {vinculo.tipoPuntuacion === 'posiciones' ? (
                    <div className="olimpiadas-admin-subsection">
                      <h4>{editingResultadoPosicionId ? 'Editar resultado por posición' : 'Registrar resultado por posición'}</h4>
                      <div className="olimpiadas-admin-row">
                        <select
                          className="olimpiadas-admin-input"
                          value={resultadoPosicionForm.facultadId}
                          onChange={(e) => setResultadoPosicionForm((p) => ({ ...p, facultadId: e.target.value }))}
                        >
                          <option value="">Facultad</option>
                          {facultades.map((f) => (
                            <option key={f.id} value={f.id}>{f.nombre}</option>
                          ))}
                        </select>
                        <input
                          className="olimpiadas-admin-input"
                          type="number"
                          placeholder="Posición"
                          value={resultadoPosicionForm.posicion}
                          onChange={(e) => setResultadoPosicionForm((p) => ({ ...p, posicion: e.target.value }))}
                        />
                        <input
                          className="olimpiadas-admin-input"
                          type="number"
                          placeholder="Puntos"
                          value={resultadoPosicionForm.puntos}
                          onChange={(e) => setResultadoPosicionForm((p) => ({ ...p, puntos: e.target.value }))}
                        />
                      </div>
                      <div className="olimpiadas-admin-row">
                        <input
                          className="olimpiadas-admin-input"
                          placeholder="Prueba (opcional)"
                          value={resultadoPosicionForm.prueba}
                          onChange={(e) => setResultadoPosicionForm((p) => ({ ...p, prueba: e.target.value }))}
                        />
                        <input
                          className="olimpiadas-admin-input"
                          type="date"
                          value={resultadoPosicionForm.fecha}
                          onChange={(e) => setResultadoPosicionForm((p) => ({ ...p, fecha: e.target.value }))}
                        />
                      </div>
                      <div className="olimpiadas-admin-row">
                        <input
                          className="olimpiadas-admin-input"
                          placeholder="Lugar (opcional)"
                          value={resultadoPosicionForm.lugar}
                          onChange={(e) => setResultadoPosicionForm((p) => ({ ...p, lugar: e.target.value }))}
                        />
                        <select
                          className="olimpiadas-admin-input"
                          value={resultadoPosicionForm.estado}
                          onChange={(e) =>
                            setResultadoPosicionForm((p) => ({ ...p, estado: e.target.value as ResultadoPosicionFormValues['estado'] }))
                          }
                        >
                          <option value="registrado">registrado</option>
                          <option value="cancelado">cancelado</option>
                        </select>
                      </div>
                      <textarea
                        className="olimpiadas-admin-input"
                        placeholder="Observaciones"
                        value={resultadoPosicionForm.observaciones}
                        onChange={(e) => setResultadoPosicionForm((p) => ({ ...p, observaciones: e.target.value }))}
                      />
                      <button type="button" className="olimpiadas-btn primary" onClick={handleGuardarResultadoPosicion}>
                        {editingResultadoPosicionId ? 'Guardar corrección' : 'Registrar posición'}
                      </button>

                      <h4 style={{ marginTop: '1rem' }}>Resultados registrados</h4>
                      {resultadosPosicion.length === 0 ? (
                        <p className="olimpiadas-admin-empty">Sin resultados registrados.</p>
                      ) : (
                        <table className="olimpiadas-admin-table">
                          <thead>
                            <tr>
                              <th>Pos.</th>
                              <th>Facultad</th>
                              <th>Puntos</th>
                              <th>Prueba</th>
                              <th>Lugar</th>
                              <th>Estado</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            {resultadosPosicion.map((p) => (
                              <tr key={p.id}>
                                <td>{p.posicion}</td>
                                <td>{p.facultadNombre ?? facultadNombre(p.facultadId)}</td>
                                <td>{p.puntos}</td>
                                <td>{p.prueba ?? '—'}</td>
                                <td>{p.lugar ?? '—'}</td>
                                <td>{p.estado}</td>
                                <td>
                                  <button type="button" className="olimpiadas-icon-btn" onClick={() => handleEditarResultadoPosicion(p)}>
                                    Editar
                                  </button>
                                </td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      )}
                    </div>
                    ) : (
                    <>
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
                    </>
                    )}

                    {vinculo.tipoPuntuacion === 'partido' && (
                    <div className="olimpiadas-admin-subsection">
                      <h4><Target size={16} /> Goleadores / Anotadores</h4>
                      <div className="olimpiadas-admin-row">
                        <select
                          className="olimpiadas-admin-input"
                          value={anotadorForm.facultadId}
                          onChange={(e) => setAnotadorForm((p) => ({ ...p, facultadId: e.target.value }))}
                        >
                          <option value="">Facultad</option>
                          {facultades.map((f) => (
                            <option key={f.id} value={f.id}>{f.nombre}</option>
                          ))}
                        </select>
                        <input
                          className="olimpiadas-admin-input"
                          placeholder="Nombre del jugador"
                          value={anotadorForm.nombreJugador}
                          onChange={(e) => setAnotadorForm((p) => ({ ...p, nombreJugador: e.target.value }))}
                        />
                        <input
                          className="olimpiadas-admin-input"
                          type="number"
                          placeholder="Cantidad (goles/puntos)"
                          value={anotadorForm.cantidad}
                          onChange={(e) => setAnotadorForm((p) => ({ ...p, cantidad: e.target.value }))}
                        />
                      </div>
                      <button type="button" className="olimpiadas-btn primary" onClick={handleGuardarAnotador}>
                        {editingAnotadorId ? 'Guardar corrección' : 'Agregar anotador'}
                      </button>

                      {anotadores.length === 0 ? (
                        <p className="olimpiadas-admin-empty">Sin anotadores registrados.</p>
                      ) : (
                        <table className="olimpiadas-admin-table">
                          <thead>
                            <tr>
                              <th>Jugador</th>
                              <th>Facultad</th>
                              <th>Goles/Pts</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            {anotadores.map((a) => (
                              <tr key={a.id}>
                                <td>{a.nombreJugador}</td>
                                <td>
                                  <span className="olimpiadas-facultad-tag" style={{ '--dot-color': facultadColor(a.facultadAbreviatura ?? a.facultadNombre) } as React.CSSProperties}>
                                    <span className="olimpiadas-facultad-dot" />
                                    {a.facultadNombre}
                                  </span>
                                </td>
                                <td><strong>{a.cantidad}</strong></td>
                                <td>
                                  <button type="button" className="olimpiadas-icon-btn" onClick={() => handleEditarAnotador(a)}>
                                    Editar
                                  </button>
                                </td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      )}
                    </div>
                    )}

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
                                <td>
                                  <span className="olimpiadas-facultad-tag" style={{ '--dot-color': facultadColor(fila.facultadAbreviatura ?? fila.facultadNombre) } as React.CSSProperties}>
                                    <span className="olimpiadas-facultad-dot" />
                                    {fila.facultadNombre ?? facultadNombre(fila.facultadId)}
                                  </span>
                                </td>
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

      {tab === 'medallero' && (
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

          {medalleroLoading ? (
            <p className="olimpiadas-admin-empty">Cargando medallero…</p>
          ) : medallero.length === 0 ? (
            <p className="olimpiadas-admin-empty">Aún no hay resultados registrados en esta edición.</p>
          ) : (
            <table className="olimpiadas-admin-table olimpiadas-medallero-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Facultad</th>
                  <th>🥇 Oro</th>
                  <th>🥈 Plata</th>
                  <th>🥉 Bronce</th>
                  <th>Disciplinas</th>
                  <th>Puntos totales</th>
                </tr>
              </thead>
              <tbody>
                {medallero.map((fila) => (
                  <tr key={fila.facultadId} className={fila.posicion === 1 ? 'olimpiadas-medallero-lider' : ''}>
                    <td>{fila.posicion}</td>
                    <td>
                      <span className="olimpiadas-facultad-tag" style={{ '--dot-color': facultadColor(fila.facultadAbreviatura ?? fila.facultadNombre) } as React.CSSProperties}>
                        <span className="olimpiadas-facultad-dot" />
                        {fila.facultadNombre}
                      </span>
                    </td>
                    <td>{fila.oros}</td>
                    <td>{fila.platas}</td>
                    <td>{fila.bronces}</td>
                    <td>{fila.disciplinas}</td>
                    <td><strong>{fila.puntosTotales}</strong></td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      )}

      {tab === 'posts' && (
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

          <div className="gestion-olimpiadas-panel-actions">
            <button type="button" className="olimpiadas-btn primary" onClick={() => setPostFormOpen((v) => !v)}>
              <Plus size={16} /> {postFormOpen ? 'Cerrar formulario' : 'Nuevo post'}
            </button>
          </div>

          {postFormOpen && (
            <div className="gestion-olimpiadas-form">
              <input
                className="olimpiadas-admin-input"
                placeholder="Título"
                value={postForm.titulo}
                onChange={(e) => setPostForm((p) => ({ ...p, titulo: e.target.value }))}
              />
              <textarea
                className="olimpiadas-admin-input"
                placeholder="Contenido / novedad"
                value={postForm.contenido}
                onChange={(e) => setPostForm((p) => ({ ...p, contenido: e.target.value }))}
              />
              <input
                className="olimpiadas-admin-input"
                placeholder="URL de imagen (opcional)"
                value={postForm.imagenUrl}
                onChange={(e) => setPostForm((p) => ({ ...p, imagenUrl: e.target.value }))}
              />
              <input
                className="olimpiadas-admin-input"
                placeholder="Autor"
                value={postForm.autor}
                onChange={(e) => setPostForm((p) => ({ ...p, autor: e.target.value }))}
              />
              <button type="button" className="olimpiadas-btn primary" onClick={handleCrearPost} disabled={submittingPost}>
                Publicar
              </button>
            </div>
          )}

          {posts.length === 0 ? (
            <p className="olimpiadas-admin-empty">Aún no hay posts publicados para esta edición.</p>
          ) : (
            <ul className="olimpiadas-admin-posts">
              {posts.map((post) => (
                <li key={post.id} className="olimpiadas-admin-post-card">
                  {post.imagenUrl && <img src={post.imagenUrl} alt={post.titulo} />}
                  <div>
                    <h4>{post.titulo}</h4>
                    <p>{post.contenido}</p>
                    <span className="olimpiadas-admin-hint">
                      {post.autor} · <MessageCircle size={12} /> {post.totalComentarios} comentarios
                    </span>
                  </div>
                  <button type="button" className="olimpiadas-icon-btn" onClick={() => handleEliminarPost(post)}>
                    Eliminar
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>
      )}
    </section>
  );
};

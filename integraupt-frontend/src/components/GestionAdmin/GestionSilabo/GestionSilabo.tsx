import React, { useState, useEffect, useCallback } from 'react';
import { getSilaboApiUrl } from '../../../utils/apiConfig';
import {
  BookOpen, Plus, Trash2, ChevronDown, ChevronRight, CheckCircle2,
  XCircle, Upload, Eye, BarChart3, Loader2, Save, Search, X,
  FileText, AlertTriangle, RefreshCw, Edit2, Pencil,
} from 'lucide-react';

interface GestionSilaboProps {
  onAuditLog: (message: string, detail?: string) => void;
}

type Tab = 'silabos' | 'avances' | 'reporte';

const SEMESTRES = ['2025-I', '2025-II', '2026-I', '2026-II', '2027-I', '2027-II', '2028-I', '2028-II'];

// Paleta pálida para diferenciar cursos en la lista
const PALETA_ADMIN = [
  { bg: '#f0f4ff', border: '#c7d2fe', acc: '#4338ca', codeBg: '#e0e7ff' }, // índigo
  { bg: '#f0fdf9', border: '#a7f3d0', acc: '#047857', codeBg: '#d1fae5' }, // esmeralda
  { bg: '#fff7ed', border: '#fed7aa', acc: '#c2410c', codeBg: '#ffedd5' }, // naranja
  { bg: '#fdf4ff', border: '#e9d5ff', acc: '#7e22ce', codeBg: '#f3e8ff' }, // violeta
  { bg: '#eff6ff', border: '#bfdbfe', acc: '#1d4ed8', codeBg: '#dbeafe' }, // azul
  { bg: '#fff1f2', border: '#fecdd3', acc: '#be123c', codeBg: '#ffe4e6' }, // rosa
  { bg: '#f0fdfa', border: '#99f6e4', acc: '#0f766e', codeBg: '#ccfbf1' }, // teal
  { bg: '#fefce8', border: '#fde68a', acc: '#a16207', codeBg: '#fef9c3' }, // amarillo
];
const CICLOS = Array.from({ length: 12 }, (_, i) => i + 1);

type FlujoPaso = 'formulario' | 'parseando' | 'revision' | null;

interface TemaEditable {
  semana: number;
  contenido_conceptual: string;
  contenido_procedimental: string;
  orden: number;
}
interface UnidadEditable {
  numero: number;
  nombre: string;
  horas_total: number;
  temas: TemaEditable[];
}
interface EstructuraParseada {
  codigo_curso: string;
  nombre_curso: string;
  ciclo_numero: number | null;
  semestre: string;
  horas: number | null;
  creditos: number | null;
  docente: string;
  dias_x_semana: number;
  unidades: UnidadEditable[];
}

// Convierte un sílabo de la API al formato editable
function silaboToEstructura(s: any): EstructuraParseada {
  return {
    codigo_curso: s.CodigoCurso ?? '',
    nombre_curso: s.NombreCurso ?? '',
    ciclo_numero: s.CicloNumero ?? null,
    semestre: s.Semestre ?? '',
    horas: s.Horas ?? null,
    creditos: s.Creditos ?? null,
    docente: s.Docente ?? '',
    dias_x_semana: s.DiasXSemana ?? 1,
    unidades: (s.unidades ?? []).map((u: any) => ({
      numero: u.Numero,
      nombre: u.Nombre,
      horas_total: u.HorasTotal ?? 0,
      temas: (u.temas ?? []).map((t: any) => ({
        semana: t.Semana,
        contenido_conceptual: t.ContenidoConceptual ?? '',
        contenido_procedimental: t.ContenidoProcedimental ?? '',
        orden: t.Orden ?? t.Semana,
      })),
    })),
  };
}

export const GestionSilabo: React.FC<GestionSilaboProps> = ({ onAuditLog }) => {
  const [activeTab, setActiveTab] = useState<Tab>('silabos');

  const [silabos, setSilabos]     = useState<any[]>([]);
  const [loading, setLoading]     = useState(false);
  const [expandedSilabo, setExpandedSilabo] = useState<number | null>(null);

  const [filtroQ, setFiltroQ]               = useState('');
  const [filtroCiclo, setFiltroCiclo]       = useState('');
  const [filtroSemestre, setFiltroSemestre] = useState('');

  // Flujo nuevo sílabo
  const [flujo, setFlujo]         = useState<FlujoPaso>(null);
  const [pdfFile, setPdfFile]     = useState<File | null>(null);
  const [parseError, setParseError] = useState<string | null>(null);
  const [estructura, setEstructura] = useState<EstructuraParseada | null>(null);
  const [guardando, setGuardando] = useState(false);
  const [guardadoMsg, setGuardadoMsg] = useState<string | null>(null);
  const [editTema, setEditTema]   = useState<{ui: number; ti: number} | null>(null);

  // Edición de sílabo existente
  const [editandoSilabo, setEditandoSilabo]     = useState<any | null>(null); // el objeto original
  const [editEstructura, setEditEstructura]     = useState<EstructuraParseada | null>(null);
  const [editGuardando, setEditGuardando]       = useState(false);
  const [editMsg, setEditMsg]                   = useState<string | null>(null);
  const [editTemaEdit, setEditTemaEdit]         = useState<{ui: number; ti: number} | null>(null);

  // Confirmación de eliminación
  const [eliminarId, setEliminarId]   = useState<number | null>(null);
  const [eliminando, setEliminando]   = useState(false);

  // Avances
  const [avances, setAvances]         = useState<any[]>([]);
  const [loadingAvances, setLoadingAvances] = useState(false);
  const [filterEstado, setFilterEstado]     = useState('pendiente');
  const [reviewingId, setReviewingId]       = useState<number | null>(null);
  const [obsText, setObsText]               = useState('');
  const [reviewSaving, setReviewSaving]     = useState(false);

  // Reporte
  const [reporteId, setReporteId]     = useState('');
  const [reporte, setReporte]         = useState<any | null>(null);
  const [loadingReporte, setLoadingReporte] = useState(false);

  const fetchSilabos = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (filtroQ)        params.set('q', filtroQ);
      if (filtroCiclo)    params.set('ciclo_numero', filtroCiclo);
      if (filtroSemestre) params.set('semestre', filtroSemestre);
      const res = await fetch(getSilaboApiUrl(`/api/silabos?${params}`));
      if (res.ok) setSilabos(await res.json());
    } catch { /* network */ } finally { setLoading(false); }
  }, [filtroQ, filtroCiclo, filtroSemestre]);

  useEffect(() => { fetchSilabos(); }, [fetchSilabos]);
  useEffect(() => { if (activeTab === 'avances') fetchAvances(); }, [activeTab, filterEstado]);

  const fetchAvances = async () => {
    setLoadingAvances(true);
    try {
      const res = await fetch(getSilaboApiUrl(`/api/avances?estado=${filterEstado}`));
      if (res.ok) setAvances(await res.json());
    } catch { /* network */ } finally { setLoadingAvances(false); }
  };

  // ─── Validación campos obligatorios ────────────────────────────────────────
  const validarEstructura = (e: EstructuraParseada): string | null => {
    if (!e.codigo_curso.trim()) return 'El código del curso es obligatorio (ej: EG-725).';
    if (!e.nombre_curso.trim()) return 'El nombre del curso es obligatorio.';
    if (!e.semestre.trim())     return 'El semestre académico es obligatorio.';
    if (!e.ciclo_numero)        return 'El ciclo académico es obligatorio.';
    if (!e.docente.trim())      return 'El nombre del docente es obligatorio.';
    for (const u of e.unidades) {
      if (!u.nombre.trim()) return `La Unidad ${u.numero} debe tener un nombre.`;
    }
    return null;
  };

  // ─── Nuevo sílabo: parsear PDF ─────────────────────────────────────────────
  const handleParsearPdf = async () => {
    if (!pdfFile) return;
    setFlujo('parseando');
    setParseError(null);
    try {
      const fd = new FormData();
      fd.append('ArchivoPdf', pdfFile);
      const res  = await fetch(getSilaboApiUrl('/api/silabos/parsear-pdf'), { method: 'POST', body: fd });
      const data = await res.json();
      if (!res.ok) { setParseError(data.message || 'Error al procesar el PDF.'); setFlujo('formulario'); return; }
      setEstructura({ ...data, dias_x_semana: data.dias_x_semana ?? 1 });
      setFlujo('revision');
    } catch (e: any) {
      setParseError('Error de conexión: ' + (e.message || ''));
      setFlujo('formulario');
    }
  };

  // ─── Nuevo sílabo: guardar ─────────────────────────────────────────────────
  const handleGuardar = async () => {
    if (!estructura) return;
    const err = validarEstructura(estructura);
    if (err) { setGuardadoMsg(err); return; }
    setGuardando(true);
    setGuardadoMsg(null);
    try {
      const fd = new FormData();
      if (pdfFile) fd.append('ArchivoPdf', pdfFile);
      fd.append('semestre',     estructura.semestre);
      fd.append('ciclo_numero', String(estructura.ciclo_numero ?? ''));
      fd.append('codigo_curso', estructura.codigo_curso);
      fd.append('nombre_curso', estructura.nombre_curso);
      fd.append('horas',        String(estructura.horas ?? ''));
      fd.append('creditos',     String(estructura.creditos ?? ''));
      fd.append('docente',        estructura.docente);
      fd.append('dias_x_semana', String(estructura.dias_x_semana ?? 1));
      fd.append('unidades',      JSON.stringify(estructura.unidades));
      const res  = await fetch(getSilaboApiUrl('/api/silabos'), { method: 'POST', body: fd });
      const data = await res.json();
      if (!res.ok) { setGuardadoMsg(data.message || 'Error al guardar.'); return; }
      onAuditLog('Sílabo creado', `${estructura.codigo_curso} – ${estructura.semestre}`);
      setSilabos(prev => [data, ...prev]);
      setFlujo(null); setPdfFile(null); setEstructura(null);
    } catch { setGuardadoMsg('Error de conexión.'); } finally { setGuardando(false); }
  };

  // ─── Editar sílabo existente ───────────────────────────────────────────────
  const abrirEdicion = (s: any) => {
    setEditandoSilabo(s);
    setEditEstructura(silaboToEstructura(s));
    setEditMsg(null);
    setEditTemaEdit(null);
  };

  const handleGuardarEdicion = async () => {
    if (!editEstructura || !editandoSilabo) return;
    const err = validarEstructura(editEstructura);
    if (err) { setEditMsg(err); return; }
    setEditGuardando(true);
    setEditMsg(null);
    try {
      const fd = new FormData();
      fd.append('semestre',     editEstructura.semestre);
      fd.append('ciclo_numero', String(editEstructura.ciclo_numero ?? ''));
      fd.append('codigo_curso', editEstructura.codigo_curso);
      fd.append('nombre_curso', editEstructura.nombre_curso);
      fd.append('horas',        String(editEstructura.horas ?? ''));
      fd.append('creditos',     String(editEstructura.creditos ?? ''));
      fd.append('docente',      editEstructura.docente);
      fd.append('unidades',     JSON.stringify(editEstructura.unidades));
      const res  = await fetch(getSilaboApiUrl(`/api/silabos/${editandoSilabo.IdSilabo}`), { method: 'POST', body: fd });
      const data = await res.json();
      if (!res.ok) { setEditMsg(data.message || 'Error al guardar.'); return; }
      onAuditLog('Sílabo modificado', `${editEstructura.codigo_curso} – ${editEstructura.semestre}`);
      setSilabos(prev => prev.map(s => s.IdSilabo === data.IdSilabo ? data : s));
      setEditandoSilabo(null); setEditEstructura(null);
    } catch { setEditMsg('Error de conexión.'); } finally { setEditGuardando(false); }
  };

  // ─── Eliminar sílabo ───────────────────────────────────────────────────────
  const handleEliminar = async () => {
    if (!eliminarId) return;
    setEliminando(true);
    try {
      const s   = silabos.find(x => x.IdSilabo === eliminarId);
      const res = await fetch(getSilaboApiUrl(`/api/silabos/${eliminarId}`), { method: 'DELETE' });
      if (res.ok) {
        onAuditLog('Sílabo eliminado', s ? `${s.CodigoCurso} – ${s.Semestre}` : `ID ${eliminarId}`);
        setSilabos(prev => prev.filter(x => x.IdSilabo !== eliminarId));
        setEliminarId(null);
      }
    } finally { setEliminando(false); }
  };

  // ─── Helpers estructura editable (reutilizados para nuevo y edición) ───────
  const makeUnidadOps = (
    setter: React.Dispatch<React.SetStateAction<EstructuraParseada | null>>
  ) => ({
    updateUnidad: (ui: number, field: keyof UnidadEditable, value: any) =>
      setter(prev => {
        if (!prev) return prev;
        const u = [...prev.unidades];
        u[ui] = { ...u[ui], [field]: value };
        return { ...prev, unidades: u };
      }),
    updateTema: (ui: number, ti: number, field: keyof TemaEditable, value: any) =>
      setter(prev => {
        if (!prev) return prev;
        const u = [...prev.unidades];
        const t = [...u[ui].temas];
        t[ti] = { ...t[ti], [field]: value };
        u[ui] = { ...u[ui], temas: t };
        return { ...prev, unidades: u };
      }),
    addTema: (ui: number) =>
      setter(prev => {
        if (!prev) return prev;
        const u = [...prev.unidades];
        const max = Math.max(0, ...u[ui].temas.map(t => t.semana));
        u[ui] = { ...u[ui], temas: [...u[ui].temas, { semana: max + 1, contenido_conceptual: '', contenido_procedimental: '', orden: max + 1 }] };
        return { ...prev, unidades: u };
      }),
    removeTema: (ui: number, ti: number) =>
      setter(prev => {
        if (!prev) return prev;
        const u = [...prev.unidades];
        u[ui] = { ...u[ui], temas: u[ui].temas.filter((_, i) => i !== ti) };
        return { ...prev, unidades: u };
      }),
  });

  const nuevoOps = makeUnidadOps(setEstructura);
  const editOps  = makeUnidadOps(setEditEstructura);

  // ─── Avances ───────────────────────────────────────────────────────────────
  const revisarAvance = async (avanceId: number, estado: 'aprobado' | 'rechazado') => {
    setReviewSaving(true);
    try {
      const res = await fetch(getSilaboApiUrl(`/api/avances/${avanceId}/estado`), {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ Estado: estado, ObservacionCoordinador: obsText }),
      });
      if (res.ok) {
        setAvances(prev => prev.filter(a => a.IdAvance !== avanceId));
        setReviewingId(null); setObsText('');
        onAuditLog(`Avance ${estado}`, `ID ${avanceId}`);
      }
    } finally { setReviewSaving(false); }
  };

  // ─── UI helpers ────────────────────────────────────────────────────────────
  const TAB = (active: boolean): React.CSSProperties => ({
    padding: '8px 18px', border: 'none', cursor: 'pointer', fontWeight: 600, fontSize: 13,
    borderBottom: active ? '2px solid #6366f1' : '2px solid transparent',
    color: active ? '#6366f1' : '#64748b', background: 'none',
  });

  const BADGE = (estado: string) => {
    const map: Record<string, {bg: string; color: string}> = {
      aprobado: {bg:'#dcfce7',color:'#166534'}, pendiente: {bg:'#fef3c7',color:'#92400e'}, rechazado: {bg:'#fee2e2',color:'#991b1b'},
    };
    const s = map[estado] || {bg:'#f1f5f9',color:'#475569'};
    return <span style={{background:s.bg,color:s.color,borderRadius:5,padding:'2px 8px',fontSize:11,fontWeight:600}}>{estado}</span>;
  };

  // ─── Panel edición de estructura (reutilizable) ────────────────────────────
  const renderEstructuraForm = (
    e: EstructuraParseada,
    setter: React.Dispatch<React.SetStateAction<EstructuraParseada | null>>,
    ops: ReturnType<typeof makeUnidadOps>,
    temaEditState: {ui:number;ti:number}|null,
    setTemaEdit: (v:{ui:number;ti:number}|null) => void,
    msg: string|null,
    onGuardar: () => void,
    guardandoFlag: boolean,
    btnExtra?: React.ReactNode,
  ) => (
    <div>
      {/* Aviso campos obligatorios */}
      {msg && (
        <div style={{background:'#fef2f2',border:'1px solid #fecaca',borderRadius:8,padding:'10px 14px',marginBottom:14,fontSize:13,color:'#b91c1c',display:'flex',gap:8}}>
          <AlertTriangle size={15} style={{flexShrink:0}} /> {msg}
        </div>
      )}

      {/* Datos generales */}
      <div style={{background:'#f8fafc',border:'1px solid #e2e8f0',borderRadius:8,padding:14,marginBottom:16}}>
        <div style={{display:'grid',gridTemplateColumns:'1fr 1fr',gap:10,marginBottom:10}}>
          <div>
            <label style={LBL}>Código del curso *</label>
            <input required value={e.codigo_curso} onChange={ev => setter(p => p ? {...p,codigo_curso:ev.target.value} : p)}
              style={{...IN, borderColor: !e.codigo_curso.trim() ? '#f87171' : '#cbd5e1'}} placeholder="Ej: EG-725" />
            {e.ciclo_numero && <span style={{fontSize:11,color:'#6366f1'}}>→ Ciclo {e.ciclo_numero} (auto-detectado)</span>}
          </div>
          <div>
            <label style={LBL}>Semestre *</label>
            <select value={e.semestre} onChange={ev => setter(p => p ? {...p,semestre:ev.target.value} : p)}
              style={{...IN, borderColor: !e.semestre ? '#f87171' : '#cbd5e1'}}>
              <option value="">— Seleccionar —</option>
              {SEMESTRES.map(s => <option key={s} value={s}>{s}</option>)}
            </select>
          </div>
        </div>
        <div style={{marginBottom:10}}>
          <label style={LBL}>Nombre del curso *</label>
          <input required value={e.nombre_curso} onChange={ev => setter(p => p ? {...p,nombre_curso:ev.target.value} : p)}
            style={{...IN, borderColor: !e.nombre_curso.trim() ? '#f87171' : '#cbd5e1'}} placeholder="Ej: Matemática Básica" />
        </div>
        <div style={{display:'grid',gridTemplateColumns:'1fr 1fr 1fr 1fr',gap:10}}>
          <div>
            <label style={LBL}>Horas</label>
            <input type="number" value={e.horas ?? ''} onChange={ev => setter(p => p ? {...p,horas:Number(ev.target.value)} : p)} style={IN} />
          </div>
          <div>
            <label style={LBL}>Créditos</label>
            <input type="number" value={e.creditos ?? ''} onChange={ev => setter(p => p ? {...p,creditos:Number(ev.target.value)} : p)} style={IN} />
          </div>
          <div>
            <label style={LBL}>Ciclo (1-12) *</label>
            <select value={e.ciclo_numero ?? ''} onChange={ev => setter(p => p ? {...p,ciclo_numero:Number(ev.target.value)||null} : p)}
              style={{...IN, borderColor: !e.ciclo_numero ? '#f87171' : '#cbd5e1'}}>
              <option value="">— Seleccionar —</option>
              {CICLOS.map(c => <option key={c} value={c}>{c}</option>)}
            </select>
          </div>
          <div>
            <label style={LBL}>Sesiones/semana *</label>
            <select value={e.dias_x_semana ?? 1} onChange={ev => setter(p => p ? {...p,dias_x_semana:Number(ev.target.value)} : p)} style={IN}>
              <option value={1}>1 sesión</option>
              <option value={2}>2 sesiones</option>
              <option value={3}>3 sesiones</option>
            </select>
          </div>
        </div>
        <div style={{marginTop:10}}>
          <label style={LBL}>Docente *</label>
          <input required value={e.docente} onChange={ev => setter(p => p ? {...p,docente:ev.target.value} : p)}
            style={{...IN, borderColor: !e.docente.trim() ? '#f87171' : '#cbd5e1'}} placeholder="Nombre completo del docente" />
        </div>
      </div>

      {/* Unidades */}
      {e.unidades.length === 0 && (
        <div style={{background:'#fffbeb',border:'1px solid #fde68a',borderRadius:8,padding:12,marginBottom:14,fontSize:13,color:'#92400e',display:'flex',gap:8}}>
          <AlertTriangle size={15}/> No se detectaron unidades. Agrégalas manualmente.
        </div>
      )}

      {e.unidades.map((unidad, ui) => (
        <div key={ui} style={{border:'1px solid #e2e8f0',borderRadius:10,marginBottom:12,overflow:'hidden'}}>
          <div style={{background:'#f0f4ff',padding:'10px 14px',display:'flex',gap:10,alignItems:'center'}}>
            <span style={{fontWeight:700,fontSize:13,color:'#4338ca',minWidth:24}}>U{unidad.numero}</span>
            <input value={unidad.nombre} onChange={ev => ops.updateUnidad(ui,'nombre',ev.target.value)}
              style={{...IN,flex:1,fontWeight:600, borderColor: !unidad.nombre.trim() ? '#f87171' : '#cbd5e1'}}
              placeholder="Nombre de la unidad *" />
            <input type="number" value={unidad.horas_total}
              onChange={ev => ops.updateUnidad(ui,'horas_total',Number(ev.target.value))}
              style={{...IN,width:70}} placeholder="Horas" title="Total de horas" />
            <span style={{fontSize:11,color:'#94a3b8',whiteSpace:'nowrap'}}>h</span>
          </div>
          <div style={{padding:'10px 14px'}}>
            {unidad.temas.length === 0 && (
              <p style={{color:'#94a3b8',fontSize:12,margin:'0 0 8px'}}>Sin temas detectados.</p>
            )}
            {unidad.temas.map((tema, ti) => {
              const isEditing = temaEditState?.ui === ui && temaEditState?.ti === ti;
              return (
                <div key={ti} style={{border:'1px solid #f1f5f9',borderRadius:7,marginBottom:8,overflow:'hidden'}}>
                  <div style={{display:'flex',alignItems:'flex-start',gap:8,padding:'8px 10px'}}>
                    <span style={{fontSize:11,background:'#e0e7ff',color:'#4338ca',borderRadius:4,padding:'2px 6px',fontWeight:700,whiteSpace:'nowrap',marginTop:1}}>
                      S{tema.semana}
                    </span>
                    <div style={{flex:1,fontSize:12,color:'#334155',lineHeight:1.4}}>
                      {tema.contenido_conceptual || <span style={{color:'#94a3b8'}}>Sin contenido conceptual</span>}
                    </div>
                    <div style={{display:'flex',gap:4}}>
                      <button onClick={() => setTemaEdit(isEditing ? null : {ui,ti})} style={{...BTN_ICON,color:'#6366f1'}}><Edit2 size={12}/></button>
                      <button onClick={() => ops.removeTema(ui,ti)} style={{...BTN_ICON,color:'#ef4444'}}><Trash2 size={12}/></button>
                    </div>
                  </div>
                  {isEditing && (
                    <div style={{background:'#f8fafc',padding:'10px 12px',borderTop:'1px solid #f1f5f9'}}>
                      <div style={{display:'grid',gridTemplateColumns:'70px 1fr',gap:8,marginBottom:8}}>
                        <div>
                          <label style={LBL}>Semana</label>
                          <input type="number" value={tema.semana} onChange={ev => ops.updateTema(ui,ti,'semana',Number(ev.target.value))} min="1" max="17" style={IN}/>
                        </div>
                        <div>
                          <label style={LBL}>Contenido Conceptual</label>
                          <textarea value={tema.contenido_conceptual} onChange={ev => ops.updateTema(ui,ti,'contenido_conceptual',ev.target.value)} rows={3} style={{...IN,resize:'vertical'}}/>
                        </div>
                      </div>
                      <div>
                        <label style={LBL}>Contenido Procedimental</label>
                        <textarea value={tema.contenido_procedimental} onChange={ev => ops.updateTema(ui,ti,'contenido_procedimental',ev.target.value)} rows={3} style={{...IN,resize:'vertical'}}/>
                      </div>
                      <button onClick={() => setTemaEdit(null)} style={{...BTN_PRI,marginTop:8,padding:'5px 12px',fontSize:12}}>
                        <CheckCircle2 size={12}/> Listo
                      </button>
                    </div>
                  )}
                </div>
              );
            })}
            <button onClick={() => ops.addTema(ui)} style={{...BTN_SEC,fontSize:11,marginTop:4}}>
              <Plus size={11}/> Agregar semana
            </button>
          </div>
        </div>
      ))}

      <div style={{display:'flex',gap:8,justifyContent:'flex-end',marginTop:8}}>
        {btnExtra}
        <button onClick={onGuardar} disabled={guardandoFlag} style={{...BTN_PRI,opacity:guardandoFlag?.6:1}}>
          {guardandoFlag ? <Loader2 size={13} style={{animation:'spin 1s linear infinite'}}/> : <Save size={13}/>}
          {guardandoFlag ? ' Guardando...' : ' Guardar sílabo'}
        </button>
      </div>
    </div>
  );

  // ─── Render ───────────────────────────────────────────────────────────────
  return (
    <div>
      <div style={{display:'flex',alignItems:'center',gap:10,marginBottom:20}}>
        <BookOpen size={22} color="#6366f1"/>
        <h2 style={{margin:0,fontSize:18,fontWeight:700,color:'#1e293b'}}>Gestión de Sílabos</h2>
      </div>

      <div style={{borderBottom:'1px solid #e2e8f0',marginBottom:20,display:'flex',gap:4}}>
        <button style={TAB(activeTab==='silabos')} onClick={() => setActiveTab('silabos')}>
          <BookOpen size={13} style={{marginRight:5,verticalAlign:'middle'}}/>Sílabos
        </button>
        <button style={TAB(activeTab==='avances')} onClick={() => setActiveTab('avances')}>
          <CheckCircle2 size={13} style={{marginRight:5,verticalAlign:'middle'}}/>Revisar Avances
        </button>
        <button style={TAB(activeTab==='reporte')} onClick={() => setActiveTab('reporte')}>
          <BarChart3 size={13} style={{marginRight:5,verticalAlign:'middle'}}/>Reporte
        </button>
      </div>

      {/* ══════════ TAB SÍLABOS ══════════ */}
      {activeTab === 'silabos' && (
        <div>
          {/* Filtros */}
          <div style={{display:'flex',gap:8,marginBottom:14,flexWrap:'wrap',alignItems:'center'}}>
            <div style={{position:'relative',flex:'1 1 160px'}}>
              <Search size={13} style={{position:'absolute',left:9,top:'50%',transform:'translateY(-50%)',color:'#94a3b8'}}/>
              <input value={filtroQ} onChange={e => setFiltroQ(e.target.value)} placeholder="Buscar curso..." style={{...IN,paddingLeft:28}}/>
            </div>
            <select value={filtroCiclo} onChange={e => setFiltroCiclo(e.target.value)} style={{...IN,flex:'0 0 120px'}}>
              <option value="">Todos los ciclos</option>
              {CICLOS.map(c => <option key={c} value={c}>Ciclo {c}</option>)}
            </select>
            <select value={filtroSemestre} onChange={e => setFiltroSemestre(e.target.value)} style={{...IN,flex:'0 0 130px'}}>
              <option value="">Todos los semestres</option>
              {SEMESTRES.map(s => <option key={s} value={s}>{s}</option>)}
            </select>
            {(filtroQ||filtroCiclo||filtroSemestre) && (
              <button onClick={() => {setFiltroQ('');setFiltroCiclo('');setFiltroSemestre('');}} style={BTN_SEC}><X size={12}/> Limpiar</button>
            )}
            <button onClick={() => {setFlujo('formulario');setParseError(null);setPdfFile(null);setEstructura(null);}} style={BTN_PRI}>
              <Plus size={14}/> Nuevo sílabo
            </button>
          </div>

          {/* Modal: Nuevo sílabo */}
          {flujo && (
            <div style={{position:'fixed',inset:0,background:'rgba(0,0,0,.5)',zIndex:1000,display:'flex',alignItems:'center',justifyContent:'center',padding:16}}>
              <div style={{background:'#fff',borderRadius:14,width:'100%',maxWidth:flujo==='revision'?780:480,maxHeight:'90vh',overflow:'auto',boxShadow:'0 20px 60px rgba(0,0,0,.25)'}}>

                {/* Paso 1: subir PDF */}
                {(flujo==='formulario'||flujo==='parseando') && (
                  <div style={{padding:28}}>
                    <div style={{display:'flex',alignItems:'center',justifyContent:'space-between',marginBottom:20}}>
                      <h3 style={{margin:0,fontSize:16,fontWeight:700,color:'#1e293b',display:'flex',alignItems:'center',gap:8}}>
                        <Upload size={18} color="#6366f1"/> Cargar sílabo desde PDF
                      </h3>
                      <button onClick={() => setFlujo(null)} style={{background:'none',border:'none',cursor:'pointer',color:'#94a3b8'}}><X size={18}/></button>
                    </div>
                    <div style={{background:'#eff6ff',border:'1px solid #bfdbfe',borderRadius:8,padding:12,marginBottom:18,fontSize:13,color:'#1d4ed8'}}>
                      <strong>¿Cómo funciona?</strong> Sube el PDF y el sistema extraerá automáticamente las unidades y temas. Luego revisas y corriges lo que sea necesario.
                    </div>
                    <div style={{marginBottom:14}}>
                      <label style={LBL}>PDF del sílabo *</label>
                      <div style={{border:`2px dashed ${pdfFile?'#6366f1':'#cbd5e1'}`,borderRadius:10,padding:24,textAlign:'center',cursor:'pointer',background:pdfFile?'#f5f3ff':'#fafafa'}}
                        onClick={() => document.getElementById('pdf-upload-nuevo')?.click()}>
                        <input id="pdf-upload-nuevo" type="file" accept="application/pdf" style={{display:'none'}}
                          onChange={e => {setPdfFile(e.target.files?.[0]||null);setParseError(null);}}/>
                        {pdfFile ? (
                          <div style={{color:'#6366f1',fontWeight:600}}>
                            <FileText size={24} style={{marginBottom:6}}/>
                            <div style={{fontSize:13}}>{pdfFile.name}</div>
                            <div style={{fontSize:11,color:'#94a3b8',marginTop:2}}>{(pdfFile.size/1024).toFixed(0)} KB</div>
                          </div>
                        ) : (
                          <div style={{color:'#94a3b8'}}>
                            <Upload size={24} style={{marginBottom:6}}/>
                            <div style={{fontSize:13}}>Haz clic o arrastra el PDF aquí</div>
                          </div>
                        )}
                      </div>
                    </div>
                    {parseError && (
                      <div style={{background:'#fef2f2',border:'1px solid #fecaca',borderRadius:8,padding:'10px 14px',marginBottom:14,fontSize:13,color:'#b91c1c',display:'flex',gap:8}}>
                        <AlertTriangle size={15} style={{flexShrink:0}}/> {parseError}
                      </div>
                    )}
                    <div style={{display:'flex',gap:8,justifyContent:'flex-end'}}>
                      <button onClick={() => setFlujo(null)} style={BTN_SEC}>Cancelar</button>
                      <button onClick={handleParsearPdf} disabled={!pdfFile||flujo==='parseando'}
                        style={{...BTN_PRI,opacity:!pdfFile||flujo==='parseando'?.6:1}}>
                        {flujo==='parseando' ? <><Loader2 size={13} style={{animation:'spin 1s linear infinite'}}/> Procesando...</> : <><FileText size={13}/> Leer PDF</>}
                      </button>
                    </div>
                  </div>
                )}

                {/* Paso 2: revisión */}
                {flujo==='revision' && estructura && (
                  <div style={{padding:28}}>
                    <div style={{display:'flex',alignItems:'center',justifyContent:'space-between',marginBottom:6}}>
                      <h3 style={{margin:0,fontSize:16,fontWeight:700,color:'#1e293b'}}>Revisa y completa la información</h3>
                      <button onClick={() => setFlujo(null)} style={{background:'none',border:'none',cursor:'pointer',color:'#94a3b8'}}><X size={18}/></button>
                    </div>
                    <p style={{margin:'0 0 14px',fontSize:13,color:'#64748b'}}>
                      Los campos marcados con <strong>*</strong> son obligatorios. Corrige cualquier dato incorrecto antes de guardar.
                    </p>
                    {renderEstructuraForm(
                      estructura, setEstructura, nuevoOps,
                      editTema, setEditTema,
                      guardadoMsg, handleGuardar, guardando,
                      <button onClick={() => setFlujo('formulario')} style={{...BTN_SEC,display:'flex',alignItems:'center',gap:5}}>
                        <RefreshCw size={12}/> Subir otro PDF
                      </button>
                    )}
                  </div>
                )}
              </div>
            </div>
          )}

          {/* Modal: Editar sílabo existente */}
          {editandoSilabo && editEstructura && (
            <div style={{position:'fixed',inset:0,background:'rgba(0,0,0,.5)',zIndex:1000,display:'flex',alignItems:'center',justifyContent:'center',padding:16}}>
              <div style={{background:'#fff',borderRadius:14,width:'100%',maxWidth:780,maxHeight:'90vh',overflow:'auto',boxShadow:'0 20px 60px rgba(0,0,0,.25)'}}>
                <div style={{padding:28}}>
                  <div style={{display:'flex',alignItems:'center',justifyContent:'space-between',marginBottom:6}}>
                    <h3 style={{margin:0,fontSize:16,fontWeight:700,color:'#1e293b',display:'flex',alignItems:'center',gap:8}}>
                      <Pencil size={16} color="#6366f1"/> Editar sílabo
                    </h3>
                    <button onClick={() => {setEditandoSilabo(null);setEditEstructura(null);}} style={{background:'none',border:'none',cursor:'pointer',color:'#94a3b8'}}><X size={18}/></button>
                  </div>
                  <p style={{margin:'0 0 14px',fontSize:13,color:'#64748b'}}>
                    Modifica los datos del sílabo. Los campos con <strong>*</strong> son obligatorios.
                  </p>
                  {renderEstructuraForm(
                    editEstructura, setEditEstructura, editOps,
                    editTemaEdit, setEditTemaEdit,
                    editMsg, handleGuardarEdicion, editGuardando,
                    <button onClick={() => {setEditandoSilabo(null);setEditEstructura(null);}} style={BTN_SEC}>
                      Cancelar
                    </button>
                  )}
                </div>
              </div>
            </div>
          )}

          {/* Modal: Confirmar eliminación */}
          {eliminarId && (
            <div style={{position:'fixed',inset:0,background:'rgba(0,0,0,.5)',zIndex:1100,display:'flex',alignItems:'center',justifyContent:'center',padding:16}}>
              <div style={{background:'#fff',borderRadius:14,width:'100%',maxWidth:400,padding:28,boxShadow:'0 20px 60px rgba(0,0,0,.25)'}}>
                <div style={{display:'flex',gap:12,marginBottom:16}}>
                  <div style={{width:44,height:44,background:'#fef2f2',borderRadius:99,display:'flex',alignItems:'center',justifyContent:'center',flexShrink:0}}>
                    <Trash2 size={22} color="#ef4444"/>
                  </div>
                  <div>
                    <h3 style={{margin:'0 0 6px',fontSize:16,fontWeight:700,color:'#1e293b'}}>¿Eliminar sílabo?</h3>
                    <p style={{margin:0,fontSize:13,color:'#64748b'}}>
                      Esta acción eliminará el sílabo y todos sus temas y avances registrados. No se puede deshacer.
                    </p>
                  </div>
                </div>
                <div style={{display:'flex',gap:8,justifyContent:'flex-end'}}>
                  <button onClick={() => setEliminarId(null)} style={BTN_SEC}>Cancelar</button>
                  <button onClick={handleEliminar} disabled={eliminando}
                    style={{...BTN_PRI,background:'#ef4444',opacity:eliminando?.6:1}}>
                    {eliminando ? <Loader2 size={13} style={{animation:'spin 1s linear infinite'}}/> : <Trash2 size={13}/>}
                    {eliminando ? ' Eliminando...' : ' Sí, eliminar'}
                  </button>
                </div>
              </div>
            </div>
          )}

          {/* Lista */}
          {loading && <div style={{textAlign:'center',padding:40}}><Loader2 size={24} color="#6366f1"/></div>}

          {!loading && silabos.length === 0 && (
            <div style={{textAlign:'center',padding:50,color:'#94a3b8'}}>
              <BookOpen size={36} color="#cbd5e1" style={{marginBottom:8}}/>
              <p>No hay sílabos registrados{filtroQ||filtroCiclo||filtroSemestre?' con estos filtros':''}.</p>
            </div>
          )}

          {silabos.map((s: any, idx: number) => {
            const expanded = expandedSilabo === s.IdSilabo;
            const pal = PALETA_ADMIN[idx % PALETA_ADMIN.length];
            return (
              <div key={s.IdSilabo} style={{border:`1px solid ${pal.border}`,borderRadius:10,marginBottom:10,overflow:'hidden'}}>
                <div style={{display:'flex',alignItems:'center',background:pal.bg}}>
                  {/* Botón expandir */}
                  <button onClick={() => setExpandedSilabo(expanded?null:s.IdSilabo)}
                    style={{flex:1,display:'flex',alignItems:'center',gap:10,padding:'12px 16px',background:'none',border:'none',cursor:'pointer',textAlign:'left'}}>
                    {expanded ? <ChevronDown size={15} color={pal.acc}/> : <ChevronRight size={15} color={pal.acc}/>}
                    <div>
                      <div style={{fontWeight:700,fontSize:13,color:'#1e293b'}}>
                        {s.CodigoCurso && (
                          <span style={{fontFamily:'monospace',background:pal.codeBg,color:pal.acc,borderRadius:4,padding:'1px 6px',marginRight:6,fontSize:12,fontWeight:700}}>
                            {s.CodigoCurso}
                          </span>
                        )}
                        {s.NombreCurso||`Sílabo #${s.IdSilabo}`}
                      </div>
                      <div style={{fontSize:11,color:'#64748b',marginTop:2}}>
                        {s.Semestre&&<span style={{marginRight:10}}>📅 {s.Semestre}</span>}
                        {s.CicloNumero&&<span style={{marginRight:10}}>Ciclo {s.CicloNumero}</span>}
                        {s.Docente&&<span>👤 {s.Docente}</span>}
                      </div>
                    </div>
                  </button>

                  {/* Acciones */}
                  <div style={{display:'flex',gap:6,alignItems:'center',paddingRight:12}}>
                    {s.ArchivoPdf && (
                      <a href={getSilaboApiUrl('/'+s.ArchivoPdf)} target="_blank" rel="noreferrer"
                        style={{fontSize:11,color:pal.acc,display:'flex',alignItems:'center',gap:3,textDecoration:'none'}}>
                        <Eye size={12}/> PDF
                      </a>
                    )}
                    <span style={{fontSize:11,color:'#94a3b8',marginRight:4}}>
                      {s.unidades?.length??0}U · {s.unidades?.reduce((t:number,u:any)=>t+(u.temas?.length??0),0)??0}T
                    </span>
                    <button onClick={() => abrirEdicion(s)} title="Editar"
                      style={{...BTN_ICON,color:pal.acc,border:`1px solid ${pal.codeBg}`,borderRadius:6,padding:'4px 8px'}}>
                      <Pencil size={13}/>
                    </button>
                    <button onClick={() => setEliminarId(s.IdSilabo)} title="Eliminar"
                      style={{...BTN_ICON,color:'#ef4444',border:'1px solid #fee2e2',borderRadius:6,padding:'4px 8px'}}>
                      <Trash2 size={13}/>
                    </button>
                  </div>
                </div>

                {expanded && (
                  <div style={{padding:'12px 16px',background:'#fff'}}>
                    {(s.unidades??[]).map((u:any) => (
                      <div key={u.IdUnidad} style={{marginBottom:10}}>
                        <div style={{fontWeight:600,fontSize:13,color:pal.acc,marginBottom:4}}>
                          Unidad {u.Numero}: {u.Nombre} ({u.HorasTotal}h)
                        </div>
                        {(u.temas??[]).map((t:any) => (
                          <div key={t.IdTema} style={{display:'flex',gap:8,padding:'4px 0',borderBottom:'1px solid #f8fafc',fontSize:12}}>
                            <span style={{fontSize:10,background:pal.codeBg,color:pal.acc,borderRadius:4,padding:'1px 5px',whiteSpace:'nowrap',fontWeight:600}}>S{t.Semana}</span>
                            <span style={{color:'#334155',flex:1}}>{t.ContenidoConceptual||'—'}</span>
                          </div>
                        ))}
                      </div>
                    ))}
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}

      {/* ══════════ TAB AVANCES ══════════ */}
      {activeTab === 'avances' && (
        <div>
          <div style={{display:'flex',gap:8,marginBottom:16}}>
            {(['pendiente','aprobado','rechazado'] as const).map(e => (
              <button key={e} onClick={() => {setFilterEstado(e);setReviewingId(null);}}
                style={{padding:'6px 14px',borderRadius:7,border:'1px solid #e2e8f0',cursor:'pointer',fontSize:12,fontWeight:600,
                  background:filterEstado===e?'#6366f1':'#fff',color:filterEstado===e?'#fff':'#64748b'}}>
                {e}
              </button>
            ))}
          </div>
          {loadingAvances && <div style={{textAlign:'center',padding:30}}><Loader2 size={24} color="#6366f1"/></div>}
          {!loadingAvances && avances.length === 0 && (
            <div style={{textAlign:'center',padding:40,color:'#94a3b8'}}>
              <CheckCircle2 size={32} color="#cbd5e1" style={{marginBottom:8}}/>
              <p>No hay avances en estado "{filterEstado}".</p>
            </div>
          )}
          {avances.map((a:any) => (
            <div key={a.IdAvance} style={{border:'1px solid #e2e8f0',borderRadius:10,padding:14,marginBottom:10}}>
              <div style={{display:'flex',justifyContent:'space-between',alignItems:'flex-start',gap:10}}>
                <div style={{flex:1}}>
                  <div style={{display:'flex',alignItems:'center',gap:8,marginBottom:6}}>
                    {BADGE(a.Estado)}
                    <span style={{fontSize:12,color:'#64748b'}}>Docente ID: {a.DocenteId}</span>
                    <span style={{fontSize:12,color:'#94a3b8'}}>{a.FechaClase?new Date(a.FechaClase).toLocaleDateString('es-PE'):''}</span>
                  </div>
                  <p style={{margin:0,fontSize:13,color:'#334155',fontWeight:500}}>
                    {a.tema?.ContenidoConceptual||`Tema ID: ${a.SilaboTemaId}`}
                  </p>
                  {a.Comentario&&<p style={{margin:'4px 0 0',fontSize:12,color:'#6366f1'}}>💬 {a.Comentario}</p>}
                </div>
                {a.Estado==='pendiente' && (
                  <button onClick={() => {setReviewingId(a.IdAvance);setObsText('');}}
                    style={{padding:'6px 12px',background:'#f8fafc',border:'1px solid #e2e8f0',borderRadius:7,cursor:'pointer',fontSize:12}}>
                    Revisar
                  </button>
                )}
              </div>
              {reviewingId===a.IdAvance && (
                <div style={{marginTop:12,background:'#f8fafc',borderRadius:8,padding:14}}>
                  <label style={LBL}>Observación (opcional)</label>
                  <textarea value={obsText} onChange={e=>setObsText(e.target.value)} rows={2}
                    placeholder="Ej: Documentación completa." style={{...IN,resize:'vertical',marginBottom:10}}/>
                  <div style={{display:'flex',gap:8}}>
                    <button onClick={() => revisarAvance(a.IdAvance,'aprobado')} disabled={reviewSaving}
                      style={{display:'flex',alignItems:'center',gap:5,background:'#22c55e',color:'#fff',border:'none',borderRadius:7,padding:'7px 14px',cursor:'pointer',fontSize:12,fontWeight:600}}>
                      <CheckCircle2 size={13}/> Aprobar
                    </button>
                    <button onClick={() => revisarAvance(a.IdAvance,'rechazado')} disabled={reviewSaving}
                      style={{display:'flex',alignItems:'center',gap:5,background:'#ef4444',color:'#fff',border:'none',borderRadius:7,padding:'7px 14px',cursor:'pointer',fontSize:12,fontWeight:600}}>
                      <XCircle size={13}/> Rechazar
                    </button>
                    <button onClick={() => setReviewingId(null)} style={BTN_SEC}>Cancelar</button>
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {/* ══════════ TAB REPORTE ══════════ */}
      {activeTab === 'reporte' && (
        <div>
          <div style={{display:'flex',gap:8,marginBottom:20}}>
            <select value={reporteId} onChange={e => setReporteId(e.target.value)} style={{...IN,flex:1}}>
              <option value="">Selecciona un sílabo...</option>
              {silabos.map((s:any) => (
                <option key={s.IdSilabo} value={s.IdSilabo}>
                  {s.CodigoCurso?`${s.CodigoCurso} – `:''}{s.NombreCurso||`#${s.IdSilabo}`} ({s.Semestre})
                </option>
              ))}
            </select>
            <button onClick={async () => {
              if (!reporteId) return;
              setLoadingReporte(true);
              try {
                const res = await fetch(getSilaboApiUrl(`/api/silabos/${reporteId}/avance`));
                if (res.ok) setReporte(await res.json());
              } finally { setLoadingReporte(false); }
            }} disabled={!reporteId||loadingReporte} style={BTN_PRI}>
              {loadingReporte?<Loader2 size={13}/>:<Eye size={13}/>} Ver reporte
            </button>
          </div>
          {reporte && (
            <div>
              <div style={{background:'#fff',border:'1px solid #e2e8f0',borderRadius:10,padding:20,marginBottom:16}}>
                <div style={{display:'flex',justifyContent:'space-between',marginBottom:8}}>
                  <span style={{fontWeight:700,color:'#1e293b'}}>Avance general</span>
                  <span style={{fontWeight:700,color:'#6366f1',fontSize:22}}>{reporte.porcentaje}%</span>
                </div>
                <div style={{background:'#e2e8f0',borderRadius:99,height:10,overflow:'hidden'}}>
                  <div style={{width:`${reporte.porcentaje}%`,background:'#6366f1',height:'100%',borderRadius:99}}/>
                </div>
                <div style={{display:'flex',gap:20,marginTop:14}}>
                  {[{label:'Total',value:reporte.total_temas,color:'#6366f1'},{label:'Aprobados',value:reporte.temas_aprobados,color:'#22c55e'},
                    {label:'En revisión',value:reporte.temas_pendientes,color:'#f59e0b'},{label:'Sin avance',value:reporte.temas_sin_avance,color:'#94a3b8'}
                  ].map(s => (
                    <div key={s.label}>
                      <div style={{fontSize:22,fontWeight:700,color:s.color}}>{s.value}</div>
                      <div style={{fontSize:11,color:'#64748b'}}>{s.label}</div>
                    </div>
                  ))}
                </div>
              </div>
              {(reporte.silabo?.unidades??[]).map((u:any) => (
                <div key={u.IdUnidad} style={{border:'1px solid #e2e8f0',borderRadius:8,marginBottom:10,overflow:'hidden'}}>
                  <div style={{background:'#f8fafc',padding:'8px 14px',fontWeight:600,fontSize:13,color:'#334155'}}>
                    U{u.Numero}: {u.Nombre}
                  </div>
                  {(u.temas??[]).map((t:any) => {
                    const aprobado  = t.avances?.some((av:any) => av.Estado==='aprobado');
                    const pendiente = !aprobado && t.avances?.some((av:any) => av.Estado==='pendiente');
                    return (
                      <div key={t.IdTema} style={{display:'flex',alignItems:'center',gap:8,padding:'6px 14px',borderBottom:'1px solid #f8fafc',fontSize:12}}>
                        {aprobado ? <CheckCircle2 size={13} color="#22c55e"/>
                          : pendiente ? <span style={{width:13,height:13,borderRadius:99,border:'2px solid #f59e0b',display:'inline-block'}}/>
                          : <span style={{width:13,height:13,borderRadius:99,border:'2px solid #cbd5e1',display:'inline-block'}}/>}
                        <span style={{fontSize:10,background:'#e0e7ff',color:'#4338ca',borderRadius:4,padding:'1px 5px'}}>S{t.Semana}</span>
                        <span style={{color:'#334155',flex:1}}>{t.ContenidoConceptual||'—'}</span>
                      </div>
                    );
                  })}
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      <style>{`@keyframes spin { to { transform: rotate(360deg); } }`}</style>
    </div>
  );
};

const IN: React.CSSProperties = {
  width:'100%',padding:'7px 10px',border:'1px solid #cbd5e1',borderRadius:6,fontSize:13,boxSizing:'border-box',
};
const LBL: React.CSSProperties = {
  fontSize:11,fontWeight:600,color:'#64748b',display:'block',marginBottom:3,
};
const BTN_PRI: React.CSSProperties = {
  display:'flex',alignItems:'center',gap:5,background:'#6366f1',color:'#fff',border:'none',
  borderRadius:7,padding:'7px 14px',cursor:'pointer',fontSize:12,fontWeight:600,
};
const BTN_SEC: React.CSSProperties = {
  display:'flex',alignItems:'center',gap:5,background:'#fff',color:'#475569',border:'1px solid #e2e8f0',
  borderRadius:7,padding:'7px 14px',cursor:'pointer',fontSize:12,
};
const BTN_ICON: React.CSSProperties = {
  background:'none',border:'none',cursor:'pointer',padding:4,borderRadius:4,display:'flex',alignItems:'center',
};

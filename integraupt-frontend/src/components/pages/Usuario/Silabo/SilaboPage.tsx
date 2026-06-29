import React, { useState, useEffect, useMemo } from 'react';
import { Navbar } from '../Navbar';
import { getSilaboApiUrl } from '../../../../utils/apiConfig';
import {
  BookOpen, CheckCircle2, Clock, XCircle, ChevronDown, ChevronRight,
  Loader2, AlertTriangle, CalendarCheck, MessageSquare, FileText,
} from 'lucide-react';

interface SilaboPageProps {
  user: any;
  onNavigateToInicio: () => void;
  onNavigateToServicios: () => void;
  onNavigateToPerfil: () => void;
  onLogout?: () => void;
  isLoggingOut?: boolean;
}

// Paleta de gradientes — uno distinto por curso
const PALETA = [
  { grad: 'linear-gradient(135deg,#6366f1,#818cf8)', acc: '#6366f1', badge: '#e0e7ff', badgeTxt: '#3730a3' },
  { grad: 'linear-gradient(135deg,#0ea5e9,#38bdf8)', acc: '#0ea5e9', badge: '#e0f2fe', badgeTxt: '#0c4a6e' },
  { grad: 'linear-gradient(135deg,#10b981,#34d399)', acc: '#10b981', badge: '#d1fae5', badgeTxt: '#064e3b' },
  { grad: 'linear-gradient(135deg,#f59e0b,#fbbf24)', acc: '#d97706', badge: '#fef3c7', badgeTxt: '#78350f' },
  { grad: 'linear-gradient(135deg,#ef4444,#f87171)', acc: '#ef4444', badge: '#fee2e2', badgeTxt: '#7f1d1d' },
  { grad: 'linear-gradient(135deg,#8b5cf6,#a78bfa)', acc: '#8b5cf6', badge: '#ede9fe', badgeTxt: '#4c1d95' },
];

const ESTADO: Record<string, { label: string; icon: React.ReactNode; color: string }> = {
  aprobado:  { label: 'Aprobado',     icon: <CheckCircle2 size={12} />, color: '#22c55e' },
  pendiente: { label: 'En revisión',  icon: <Clock size={12} />,        color: '#f59e0b' },
  rechazado: { label: 'Rechazado',    icon: <XCircle size={12} />,      color: '#ef4444' },
};

// Etiquetas de sesión según su índice (1-based)
const SESION_LABEL = ['Sesión teórica', 'Sesión práctica', 'Laboratorio / Repaso'];

export const SilaboPage: React.FC<SilaboPageProps> = ({
  user, onNavigateToInicio, onNavigateToServicios, onNavigateToPerfil, onLogout, isLoggingOut = false,
}) => {
  const displayName = user.user_metadata?.name?.trim() || user.user_metadata?.codigo || 'Usuario';
  const docenteId   = user.id;

  // Estudiantes solo ven; docentes y cualquier otro rol pueden registrar
  const rol = (user.user_metadata?.role ?? '').toLowerCase();
  const esDocente = !['estudiante', 'student', 'alumno'].includes(rol);

  const [silabos, setSilabos]         = useState<any[]>([]);
  const [loading, setLoading]         = useState(true);
  const [error, setError]             = useState<string | null>(null);
  const [expandedUnidades, setExpandedUnidades] = useState<Set<number>>(new Set());
  const [avances, setAvances]         = useState<any[]>([]);

  // Modal registrar avance (solo docentes)
  const [modalInfo, setModalInfo]     = useState<{ tema: any; sesion: number; label: string } | null>(null);
  const [fechaClase, setFechaClase]   = useState('');
  const [comentario, setComentario]   = useState('');
  const [guardando, setGuardando]     = useState(false);
  const [guardadoMsg, setGuardadoMsg] = useState<string | null>(null);

  useEffect(() => { fetchData(); }, []);

  const fetchData = async () => {
    setLoading(true);
    setError(null);
    try {
      const [sr, ar] = await Promise.all([
        fetch(getSilaboApiUrl('/api/silabos')),
        esDocente ? fetch(getSilaboApiUrl(`/api/avances/docente/${docenteId}`)) : Promise.resolve(null),
      ]);
      if (!sr.ok) throw new Error('Error al cargar los sílabos.');
      const sd = await sr.json();
      setSilabos(sd);
      if (ar?.ok) setAvances(await ar.json());
      // Expandir primera unidad de cada sílabo
      const ids = new Set<number>();
      sd.forEach((s: any) => { if (s.unidades?.[0]) ids.add(s.unidades[0].IdUnidad); });
      setExpandedUnidades(ids);
    } catch (e: any) {
      setError(e.message || 'Error de conexión.');
    } finally {
      setLoading(false);
    }
  };

  // Clave: temaId_sesion → avance
  const avanceMap = useMemo(() => {
    const m: Record<string, any> = {};
    avances.forEach(a => { m[`${a.SilaboTemaId}_${a.Sesion ?? 1}`] = a; });
    return m;
  }, [avances]);

  const toggleUnidad = (id: number) =>
    setExpandedUnidades(prev => { const n = new Set(prev); n.has(id) ? n.delete(id) : n.add(id); return n; });

  const abrirModal = (tema: any, sesion: number, label: string) => {
    setModalInfo({ tema, sesion, label });
    setFechaClase(new Date().toISOString().split('T')[0]);
    setComentario('');
    setGuardadoMsg(null);
  };

  const registrarAvance = async () => {
    if (!modalInfo || !fechaClase) return;
    setGuardando(true);
    setGuardadoMsg(null);
    try {
      const res = await fetch(getSilaboApiUrl('/api/avances'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          SilaboTemaId: modalInfo.tema.IdTema,
          DocenteId:    docenteId,
          Sesion:       modalInfo.sesion,
          FechaClase:   fechaClase,
          Comentario:   comentario || null,
        }),
      });
      const data = await res.json();
      if (!res.ok) {
        setGuardadoMsg(data.message || 'Error al registrar.');
      } else {
        setGuardadoMsg('¡Registrado! Pendiente de aprobación.');
        setAvances(prev => [...prev, data]);
        setTimeout(() => setModalInfo(null), 1600);
      }
    } catch { setGuardadoMsg('Error de conexión.'); }
    finally { setGuardando(false); }
  };

  // Estadísticas globales (solo docentes)
  const { totalSes, aprobSes, pendSes } = useMemo(() => {
    let totalSes = 0, aprobSes = 0, pendSes = 0;
    silabos.forEach(s => {
      const dias = s.DiasXSemana ?? 1;
      (s.unidades ?? []).forEach((u: any) =>
        (u.temas ?? []).forEach(() => {
          totalSes += dias;
        })
      );
    });
    avances.forEach(a => {
      if (a.Estado === 'aprobado')  aprobSes++;
      if (a.Estado === 'pendiente') pendSes++;
    });
    return { totalSes, aprobSes, pendSes };
  }, [silabos, avances]);

  // ─── Contenido de cada sesión ───────────────────────────────────────────────
  // Distribuye el contenido del tema entre las N sesiones de la semana
  const getSesionContenido = (tema: any, sesion: number, totalSesiones: number): string => {
    if (totalSesiones === 1) return tema.ContenidoConceptual || '';
    if (sesion === 1) return tema.ContenidoConceptual || '(Contenido conceptual)';
    if (sesion === 2) return tema.ContenidoProcedimental || tema.ContenidoConceptual || '(Contenido práctico)';
    return `Repaso / Evaluación: ${tema.ContenidoConceptual?.slice(0, 80) ?? ''}`;
  };

  return (
    <div style={{ minHeight: '100vh', backgroundColor: '#f1f5f9' }}>
      <Navbar
        displayName={displayName}
        currentPage="silabo"
        onNavigateToInicio={onNavigateToInicio}
        onNavigateToServicios={onNavigateToServicios}
        onNavigateToPerfil={onNavigateToPerfil}
        onLogout={onLogout}
        isLoggingOut={isLoggingOut}
      />

      <div style={{ maxWidth: 1200, margin: '0 auto', padding: '28px 16px' }}>

        {/* Título */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 20 }}>
          <BookOpen size={26} color="#6366f1" />
          <div>
            <h1 style={{ margin: 0, fontSize: 20, fontWeight: 700, color: '#1e293b' }}>
              {esDocente ? 'Mis sílabos' : 'Sílabos del semestre'}
            </h1>
            <p style={{ margin: 0, fontSize: 13, color: '#64748b' }}>
              {esDocente
                ? 'Registra las sesiones de clase que has dictado'
                : 'Consulta los contenidos del sílabo de tus cursos'}
            </p>
          </div>
        </div>

        {/* Stats — solo docentes */}
        {esDocente && !loading && !error && totalSes > 0 && (
          <div style={{ display: 'flex', gap: 10, marginBottom: 20, flexWrap: 'wrap' }}>
            {[
              { label: 'Sesiones totales', value: totalSes,              color: '#6366f1' },
              { label: 'Aprobadas',        value: aprobSes,              color: '#22c55e' },
              { label: 'En revisión',      value: pendSes,               color: '#f59e0b' },
              { label: 'Sin registrar',    value: totalSes - aprobSes - pendSes, color: '#94a3b8' },
            ].map(s => (
              <div key={s.label} style={{
                flex: '1 1 110px', background: '#fff', borderRadius: 10, padding: '10px 14px',
                boxShadow: '0 1px 4px rgba(0,0,0,.06)', borderLeft: `3px solid ${s.color}`,
              }}>
                <div style={{ fontSize: 20, fontWeight: 700, color: s.color }}>{s.value}</div>
                <div style={{ fontSize: 11, color: '#64748b' }}>{s.label}</div>
              </div>
            ))}
          </div>
        )}

        {/* Loading / Error / Vacío */}
        {loading && (
          <div style={{ textAlign: 'center', padding: 60 }}>
            <Loader2 size={32} color="#6366f1" style={{ animation: 'spin 1s linear infinite' }} />
            <p style={{ color: '#64748b', marginTop: 10 }}>Cargando...</p>
          </div>
        )}
        {error && (
          <div style={{ background: '#fef2f2', border: '1px solid #fecaca', borderRadius: 10, padding: 18, display: 'flex', gap: 10 }}>
            <AlertTriangle size={20} color="#ef4444" style={{ flexShrink: 0 }} />
            <div>
              <p style={{ margin: 0, color: '#b91c1c', fontWeight: 600 }}>Error</p>
              <p style={{ margin: '4px 0 0', fontSize: 13, color: '#dc2626' }}>{error}</p>
            </div>
          </div>
        )}
        {!loading && !error && silabos.length === 0 && (
          <div style={{ textAlign: 'center', padding: 60, color: '#64748b' }}>
            <BookOpen size={40} color="#cbd5e1" style={{ marginBottom: 10 }} />
            <p style={{ fontWeight: 600 }}>No hay sílabos disponibles</p>
            <p style={{ fontSize: 13 }}>Coordina con el área académica.</p>
          </div>
        )}

        {/* ── Grilla de tarjetas ── */}
        {!loading && !error && silabos.length > 0 && (
          <div style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(auto-fill, minmax(340px, 1fr))',
            gap: 18,
            alignItems: 'start',
          }}>
            {silabos.map((silabo: any, idx: number) => {
              const pal  = PALETA[idx % PALETA.length];
              const dias = silabo.DiasXSemana ?? 1;
              const allTemas = (silabo.unidades ?? []).flatMap((u: any) => u.temas ?? []);
              const totalSesCurso  = allTemas.length * dias;
              const aprobSesCurso  = allTemas.reduce((acc: number, t: any) => {
                for (let s = 1; s <= dias; s++) if (avanceMap[`${t.IdTema}_${s}`]?.Estado === 'aprobado') acc++;
                return acc;
              }, 0);
              const pct = totalSesCurso > 0 ? Math.round((aprobSesCurso / totalSesCurso) * 100) : 0;

              return (
                <div key={silabo.IdSilabo} style={{
                  background: '#fff', borderRadius: 14, overflow: 'hidden',
                  boxShadow: '0 2px 12px rgba(0,0,0,.08)',
                  display: 'flex', flexDirection: 'column',
                }}>
                  {/* ── Cabecera de la tarjeta ── */}
                  <div style={{ background: pal.grad, padding: '16px 18px', color: '#fff' }}>
                    <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 8 }}>
                      <div style={{ flex: 1, minWidth: 0 }}>
                        <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap', marginBottom: 5 }}>
                          {silabo.CodigoCurso && (
                            <span style={{ background: 'rgba(255,255,255,.25)', borderRadius: 5, padding: '1px 8px', fontSize: 11, fontFamily: 'monospace', fontWeight: 700 }}>
                              {silabo.CodigoCurso}
                            </span>
                          )}
                          {silabo.CicloNumero && (
                            <span style={{ background: 'rgba(255,255,255,.18)', borderRadius: 5, padding: '1px 8px', fontSize: 11 }}>
                              Ciclo {silabo.CicloNumero}
                            </span>
                          )}
                          {silabo.Semestre && (
                            <span style={{ background: 'rgba(255,255,255,.15)', borderRadius: 5, padding: '1px 8px', fontSize: 11 }}>
                              {silabo.Semestre}
                            </span>
                          )}
                        </div>
                        <div style={{ fontWeight: 700, fontSize: 15, lineHeight: 1.25, marginBottom: 4 }}>
                          {silabo.NombreCurso || '(Sin nombre)'}
                        </div>
                        {silabo.Docente && (
                          <div style={{ fontSize: 11, opacity: .85 }}>👤 {silabo.Docente}</div>
                        )}
                      </div>
                      {silabo.ArchivoPdf && (
                        <a href={getSilaboApiUrl('/' + silabo.ArchivoPdf)} target="_blank" rel="noreferrer"
                          style={{ background: 'rgba(255,255,255,.2)', borderRadius: 7, padding: '5px 10px', fontSize: 11, color: '#fff', textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 4, whiteSpace: 'nowrap', flexShrink: 0 }}>
                          <FileText size={12} /> PDF
                        </a>
                      )}
                    </div>

                    {/* Barra de progreso (solo docentes) */}
                    {esDocente && (
                      <div style={{ marginTop: 10 }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 11, opacity: .85, marginBottom: 4 }}>
                          <span>{aprobSesCurso}/{totalSesCurso} sesiones aprobadas</span>
                          <span style={{ fontWeight: 700 }}>{pct}%</span>
                        </div>
                        <div style={{ background: 'rgba(255,255,255,.25)', borderRadius: 99, height: 5 }}>
                          <div style={{ width: `${pct}%`, background: '#fff', height: '100%', borderRadius: 99, transition: 'width .4s' }} />
                        </div>
                      </div>
                    )}
                  </div>

                  {/* ── Unidades ── */}
                  <div style={{ flex: 1 }}>
                    {(silabo.unidades ?? []).map((unidad: any) => {
                      const expanded = expandedUnidades.has(unidad.IdUnidad);
                      const temas: any[] = unidad.temas ?? [];

                      return (
                        <div key={unidad.IdUnidad} style={{ borderTop: '1px solid #f1f5f9' }}>
                          <button onClick={() => toggleUnidad(unidad.IdUnidad)} style={{
                            width: '100%', display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                            padding: '10px 16px', background: 'none', border: 'none', cursor: 'pointer', textAlign: 'left',
                          }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                              {expanded ? <ChevronDown size={14} color={pal.acc} /> : <ChevronRight size={14} color={pal.acc} />}
                              <span style={{ fontWeight: 600, color: '#1e293b', fontSize: 13 }}>
                                U{unidad.Numero}: {unidad.Nombre}
                              </span>
                            </div>
                            <span style={{ fontSize: 11, color: '#94a3b8', whiteSpace: 'nowrap', marginLeft: 8 }}>
                              {temas.length} sem · {unidad.HorasTotal}h
                            </span>
                          </button>

                          {expanded && (
                            <div style={{ padding: '0 12px 12px' }}>
                              {temas.length === 0 && (
                                <p style={{ color: '#94a3b8', fontSize: 12, padding: '4px 4px' }}>Sin temas.</p>
                              )}
                              {temas.map((tema: any) => (
                                <div key={tema.IdTema} style={{ marginBottom: 10, border: `1px solid ${pal.badge}`, borderRadius: 8, overflow: 'hidden' }}>
                                  {/* Cabecera semana */}
                                  <div style={{ background: pal.badge, padding: '6px 12px', display: 'flex', alignItems: 'center', gap: 6 }}>
                                    <span style={{ background: pal.acc, color: '#fff', borderRadius: 4, padding: '1px 7px', fontSize: 11, fontWeight: 700, whiteSpace: 'nowrap' }}>
                                      Sem {tema.Semana}
                                    </span>
                                    <span style={{ fontSize: 12, color: pal.badgeTxt, fontWeight: 500, lineHeight: 1.3 }}>
                                      {tema.ContenidoConceptual
                                        ? (tema.ContenidoConceptual.length > 70
                                            ? tema.ContenidoConceptual.slice(0, 70) + '…'
                                            : tema.ContenidoConceptual)
                                        : '(Sin contenido)'}
                                    </span>
                                  </div>

                                  {/* Sesiones distribuidas */}
                                  <div style={{ padding: '8px 10px', display: 'flex', flexDirection: 'column', gap: 6 }}>
                                    {Array.from({ length: dias }, (_, i) => i + 1).map(sesion => {
                                      const key    = `${tema.IdTema}_${sesion}`;
                                      const avance = avanceMap[key];
                                      const est    = avance ? ESTADO[avance.Estado] : null;
                                      const label  = dias === 1 ? 'Sesión' : (SESION_LABEL[sesion - 1] ?? `Sesión ${sesion}`);
                                      const contenidoSesion = getSesionContenido(tema, sesion, dias);

                                      return (
                                        <div key={sesion} style={{
                                          background: avance ? '#f8fafc' : '#fafafa',
                                          border: '1px solid #f1f5f9', borderRadius: 7,
                                          padding: '7px 10px',
                                          display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 8,
                                        }}>
                                          <div style={{ flex: 1, minWidth: 0 }}>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: 5, marginBottom: 2, flexWrap: 'wrap' }}>
                                              <span style={{ fontSize: 10, fontWeight: 600, color: '#64748b', background: '#f1f5f9', borderRadius: 4, padding: '1px 5px' }}>
                                                {label}
                                              </span>
                                              {est && (
                                                <span style={{ fontSize: 10, display: 'flex', alignItems: 'center', gap: 2, color: est.color, fontWeight: 600 }}>
                                                  {est.icon} {est.label}
                                                </span>
                                              )}
                                            </div>
                                            {dias > 1 && (
                                              <div style={{ fontSize: 11, color: '#64748b', lineHeight: 1.35 }}>
                                                {contenidoSesion.length > 80 ? contenidoSesion.slice(0, 80) + '…' : contenidoSesion}
                                              </div>
                                            )}
                                            {avance?.Comentario && (
                                              <div style={{ fontSize: 11, color: pal.acc, marginTop: 2, display: 'flex', gap: 3, alignItems: 'flex-start' }}>
                                                <MessageSquare size={10} style={{ marginTop: 1, flexShrink: 0 }} />
                                                {avance.Comentario}
                                              </div>
                                            )}
                                            {avance?.ObservacionCoordinador && (
                                              <div style={{ fontSize: 11, color: avance.Estado === 'rechazado' ? '#ef4444' : '#059669', marginTop: 2 }}>
                                                📋 {avance.ObservacionCoordinador}
                                              </div>
                                            )}
                                          </div>

                                          {/* Acción — SOLO DOCENTES */}
                                          {esDocente && (
                                            <div style={{ flexShrink: 0 }}>
                                              {!avance ? (
                                                <button onClick={() => abrirModal(tema, sesion, label)} style={{
                                                  background: pal.acc, color: '#fff', border: 'none', borderRadius: 6,
                                                  padding: '4px 10px', fontSize: 11, cursor: 'pointer', fontWeight: 600, whiteSpace: 'nowrap',
                                                }}>
                                                  + Registrar
                                                </button>
                                              ) : avance.Estado === 'rechazado' ? (
                                                <button onClick={() => abrirModal(tema, sesion, label)} style={{
                                                  background: '#fef2f2', color: '#ef4444', border: '1px solid #fecaca',
                                                  borderRadius: 6, padding: '4px 10px', fontSize: 11, cursor: 'pointer', fontWeight: 600,
                                                }}>
                                                  <CalendarCheck size={11} /> Re-reg.
                                                </button>
                                              ) : (
                                                <span style={{ fontSize: 10, color: '#94a3b8' }}>
                                                  {avance.FechaClase ? new Date(avance.FechaClase).toLocaleDateString('es-PE', { day:'2-digit', month:'short' }) : ''}
                                                </span>
                                              )}
                                            </div>
                                          )}
                                        </div>
                                      );
                                    })}
                                  </div>
                                </div>
                              ))}
                            </div>
                          )}
                        </div>
                      );
                    })}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>

      {/* ── Modal registrar avance (solo docentes) ── */}
      {esDocente && modalInfo && (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,.45)', zIndex: 1000, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 16 }}>
          <div style={{ background: '#fff', borderRadius: 14, padding: 26, width: '100%', maxWidth: 460, boxShadow: '0 20px 40px rgba(0,0,0,.2)' }}>
            <h3 style={{ margin: '0 0 4px', color: '#1e293b', fontSize: 15, fontWeight: 700 }}>
              Registrar — Semana {modalInfo.tema.Semana}, {modalInfo.label}
            </h3>
            <p style={{ margin: '0 0 16px', fontSize: 12, color: '#64748b', lineHeight: 1.4 }}>
              {getSesionContenido(modalInfo.tema, modalInfo.sesion,
                silabos.find(s => s.unidades?.some((u: any) => u.temas?.some((t: any) => t.IdTema === modalInfo.tema.IdTema)))?.DiasXSemana ?? 1
              )}
            </p>

            <label style={{ fontSize: 13, fontWeight: 600, color: '#334155', display: 'block', marginBottom: 4 }}>Fecha de clase *</label>
            <input type="date" value={fechaClase} onChange={e => setFechaClase(e.target.value)}
              max={new Date().toISOString().split('T')[0]}
              style={{ width: '100%', padding: '8px 12px', border: '1px solid #cbd5e1', borderRadius: 7, fontSize: 14, marginBottom: 14, boxSizing: 'border-box' }} />

            <label style={{ fontSize: 13, fontWeight: 600, color: '#334155', display: 'block', marginBottom: 4 }}>Comentario (opcional)</label>
            <textarea value={comentario} onChange={e => setComentario(e.target.value)}
              placeholder="Ej: Se cubrió hasta el punto 2.3, pendiente lab."
              rows={3}
              style={{ width: '100%', padding: '8px 12px', border: '1px solid #cbd5e1', borderRadius: 7, fontSize: 13, resize: 'vertical', boxSizing: 'border-box', marginBottom: 14 }} />

            {guardadoMsg && (
              <div style={{
                background: guardadoMsg.startsWith('¡') ? '#f0fdf4' : '#fef2f2',
                color: guardadoMsg.startsWith('¡') ? '#166534' : '#b91c1c',
                border: `1px solid ${guardadoMsg.startsWith('¡') ? '#bbf7d0' : '#fecaca'}`,
                borderRadius: 7, padding: '8px 12px', fontSize: 13, marginBottom: 12,
              }}>{guardadoMsg}</div>
            )}

            <div style={{ display: 'flex', gap: 8, justifyContent: 'flex-end' }}>
              <button onClick={() => setModalInfo(null)}
                style={{ padding: '8px 16px', border: '1px solid #e2e8f0', borderRadius: 7, background: '#fff', cursor: 'pointer', fontSize: 13 }}>
                Cancelar
              </button>
              <button onClick={registrarAvance} disabled={guardando || !fechaClase}
                style={{ padding: '8px 16px', background: '#6366f1', color: '#fff', border: 'none', borderRadius: 7, cursor: 'pointer', fontSize: 13, fontWeight: 600, opacity: guardando || !fechaClase ? .6 : 1 }}>
                {guardando ? 'Guardando...' : 'Confirmar'}
              </button>
            </div>
          </div>
        </div>
      )}

      <style>{`@keyframes spin { to { transform: rotate(360deg); } }`}</style>
    </div>
  );
};


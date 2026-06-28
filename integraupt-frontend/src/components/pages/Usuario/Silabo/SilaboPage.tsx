import React, { useState, useEffect, useMemo } from 'react';
import { Navbar } from '../Navbar';
import { getSilaboApiUrl } from '../../../../utils/apiConfig';
import {
  BookOpen, CheckCircle2, Clock, XCircle, ChevronDown, ChevronRight,
  Loader2, AlertTriangle, CalendarCheck, MessageSquare, Plus
} from 'lucide-react';

interface SilaboPageProps {
  user: any;
  onNavigateToInicio: () => void;
  onNavigateToServicios: () => void;
  onNavigateToPerfil: () => void;
  onLogout?: () => void;
  isLoggingOut?: boolean;
}

const ESTADO_CONFIG: Record<string, { label: string; icon: React.ReactNode; color: string }> = {
  aprobado:  { label: 'Aprobado',  icon: <CheckCircle2 size={14} />, color: '#22c55e' },
  pendiente: { label: 'Pendiente', icon: <Clock size={14} />,        color: '#f59e0b' },
  rechazado: { label: 'Rechazado', icon: <XCircle size={14} />,      color: '#ef4444' },
};

export const SilaboPage: React.FC<SilaboPageProps> = ({
  user, onNavigateToInicio, onNavigateToServicios, onNavigateToPerfil, onLogout, isLoggingOut = false,
}) => {
  const displayName = user.user_metadata?.name?.trim() || user.user_metadata?.codigo || 'Docente';
  const docenteId   = user.id;

  const [silabos, setSilabos] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState<string | null>(null);

  const [expandedUnidades, setExpandedUnidades] = useState<Set<number>>(new Set());
  const [avancesDelDocente, setAvancesDelDocente] = useState<any[]>([]);

  // Modal para registrar avance
  const [modalTema, setModalTema]         = useState<any | null>(null);
  const [fechaClase, setFechaClase]       = useState('');
  const [comentario, setComentario]       = useState('');
  const [guardando, setGuardando]         = useState(false);
  const [guardadoMsg, setGuardadoMsg]     = useState<string | null>(null);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    setError(null);
    try {
      const [silaboRes, avanceRes] = await Promise.all([
        fetch(getSilaboApiUrl('/api/silabos')),
        fetch(getSilaboApiUrl(`/api/avances/docente/${docenteId}`)),
      ]);

      if (!silaboRes.ok) throw new Error('Error al cargar los sílabos.');
      const silaboData = await silaboRes.json();
      setSilabos(silaboData);

      if (avanceRes.ok) {
        const avanceData = await avanceRes.json();
        setAvancesDelDocente(avanceData);
      }

      // Expand first unidad of each silabo by default
      const ids = new Set<number>();
      silaboData.forEach((s: any) => {
        if (s.unidades?.[0]) ids.add(s.unidades[0].IdUnidad);
      });
      setExpandedUnidades(ids);
    } catch (e: any) {
      setError(e.message || 'Error de conexión con el servidor.');
    } finally {
      setLoading(false);
    }
  };

  const avanceMap = useMemo(() => {
    const map: Record<number, any> = {};
    avancesDelDocente.forEach(a => { map[a.SilaboTemaId] = a; });
    return map;
  }, [avancesDelDocente]);

  const toggleUnidad = (id: number) => {
    setExpandedUnidades(prev => {
      const next = new Set(prev);
      next.has(id) ? next.delete(id) : next.add(id);
      return next;
    });
  };

  const abrirModal = (tema: any) => {
    setModalTema(tema);
    setFechaClase(new Date().toISOString().split('T')[0]);
    setComentario('');
    setGuardadoMsg(null);
  };

  const registrarAvance = async () => {
    if (!modalTema || !fechaClase) return;
    setGuardando(true);
    setGuardadoMsg(null);
    try {
      const res = await fetch(getSilaboApiUrl('/api/avances'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          SilaboTemaId:   modalTema.IdTema,
          DocenteId:      docenteId,
          FechaClase:     fechaClase,
          Comentario:     comentario || null,
        }),
      });
      const data = await res.json();
      if (!res.ok) {
        setGuardadoMsg(data.message || 'Error al registrar avance.');
      } else {
        setGuardadoMsg('¡Avance registrado! Pendiente de revisión del coordinador.');
        setAvancesDelDocente(prev => [...prev, data]);
        setTimeout(() => setModalTema(null), 1800);
      }
    } catch {
      setGuardadoMsg('Error de conexión.');
    } finally {
      setGuardando(false);
    }
  };

  const totalTemas    = useMemo(() => silabos.flatMap(s => s.unidades?.flatMap((u: any) => u.temas ?? []) ?? []).length, [silabos]);
  const temasAprobados = useMemo(() => avancesDelDocente.filter(a => a.Estado === 'aprobado').length, [avancesDelDocente]);
  const temasPendientes = useMemo(() => avancesDelDocente.filter(a => a.Estado === 'pendiente').length, [avancesDelDocente]);

  return (
    <div style={{ minHeight: '100vh', backgroundColor: '#f8fafc' }}>
      <Navbar
        displayName={displayName}
        currentPage="silabo"
        onNavigateToInicio={onNavigateToInicio}
        onNavigateToServicios={onNavigateToServicios}
        onNavigateToPerfil={onNavigateToPerfil}
        onLogout={onLogout}
        isLoggingOut={isLoggingOut}
      />

      <div style={{ maxWidth: 900, margin: '0 auto', padding: '32px 16px' }}>
        {/* Header */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 24 }}>
          <BookOpen size={28} color="#6366f1" />
          <div>
            <h1 style={{ margin: 0, fontSize: 22, fontWeight: 700, color: '#1e293b' }}>Mi Sílabo</h1>
            <p style={{ margin: 0, fontSize: 13, color: '#64748b' }}>
              Registra los temas que has dictado en clase
            </p>
          </div>
        </div>

        {/* Stats */}
        {!loading && !error && totalTemas > 0 && (
          <div style={{ display: 'flex', gap: 12, marginBottom: 24, flexWrap: 'wrap' }}>
            {[
              { label: 'Total temas', value: totalTemas, color: '#6366f1' },
              { label: 'Aprobados', value: temasAprobados, color: '#22c55e' },
              { label: 'En revisión', value: temasPendientes, color: '#f59e0b' },
              { label: 'Sin registrar', value: totalTemas - temasAprobados - temasPendientes, color: '#94a3b8' },
            ].map(s => (
              <div key={s.label} style={{
                flex: '1 1 100px', background: '#fff', borderRadius: 10, padding: '12px 16px',
                boxShadow: '0 1px 4px rgba(0,0,0,.06)', borderLeft: `4px solid ${s.color}`,
              }}>
                <div style={{ fontSize: 22, fontWeight: 700, color: s.color }}>{s.value}</div>
                <div style={{ fontSize: 12, color: '#64748b' }}>{s.label}</div>
              </div>
            ))}
          </div>
        )}

        {loading && (
          <div style={{ textAlign: 'center', padding: 60 }}>
            <Loader2 size={32} className="spin" color="#6366f1" style={{ animation: 'spin 1s linear infinite' }} />
            <p style={{ color: '#64748b', marginTop: 12 }}>Cargando sílabo...</p>
          </div>
        )}

        {error && (
          <div style={{ background: '#fef2f2', border: '1px solid #fecaca', borderRadius: 10, padding: 20, display: 'flex', gap: 12, alignItems: 'flex-start' }}>
            <AlertTriangle size={20} color="#ef4444" />
            <div>
              <p style={{ margin: 0, color: '#b91c1c', fontWeight: 600 }}>Error al cargar</p>
              <p style={{ margin: '4px 0 0', color: '#dc2626', fontSize: 13 }}>{error}</p>
            </div>
          </div>
        )}

        {!loading && !error && silabos.length === 0 && (
          <div style={{ textAlign: 'center', padding: 60, color: '#64748b' }}>
            <BookOpen size={40} color="#cbd5e1" style={{ marginBottom: 12 }} />
            <p>No tienes sílabos asignados aún.</p>
            <p style={{ fontSize: 13 }}>Coordina con el área académica para que registren tu sílabo.</p>
          </div>
        )}

        {/* Sílabos list */}
        {silabos.map((silabo: any) => (
          <div key={silabo.IdSilabo} style={{ background: '#fff', borderRadius: 12, boxShadow: '0 1px 6px rgba(0,0,0,.07)', marginBottom: 20, overflow: 'hidden' }}>
            {/* Silabo header */}
            <div style={{ background: 'linear-gradient(135deg,#6366f1,#818cf8)', padding: '14px 20px', color: '#fff' }}>
              <div style={{ fontWeight: 700, fontSize: 15 }}>Ciclo {silabo.Ciclo}</div>
              <div style={{ fontSize: 12, opacity: .85 }}>Sílabo #{silabo.IdSilabo}</div>
              {silabo.ArchivoPdf && (
                <a
                  href={getSilaboApiUrl('/' + silabo.ArchivoPdf)}
                  target="_blank" rel="noreferrer"
                  style={{ fontSize: 12, color: '#e0e7ff', marginTop: 4, display: 'inline-block' }}
                >
                  📄 Ver PDF del sílabo
                </a>
              )}
            </div>

            {/* Unidades */}
            {(silabo.unidades ?? []).map((unidad: any) => {
              const expanded = expandedUnidades.has(unidad.IdUnidad);
              const temasUnidad: any[] = unidad.temas ?? [];
              const aprobadosUnidad = temasUnidad.filter(t => avanceMap[t.IdTema]?.Estado === 'aprobado').length;
              return (
                <div key={unidad.IdUnidad} style={{ borderTop: '1px solid #f1f5f9' }}>
                  <button
                    onClick={() => toggleUnidad(unidad.IdUnidad)}
                    style={{
                      width: '100%', display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                      padding: '12px 20px', background: 'none', border: 'none', cursor: 'pointer',
                      textAlign: 'left',
                    }}
                  >
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                      {expanded ? <ChevronDown size={16} color="#6366f1" /> : <ChevronRight size={16} color="#6366f1" />}
                      <span style={{ fontWeight: 600, color: '#1e293b', fontSize: 14 }}>
                        Unidad {unidad.Numero}: {unidad.Nombre}
                      </span>
                    </div>
                    <div style={{ fontSize: 12, color: '#64748b' }}>
                      {aprobadosUnidad}/{temasUnidad.length} aprobados · {unidad.HorasTotal}h
                    </div>
                  </button>

                  {expanded && (
                    <div style={{ padding: '0 20px 16px' }}>
                      {temasUnidad.length === 0 && (
                        <p style={{ color: '#94a3b8', fontSize: 13 }}>Sin temas registrados en esta unidad.</p>
                      )}
                      {temasUnidad.map((tema: any) => {
                        const avance = avanceMap[tema.IdTema];
                        const estado = avance ? ESTADO_CONFIG[avance.Estado] : null;
                        return (
                          <div key={tema.IdTema} style={{
                            border: '1px solid #e2e8f0', borderRadius: 8, padding: 14, marginBottom: 10,
                            background: avance ? '#f8fafc' : '#fff',
                          }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 10 }}>
                              <div style={{ flex: 1 }}>
                                <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 4 }}>
                                  <span style={{ fontSize: 11, background: '#e0e7ff', color: '#4338ca', borderRadius: 4, padding: '1px 6px', fontWeight: 600 }}>
                                    Semana {tema.Semana}
                                  </span>
                                  {estado && (
                                    <span style={{ fontSize: 11, display: 'flex', alignItems: 'center', gap: 3, color: estado.color, fontWeight: 600 }}>
                                      {estado.icon} {estado.label}
                                    </span>
                                  )}
                                </div>
                                <p style={{ margin: 0, fontSize: 13, color: '#334155', fontWeight: 500, lineHeight: 1.4 }}>
                                  {tema.ContenidoConceptual || '(Sin contenido conceptual)'}
                                </p>
                                {tema.ContenidoProcedimental && (
                                  <p style={{ margin: '4px 0 0', fontSize: 12, color: '#64748b', lineHeight: 1.4 }}>
                                    <em>Procedimental:</em> {tema.ContenidoProcedimental}
                                  </p>
                                )}
                                {avance?.Comentario && (
                                  <p style={{ margin: '6px 0 0', fontSize: 12, color: '#6366f1', display: 'flex', gap: 4 }}>
                                    <MessageSquare size={12} style={{ marginTop: 2 }} /> {avance.Comentario}
                                  </p>
                                )}
                                {avance?.ObservacionCoordinador && (
                                  <p style={{ margin: '4px 0 0', fontSize: 12, color: avance.Estado === 'rechazado' ? '#ef4444' : '#059669' }}>
                                    📋 Coordinador: {avance.ObservacionCoordinador}
                                  </p>
                                )}
                              </div>
                              <div>
                                {!avance ? (
                                  <button
                                    onClick={() => abrirModal(tema)}
                                    style={{
                                      display: 'flex', alignItems: 'center', gap: 6, background: '#6366f1',
                                      color: '#fff', border: 'none', borderRadius: 7, padding: '7px 12px',
                                      fontSize: 12, cursor: 'pointer', fontWeight: 600, whiteSpace: 'nowrap',
                                    }}
                                  >
                                    <Plus size={13} /> Marcar cubierto
                                  </button>
                                ) : avance.Estado === 'rechazado' ? (
                                  <button
                                    onClick={() => abrirModal(tema)}
                                    style={{
                                      display: 'flex', alignItems: 'center', gap: 6, background: '#fef2f2',
                                      color: '#ef4444', border: '1px solid #fecaca', borderRadius: 7,
                                      padding: '7px 12px', fontSize: 12, cursor: 'pointer', fontWeight: 600,
                                    }}
                                  >
                                    <CalendarCheck size={13} /> Re-registrar
                                  </button>
                                ) : (
                                  <div style={{ fontSize: 11, color: '#94a3b8', textAlign: 'center' }}>
                                    {avance.FechaClase ? new Date(avance.FechaClase).toLocaleDateString('es-PE') : ''}
                                  </div>
                                )}
                              </div>
                            </div>
                          </div>
                        );
                      })}
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        ))}
      </div>

      {/* Modal registrar avance */}
      {modalTema && (
        <div style={{
          position: 'fixed', inset: 0, background: 'rgba(0,0,0,.45)', zIndex: 1000,
          display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 16,
        }}>
          <div style={{ background: '#fff', borderRadius: 14, padding: 28, width: '100%', maxWidth: 480, boxShadow: '0 20px 40px rgba(0,0,0,.2)' }}>
            <h3 style={{ margin: '0 0 6px', color: '#1e293b', fontSize: 16, fontWeight: 700 }}>
              Registrar avance — Semana {modalTema.Semana}
            </h3>
            <p style={{ margin: '0 0 16px', fontSize: 13, color: '#64748b', lineHeight: 1.4 }}>
              {modalTema.ContenidoConceptual}
            </p>

            <label style={{ fontSize: 13, fontWeight: 600, color: '#334155', display: 'block', marginBottom: 4 }}>
              Fecha de clase *
            </label>
            <input
              type="date"
              value={fechaClase}
              onChange={e => setFechaClase(e.target.value)}
              max={new Date().toISOString().split('T')[0]}
              style={{ width: '100%', padding: '8px 12px', border: '1px solid #cbd5e1', borderRadius: 7, fontSize: 14, marginBottom: 14, boxSizing: 'border-box' }}
            />

            <label style={{ fontSize: 13, fontWeight: 600, color: '#334155', display: 'block', marginBottom: 4 }}>
              Comentario (opcional)
            </label>
            <textarea
              value={comentario}
              onChange={e => setComentario(e.target.value)}
              placeholder="Ej: Se cubrió hasta el punto 2.3, pendiente lab."
              rows={3}
              style={{ width: '100%', padding: '8px 12px', border: '1px solid #cbd5e1', borderRadius: 7, fontSize: 13, resize: 'vertical', boxSizing: 'border-box', marginBottom: 16 }}
            />

            {guardadoMsg && (
              <div style={{
                background: guardadoMsg.startsWith('¡') ? '#f0fdf4' : '#fef2f2',
                color: guardadoMsg.startsWith('¡') ? '#166534' : '#b91c1c',
                border: `1px solid ${guardadoMsg.startsWith('¡') ? '#bbf7d0' : '#fecaca'}`,
                borderRadius: 7, padding: '8px 12px', fontSize: 13, marginBottom: 14,
              }}>
                {guardadoMsg}
              </div>
            )}

            <div style={{ display: 'flex', gap: 10, justifyContent: 'flex-end' }}>
              <button
                onClick={() => setModalTema(null)}
                style={{ padding: '8px 18px', border: '1px solid #e2e8f0', borderRadius: 7, background: '#fff', cursor: 'pointer', fontSize: 13 }}
              >
                Cancelar
              </button>
              <button
                onClick={registrarAvance}
                disabled={guardando || !fechaClase}
                style={{
                  padding: '8px 18px', background: '#6366f1', color: '#fff', border: 'none',
                  borderRadius: 7, cursor: 'pointer', fontSize: 13, fontWeight: 600,
                  opacity: guardando || !fechaClase ? .6 : 1,
                }}
              >
                {guardando ? 'Guardando...' : 'Registrar avance'}
              </button>
            </div>
          </div>
        </div>
      )}

      <style>{`@keyframes spin { to { transform: rotate(360deg); } }`}</style>
    </div>
  );
};

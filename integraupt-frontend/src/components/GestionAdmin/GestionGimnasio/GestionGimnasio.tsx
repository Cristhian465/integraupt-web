import React, { useState, useEffect, useMemo, useCallback } from 'react';
import { Loader2, AlertCircle, RefreshCw, Search, SlidersHorizontal, Filter, ShieldCheck, Dumbbell, Clock } from 'lucide-react';
import { obtenerAsistencias } from '../../pages/Usuario/Gimnasio/services/gimnasioService';
import type { AsistenciaGimnasioResponse } from '../../pages/Usuario/Gimnasio/types';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import * as XLSX from 'xlsx';
import { AuditExport } from '../GestionAuditoria/components/AuditExport';
import './styles/GestionGimnasio.css';

export const GestionGimnasio: React.FC = () => {
  const [asistencias, setAsistencias] = useState<AsistenciaGimnasioResponse[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  const [searchTerm, setSearchTerm] = useState("");
  const [statusMessage, setStatusMessage] = useState<{type: "success" | "error"; text: string} | null>(null);

  const [filterForm, setFilterForm] = useState({
    idAsistencia: "",
    estado: "",
    usuario: "",
    fechaInicio: "",
    fechaFin: ""
  });

  const notifyStatus = useCallback((type: "success" | "error", text: string) => {
    setStatusMessage({ type, text });
    setTimeout(() => setStatusMessage(null), 4500);
  }, []);

  const cargarDatos = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await obtenerAsistencias();
      setAsistencias(data);
    } catch (e: any) {
      const msg = e.message || 'Error al cargar asistencias';
      setError(msg);
      notifyStatus("error", msg);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    cargarDatos();
  }, []);

  const handleReload = async () => {
    await cargarDatos();
    notifyStatus("success", "Registros sincronizados correctamente.");
  };

  const handleExport = async (format: "pdf" | "excel") => {
    try {
      const timestamp = new Date().toISOString().slice(0, 19).replace(/[:.]/g, "-");
      
      if (format === "pdf") {
        const doc = new jsPDF();
        doc.text('Reporte de Asistencias - Gimnasio UPT', 14, 15);
        
        const tableColumn = ["ID", "Código", "Usuario", "Escuela/Facultad", "Fecha", "Ingreso", "Salida", "Duración (min)"];
        const tableRows = filteredAsistencias.map(a => [
          a.id_asistencia,
          a.codigo_estudiante || '-',
          a.usuario_nombre || `Usuario #${a.id_usuario}`,
          a.escuela_facultad || '-',
          a.fecha,
          a.hora_ingreso,
          a.hora_salida || 'En progreso',
          a.duracion_calculada ?? '-'
        ]);

        autoTable(doc, {
          head: [tableColumn],
          body: tableRows,
          startY: 20,
        });

        doc.save(`reporte_gimnasio_${timestamp}.pdf`);
      } else {
        const worksheetData = filteredAsistencias.map(a => ({
          ID: a.id_asistencia,
          'Código': a.codigo_estudiante || '-',
          Usuario: a.usuario_nombre || `Usuario #${a.id_usuario}`,
          'Escuela/Facultad': a.escuela_facultad || '-',
          Fecha: a.fecha,
          Ingreso: a.hora_ingreso,
          Salida: a.hora_salida || 'En progreso',
          'Duración (min)': a.duracion_calculada ?? '-'
        }));

        const worksheet = XLSX.utils.json_to_sheet(worksheetData);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Asistencias");
        XLSX.writeFile(workbook, `reporte_gimnasio_${timestamp}.xlsx`);
      }
      notifyStatus("success", `Archivo ${format.toUpperCase()} generado correctamente.`);
    } catch (exportError: any) {
      notifyStatus("error", exportError.message || "No se pudo exportar el reporte.");
    }
  };

  const handleApplyFilters = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    notifyStatus("success", "Filtros aplicados correctamente.");
  };

  const handleResetFilters = () => {
    setFilterForm({
      idAsistencia: "",
      estado: "",
      usuario: "",
      fechaInicio: "",
      fechaFin: ""
    });
    setSearchTerm("");
    notifyStatus("success", "Se restablecieron los filtros.");
  };

  const filteredAsistencias = useMemo(() => {
    let result = [...asistencias];
    
    // Búsqueda general
    const query = searchTerm.trim().toLowerCase();
    if (query) {
      result = result.filter(a => 
        a.id_asistencia.toString().includes(query) ||
        (a.usuario_nombre || "").toLowerCase().includes(query) ||
        (a.codigo_estudiante || "").toLowerCase().includes(query) ||
        (a.escuela_facultad || "").toLowerCase().includes(query) ||
        a.fecha.includes(query)
      );
    }

    // Filtros avanzados
    if (filterForm.idAsistencia) {
      result = result.filter(a => a.id_asistencia.toString() === filterForm.idAsistencia);
    }
    if (filterForm.usuario) {
      const usrQuery = filterForm.usuario.toLowerCase();
      result = result.filter(a => (a.usuario_nombre || "").toLowerCase().includes(usrQuery));
    }
    if (filterForm.estado) {
      if (filterForm.estado === "Finalizada") {
        result = result.filter(a => a.hora_salida != null);
      } else if (filterForm.estado === "En progreso") {
        result = result.filter(a => a.hora_salida == null);
      }
    }
    if (filterForm.fechaInicio) {
      const filterDate = new Date(filterForm.fechaInicio).getTime();
      result = result.filter(a => new Date(`${a.fecha}T${a.hora_ingreso}`).getTime() >= filterDate);
    }
    if (filterForm.fechaFin) {
      const filterDate = new Date(filterForm.fechaFin).getTime();
      result = result.filter(a => new Date(`${a.fecha}T${a.hora_ingreso}`).getTime() <= filterDate);
    }

    return result;
  }, [asistencias, searchTerm, filterForm]);

  const totalRegistros = asistencias.length;
  const totalFiltrados = filteredAsistencias.length;
  const totalEnProgreso = asistencias.filter(a => !a.hora_salida).length;
  const totalFinalizadas = asistencias.filter(a => a.hora_salida).length;

  return (
    <div className="gestion-gimnasio">
      <div className="gestion-gimnasio-header">
        <div>
          <h2 className="gestion-gimnasio-title">Gestión de Asistencias del Gimnasio</h2>
          <p className="gestion-gimnasio-subtitle">
            Supervisa en tiempo real los ingresos, salidas y duración de las sesiones en el Gimnasio UPT.
          </p>
        </div>
        <div className="gestion-gimnasio-actions">
          <button
            type="button"
            className="gimnasio-btn secondary"
            onClick={handleReload}
            disabled={loading}
          >
            <RefreshCw size={16} className={loading ? "spin" : ""} />
            Actualizar
          </button>
        </div>
      </div>

      <div className="gestion-gimnasio-stats">
        <div className="gimnasio-stat-card">
          <Dumbbell size={20} />
          <div>
            <p>Total de Registros</p>
            <strong>{totalRegistros}</strong>
          </div>
        </div>
        <div className="gimnasio-stat-card">
          <span className="gimnasio-dot" style={{ backgroundColor: '#f59e0b' }} />
          <div>
            <p>En Progreso</p>
            <strong>{totalEnProgreso}</strong>
          </div>
        </div>
        <div className="gimnasio-stat-card">
          <span className="gimnasio-dot" style={{ backgroundColor: '#10b981' }} />
          <div>
            <p>Finalizadas</p>
            <strong>{totalFinalizadas}</strong>
          </div>
        </div>
      </div>

      <div className="gestion-gimnasio-toolbar">
        <div className="gestion-gimnasio-search">
          <Search size={16} />
          <input
            type="search"
            maxLength={50}
            placeholder="Buscar por ID de asistencia, usuario o fecha"
            value={searchTerm}
            onChange={(event) => setSearchTerm(event.target.value)}
          />
        </div>
        <div className="gestion-gimnasio-meta">
          {totalFiltrados} resultados visibles (de {totalRegistros})
        </div>
      </div>

      <form className="gestion-gimnasio-filters" onSubmit={handleApplyFilters}>
        <div className="gestion-gimnasio-filters-title">
          <SlidersHorizontal size={16} />
          <span>Filtros avanzados</span>
        </div>
        <div className="gestion-gimnasio-filters-grid">
          <label>
            <span>ID Asistencia</span>
            <input
              type="text"
              inputMode="numeric"
              pattern="[0-9]{1,6}"
              maxLength={6}
              value={filterForm.idAsistencia}
              onChange={(event) => {
                const value = event.target.value;
                if (/^\d{0,6}$/.test(value)) {
                  setFilterForm((prev) => ({ ...prev, idAsistencia: value }));
                }
              }}
              placeholder="Ej: 15"
            />
          </label>
          <label>
            <span>Estado</span>
            <select
              value={filterForm.estado}
              onChange={(event) =>
                setFilterForm((prev) => ({ ...prev, estado: event.target.value }))
              }
            >
              <option value="">Todos</option>
              <option value="En progreso">En progreso</option>
              <option value="Finalizada">Finalizada</option>
            </select>
          </label>

          <label>
            <span>Usuario</span>
            <input
              type="text"
              maxLength={25}
              value={filterForm.usuario}
              onChange={(event) =>
                setFilterForm((prev) => ({
                  ...prev,
                  usuario: event.target.value
                }))
              }
              placeholder="Nombre del estudiante"
            />
          </label>
          <label>
            <span>Fecha inicio (desde)</span>
            <input
              type="date"
              value={filterForm.fechaInicio}
              onChange={(event) =>
                setFilterForm((prev) => ({ ...prev, fechaInicio: event.target.value }))
              }
            />
          </label>

          <label>
            <span>Fecha fin (hasta)</span>
            <input
              type="date"
              value={filterForm.fechaFin}
              onChange={(event) =>
                setFilterForm((prev) => ({ ...prev, fechaFin: event.target.value }))
              }
            />
          </label>
        </div>
        <div className="gestion-gimnasio-filters-actions">
          <button type="submit" className="gimnasio-btn primary" disabled={loading}>
            <Filter size={16} />
            Aplicar filtros
          </button>
          <button
            type="button"
            className="gimnasio-btn ghost"
            onClick={handleResetFilters}
            disabled={loading}
          >
            Restablecer
          </button>
        </div>
      </form>

      {statusMessage && (
        <div
          className={`gestion-gimnasio-alert ${
            statusMessage.type === "success" ? "success" : "error"
          }`}
        >
          {statusMessage.type === "error" && <AlertCircle size={16} />}
          <span>{statusMessage.text}</span>
        </div>
      )}

      {error && (
        <div className="gestion-gimnasio-alert error">
          <AlertCircle size={16} />
          <span>{error}</span>
        </div>
      )}

      <AuditExport onExport={handleExport} />

      <div className="gimnasio-table-wrapper" style={{ marginTop: '1rem', backgroundColor: '#fff', borderRadius: '0.75rem', border: '1px solid #e2e8f0', overflowX: 'auto' }}>
        <table className="gimnasio-table" style={{ width: '100%', borderCollapse: 'collapse', fontSize: '0.92rem' }}>
          <thead style={{ backgroundColor: '#f1f5f9', textTransform: 'uppercase', fontSize: '0.75rem', letterSpacing: '0.05em' }}>
            <tr>
              <th style={{ padding: '0.75rem', textAlign: 'left', borderBottom: '1px solid #e2e8f0' }}>ID</th>
              <th style={{ padding: '0.75rem', textAlign: 'left', borderBottom: '1px solid #e2e8f0' }}>Código</th>
              <th style={{ padding: '0.75rem', textAlign: 'left', borderBottom: '1px solid #e2e8f0' }}>Usuario</th>
              <th style={{ padding: '0.75rem', textAlign: 'left', borderBottom: '1px solid #e2e8f0' }}>Escuela/Facultad</th>
              <th style={{ padding: '0.75rem', textAlign: 'left', borderBottom: '1px solid #e2e8f0' }}>Fecha</th>
              <th style={{ padding: '0.75rem', textAlign: 'left', borderBottom: '1px solid #e2e8f0' }}>Ingreso</th>
              <th style={{ padding: '0.75rem', textAlign: 'left', borderBottom: '1px solid #e2e8f0' }}>Salida</th>
              <th style={{ padding: '0.75rem', textAlign: 'left', borderBottom: '1px solid #e2e8f0' }}>Duración</th>
            </tr>
          </thead>
          <tbody>
            {loading && asistencias.length === 0 ? (
              <tr>
                <td colSpan={8} style={{ textAlign: 'center', padding: '2rem' }}>
                  <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '0.5rem', color: '#475569' }}>
                    <Loader2 className="spin" size={20} /> Cargando registros...
                  </div>
                </td>
              </tr>
            ) : filteredAsistencias.length === 0 ? (
              <tr>
                <td colSpan={8} style={{ textAlign: 'center', padding: '2rem', color: '#475569' }}>
                  No se encontraron registros de asistencia.
                </td>
              </tr>
            ) : (
              filteredAsistencias.map(a => (
                <tr key={a.id_asistencia} style={{ borderBottom: '1px solid #e2e8f0' }}>
                  <td style={{ padding: '0.75rem' }}>{a.id_asistencia}</td>
                  <td style={{ padding: '0.75rem', fontFamily: 'monospace', fontWeight: 600 }}>{a.codigo_estudiante || <span style={{ color: '#94a3b8' }}>-</span>}</td>
                  <td style={{ padding: '0.75rem', fontWeight: 500 }}>{a.usuario_nombre || `Usuario #${a.id_usuario}`}</td>
                  <td style={{ padding: '0.75rem' }}>{a.escuela_facultad || <span style={{ color: '#94a3b8' }}>-</span>}</td>
                  <td style={{ padding: '0.75rem' }}>{a.fecha}</td>
                  <td style={{ padding: '0.75rem' }}>{a.hora_ingreso}</td>
                  <td style={{ padding: '0.75rem' }}>
                    {a.hora_salida ? (
                      a.hora_salida
                    ) : (
                      <span className="gimnasio-chip" style={{ backgroundColor: '#fef3c7', color: '#92400e', padding: '0.15rem 0.5rem', borderRadius: '999px', fontSize: '0.75rem', display: 'inline-block' }}>En progreso</span>
                    )}
                  </td>
                  <td style={{ padding: '0.75rem' }}>
                    {a.duracion_calculada != null ? (
                      `${a.duracion_calculada} min`
                    ) : (
                      <span style={{ color: '#94a3b8' }}>-</span>
                    )}
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};

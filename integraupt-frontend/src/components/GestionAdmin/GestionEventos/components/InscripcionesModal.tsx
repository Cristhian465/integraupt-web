import React, { useEffect, useState } from "react";
import { X, UserPlus, QrCode, XCircle } from "lucide-react";
import type { Evento, EventoInscripcion } from "../types";
import {
  cancelarInscripcion,
  checkinPorQr,
  fetchInscripciones,
  fetchReporteAsistencia,
  inscribirUsuario
} from "../eventosService";
import type { ReporteAsistencia } from "../types";

interface InscripcionesModalProps {
  evento: Evento | null;
  onClose: () => void;
}

export const InscripcionesModal: React.FC<InscripcionesModalProps> = ({ evento, onClose }) => {
  const [inscripciones, setInscripciones] = useState<EventoInscripcion[]>([]);
  const [reporte, setReporte] = useState<ReporteAsistencia | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [usuarioId, setUsuarioId] = useState("");
  const [codigoQr, setCodigoQr] = useState("");

  const recargar = async (idEvento: number) => {
    setLoading(true);
    try {
      const [inscripcionesData, reporteData] = await Promise.all([
        fetchInscripciones(idEvento),
        fetchReporteAsistencia(idEvento)
      ]);
      setInscripciones(inscripcionesData);
      setReporte(reporteData);
      setError(null);
    } catch (loadError) {
      setError(loadError instanceof Error ? loadError.message : "No se pudieron cargar las inscripciones.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (evento) {
      void recargar(evento.id);
      setUsuarioId("");
      setCodigoQr("");
      setError(null);
    }
  }, [evento]);

  if (!evento) {
    return null;
  }

  const handleInscribir = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!usuarioId) return;

    try {
      await inscribirUsuario(evento.id, Number(usuarioId));
      setUsuarioId("");
      await recargar(evento.id);
    } catch (inscribirError) {
      setError(inscribirError instanceof Error ? inscribirError.message : "No se pudo inscribir al usuario.");
    }
  };

  const handleCheckin = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!codigoQr.trim()) return;

    try {
      await checkinPorQr(evento.id, codigoQr.trim());
      setCodigoQr("");
      await recargar(evento.id);
    } catch (checkinError) {
      setError(checkinError instanceof Error ? checkinError.message : "No se pudo registrar el ingreso.");
    }
  };

  const handleCancelar = async (idInscripcion: number) => {
    try {
      await cancelarInscripcion(evento.id, idInscripcion);
      await recargar(evento.id);
    } catch (cancelError) {
      setError(cancelError instanceof Error ? cancelError.message : "No se pudo cancelar la inscripcion.");
    }
  };

  return (
    <div className="evento-modal-backdrop" role="dialog" aria-modal="true">
      <div className="evento-modal evento-modal-wide">
        <div className="evento-modal-header">
          <h3>Inscripciones: {evento.titulo}</h3>
          <button type="button" className="evento-modal-close" aria-label="Cerrar" onClick={onClose}>
            <X size={18} />
          </button>
        </div>

        {reporte && (
          <div className="evento-reporte-stats">
            <span>
              Inscritos: <strong>{reporte.inscritos}</strong>
            </span>
            <span>
              Asistieron: <strong>{reporte.asistieron}</strong>
            </span>
            <span>
              Aforo: <strong>{reporte.aforoMaximo ?? "Sin limite"}</strong>
            </span>
          </div>
        )}

        <div className="evento-inscripcion-forms">
          <form onSubmit={handleInscribir} className="evento-inline-form">
            <input
              type="text"
              inputMode="numeric"
              placeholder="ID de usuario a inscribir"
              value={usuarioId}
              onChange={(event) => /^\d{0,10}$/.test(event.target.value) && setUsuarioId(event.target.value)}
            />
            <button type="submit" className="gestion-eventos-btn primary">
              <UserPlus size={16} /> Inscribir
            </button>
          </form>

          <form onSubmit={handleCheckin} className="evento-inline-form">
            <input
              type="text"
              placeholder="Codigo QR de check-in"
              value={codigoQr}
              onChange={(event) => setCodigoQr(event.target.value)}
            />
            <button type="submit" className="gestion-eventos-btn primary">
              <QrCode size={16} /> Registrar ingreso
            </button>
          </form>
        </div>

        {error && <div className="evento-form-errors"><p>{error}</p></div>}

        {loading ? (
          <div className="evento-table-empty">Cargando...</div>
        ) : inscripciones.length === 0 ? (
          <div className="evento-table-empty">Aun no hay inscritos en este evento.</div>
        ) : (
          <div className="evento-table-wrapper">
            <table className="evento-table">
              <thead>
                <tr>
                  <th>Usuario</th>
                  <th>Tipo</th>
                  <th>Estado</th>
                  <th>Codigo QR</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {inscripciones.map((inscripcion) => (
                  <tr key={inscripcion.id}>
                    <td>{inscripcion.usuarioNombre ?? `Usuario #${inscripcion.usuarioId}`}</td>
                    <td>{inscripcion.tipoUsuario}</td>
                    <td>
                      <span className={`evento-status-badge inscripcion-${inscripcion.estado}`}>
                        {inscripcion.estado}
                      </span>
                    </td>
                    <td className="evento-qr-cell">{inscripcion.codigoQr}</td>
                    <td>
                      {inscripcion.estado !== "cancelado" && (
                        <button
                          type="button"
                          className="evento-action-button cancel"
                          onClick={() => handleCancelar(inscripcion.id)}
                          title="Cancelar inscripcion"
                        >
                          <XCircle size={16} />
                        </button>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
};

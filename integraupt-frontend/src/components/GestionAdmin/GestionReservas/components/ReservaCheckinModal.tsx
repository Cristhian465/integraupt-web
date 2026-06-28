import React, { useEffect, useRef, useState } from "react";
import { Camera, CameraOff, CheckCircle2, QrCode, ShieldAlert, X } from "lucide-react";
import { Html5Qrcode } from "html5-qrcode";
import {
  checkinReservaPorQr,
  gestionarReservaAdmin,
  obtenerEstadoActualReserva,
  type ReservaCheckinResultado
} from "../reservasService";

const QR_READER_ELEMENT_ID = "reserva-qr-reader";

const MOTIVO_APROBACION_AUTOMATICA = "Aprobado automaticamente al registrar el ingreso por QR.";

interface ReservaCheckinModalProps {
  open: boolean;
  usuarioGestionId: number | null;
  onClose: () => void;
}

export const ReservaCheckinModal: React.FC<ReservaCheckinModalProps> = ({ open, usuarioGestionId, onClose }) => {
  const [codigoQr, setCodigoQr] = useState("");
  const [escaneando, setEscaneando] = useState(false);
  const [procesando, setProcesando] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [resultado, setResultado] = useState<ReservaCheckinResultado | null>(null);
  const [aprobadaAutomaticamente, setAprobadaAutomaticamente] = useState(false);
  const scannerRef = useRef<Html5Qrcode | null>(null);

  useEffect(() => {
    if (open) {
      setCodigoQr("");
      setError(null);
      setResultado(null);
      setAprobadaAutomaticamente(false);
    } else {
      setEscaneando(false);
    }
  }, [open]);

  const detenerEscaner = async () => {
    const scanner = scannerRef.current;
    scannerRef.current = null;
    if (scanner) {
      try {
        await scanner.stop();
      } catch {
        // ya estaba detenido
      }
      scanner.clear();
    }
  };

  const procesarCodigo = async (valor: string) => {
    if (!valor.trim()) return;
    setProcesando(true);
    setError(null);
    setAprobadaAutomaticamente(false);
    try {
      const data = await checkinReservaPorQr(valor.trim());
      setResultado(data);
      setCodigoQr("");

      const reservaId = data.reserva.reservaId;
      if (reservaId != null) {
        const estadoActual = await obtenerEstadoActualReserva(reservaId);
        if (estadoActual?.trim().toLowerCase() === "pendiente") {
          if (usuarioGestionId == null) {
            setError(
              "El ingreso se registro, pero no se pudo aprobar automaticamente la reserva: no se identifico al administrador."
            );
          } else {
            try {
              await gestionarReservaAdmin(reservaId, {
                usuarioGestionId,
                accion: "Aprobar",
                motivo: MOTIVO_APROBACION_AUTOMATICA
              });
              setAprobadaAutomaticamente(true);
            } catch (aprobarError) {
              setError(
                aprobarError instanceof Error
                  ? `El ingreso se registro, pero no se pudo aprobar la reserva: ${aprobarError.message}`
                  : "El ingreso se registro, pero no se pudo aprobar la reserva automaticamente."
              );
            }
          }
        }
      }
    } catch (checkinError) {
      setError(checkinError instanceof Error ? checkinError.message : "No se pudo registrar el ingreso.");
      setResultado(null);
    } finally {
      setProcesando(false);
    }
  };

  useEffect(() => {
    if (!escaneando || !open) {
      return;
    }

    const scanner = new Html5Qrcode(QR_READER_ELEMENT_ID);
    scannerRef.current = scanner;
    let activo = true;

    scanner
      .start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 220 },
        async (decodedText) => {
          if (!activo) return;
          activo = false;
          await detenerEscaner();
          setEscaneando(false);
          await procesarCodigo(decodedText);
        },
        () => {
          // ignorar fotogramas sin codigo detectado
        }
      )
      .catch((startError) => {
        setError(
          startError instanceof Error
            ? `No se pudo acceder a la camara: ${startError.message}`
            : "No se pudo acceder a la camara."
        );
        setEscaneando(false);
      });

    return () => {
      activo = false;
      void detenerEscaner();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [escaneando, open]);

  if (!open) {
    return null;
  }

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    void procesarCodigo(codigoQr);
  };

  return (
    <div className="admin-modal-overlay" role="dialog" aria-modal="true">
      <div className="admin-modal admin-modal-md">
        <header className="admin-modal-header">
          <h3 className="admin-modal-title">
            <QrCode className="admin-modal-title-icon" />
            Check-in de reserva por QR
          </h3>
          <button type="button" className="admin-modal-close" onClick={onClose}>
            <X className="admin-modal-close-icon" />
          </button>
        </header>

        <div className="admin-modal-form">
          <p className="admin-modal-text">
            Escanea el codigo QR mostrado por el solicitante o ingresalo manualmente para registrar su ingreso.
          </p>

          <form onSubmit={handleSubmit} className="reserva-checkin-form">
            <input
              type="text"
              placeholder="Token o codigo QR de la reserva"
              value={codigoQr}
              onChange={(event) => setCodigoQr(event.target.value)}
              className="admin-input"
              disabled={procesando}
            />
            <button type="submit" className="admin-modal-btn admin-modal-primary" disabled={procesando}>
              Registrar ingreso
            </button>
            <button
              type="button"
              className="admin-modal-btn admin-modal-secondary"
              onClick={() => setEscaneando((prev) => !prev)}
            >
              {escaneando ? <CameraOff className="admin-modal-btn-icon" /> : <Camera className="admin-modal-btn-icon" />}
              {escaneando ? "Detener camara" : "Escanear con camara"}
            </button>
          </form>

          {escaneando && (
            <div className="reserva-checkin-scanner-box">
              <div id={QR_READER_ELEMENT_ID} />
            </div>
          )}

          {error && (
            <p className="admin-error-message">
              <ShieldAlert size={16} /> {error}
            </p>
          )}

          {resultado && (
            <div className="reserva-checkin-resultado">
              <p className="reserva-checkin-resultado-titulo">
                <CheckCircle2 size={18} />
                {aprobadaAutomaticamente
                  ? "Ingreso registrado y reserva aprobada automaticamente."
                  : resultado.mensaje}
              </p>
              <dl>
                <dt>Solicitante</dt>
                <dd>{resultado.reserva.solicitanteNombre ?? "No disponible"}</dd>
                <dt>Codigo</dt>
                <dd>{resultado.reserva.solicitanteCodigo ?? "No disponible"}</dd>
                <dt>Espacio</dt>
                <dd>{resultado.reserva.laboratorio ?? "No disponible"}</dd>
                <dt>Fecha</dt>
                <dd>{resultado.reserva.fecha ?? "No disponible"}</dd>
                <dt>Horario</dt>
                <dd>{resultado.reserva.hora ?? "No disponible"}</dd>
              </dl>
            </div>
          )}

          <div className="admin-modal-actions">
            <button type="button" className="admin-modal-btn admin-modal-secondary" onClick={onClose}>
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

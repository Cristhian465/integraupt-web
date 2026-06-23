package com.integraupt.qr.service;

import java.time.Instant;
import java.util.Locale;
import java.util.Map;
import java.util.Objects;
import java.util.Optional;
import java.util.UUID;
import java.util.concurrent.ConcurrentHashMap;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;

import com.integraupt.qr.dto.ReservaInfo;
import com.integraupt.qr.dto.ReservaQrRequest;
import com.integraupt.qr.dto.ReservaQrResponse;
import com.integraupt.qr.dto.ReservaQrVerificationResponse;
import com.integraupt.qr.exception.ReservaQrNotFoundException;
import com.integraupt.qr.model.ReservaQrData;
import com.integraupt.qr.service.storage.ReservaQrStorage;

@Service
public class ReservaQrService {

    private final Map<String, ReservaQrData> almacen = new ConcurrentHashMap<>();
    private final QrGeneratorService qrGeneratorService;
    private final ReservaQrStorage reservaQrStorage;
    private final String verificationBaseUrl;

    public ReservaQrService(QrGeneratorService qrGeneratorService,
                            ReservaQrStorage reservaQrStorage,
                            @Value("${app.verification-base-url:http://localhost:5173}") String verificationBaseUrl) {
        this.qrGeneratorService = qrGeneratorService;
        this.reservaQrStorage = reservaQrStorage;
        String normalizedBaseUrl = Objects.requireNonNullElse(verificationBaseUrl, "").trim();
        if (normalizedBaseUrl.isEmpty()) {
            normalizedBaseUrl = "http://localhost:5173";
        }
        this.verificationBaseUrl = normalizedBaseUrl.endsWith("/")
                ? normalizedBaseUrl.substring(0, normalizedBaseUrl.length() - 1)
                : normalizedBaseUrl;
    }

    public ReservaQrResponse crearQr(ReservaQrRequest request) {
        ReservaQrData existente = buscarReservaExistente(request.getReservaId());

        String token = Optional.ofNullable(normalizar(request.getToken()))
                .filter(s -> !s.isEmpty())
                .orElseGet(() -> existente != null ? existente.getToken() : UUID.randomUUID().toString());

        ReservaInfo reserva = new ReservaInfo(
                request.getReservaId(),
                request.getLaboratorio(),
                request.getFecha(),
                request.getHora(),
                request.getEstado(),
                request.getSolicitanteNombre(),
                request.getSolicitanteCodigo());

        Instant generadoEn = Instant.now();
        ReservaQrData data = new ReservaQrData(token, reserva, generadoEn);
        String llave = normalizarLlave(token);
        if (llave != null) {
            if (existente != null) {
                String llaveExistente = normalizarLlave(existente.getToken());
                if (llaveExistente != null && !llaveExistente.equals(llave)) {
                    almacen.remove(llaveExistente);
                }
            }
            almacen.put(llave, data);
        }
        reservaQrStorage.save(data);

        String verificacionUrl = verificationBaseUrl + "/qr/reservas/" + token;
        return construirRespuesta(token, reserva, generadoEn, verificacionUrl);
    }

    public ReservaQrVerificationResponse obtenerPorToken(String token) {
        String llave = normalizarLlave(token);
        if (llave == null) {
            throw new ReservaQrNotFoundException(token);
        }

        ReservaQrData data = almacen.get(llave);
        if (data == null) {
            data = reservaQrStorage.findByToken(llave).orElse(null);
            if (data != null) {
                almacen.put(llave, data);
            }
        }

        if (data == null) {
            throw new ReservaQrNotFoundException(llave);
        }

        return new ReservaQrVerificationResponse(
                data.getToken(),
                data.getReserva(),
                data.getGeneradoEn(),
                Instant.now());
    }

    public ReservaQrResponse obtenerPorReservaId(Long reservaId) {
        ReservaQrData data = buscarReservaExistente(reservaId);
        if (data == null) {
            throw new ReservaQrNotFoundException(String.valueOf(reservaId));
        }

        String verificacionUrl = verificationBaseUrl + "/qr/reservas/" + data.getToken();
        return construirRespuesta(data.getToken(), data.getReserva(), data.getGeneradoEn(), verificacionUrl);
    }


    private ReservaQrData buscarReservaExistente(Long reservaId) {
        if (reservaId == null) {
            return null;
        }

        ReservaQrData enMemoria = almacen.values().stream()
                .filter(data -> data.getReserva() != null
                        && reservaId.equals(data.getReserva().getReservaId()))
                .findFirst()
                .orElse(null);

        if (enMemoria != null) {
            return enMemoria;
        }

        ReservaQrData persistido = reservaQrStorage.findByReservaId(reservaId).orElse(null);
        if (persistido != null) {
            String llave = normalizarLlave(persistido.getToken());
            if (llave != null) {
                almacen.putIfAbsent(llave, persistido);
            }
        }
        return persistido;
    }


    private String normalizarLlave(String valor) {
        String normalizado = normalizar(valor);
        if (Objects.isNull(normalizado) || normalizado.isEmpty()) {
            return null;
        }
        return normalizado.toLowerCase(Locale.ROOT);
    }


    private String normalizar(String valor) {
        return Objects.nonNull(valor) ? valor.trim() : null;
    }


    private ReservaQrResponse construirRespuesta(String token,
                                                 ReservaInfo reserva,
                                                 Instant generadoEn,
                                                 String verificacionUrl) {
        String qrBase64 = qrGeneratorService.generarCodigo(verificacionUrl);
        return new ReservaQrResponse(token, verificacionUrl, qrBase64, reserva, generadoEn);
    }
}
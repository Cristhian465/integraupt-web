package com.reserva.service;

import java.time.format.DateTimeFormatter;
import java.util.Objects;
import java.util.Optional;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.boot.web.client.RestTemplateBuilder;
import org.springframework.http.ResponseEntity;
import org.springframework.stereotype.Service;
import org.springframework.web.client.RestClientException;
import org.springframework.web.client.RestTemplate;

import com.reserva.dto.ReservaQrRequest;
import com.reserva.dto.ReservaQrResponse;
import com.reserva.dto.SolicitanteInfo;
import com.reserva.model.BloqueHorario;
import com.reserva.model.Espacio;
import com.reserva.model.Reserva;

@Service
public class ReservaQrClient {

    private static final Logger LOGGER = LoggerFactory.getLogger(ReservaQrClient.class);
    private static final DateTimeFormatter HORA_FORMATO = DateTimeFormatter.ofPattern("HH:mm");


    private final RestTemplate restTemplate;
    private final String baseUrl;
    private final UsuarioInfoService usuarioInfoService;

    public ReservaQrClient(RestTemplateBuilder restTemplateBuilder,
                           @Value("${app.qr-service.base-url:http://localhost:8090}") String baseUrl,
                           UsuarioInfoService usuarioInfoService) {
        this.restTemplate = restTemplateBuilder.build();
        this.baseUrl = normalizarBaseUrl(baseUrl);
        this.usuarioInfoService = usuarioInfoService;
    }

    public Optional<ReservaQrResponse> generarQr(Reserva reserva, Espacio espacio, BloqueHorario bloque) {
        if (Objects.isNull(reserva.getId())) {
            throw new IllegalArgumentException("La reserva debe estar persistida antes de generar el QR");
        }

        ReservaQrRequest request = construirRequest(reserva, espacio, bloque);

        try {
            ResponseEntity<ReservaQrResponse> response = restTemplate.postForEntity(
                    baseUrl + "/api/v1/qr/reservas", request, ReservaQrResponse.class);
            return Optional.ofNullable(response.getBody());
        } catch (RestClientException ex) {
            LOGGER.warn("No se pudo generar el QR para la reserva {}: {}", reserva.getId(), ex.getMessage());
            return Optional.empty();
        }
    }


    public Optional<ReservaQrResponse> obtenerQrExistente(Long reservaId) {
        if (reservaId == null) {
            return Optional.empty();
        }

        try {
            ResponseEntity<ReservaQrResponse> response = restTemplate.getForEntity(
                    baseUrl + "/api/v1/qr/reservas/reserva/" + reservaId,
                    ReservaQrResponse.class);
            return Optional.ofNullable(response.getBody());
        } catch (RestClientException ex) {
            LOGGER.warn("No se pudo recuperar el QR existente para la reserva {}: {}", reservaId, ex.getMessage());
            return Optional.empty();
        }
    }

    private ReservaQrRequest construirRequest(Reserva reserva, Espacio espacio, BloqueHorario bloque) {
        ReservaQrRequest request = new ReservaQrRequest();
        request.setReservaId(reserva.getId().longValue());
        request.setLaboratorio(obtenerNombreEspacio(reserva, espacio));
        request.setFecha(reserva.getFechaReserva() != null ? reserva.getFechaReserva().toString() : "");
        request.setHora(obtenerHorario(reserva, bloque));
        request.setEstado(reserva.getEstado());
        SolicitanteInfo solicitante = obtenerSolicitante(reserva.getUsuarioId());
        request.setSolicitanteNombre(solicitante.nombreCompleto());
        request.setSolicitanteCodigo(solicitante.codigo());
        return request;
    }

    private SolicitanteInfo obtenerSolicitante(Integer usuarioId) {
        return usuarioInfoService.obtenerSolicitante(usuarioId)
                .orElseGet(() -> new SolicitanteInfo(
                        "Usuario " + (usuarioId != null ? usuarioId : "desconocido"),
                        usuarioId != null ? usuarioId.toString() : "SIN-CODIGO",
                        "Usuario"));
    }


    private String obtenerNombreEspacio(Reserva reserva, Espacio espacio) {
        if (Objects.nonNull(espacio)) {
            String nombre = normalizarTexto(espacio.getNombre());
            if (!nombre.isEmpty()) {
                return nombre;
            }
    }
        Integer espacioId = reserva.getEspacioId();
        if (Objects.nonNull(espacioId)) {
            return "Espacio " + espacioId;
        }
        return "Espacio por confirmar";
    }
    private String obtenerHorario(Reserva reserva, BloqueHorario bloque) {
        if (Objects.nonNull(bloque)) {
            String horario = normalizarTexto(formatearHorario(bloque));
            String nombreBloque = normalizarTexto(bloque.getNombre());

            if (!horario.isEmpty() && !nombreBloque.isEmpty()) {
                return nombreBloque + " (" + horario + ")";
            }
            if (!horario.isEmpty()) {
                return horario;
            }
            if (!nombreBloque.isEmpty()) {
                return nombreBloque;
            }
        }

        Integer bloqueId = reserva.getBloqueId();
        if (Objects.nonNull(bloqueId)) {
            return "Bloque " + bloqueId;
        }
        return "Horario por confirmar";
    }

    private String formatearHorario(BloqueHorario bloque) {
        if (Objects.isNull(bloque)) {
            return "";
        }
        String inicio = bloque.getHoraInicio() != null ? bloque.getHoraInicio().format(HORA_FORMATO) : null;
        String fin = bloque.getHoraFinal() != null ? bloque.getHoraFinal().format(HORA_FORMATO) : null;

        if (inicio != null && fin != null) {
            return inicio + " - " + fin;
        }
        if (inicio != null) {
            return inicio;
        }
        if (fin != null) {
            return fin;
        }
        return "";
    }

    private String normalizarBaseUrl(String url) {
        String valor = Objects.requireNonNullElse(url, "").trim();
        if (valor.isEmpty()) {
            valor = "http://localhost:8090";
        }
        if (valor.endsWith("/")) {
            return valor.substring(0, valor.length() - 1);
        }
        return valor;
    }

    private String normalizarTexto(String valor) {
        return Objects.nonNull(valor) ? valor.trim() : "";
    }
}
package com.reserva.service;

import com.reserva.dto.SancionVerificacionResponse;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.boot.web.client.RestTemplateBuilder;
import org.springframework.http.ResponseEntity;
import org.springframework.stereotype.Service;
import org.springframework.web.client.RestClientException;
import org.springframework.web.client.RestTemplate;

import java.util.Optional;

@Service
public class SancionVerificationClient {

    private static final Logger LOGGER = LoggerFactory.getLogger(SancionVerificationClient.class);

    private final RestTemplate restTemplate;
    private final String baseUrl;

    public SancionVerificationClient(RestTemplateBuilder restTemplateBuilder,
                                     @Value("${app.sanciones-service.base-url:http://localhost:8087}") String baseUrl) {
        this.restTemplate = restTemplateBuilder.build();
        this.baseUrl = normalizarBaseUrl(baseUrl);
    }

    public Optional<SancionVerificacionResponse> verificarSancionActiva(Long usuarioId, String tipoUsuario) {
        if (usuarioId == null || tipoUsuario == null || tipoUsuario.isBlank()) {
            return Optional.empty();
        }

        try {
            ResponseEntity<SancionVerificacionResponse> response = restTemplate.getForEntity(
                    baseUrl + "/api/sanciones/verificacion?usuarioId=" + usuarioId + "&tipoUsuario=" + tipoUsuario,
                    SancionVerificacionResponse.class);
            return Optional.ofNullable(response.getBody());
        } catch (RestClientException ex) {
            LOGGER.warn("No se pudo verificar sanciones para el usuario {}: {}", usuarioId, ex.getMessage());
            return Optional.empty();
        }
    }

    private String normalizarBaseUrl(String url) {
        String valor = url != null ? url.trim() : "";
        if (valor.isEmpty()) {
            valor = "http://localhost:8087";
        }
        if (valor.endsWith("/")) {
            return valor.substring(0, valor.length() - 1);
        }
        return valor;
    }
}
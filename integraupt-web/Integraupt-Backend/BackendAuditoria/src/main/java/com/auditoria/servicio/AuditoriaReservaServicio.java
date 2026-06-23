package com.auditoria.servicio;

import com.auditoria.dto.AuditoriaReservaFiltro;
import com.auditoria.dto.AuditoriaReservaResponse;
import com.auditoria.interfaces.IAuditoriaReservaServicio;
import com.auditoria.repositorio.AuditoriaReservaRepositorio;
import com.auditoria.repositorio.proyecciones.AuditoriaReservaResumenProjection;
import java.util.List;
import java.util.Objects;
import java.util.stream.Collectors;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.server.ResponseStatusException;

@Service
@Transactional(readOnly = true)
public class AuditoriaReservaServicio implements IAuditoriaReservaServicio {

    private final AuditoriaReservaRepositorio auditoriaReservaRepositorio;

    public AuditoriaReservaServicio(AuditoriaReservaRepositorio auditoriaReservaRepositorio) {
        this.auditoriaReservaRepositorio = auditoriaReservaRepositorio;
    }

    @Override
    public List<AuditoriaReservaResponse> buscar(AuditoriaReservaFiltro filtro) {
        List<AuditoriaReservaResumenProjection> registros = auditoriaReservaRepositorio.buscarResumenes(
                filtro.reservaIdOptional().orElse(null),
                filtro.estadoOptional().map(String::toUpperCase).orElse(null),
                filtro.terminoUsuarioOptional().orElse(null),
                filtro.fechaInicioOptional().orElse(null),
                filtro.fechaFinOptional().orElse(null)
        );

        return mapear(registros);
    }

    @Override
    public List<AuditoriaReservaResponse> listarPorReserva(Integer reservaId) {
        List<AuditoriaReservaResumenProjection> registros = auditoriaReservaRepositorio.listarPorReserva(reservaId);
        return mapear(registros);
    }

    @Override
    public AuditoriaReservaResponse buscarPorId(Integer idAudit) {
        return auditoriaReservaRepositorio.obtenerDetalle(idAudit)
                .map(this::mapear)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Auditoria no encontrada"));
    }

    private List<AuditoriaReservaResponse> mapear(List<AuditoriaReservaResumenProjection> registros) {
        return registros.stream()
                .filter(Objects::nonNull)
                .map(this::mapear)
                .collect(Collectors.toList());
    }

    private AuditoriaReservaResponse mapear(AuditoriaReservaResumenProjection projection) {
        return new AuditoriaReservaResponse(
                projection.getIdAudit(),
                projection.getIdReserva(),
                projection.getEstadoAnterior(),
                projection.getEstadoNuevo(),
                projection.getFechaCambio(),
                projection.getUsuarioCambioId(),
                normalizarNombre(projection.getUsuarioCambioNombre()),
                projection.getUsuarioCambioDocumento(),
                projection.getEstadoReservaActual(),
                projection.getDescripcionUso(),
                projection.getFechaReserva(),
                projection.getCodigoEspacio(),
                projection.getNombreEspacio()
        );
    }

    private String normalizarNombre(String nombre) {
        if (nombre == null) {
            return null;
        }
        return nombre.trim().replaceAll("\\s{2,}", " ");
    }
}

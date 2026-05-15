package com.AdReserva.service.mapper;

import com.AdReserva.dto.AdminReservaCardDto;
import com.AdReserva.repository.projection.AdminReservaProjection;

public final class AdminReservaMapper {

    private AdminReservaMapper() {
    }

    public static AdminReservaCardDto toDto(AdminReservaProjection projection) {
        if (projection == null) {
            return null;
        }
        return new AdminReservaCardDto(
                projection.getReservaId(),
                projection.getEstado(),
                projection.getFechaReserva(),
                projection.getFechaSolicitud(),
                projection.getDescripcionUso(),
                projection.getCantidadEstudiantes(),
                projection.getEspacioCodigo(),
                projection.getEspacioNombre(),
                projection.getEspacioTipo(),
                projection.getEscuelaId(),
                projection.getEscuelaNombre(),
                projection.getFacultadId(),
                projection.getFacultadNombre(),
                projection.getBloqueNombre(),
                projection.getHoraInicio(),
                projection.getHoraFin(),
                projection.getCursoNombre(),
                projection.getSolicitanteId(),
                projection.getSolicitanteNombre(),
                projection.getSolicitanteApellido(),
                projection.getSolicitanteCorreo(),
                projection.getUltimaAccion(),
                projection.getMotivo(),
                projection.getComentarios(),
                projection.getFechaGestion(),
                projection.getGestorId(),
                projection.getGestorNombre(),
                projection.getGestorApellido()
        );
    }
}
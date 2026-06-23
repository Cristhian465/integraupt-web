package com.horarios.dto;

import java.util.List;

public record HorarioSemanalResponse(
        Integer bloqueId,
        String bloqueNombre,
        String horaInicio,
        String horaFin,
        List<HorarioDiaResponse> dias
) {
}
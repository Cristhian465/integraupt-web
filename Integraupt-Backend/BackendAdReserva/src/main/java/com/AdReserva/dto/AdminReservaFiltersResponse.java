package com.AdReserva.dto;

import java.util.List;

public record AdminReservaFiltersResponse(
        List<String> tiposEspacio,
        List<SimpleOptionDto> facultades,
        List<EscuelaOptionDto> escuelas
) {
}
package com.AdReserva.dto;

import java.util.List;

public record AdminReservaListResponse(
        List<AdminReservaCardDto> reservas,
        AdminReservaSummaryDto resumen
) {
}
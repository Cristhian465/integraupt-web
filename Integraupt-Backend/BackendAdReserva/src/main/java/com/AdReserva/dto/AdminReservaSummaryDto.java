package com.AdReserva.dto;

public record AdminReservaSummaryDto(
        long pendientes,
        long aprobadas,
        long rechazadas,
        long canceladas
) {
    public static AdminReservaSummaryDto empty() {
        return new AdminReservaSummaryDto(0, 0, 0, 0);
    }
}
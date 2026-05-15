package com.AdReserva.controller;

import com.AdReserva.dto.AdminGestionReservaRequest;
import com.AdReserva.dto.AdminReservaCardDto;
import com.AdReserva.dto.AdminReservaFilter;
import com.AdReserva.dto.AdminReservaFiltersResponse;
import com.AdReserva.dto.AdminReservaListResponse;
import com.AdReserva.service.AdminReservaService;
import jakarta.validation.Valid;
import org.springframework.format.annotation.DateTimeFormat;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.time.LocalDate;

@RestController
@RequestMapping("/api/admin/reservas")
public class AdminReservaController {

    private final AdminReservaService adminReservaService;

    public AdminReservaController(AdminReservaService adminReservaService) {
        this.adminReservaService = adminReservaService;
    }

    @GetMapping
    public AdminReservaListResponse listarReservas(
            @RequestParam(name = "estado", required = false) String estado,
            @RequestParam(name = "tipoEspacio", required = false) String tipoEspacio,
            @RequestParam(name = "facultadId", required = false) Integer facultadId,
            @RequestParam(name = "escuelaId", required = false) Integer escuelaId,
            @RequestParam(name = "fecha", required = false)
            @DateTimeFormat(iso = DateTimeFormat.ISO.DATE) LocalDate fecha,
            @RequestParam(name = "search", required = false) String search,
            @RequestParam(name = "usuarioId", required = false) Integer usuarioId,
            @RequestParam(name = "rol", required = false) String rol
    ) {
        AdminReservaFilter filter = new AdminReservaFilter(
                estado,
                tipoEspacio,
                facultadId,
                escuelaId,
                fecha,
                search,
                usuarioId,
                rol
        );
        return adminReservaService.obtenerReservas(filter);
    }

    @GetMapping("/filtros")
    public AdminReservaFiltersResponse obtenerFiltros(
            @RequestParam(name = "usuarioId", required = false) Integer usuarioId,
            @RequestParam(name = "rol", required = false) String rol
    ) {
        return adminReservaService.obtenerFiltros(usuarioId, rol);
    }


    @PostMapping("/{reservaId}/gestionar")
    public AdminReservaCardDto gestionarReserva(
            @PathVariable Integer reservaId,
            @Valid @RequestBody AdminGestionReservaRequest request
    ) {
        return adminReservaService.gestionarReserva(reservaId, request);
    }
}
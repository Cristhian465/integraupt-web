package com.BackendReportes.servicio;

import com.BackendReportes.dto.EstadisticasGeneralesResponse;
import com.BackendReportes.dto.UsoEspacioResponse;
import com.BackendReportes.dto.ReservasMesResponse;
import com.BackendReportes.repositorio.ReportesRepositorio;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;

@Service
@Transactional(readOnly = true)
public class ReportesServicio {

    private final ReportesRepositorio reportesRepositorio;

    public ReportesServicio(ReportesRepositorio reportesRepositorio) {
        this.reportesRepositorio = reportesRepositorio;
    }

    public EstadisticasGeneralesResponse obtenerEstadisticasGenerales() {
        return reportesRepositorio.obtenerEstadisticasGenerales();
    }

    public List<UsoEspacioResponse> obtenerUsoEspacios() {
        return reportesRepositorio.obtenerUsoEspacios();
    }

    public List<ReservasMesResponse> obtenerReservasPorMes() {
        return reportesRepositorio.obtenerReservasPorMes();
    }
}
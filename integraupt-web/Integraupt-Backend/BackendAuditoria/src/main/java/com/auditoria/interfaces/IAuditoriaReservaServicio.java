package com.auditoria.interfaces;

import com.auditoria.dto.AuditoriaReservaFiltro;
import com.auditoria.dto.AuditoriaReservaResponse;
import java.util.List;

public interface IAuditoriaReservaServicio {

    List<AuditoriaReservaResponse> buscar(AuditoriaReservaFiltro filtro);

    List<AuditoriaReservaResponse> listarPorReserva(Integer reservaId);

    AuditoriaReservaResponse buscarPorId(Integer idAudit);
}

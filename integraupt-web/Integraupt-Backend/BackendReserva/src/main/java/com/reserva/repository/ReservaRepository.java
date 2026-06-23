package com.reserva.repository;

import com.reserva.model.Reserva;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.time.LocalDate;
import java.util.Collection;
import java.util.List;
import java.util.Optional;

@Repository
public interface ReservaRepository extends JpaRepository<Reserva, Integer> {
    List<Reserva> findByUsuarioId(Integer usuarioId);

    List<Reserva> findByUsuarioIdAndEstadoIn(Integer usuarioId, Collection<String> estados);

    List<Reserva> findTop20ByUsuarioIdOrderByFechaReservaDesc(Integer usuarioId);

    Optional<Reserva> findByUsuarioIdAndBloqueIdAndFechaReserva(
            Integer usuarioId,
            Integer bloqueId,
            LocalDate fechaReserva);

    Optional<Reserva> findByEspacioIdAndBloqueIdAndFechaReserva(
            Integer espacioId,
            Integer bloqueId,
            LocalDate fechaReserva);
}
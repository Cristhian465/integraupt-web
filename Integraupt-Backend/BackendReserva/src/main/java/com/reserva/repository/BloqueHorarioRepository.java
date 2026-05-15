package com.reserva.repository;

import com.reserva.model.BloqueHorario;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface BloqueHorarioRepository extends JpaRepository<BloqueHorario, Integer> {

    @Query(value = """
            SELECT DISTINCT b.*
            FROM bloqueshorarios b
            INNER JOIN horarios h ON h.bloque = b.IdBloque
            WHERE h.espacio = :espacioId
            ORDER BY b.Orden ASC, b.Nombre ASC
            """, nativeQuery = true)
    List<BloqueHorario> findDistinctByEspacioIdOrderByOrden(@Param("espacioId") Integer espacioId);
}
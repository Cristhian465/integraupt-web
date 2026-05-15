package com.AdReserva.repository;

import com.AdReserva.model.ReservaGestion;
import org.springframework.data.jpa.repository.JpaRepository;

public interface ReservaGestionRepository extends JpaRepository<ReservaGestion, Integer> {
}
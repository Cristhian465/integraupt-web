package com.AdReserva.repository;

import com.AdReserva.model.Escuela;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

public interface EscuelaRepository extends JpaRepository<Escuela, Integer> {
    List<Escuela> findByFacultadId(Integer facultadId);
}
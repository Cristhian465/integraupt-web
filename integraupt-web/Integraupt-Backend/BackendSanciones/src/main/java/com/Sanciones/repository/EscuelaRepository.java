package com.Sanciones.repository;

import com.Sanciones.model.Escuela;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

public interface EscuelaRepository extends JpaRepository<Escuela, Long> {

    List<Escuela> findByFacultadIdOrderByNombreAsc(Long facultadId);

    List<Escuela> findAllByOrderByNombreAsc();
}
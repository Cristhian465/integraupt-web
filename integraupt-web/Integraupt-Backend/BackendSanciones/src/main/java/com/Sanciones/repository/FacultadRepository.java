package com.Sanciones.repository;

import com.Sanciones.model.Facultad;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

public interface FacultadRepository extends JpaRepository<Facultad, Long> {

    List<Facultad> findAllByOrderByNombreAsc();
}
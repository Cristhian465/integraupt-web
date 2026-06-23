package com.horarios.repository;

import com.horarios.model.Espacio;
import org.springframework.data.jpa.repository.JpaRepository;

public interface EspacioRepository extends JpaRepository<Espacio, Integer> {
}
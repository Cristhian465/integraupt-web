package com.inicidencia.repository;

import com.inicidencia.model.Espacio;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface EspacioRepository extends JpaRepository<Espacio, Integer> {
}
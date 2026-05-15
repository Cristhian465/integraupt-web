package com.AdReserva.repository;

import com.AdReserva.model.Espacio;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;

import java.util.List;

public interface EspacioRepository extends JpaRepository<Espacio, Integer> {

    @Query(value = "SELECT DISTINCT Tipo FROM espacio ORDER BY Tipo", nativeQuery = true)
    List<String> obtenerTiposDeEspacio();
}
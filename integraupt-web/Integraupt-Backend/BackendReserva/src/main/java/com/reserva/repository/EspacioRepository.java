package com.reserva.repository;

import com.reserva.model.Espacio;
import org.springframework.data.domain.Sort;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface EspacioRepository extends JpaRepository<Espacio, Integer> {

    List<Espacio> findByEstadoOrderByNombreAsc(Integer estado);

    List<Espacio> findByEstadoAndEscuelaIdOrderByNombreAsc(Integer estado, Integer escuelaId);

    List<Espacio> findByEscuelaIdOrderByNombreAsc(Integer escuelaId);

    default List<Espacio> findAllOrdenados() {
        return findAll(Sort.by(Sort.Direction.ASC, "nombre"));
    }
}
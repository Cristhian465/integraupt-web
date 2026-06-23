package com.reserva.repository;

import com.reserva.model.Escuela;
import org.springframework.data.domain.Sort;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface EscuelaRepository extends JpaRepository<Escuela, Integer> {

    List<Escuela> findByFacultadIdOrderByNombreAsc(Integer facultadId);

    default List<Escuela> findAllOrdenados() {
        return findAll(Sort.by(Sort.Direction.ASC, "nombre"));
    }
}
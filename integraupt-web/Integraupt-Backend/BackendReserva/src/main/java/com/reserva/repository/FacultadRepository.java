package com.reserva.repository;

import com.reserva.model.Facultad;
import org.springframework.data.domain.Sort;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface FacultadRepository extends JpaRepository<Facultad, Integer> {

    default List<Facultad> findAllOrdenados() {
        return findAll(Sort.by(Sort.Direction.ASC, "nombre"));
    }
}
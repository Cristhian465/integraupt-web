package com.reserva.repository;

import com.reserva.model.Curso;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface CursoRepository extends JpaRepository<Curso, Integer> {

    List<Curso> findByEscuelaIdAndEstadoOrderByNombreAsc(Integer escuelaId, Integer estado);
}
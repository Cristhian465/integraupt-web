package com.horarios.repository;

import com.horarios.model.DiaSemana;
import com.horarios.model.Horario;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;
import java.util.Optional;

public interface HorarioRepository extends JpaRepository<Horario, Integer> {

    List<Horario> findByEspacioIdOrderByBloqueOrdenAscDiaSemanaAsc(Integer espacioId);

    List<Horario> findByDiaSemanaOrderByBloqueOrdenAsc(DiaSemana diaSemana);

    List<Horario> findByOcupado(boolean ocupado);

    Optional<Horario> findByEspacioIdAndBloqueIdAndDiaSemana(Integer espacioId, Integer bloqueId, DiaSemana diaSemana);
}
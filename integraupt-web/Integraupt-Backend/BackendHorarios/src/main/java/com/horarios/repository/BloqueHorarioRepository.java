package com.horarios.repository;

import com.horarios.model.BloqueHorario;
import org.springframework.data.jpa.repository.JpaRepository;

public interface BloqueHorarioRepository extends JpaRepository<BloqueHorario, Integer> {
}
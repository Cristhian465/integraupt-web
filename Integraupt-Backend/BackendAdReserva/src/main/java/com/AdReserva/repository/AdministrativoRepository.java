package com.AdReserva.repository;

import com.AdReserva.model.Administrativo;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.Optional;

public interface AdministrativoRepository extends JpaRepository<Administrativo, Integer> {

    Optional<Administrativo> findByUsuarioId(Integer usuarioId);
}
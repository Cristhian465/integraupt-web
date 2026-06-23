package com.Sanciones.repository;

import com.Sanciones.model.Sancion;
import com.Sanciones.model.SancionEstado;
import com.Sanciones.model.TipoUsuario;
import org.springframework.data.jpa.repository.JpaRepository;

import java.time.LocalDate;
import java.util.List;
import java.util.Optional;

public interface SancionRepository extends JpaRepository<Sancion, Long> {

    List<Sancion> findByEstado(SancionEstado estado);

    Optional<Sancion> findFirstByUsuarioIdAndTipoUsuarioAndEstado(Long usuarioId, TipoUsuario tipoUsuario, SancionEstado estado);

    List<Sancion> findByUsuarioIdAndTipoUsuario(Long usuarioId, TipoUsuario tipoUsuario);

    boolean existsByUsuarioIdAndTipoUsuarioAndEstadoAndFechaFinGreaterThanEqual(Long usuarioId, TipoUsuario tipoUsuario, SancionEstado estado, LocalDate fecha);
}
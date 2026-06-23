package com.Sanciones.service;

import com.Sanciones.dto.usuario.UsuarioBusquedaResponse;
import com.Sanciones.model.Docente;
import com.Sanciones.model.Estudiante;
import com.Sanciones.model.RolUsuarioBusqueda;
import com.Sanciones.model.TipoUsuario;
import com.Sanciones.repository.DocenteRepository;
import com.Sanciones.repository.EstudianteRepository;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;

import java.util.List;
import java.util.stream.Collectors;

@Service
public class UsuarioBusquedaService {

    private static final int DEFAULT_LIMIT = 20;

    private final EstudianteRepository estudianteRepository;
    private final DocenteRepository docenteRepository;

    public UsuarioBusquedaService(EstudianteRepository estudianteRepository, DocenteRepository docenteRepository) {
        this.estudianteRepository = estudianteRepository;
        this.docenteRepository = docenteRepository;
    }

    public List<UsuarioBusquedaResponse> buscarUsuarios(String tipoUsuarioValor,
                                                        String rolValor,
                                                        String query,
                                                        Long facultadId,
                                                        Long escuelaId,
                                                        Long escuelaContextoId,
                                                        Integer limite) {
        TipoUsuario tipoUsuario = TipoUsuario.fromNombre(tipoUsuarioValor);
        RolUsuarioBusqueda rolBusqueda = RolUsuarioBusqueda.fromNombre(rolValor);
        String termino = StringUtils.hasText(query) ? query.trim() : null;
        Pageable pageable = PageRequest.of(0, (limite != null && limite > 0) ? limite : DEFAULT_LIMIT);

        Long facultadFiltro = facultadId;
        Long escuelaFiltro = escuelaId;

        if (rolBusqueda == RolUsuarioBusqueda.SUPERVISOR) {
            Long escuelaRestriccion = escuelaContextoId != null ? escuelaContextoId : escuelaId;
            if (escuelaRestriccion == null) {
                throw new IllegalArgumentException("Los supervisores deben tener una escuela asociada para realizar búsquedas.");
            }
            escuelaFiltro = escuelaRestriccion;
            facultadFiltro = null;
        }

        return switch (tipoUsuario) {
            case ESTUDIANTE -> estudianteRepository.buscar(termino, facultadFiltro, escuelaFiltro, pageable)
                    .stream()
                    .map(this::mapearEstudiante)
                    .collect(Collectors.toList());
            case DOCENTE -> docenteRepository.buscar(termino, facultadFiltro, escuelaFiltro, pageable)
                    .stream()
                    .map(this::mapearDocente)
                    .collect(Collectors.toList());
        };
    }

    private UsuarioBusquedaResponse mapearEstudiante(Estudiante estudiante) {
        String nombreCompleto = estudiante.getUsuario() != null
                ? estudiante.getUsuario().getNombreCompleto()
                : null;
        String escuelaNombre = estudiante.getEscuela() != null ? estudiante.getEscuela().getNombre() : null;
        Long facultadId = estudiante.getEscuela() != null ? estudiante.getEscuela().getFacultadId() : null;
        String facultadNombre = (estudiante.getEscuela() != null && estudiante.getEscuela().getFacultad() != null)
                ? estudiante.getEscuela().getFacultad().getNombre()
                : null;

        return new UsuarioBusquedaResponse(
                estudiante.getUsuarioId(),
                nombreCompleto,
                estudiante.getCodigo(),
                estudiante.getEscuelaId(),
                escuelaNombre,
                facultadId,
                facultadNombre
        );
    }

    private UsuarioBusquedaResponse mapearDocente(Docente docente) {
        String nombreCompleto = docente.getUsuario() != null ? docente.getUsuario().getNombreCompleto() : null;
        String escuelaNombre = docente.getEscuela() != null ? docente.getEscuela().getNombre() : null;
        Long facultadId = docente.getEscuela() != null ? docente.getEscuela().getFacultadId() : null;
        String facultadNombre = (docente.getEscuela() != null && docente.getEscuela().getFacultad() != null)
                ? docente.getEscuela().getFacultad().getNombre()
                : null;

        return new UsuarioBusquedaResponse(
                docente.getUsuarioId(),
                nombreCompleto,
                docente.getCodigoDocente(),
                docente.getEscuelaId(),
                escuelaNombre,
                facultadId,
                facultadNombre
        );
    }
}
package com.horarios.service;

import com.horarios.dto.HorarioCursoRequest;
import com.horarios.dto.HorarioCursoResponse;
import com.horarios.mapper.HorarioCursoMapper;
import com.horarios.model.Curso;
import com.horarios.model.DiaSemana;
import com.horarios.model.Horario;
import com.horarios.model.HorarioCurso;
import com.horarios.model.Usuario;
import com.horarios.repository.BloqueHorarioRepository;
import com.horarios.repository.CursoRepository;
import com.horarios.repository.EspacioRepository;
import com.horarios.repository.HorarioCursoRepository;
import com.horarios.repository.HorarioRepository;
import com.horarios.repository.UsuarioRepository;
import jakarta.transaction.Transactional;
import org.springframework.stereotype.Service;

import java.time.LocalDate;
import java.util.List;
import java.util.Optional;

@Service
@Transactional
public class HorarioCursoService {

    private final HorarioCursoRepository horarioCursoRepository;
    private final CursoRepository cursoRepository;
    private final UsuarioRepository usuarioRepository;
    private final EspacioRepository espacioRepository;
    private final BloqueHorarioRepository bloqueHorarioRepository;
    private final HorarioRepository horarioRepository;

    public HorarioCursoService(HorarioCursoRepository horarioCursoRepository,
                               CursoRepository cursoRepository,
                               UsuarioRepository usuarioRepository,
                               EspacioRepository espacioRepository,
                               BloqueHorarioRepository bloqueHorarioRepository,
                               HorarioRepository horarioRepository) {
        this.horarioCursoRepository = horarioCursoRepository;
        this.cursoRepository = cursoRepository;
        this.usuarioRepository = usuarioRepository;
        this.espacioRepository = espacioRepository;
        this.bloqueHorarioRepository = bloqueHorarioRepository;
        this.horarioRepository = horarioRepository;
    }

    public List<HorarioCursoResponse> listarTodos() {
        return horarioCursoRepository.findAll().stream()
                .map(HorarioCursoMapper::toResponse)
                .toList();
    }

    public Optional<HorarioCursoResponse> buscarPorId(Integer id) {
        return horarioCursoRepository.findById(id).map(HorarioCursoMapper::toResponse);
    }

    public List<HorarioCursoResponse> buscarPorFiltro(String filtro) {
        String criterio = (filtro == null || filtro.isBlank()) ? null : filtro.trim();
        return horarioCursoRepository.buscarPorFiltro(criterio).stream()
                .map(HorarioCursoMapper::toResponse)
                .toList();
    }

    public HorarioCursoResponse crearHorarioCurso(HorarioCursoRequest request) {
        validarRangoFechas(request.fechaInicio(), request.fechaFin());

        HorarioCurso horarioCurso = new HorarioCurso();
        horarioCurso.setCurso(obtenerCurso(request.cursoId()));
        horarioCurso.setDocente(obtenerDocente(request.docenteId()));
        horarioCurso.setEspacio(espacioRepository.findById(request.espacioId())
                .orElseThrow(() -> new IllegalArgumentException("Espacio no encontrado")));
        horarioCurso.setBloque(bloqueHorarioRepository.findById(request.bloqueId())
                .orElseThrow(() -> new IllegalArgumentException("Bloque horario no encontrado")));
        horarioCurso.setDiaSemana(DiaSemana.fromNombre(request.diaSemana()));
        horarioCurso.setFechaInicio(request.fechaInicio());
        horarioCurso.setFechaFin(request.fechaFin());
        horarioCurso.setEstado(Boolean.TRUE.equals(request.estado()));

        verificarDisponibilidadHorario(horarioCurso, null);

        HorarioCurso guardado = horarioCursoRepository.save(horarioCurso);
        actualizarEstadoHorarioBase(guardado);
        return HorarioCursoMapper.toResponse(guardado);
    }

    public HorarioCursoResponse actualizarHorarioCurso(Integer id, HorarioCursoRequest request) {
        HorarioCurso horarioCurso = horarioCursoRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Horario de curso no encontrado"));

        validarRangoFechas(request.fechaInicio(), request.fechaFin());

        horarioCurso.setCurso(obtenerCurso(request.cursoId()));
        horarioCurso.setDocente(obtenerDocente(request.docenteId()));
        horarioCurso.setEspacio(espacioRepository.findById(request.espacioId())
                .orElseThrow(() -> new IllegalArgumentException("Espacio no encontrado")));
        horarioCurso.setBloque(bloqueHorarioRepository.findById(request.bloqueId())
                .orElseThrow(() -> new IllegalArgumentException("Bloque horario no encontrado")));
        horarioCurso.setDiaSemana(DiaSemana.fromNombre(request.diaSemana()));
        horarioCurso.setFechaInicio(request.fechaInicio());
        horarioCurso.setFechaFin(request.fechaFin());
        horarioCurso.setEstado(Boolean.TRUE.equals(request.estado()));

        verificarDisponibilidadHorario(horarioCurso, id);
        actualizarEstadoHorarioBase(horarioCurso);
        return HorarioCursoMapper.toResponse(horarioCurso);
    }

    public void eliminarHorarioCurso(Integer id) {
        HorarioCurso horarioCurso = horarioCursoRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Horario de curso no encontrado"));
        horarioCursoRepository.delete(horarioCurso);
        actualizarEstadoHorarioBase(horarioCurso);
    }

    private void verificarDisponibilidadHorario(HorarioCurso nuevoHorario, Integer ignorarId) {
        Horario horarioBase = horarioRepository.findByEspacioIdAndBloqueIdAndDiaSemana(
                        nuevoHorario.getEspacio().getId(),
                        nuevoHorario.getBloque().getId(),
                        nuevoHorario.getDiaSemana())
                .orElseThrow(() -> new IllegalArgumentException("No existe un horario base configurado para el espacio seleccionado"));

        boolean traslape = horarioCursoRepository.existeTraslapeActivo(
                nuevoHorario.getEspacio().getId(),
                nuevoHorario.getBloque().getId(),
                nuevoHorario.getDiaSemana(),
                nuevoHorario.getFechaInicio(),
                nuevoHorario.getFechaFin(),
                ignorarId);

        if (traslape) {
            throw new IllegalArgumentException("El bloque seleccionado ya está ocupado por otro curso activo en el rango de fechas indicado");
        }

        if (horarioBase.isOcupado() && (ignorarId == null || !ignorarId.equals(nuevoHorario.getId()))) {
            boolean bloqueadoPorCurso = horarioCursoRepository.existeTraslapeActivo(
                    nuevoHorario.getEspacio().getId(),
                    nuevoHorario.getBloque().getId(),
                    nuevoHorario.getDiaSemana(),
                    LocalDate.now(),
                    LocalDate.now(),
                    null);
            if (bloqueadoPorCurso) {
                throw new IllegalArgumentException("El bloque se encuentra reservado por otro curso en la fecha actual");
            }
        }
    }

    private void actualizarEstadoHorarioBase(HorarioCurso horarioCurso) {
        horarioRepository.findByEspacioIdAndBloqueIdAndDiaSemana(
                        horarioCurso.getEspacio().getId(),
                        horarioCurso.getBloque().getId(),
                        horarioCurso.getDiaSemana())
                .ifPresent(horario -> {
                    boolean ocupado = horarioCursoRepository.existeTraslapeActivo(
                            horarioCurso.getEspacio().getId(),
                            horarioCurso.getBloque().getId(),
                            horarioCurso.getDiaSemana(),
                            LocalDate.now(),
                            LocalDate.now(),
                            null);
                    horario.setOcupado(ocupado);
                });
    }

    private void validarRangoFechas(LocalDate fechaInicio, LocalDate fechaFin) {
        if (fechaInicio == null || fechaFin == null) {
            throw new IllegalArgumentException("Las fechas de inicio y fin son obligatorias");
        }
        if (fechaFin.isBefore(fechaInicio)) {
            throw new IllegalArgumentException("La fecha fin no puede ser anterior a la fecha de inicio");
        }
    }

    private Curso obtenerCurso(Integer cursoId) {
        return cursoRepository.findById(cursoId)
                .orElseThrow(() -> new IllegalArgumentException("Curso no encontrado"));
    }

    private Usuario obtenerDocente(Integer docenteId) {
        return usuarioRepository.findById(docenteId)
                .orElseThrow(() -> new IllegalArgumentException("Docente no encontrado"));
    }
}
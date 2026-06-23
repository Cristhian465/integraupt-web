package com.reserva.service;

import com.reserva.model.Espacio;
import com.reserva.repository.EspacioRepository;
import com.reserva.model.BloqueHorario;
import com.reserva.model.Curso;
import com.reserva.repository.BloqueHorarioRepository;
import com.reserva.repository.CursoRepository;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.ArrayList;
import java.util.List;
import java.util.Optional;

@Service
@Transactional(readOnly = true)
public class EspacioService {

    private final EspacioRepository espacioRepository;
    private final CursoRepository cursoRepository;
    private final BloqueHorarioRepository bloqueHorarioRepository;

    public EspacioService(EspacioRepository espacioRepository,
                          CursoRepository cursoRepository,
                          BloqueHorarioRepository bloqueHorarioRepository) {
        this.espacioRepository = espacioRepository;
        this.cursoRepository = cursoRepository;
        this.bloqueHorarioRepository = bloqueHorarioRepository;
    }

    public List<Espacio> listarActivos() {
        return new ArrayList<>(espacioRepository.findByEstadoOrderByNombreAsc(1));
    }

    public List<Espacio> listarTodos() {
        return new ArrayList<>(espacioRepository.findAllOrdenados());
    }

    public List<Espacio> listarActivosPorEscuela(Integer escuelaId) {
        if (escuelaId == null) {
            return listarActivos();
        }
        return new ArrayList<>(espacioRepository.findByEstadoAndEscuelaIdOrderByNombreAsc(1, escuelaId));
    }

    public List<Espacio> listarTodosPorEscuela(Integer escuelaId) {
        if (escuelaId == null) {
            return listarTodos();
        }
        return new ArrayList<>(espacioRepository.findByEscuelaIdOrderByNombreAsc(escuelaId));
    }


    public Optional<Espacio> buscarPorId(Integer id) {
        return espacioRepository.findById(id);
    }

    public List<Curso> listarCursosActivosPorEspacio(Integer espacioId) {
        if (espacioId == null) {
            return new ArrayList<>();
        }

        return espacioRepository.findById(espacioId)
                .map(Espacio::getEscuelaId)
                .map(escuelaId -> cursoRepository.findByEscuelaIdAndEstadoOrderByNombreAsc(escuelaId, 1))
                .map(ArrayList::new)
                .orElseGet(ArrayList::new);
    }

    public List<BloqueHorario> listarBloquesPorEspacio(Integer espacioId) {
        if (espacioId == null) {
            return new ArrayList<>();
        }

        if (!espacioRepository.existsById(espacioId)) {
            return new ArrayList<>();
        }

        return new ArrayList<>(bloqueHorarioRepository.findDistinctByEspacioIdOrderByOrden(espacioId));
    }
}
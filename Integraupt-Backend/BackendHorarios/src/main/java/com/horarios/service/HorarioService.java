package com.horarios.service;

import com.horarios.dto.ActualizarOcupacionRequest;
import com.horarios.dto.HorarioDiaResponse;
import com.horarios.dto.HorarioRequest;
import com.horarios.dto.HorarioResponse;
import com.horarios.dto.HorarioSemanalResponse;
import com.horarios.mapper.HorarioMapper;
import com.horarios.model.DiaSemana;
import com.horarios.model.Espacio;
import com.horarios.model.Horario;
import com.horarios.repository.BloqueHorarioRepository;
import com.horarios.repository.EspacioRepository;
import com.horarios.repository.HorarioRepository;
import jakarta.transaction.Transactional;
import org.springframework.stereotype.Service;
import org.springframework.data.domain.Sort;
import com.horarios.model.BloqueHorario;

import java.time.format.DateTimeFormatter;
import java.util.Arrays;
import java.util.EnumMap;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Optional;

@Service
@Transactional
public class HorarioService {

    private static final DateTimeFormatter HORA_FORMATTER = DateTimeFormatter.ofPattern("HH:mm");
    private final HorarioRepository horarioRepository;
    private final EspacioRepository espacioRepository;
    private final BloqueHorarioRepository bloqueHorarioRepository;

    public HorarioService(HorarioRepository horarioRepository,
                          EspacioRepository espacioRepository,
                          BloqueHorarioRepository bloqueHorarioRepository) {
        this.horarioRepository = horarioRepository;
        this.espacioRepository = espacioRepository;
        this.bloqueHorarioRepository = bloqueHorarioRepository;
    }

    public List<HorarioResponse> listarTodos() {
        return horarioRepository.findAll().stream()
                .map(HorarioMapper::toResponse)
                .toList();
    }

    public Optional<HorarioResponse> buscarPorId(Integer id) {
        return horarioRepository.findById(id).map(HorarioMapper::toResponse);
    }

    public List<HorarioResponse> listarPorEspacio(Integer espacioId) {
        return horarioRepository.findByEspacioIdOrderByBloqueOrdenAscDiaSemanaAsc(espacioId).stream()
                .map(HorarioMapper::toResponse)
                .toList();
    }

    public List<HorarioResponse> listarPorDia(String diaSemana) {
        DiaSemana dia = DiaSemana.fromNombre(diaSemana);
        return horarioRepository.findByDiaSemanaOrderByBloqueOrdenAsc(dia).stream()
                .map(HorarioMapper::toResponse)
                .toList();
    }

    public List<HorarioResponse> listarPorOcupacion(boolean ocupado) {
        return horarioRepository.findByOcupado(ocupado).stream()
                .map(HorarioMapper::toResponse)
                .toList();
    }

    public List<HorarioSemanalResponse> obtenerHorarioSemanalPorEspacio(Integer espacioId) {
        List<Horario> horarios = horarioRepository.findByEspacioIdOrderByBloqueOrdenAscDiaSemanaAsc(espacioId);

        Map<Integer, Map<DiaSemana, Boolean>> estadosPorBloque = new HashMap<>();
        horarios.forEach(horario -> {
            BloqueHorario bloque = horario.getBloque();
            DiaSemana dia = horario.getDiaSemana();

            if (bloque == null || bloque.getId() == null || dia == null) {
                return;
            }
            estadosPorBloque
                    .computeIfAbsent(bloque.getId(), id -> new EnumMap<>(DiaSemana.class))
                    .put(dia, horario.isOcupado());
        });

        return bloqueHorarioRepository.findAll(Sort.by(Sort.Direction.ASC, "orden")).stream()
                .map(bloque -> {
                    Map<DiaSemana, Boolean> estados = estadosPorBloque.getOrDefault(bloque.getId(), Map.of());

                    List<HorarioDiaResponse> dias = Arrays.stream(DiaSemana.values())
                            .map(dia -> new HorarioDiaResponse(dia.getNombre(), estados.getOrDefault(dia, false)))
                            .toList();

                    return new HorarioSemanalResponse(
                            bloque.getId(),
                            Optional.ofNullable(bloque.getNombre()).filter(nombre -> !nombre.isBlank())
                                    .orElseGet(() -> String.format("Bloque %d", bloque.getId())),
                            Optional.ofNullable(bloque.getHoraInicio())
                                    .map(HORA_FORMATTER::format)
                                    .orElse("--:--"),
                            Optional.ofNullable(bloque.getHoraFin())
                                    .map(HORA_FORMATTER::format)
                                    .orElse("--:--"),
                            dias
                    );

                })
                .toList();
    }

    public HorarioResponse crearHorario(HorarioRequest request) {
        Horario horario = new Horario();
        horario.setEspacio(obtenerEspacio(request.espacioId()));
        horario.setBloque(bloqueHorarioRepository.findById(request.bloqueId())
                .orElseThrow(() -> new IllegalArgumentException("Bloque horario no encontrado")));
        horario.setDiaSemana(DiaSemana.fromNombre(request.diaSemana()));
        horario.setOcupado(Boolean.TRUE.equals(request.ocupado()));
        Horario guardado = horarioRepository.save(horario);
        return HorarioMapper.toResponse(guardado);
    }

    public HorarioResponse actualizarHorario(Integer id, HorarioRequest request) {
        Horario horario = horarioRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Horario no encontrado"));
        horario.setEspacio(obtenerEspacio(request.espacioId()));
        horario.setBloque(bloqueHorarioRepository.findById(request.bloqueId())
                .orElseThrow(() -> new IllegalArgumentException("Bloque horario no encontrado")));
        horario.setDiaSemana(DiaSemana.fromNombre(request.diaSemana()));
        if (request.ocupado() != null) {
            horario.setOcupado(request.ocupado());
        }
        return HorarioMapper.toResponse(horario);
    }

    public void eliminarHorario(Integer id) {
        if (!horarioRepository.existsById(id)) {
            throw new IllegalArgumentException("Horario no encontrado");
        }
        horarioRepository.deleteById(id);
    }

    public HorarioResponse actualizarOcupacion(Integer id, ActualizarOcupacionRequest request) {
        Horario horario = horarioRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Horario no encontrado"));
        horario.setOcupado(request.ocupado());
        return HorarioMapper.toResponse(horario);
    }

    private Espacio obtenerEspacio(Integer espacioId) {
        return espacioRepository.findById(espacioId)
                .orElseThrow(() -> new IllegalArgumentException("Espacio no encontrado"));
    }
}
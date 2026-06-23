package com.horariocurso.servicio;

import com.horariocurso.dto.HorarioCursoRequest;
import com.horariocurso.dto.HorarioCursoResponse;
import com.horariocurso.modelo.HorarioCurso;
import com.horariocurso.projection.HorarioCursoDetalleProjection;
import com.horariocurso.repositorio.CatalogoRepositorio;
import com.horariocurso.repositorio.HorarioCursoRepositorio;
import jakarta.validation.Valid;
import java.time.LocalDate;
import java.util.List;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.server.ResponseStatusException;

@Service
@Transactional
public class HorarioCursoServicio {

    private final HorarioCursoRepositorio horarioCursoRepositorio;
    private final CatalogoRepositorio catalogoRepositorio;

    public HorarioCursoServicio(HorarioCursoRepositorio horarioCursoRepositorio,
            CatalogoRepositorio catalogoRepositorio) {
        this.horarioCursoRepositorio = horarioCursoRepositorio;
        this.catalogoRepositorio = catalogoRepositorio;
    }

    @Transactional(readOnly = true)
    public List<HorarioCursoResponse> listar() {
        return horarioCursoRepositorio.listarDetalle()
                .stream()
                .map(this::mapearRespuesta)
                .toList();
    }

    @Transactional(readOnly = true)
    public HorarioCursoResponse buscarPorId(Integer id) {
        HorarioCursoDetalleProjection projection = horarioCursoRepositorio.buscarDetallePorId(id)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND,
                "No se encontro el horario con id " + id));
        return mapearRespuesta(projection);
    }

    public HorarioCursoResponse crear(@Valid HorarioCursoRequest request) {
        validarReferencias(request);
        validarRangoFechas(request.getFechaInicio(), request.getFechaFin());
        HorarioCurso horarioCurso = new HorarioCurso();
        aplicarDatos(horarioCurso, request);
        HorarioCurso guardado = horarioCursoRepositorio.save(horarioCurso);
        return buscarPorId(guardado.getId());
    }

    public HorarioCursoResponse actualizar(Integer id, @Valid HorarioCursoRequest request) {
        HorarioCurso horarioCurso = horarioCursoRepositorio.findById(id)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND,
                "No se encontro el horario con id " + id));
        validarReferencias(request);
        validarRangoFechas(request.getFechaInicio(), request.getFechaFin());
        aplicarDatos(horarioCurso, request);
        horarioCursoRepositorio.save(horarioCurso);
        return buscarPorId(id);
    }

    public void eliminar(Integer id) {
        HorarioCurso horarioCurso = horarioCursoRepositorio.findById(id)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND,
                "No se encontro el horario con id " + id));
        horarioCursoRepositorio.delete(horarioCurso);
    }

    private void aplicarDatos(HorarioCurso destino, HorarioCursoRequest request) {
        destino.setCurso(request.getCurso());
        destino.setDocente(request.getDocente());
        destino.setEspacio(request.getEspacio());
        destino.setBloque(request.getBloque());
        destino.setDiaSemana(request.getDiaSemana());
        destino.setFechaInicio(request.getFechaInicio());
        destino.setFechaFin(request.getFechaFin());
        destino.setEstado(request.getEstado());
    }

    private void validarReferencias(HorarioCursoRequest request) {
        if (!catalogoRepositorio.existeCurso(request.getCurso())) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST,
                    "El curso seleccionado no es valido.");
        }
        if (!catalogoRepositorio.existeDocente(request.getDocente())) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST,
                    "El docente seleccionado no es valido.");
        }
        if (!catalogoRepositorio.existeEspacio(request.getEspacio())) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST,
                    "El espacio seleccionado no es valido.");
        }
        if (!catalogoRepositorio.existeBloque(request.getBloque())) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST,
                    "El bloque seleccionado no es valido.");
        }
    }

    private void validarRangoFechas(LocalDate inicio, LocalDate fin) {
        if (inicio == null || fin == null) {
            return;
        }
        if (fin.isBefore(inicio)) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST,
                    "La fecha fin no puede ser anterior a la fecha inicio.");
        }
    }

    private HorarioCursoResponse mapearRespuesta(HorarioCursoDetalleProjection projection) {
        HorarioCursoResponse response = new HorarioCursoResponse();
        response.setIdHorarioCurso(projection.getIdHorarioCurso());
        response.setCurso(projection.getCurso());
        response.setDocente(projection.getDocente());
        response.setEspacio(projection.getEspacio());
        response.setBloque(projection.getBloque());
        response.setDiaSemana(projection.getDiaSemana());
        response.setFechaInicio(projection.getFechaInicio());
        response.setFechaFin(projection.getFechaFin());
        response.setEstado(projection.getEstado());
        response.setNombreCurso(projection.getNombreCurso());
        response.setNombreDocente(projection.getNombreDocente());
        response.setNombreEspacio(projection.getNombreEspacio());
        response.setCodigoEspacio(projection.getCodigoEspacio());
        response.setNombreBloque(projection.getNombreBloque());
        response.setHoraInicioBloque(projection.getHoraInicio());
        response.setHoraFinBloque(projection.getHoraFinal());
        return response;
    }
}

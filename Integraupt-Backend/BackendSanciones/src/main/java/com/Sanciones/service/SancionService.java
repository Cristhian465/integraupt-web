package com.Sanciones.service;

import com.Sanciones.dto.SancionRequest;
import com.Sanciones.dto.SancionResponse;
import com.Sanciones.dto.VerificacionSancionResponse;
import com.Sanciones.exception.ResourceNotFoundException;
import com.Sanciones.model.Docente;
import com.Sanciones.model.Estudiante;
import com.Sanciones.model.RolUsuarioBusqueda;
import com.Sanciones.model.Sancion;
import com.Sanciones.model.SancionEstado;
import com.Sanciones.model.TipoUsuario;
import com.Sanciones.model.Usuario;
import com.Sanciones.repository.DocenteRepository;
import com.Sanciones.repository.EstudianteRepository;
import com.Sanciones.repository.SancionRepository;
import com.Sanciones.repository.UsuarioRepository;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.util.Assert;
import org.springframework.util.StringUtils;

import java.time.LocalDate;
import java.util.Arrays;
import java.util.List;
import java.util.Optional;
import java.util.function.Function;
import java.util.stream.Collectors;

@Service
@Transactional
public class SancionService {

    private final SancionRepository sancionRepository;
    private final EstudianteRepository estudianteRepository;
    private final DocenteRepository docenteRepository;
    private final UsuarioRepository usuarioRepository;

    public SancionService(SancionRepository sancionRepository,
                          EstudianteRepository estudianteRepository,
                          DocenteRepository docenteRepository,
                          UsuarioRepository usuarioRepository) {
        this.sancionRepository = sancionRepository;
        this.estudianteRepository = estudianteRepository;
        this.docenteRepository = docenteRepository;
        this.usuarioRepository = usuarioRepository;
    }

    public SancionResponse registrarSancion(SancionRequest request) {
        TipoUsuario tipoUsuario = TipoUsuario.fromNombre(request.getTipoUsuario());
        validarRangoFechas(request.getFechaInicio(), request.getFechaFin());

        RolUsuarioBusqueda rol = RolUsuarioBusqueda.fromNombre(request.getRol());
        FiltroContexto filtros = construirFiltroContexto(rol, request.getFacultadId(), request.getEscuelaId(), request.getEscuelaContextoId());

        Long usuarioId = resolverUsuarioId(request, tipoUsuario);

        UsuarioDetalle detalle = obtenerDetalleUsuario(usuarioId, tipoUsuario);
        validarUsuarioContraFiltros(detalle, filtros);

        Sancion sancion = new Sancion();
        sancion.setUsuarioId(usuarioId);
        sancion.setTipoUsuario(tipoUsuario);
        sancion.setMotivo(request.getMotivo());
        sancion.setFechaInicio(request.getFechaInicio());
        sancion.setFechaFin(request.getFechaFin());
        sancion.setEstado(SancionEstado.ACTIVA);

        return mapearRespuesta(sancionRepository.save(sancion));
    }

    public List<SancionResponse> obtenerTodas(String rolValor, Long facultadId, Long escuelaId, Long escuelaContextoId) {
        RolUsuarioBusqueda rol = RolUsuarioBusqueda.fromNombre(rolValor);
        FiltroContexto filtros = construirFiltroContexto(rol, facultadId, escuelaId, escuelaContextoId);
        return obtenerSancionesFiltradas(filtros, false);
    }

    public List<SancionResponse> obtenerActivas(String rolValor, Long facultadId, Long escuelaId, Long escuelaContextoId) {
        RolUsuarioBusqueda rol = RolUsuarioBusqueda.fromNombre(rolValor);
        FiltroContexto filtros = construirFiltroContexto(rol, facultadId, escuelaId, escuelaContextoId);
        return obtenerSancionesFiltradas(filtros, true);
    }

    public SancionResponse levantarSancion(Long sancionId) {
        Sancion sancion = sancionRepository.findById(sancionId)
                .orElseThrow(() -> new ResourceNotFoundException("No se encontró la sanción con id " + sancionId));

        if (sancion.getEstado() == SancionEstado.CUMPLIDA) {
            return mapearRespuesta(sancion);
        }

        sancion.setEstado(SancionEstado.CUMPLIDA);
        if (sancion.getFechaFin().isAfter(LocalDate.now())) {
            sancion.setFechaFin(LocalDate.now());
        }

        return mapearRespuesta(sancionRepository.save(sancion));
    }

    public VerificacionSancionResponse verificarUsuarioSancionado(Long usuarioId, String tipoUsuarioValor) {
        TipoUsuario tipoUsuario = TipoUsuario.fromNombre(tipoUsuarioValor);

        Optional<Sancion> sancionActiva = sancionRepository
                .findFirstByUsuarioIdAndTipoUsuarioAndEstado(usuarioId, tipoUsuario, SancionEstado.ACTIVA)
                .map(this::actualizarSancionSiVencida);

        VerificacionSancionResponse response = new VerificacionSancionResponse();
        if (sancionActiva.isEmpty()) {
            response.setSancionado(false);
            return response;
        }

        Sancion sancion = sancionActiva.get();
        boolean vigente = sancion.getFechaFin().isAfter(LocalDate.now()) || sancion.getFechaFin().isEqual(LocalDate.now());
        response.setSancionado(vigente);
        response.setSancionId(sancion.getId());
        response.setMotivo(sancion.getMotivo());
        response.setFechaFin(sancion.getFechaFin());

        if (!vigente) {
            sancion.setEstado(SancionEstado.CUMPLIDA);
            sancionRepository.save(sancion);
            response.setEstado(SancionEstado.CUMPLIDA);
        } else {
            response.setEstado(sancion.getEstado());
        }

        return response;
    }

    public boolean tieneSancionActiva(Long usuarioId, String tipoUsuarioValor) {
        TipoUsuario tipoUsuario = TipoUsuario.fromNombre(tipoUsuarioValor);
        return sancionRepository.existsByUsuarioIdAndTipoUsuarioAndEstadoAndFechaFinGreaterThanEqual(
                usuarioId,
                tipoUsuario,
                SancionEstado.ACTIVA,
                LocalDate.now());
    }

    private void validarRangoFechas(LocalDate inicio, LocalDate fin) {
        Assert.notNull(inicio, "La fecha de inicio es obligatoria");
        Assert.notNull(fin, "La fecha de fin es obligatoria");
        if (fin.isBefore(inicio)) {
            throw new IllegalArgumentException("La fecha de fin no puede ser anterior a la fecha de inicio");
        }
    }

    private SancionResponse mapearRespuesta(Sancion sancion) {
        SancionResponse response = new SancionResponse();
        response.setId(sancion.getId());
        response.setUsuarioId(sancion.getUsuarioId());
        response.setTipoUsuario(sancion.getTipoUsuario());
        response.setMotivo(sancion.getMotivo());
        response.setFechaInicio(sancion.getFechaInicio());
        response.setFechaFin(sancion.getFechaFin());
        response.setEstado(sancion.getEstado());

        UsuarioDetalle detalle = obtenerDetalleUsuario(sancion.getUsuarioId(), sancion.getTipoUsuario());
        response.setUsuarioNombre(detalle.getNombreCompleto());
        response.setUsuarioCodigo(detalle.getCodigo());
        response.setUsuarioEscuela(detalle.getEscuelaNombre());
        response.setUsuarioEscuelaId(detalle.getEscuelaId());
        response.setUsuarioFacultad(detalle.getFacultadNombre());
        response.setUsuarioFacultadId(detalle.getFacultadId());
        return response;
    }

    private Sancion actualizarSancionSiVencida(Sancion sancion) {
        if (sancion.getEstado() == SancionEstado.ACTIVA && sancion.getFechaFin().isBefore(LocalDate.now())) {
            sancion.setEstado(SancionEstado.CUMPLIDA);
            return sancionRepository.save(sancion);
        }
        return sancion;
    }
    private Long resolverUsuarioId(SancionRequest request, TipoUsuario tipoUsuario) {
        Long usuarioId = null;

        if (request.getUsuarioId() != null) {
            validarUsuarioPorTipo(request.getUsuarioId(), tipoUsuario);
            usuarioId = request.getUsuarioId();
        }

        if (StringUtils.hasText(request.getUsuarioCodigo())) {
            Long usuarioIdPorCodigo = obtenerUsuarioIdPorCodigo(request.getUsuarioCodigo(), tipoUsuario);
            usuarioId = combinarUsuarioId(usuarioId, usuarioIdPorCodigo, "código");
        }

        if (StringUtils.hasText(request.getUsuarioNombre())) {
            Long usuarioIdPorNombre = obtenerUsuarioIdPorNombre(request.getUsuarioNombre(), tipoUsuario);
            usuarioId = combinarUsuarioId(usuarioId, usuarioIdPorNombre, "nombre");
        }

        if (usuarioId != null) {
            return usuarioId;
        }

        throw new IllegalArgumentException("Debe proporcionar el código o el nombre del usuario a sancionar.");
    }

    private void validarUsuarioPorTipo(Long usuarioId, TipoUsuario tipoUsuario) {
        boolean existe = switch (tipoUsuario) {
            case ESTUDIANTE -> estudianteRepository.findByUsuarioId(usuarioId).isPresent();
            case DOCENTE -> docenteRepository.findByUsuarioId(usuarioId).isPresent();
        };

        if (!existe) {
            String tipo = tipoUsuario == TipoUsuario.ESTUDIANTE ? "estudiante" : "docente";
            throw new ResourceNotFoundException("El " + tipo + " indicado no existe o no pertenece al tipo seleccionado.");
        }
    }

    private UsuarioDetalle obtenerDetalleUsuario(Long usuarioId, TipoUsuario tipoUsuario) {
        if (usuarioId == null) {
            return UsuarioDetalle.vacio();
        }

        Usuario usuario = usuarioRepository.findById(usuarioId).orElse(null);

        return switch (tipoUsuario) {
            case ESTUDIANTE -> estudianteRepository.findByUsuarioId(usuarioId)
                    .map(estudiante -> UsuarioDetalle.crear(
                            usuario != null ? usuario.getNombreCompleto() : null,
                            estudiante.getCodigo(),
                            estudiante.getEscuelaId(),
                            estudiante.getEscuela() != null ? estudiante.getEscuela().getNombre() : null,
                            estudiante.getEscuela() != null ? estudiante.getEscuela().getFacultadId() : null,
                            (estudiante.getEscuela() != null && estudiante.getEscuela().getFacultad() != null)
                                    ? estudiante.getEscuela().getFacultad().getNombre()
                                    : null
                    ))
                    .orElseGet(() -> UsuarioDetalle.desdeUsuario(usuario));
            case DOCENTE -> docenteRepository.findByUsuarioId(usuarioId)
                    .map(docente -> UsuarioDetalle.crear(
                            usuario != null ? usuario.getNombreCompleto() : null,
                            docente.getCodigoDocente(),
                            docente.getEscuelaId(),
                            docente.getEscuela() != null ? docente.getEscuela().getNombre() : null,
                            docente.getEscuela() != null ? docente.getEscuela().getFacultadId() : null,
                            (docente.getEscuela() != null && docente.getEscuela().getFacultad() != null)
                                    ? docente.getEscuela().getFacultad().getNombre()
                                    : null
                    ))
                    .orElseGet(() -> UsuarioDetalle.desdeUsuario(usuario));
        };
    }

    private Long obtenerUsuarioIdPorCodigo(String codigo, TipoUsuario tipoUsuario) {
        String codigoNormalizado = codigo.trim();
        if (!StringUtils.hasText(codigoNormalizado)) {
            throw new IllegalArgumentException("El código del usuario no puede estar vacío.");
        }

        return switch (tipoUsuario) {
            case ESTUDIANTE -> estudianteRepository.findByCodigoIgnoreCase(codigoNormalizado)
                    .map(Estudiante::getUsuarioId)
                    .orElseThrow(() -> new ResourceNotFoundException(
                            "No se encontró un estudiante con el código " + codigoNormalizado
                    ));
            case DOCENTE -> docenteRepository.findByCodigoDocenteIgnoreCase(codigoNormalizado)
                    .map(Docente::getUsuarioId)
                    .orElseThrow(() -> new ResourceNotFoundException(
                            "No se encontró un docente con el código " + codigoNormalizado
                    ));
        };
    }

    private Long obtenerUsuarioIdPorNombre(String nombre, TipoUsuario tipoUsuario) {
        String nombreNormalizado = normalizarNombre(nombre);

        return switch (tipoUsuario) {
            case ESTUDIANTE -> obtenerUnicoUsuarioId(
                    estudianteRepository.buscarPorNombreCompleto(nombreNormalizado),
                    Estudiante::getUsuarioId,
                    "estudiante",
                    "nombre",
                    nombreNormalizado
            );
            case DOCENTE -> obtenerUnicoUsuarioId(
                    docenteRepository.buscarPorNombreCompleto(nombreNormalizado),
                    Docente::getUsuarioId,
                    "docente",
                    "nombre",
                    nombreNormalizado
            );
        };
    }

    private <T> Long obtenerUnicoUsuarioId(List<T> resultados,
                                           Function<T, Long> extractor,
                                           String tipoDescripcion,
                                           String criterio,
                                           String valorBuscado) {
        if (resultados.isEmpty()) {
            throw new ResourceNotFoundException(
                    "No se encontró un " + tipoDescripcion + " con el " + criterio + " " + valorBuscado
            );
        }

        if (resultados.size() > 1) {
            throw new IllegalArgumentException(
                    "Se encontró más de un " + tipoDescripcion + " con el " + criterio + " " + valorBuscado + "."
            );
        }

        Long usuarioId = extractor.apply(resultados.get(0));
        if (usuarioId == null) {
            throw new IllegalStateException(
                    "El " + tipoDescripcion + " localizado no tiene un usuario asociado.");
        }

        return usuarioId;
    }

    private Long combinarUsuarioId(Long actual, Long nuevo, String criterio) {
        if (nuevo == null) {
            return actual;
        }

        if (actual != null && !actual.equals(nuevo)) {
            throw new IllegalArgumentException(
                    "Los datos del usuario proporcionados no coinciden entre sí (difieren en el " + criterio + ")."
            );
        }

        return nuevo;
    }

    private String normalizarNombre(String nombre) {
        String nombreLimpio = nombre != null ? nombre.trim() : "";
        if (!StringUtils.hasText(nombreLimpio)) {
            throw new IllegalArgumentException("El nombre del usuario no puede estar vacío.");
        }

        return Arrays.stream(nombreLimpio.split("\\s+"))
                .filter(StringUtils::hasText)
                .collect(Collectors.joining(" "));
    }

    private List<SancionResponse> obtenerSancionesFiltradas(FiltroContexto filtros, boolean soloActivas) {
        return sancionRepository.findAll()
                .stream()
                .map(this::actualizarSancionSiVencida)
                .filter(sancion -> !soloActivas || sancion.getEstado() == SancionEstado.ACTIVA)
                .map(this::mapearRespuesta)
                .filter(response -> coincideConFiltros(response, filtros))
                .collect(Collectors.toList());
    }

    private boolean coincideConFiltros(SancionResponse response, FiltroContexto filtros) {
        if (filtros.getEscuelaId() != null) {
            return filtros.getEscuelaId().equals(response.getUsuarioEscuelaId());
        }

        if (filtros.getFacultadId() != null) {
            return filtros.getFacultadId().equals(response.getUsuarioFacultadId());
        }

        return true;
    }

    private FiltroContexto construirFiltroContexto(RolUsuarioBusqueda rol,
                                                   Long facultadId,
                                                   Long escuelaId,
                                                   Long escuelaContextoId) {
        Long facultadFiltro = facultadId;
        Long escuelaFiltro = escuelaId;

        if (rol == RolUsuarioBusqueda.SUPERVISOR) {
            Long escuelaRestriccion = escuelaContextoId != null ? escuelaContextoId : escuelaId;
            if (escuelaRestriccion == null) {
                throw new IllegalArgumentException("Los supervisores deben tener una escuela asociada para gestionar sanciones.");
            }
            escuelaFiltro = escuelaRestriccion;
            facultadFiltro = null;
        }

        return new FiltroContexto(rol, facultadFiltro, escuelaFiltro);
    }

    private void validarUsuarioContraFiltros(UsuarioDetalle detalle, FiltroContexto filtros) {
        if (filtros.getRol() == RolUsuarioBusqueda.SUPERVISOR) {
            if (detalle.getEscuelaId() == null || !detalle.getEscuelaId().equals(filtros.getEscuelaId())) {
                throw new IllegalArgumentException("No tienes permisos para sancionar usuarios fuera de tu escuela asignada.");
            }
            return;
        }

        if (filtros.getEscuelaId() != null) {
            if (detalle.getEscuelaId() == null || !filtros.getEscuelaId().equals(detalle.getEscuelaId())) {
                throw new IllegalArgumentException("El usuario seleccionado no pertenece a la escuela indicada.");
            }
        }

        if (filtros.getFacultadId() != null) {
            if (detalle.getFacultadId() == null || !filtros.getFacultadId().equals(detalle.getFacultadId())) {
                throw new IllegalArgumentException("El usuario seleccionado no pertenece a la facultad indicada.");
            }
        }
    }

    private static final class FiltroContexto {
        private final RolUsuarioBusqueda rol;
        private final Long facultadId;
        private final Long escuelaId;

        private FiltroContexto(RolUsuarioBusqueda rol, Long facultadId, Long escuelaId) {
            this.rol = rol;
            this.facultadId = facultadId;
            this.escuelaId = escuelaId;
        }

        public RolUsuarioBusqueda getRol() {
            return rol;
        }

        public Long getFacultadId() {
            return facultadId;
        }

        public Long getEscuelaId() {
            return escuelaId;
        }
    }

    private static final class UsuarioDetalle {
        private final String nombreCompleto;
        private final String codigo;
        private final Long escuelaId;
        private final String escuelaNombre;
        private final Long facultadId;
        private final String facultadNombre;

        private UsuarioDetalle(String nombreCompleto,
                               String codigo,
                               Long escuelaId,
                               String escuelaNombre,
                               Long facultadId,
                               String facultadNombre) {
            this.nombreCompleto = nombreCompleto;
            this.codigo = codigo;
            this.escuelaId = escuelaId;
            this.escuelaNombre = escuelaNombre;
            this.facultadId = facultadId;
            this.facultadNombre = facultadNombre;
        }

        private static UsuarioDetalle crear(String nombreCompleto,
                                            String codigo,
                                            Long escuelaId,
                                            String escuelaNombre,
                                            Long facultadId,
                                            String facultadNombre) {
            return new UsuarioDetalle(nombreCompleto, codigo, escuelaId, escuelaNombre, facultadId, facultadNombre);
        }

        private static UsuarioDetalle desdeUsuario(Usuario usuario) {
            return usuario == null
                    ? vacio()
                    : new UsuarioDetalle(usuario.getNombreCompleto(), null, null, null, null, null);
        }

        private static UsuarioDetalle vacio() {
            return new UsuarioDetalle(null, null, null, null, null, null);
        }

        public String getNombreCompleto() {
            return nombreCompleto;
        }

        public String getCodigo() {
            return codigo;
        }
        public Long getEscuelaId() {
            return escuelaId;
        }


        public String getEscuelaNombre() {
            return escuelaNombre;
        }
        public Long getFacultadId() {
            return facultadId;
        }


        public String getFacultadNombre() {
            return facultadNombre;
        }
    }
}
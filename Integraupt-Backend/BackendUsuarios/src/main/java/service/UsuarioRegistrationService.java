package service;

import dto.AdministrativoForm;
import dto.DocenteForm;
import dto.EstudianteForm;
import model.*;
import repository.*;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.Base64;
import java.util.List;
import java.util.Optional;

@Service
public class UsuarioRegistrationService {

    @Autowired
    private UsuarioRepository usuarioRepo;
    
    @Autowired
    private UsuarioAuthRepository authRepo;
    
    @Autowired
    private EstudianteRepository estudianteRepo;
    
    @Autowired
    private DocenteRepository docenteRepo;
    
    @Autowired
    private AdministrativoRepository administrativoRepo;
    
    @Autowired
    private TipoDocumentoRepository tipoDocRepo;
    
    @Autowired
    private RolRepository rolRepo;
    
    @Autowired
    private EscuelaRepository escuelaRepo;

    private void validarDocumento(String numDoc, Integer idUsuarioActual) {
        Optional<Usuario> existenteOpt = usuarioRepo.findByNumDoc(numDoc);
        if (existenteOpt.isPresent() && 
            (idUsuarioActual == null || !existenteOpt.get().idUsuario.equals(idUsuarioActual))) {
            throw new RuntimeException("El documento ya está registrado.");
        }
    }

    private void validarCorreo(String correo, Integer idUsuarioActual) {
        Optional<UsuarioAuth> existenteOpt = authRepo.findByCorreoU(correo);
        if (existenteOpt.isPresent() && 
            (idUsuarioActual == null || !existenteOpt.get().usuario.idUsuario.equals(idUsuarioActual))) {
            throw new RuntimeException("El correo ya está registrado.");
        }
    }

    private void validarPasswordObligatoria(String password) {
        if (password == null || password.isBlank()) {
            throw new RuntimeException("La contraseña es obligatoria.");
        }
    }

    private String codificarPassword(String password) {
        return Base64.getEncoder().encodeToString(password.getBytes());
    }

    private void actualizarPasswordSiCorresponde(UsuarioAuth auth, String password) {
        if (password != null && !password.isBlank()) {
            auth.password = codificarPassword(password);
        }
    }
    
    private Usuario actualizarEstadoUsuario(Usuario usuario, boolean activo) {
        usuario.estado = activo ? 1 : 0;
        return usuarioRepo.save(usuario);
    }
    
    private UsuarioAuth obtenerCredencial(Usuario usuario) {
        Optional<UsuarioAuth> authOpt = authRepo.findByUsuario(usuario);
        if (authOpt.isEmpty()) {
            throw new RuntimeException("No se encontraron credenciales para el usuario indicado.");
        }
        return authOpt.get();
    }

    private LocalDate parsearFecha(String fecha) {
        try {
            return LocalDate.parse(fecha, DateTimeFormatter.ISO_DATE);
        } catch (Exception e) {
            throw new RuntimeException("Formato de fecha inválido. Utiliza AAAA-MM-DD.");
        }
    }
    
    private LocalDate fechaActualSiVacia(String fecha) {
        return (fecha == null || fecha.isBlank()) ? LocalDate.now() : parsearFecha(fecha);
    }

    private Escuela obtenerEscuelaOpcional(Integer idEscuela) {
        if (idEscuela == null) {
            return null;
        }
        return escuelaRepo.findById(idEscuela)
            .orElseThrow(() -> new RuntimeException("Escuela no válida"));
    }

    @Transactional
    public Estudiante registerEstudiante(EstudianteForm form) {
        validarDocumento(form.numDoc, null);
        validarCorreo(form.correo, null);
        validarPasswordObligatoria(form.password);

        TipoDocumento tipoDoc = tipoDocRepo.findById(form.idTipoDoc)
            .orElseThrow(() -> new RuntimeException("Tipo de documento no válido"));
        Rol rol = rolRepo.findById(2)
            .orElseThrow(() -> new RuntimeException("Rol Estudiante no encontrado"));
        Escuela escuela = escuelaRepo.findById(form.idEscuela)
            .orElseThrow(() -> new RuntimeException("Escuela no válida"));

        Usuario usuario = new Usuario();
        usuario.nombre = form.nombre;
        usuario.apellido = form.apellido;
        usuario.tipoDoc = tipoDoc;
        usuario.numDoc = form.numDoc;
        usuario.celular = form.celular;
        usuario.genero = form.genero;
        usuario.rol = rol;
        usuario.estado = 1;
        usuario.fechaRegistro = java.time.LocalDateTime.now();
        usuarioRepo.save(usuario);

        UsuarioAuth auth = new UsuarioAuth();
        auth.usuario = usuario;
        auth.correoU = form.correo;
        auth.password = codificarPassword(form.password);
        authRepo.save(auth);

        Estudiante estudiante = new Estudiante();
        estudiante.usuario = usuario;
        estudiante.escuela = escuela;
        estudiante.codigo = form.codigo;
        return estudianteRepo.save(estudiante);
    }

    @Transactional(readOnly = true)
    public List<Estudiante> listarEstudiantes() {
        return estudianteRepo.findAll();
    }

    @Transactional(readOnly = true)
    public Estudiante obtenerEstudiante(Integer id) {
        return estudianteRepo.findById(id)
            .orElseThrow(() -> new RuntimeException("Estudiante no encontrado."));
    }

    @Transactional
    public void eliminarEstudiante(Integer id) {
        Estudiante estudiante = obtenerEstudiante(id);
        actualizarEstadoEstudiante(id, false);
    }

    @Transactional
    public Estudiante actualizarEstadoEstudiante(Integer id, boolean activo) {
        Estudiante estudiante = obtenerEstudiante(id);
        Usuario usuario = estudiante.usuario;
        actualizarEstadoUsuario(usuario, activo);
        estudiante.usuario.estado = usuario.estado;
        return estudiante;    }
    
    @Transactional
    public Estudiante actualizarEstudiante(Integer id, EstudianteForm form) {
        Estudiante estudiante = obtenerEstudiante(id);
        Usuario usuario = estudiante.usuario;

        validarDocumento(form.numDoc, usuario.idUsuario);
        validarCorreo(form.correo, usuario.idUsuario);

        TipoDocumento tipoDoc = tipoDocRepo.findById(form.idTipoDoc)
            .orElseThrow(() -> new RuntimeException("Tipo de documento no válido"));
        Rol rol = rolRepo.findById(2)
            .orElseThrow(() -> new RuntimeException("Rol Estudiante no encontrado"));
        Escuela escuela = escuelaRepo.findById(form.idEscuela)
            .orElseThrow(() -> new RuntimeException("Escuela no válida"));

        usuario.nombre = form.nombre;
        usuario.apellido = form.apellido;
        usuario.tipoDoc = tipoDoc;
        usuario.numDoc = form.numDoc;
        usuario.celular = form.celular;
        usuario.genero = form.genero;
        usuario.rol = rol;
        usuarioRepo.save(usuario);

        UsuarioAuth auth = obtenerCredencial(usuario);
        auth.correoU = form.correo;
        actualizarPasswordSiCorresponde(auth, form.password);
        authRepo.save(auth);

        estudiante.escuela = escuela;
        estudiante.codigo = form.codigo;
        return estudianteRepo.save(estudiante);
    }
    
    @Transactional
    public Docente registerDocente(DocenteForm form) {
        validarDocumento(form.numDoc, null);
        validarCorreo(form.correo, null);
        validarPasswordObligatoria(form.password);

        TipoDocumento tipoDoc = tipoDocRepo.findById(form.idTipoDoc)
            .orElseThrow(() -> new RuntimeException("Tipo de documento no válido"));
        Rol rol = rolRepo.findById(1)
            .orElseThrow(() -> new RuntimeException("Rol Docente no encontrado"));
        Escuela escuela = obtenerEscuelaOpcional(form.idEscuela);

        Usuario usuario = new Usuario();
        usuario.nombre = form.nombre;
        usuario.apellido = form.apellido;
        usuario.tipoDoc = tipoDoc;
        usuario.numDoc = form.numDoc;
        usuario.celular = form.celular;
        usuario.genero = form.genero;
        usuario.rol = rol;
        usuario.estado = 1;
        usuario.fechaRegistro = java.time.LocalDateTime.now();
        usuarioRepo.save(usuario);

        UsuarioAuth auth = new UsuarioAuth();
        auth.usuario = usuario;
        auth.correoU = form.correo;
        auth.password = codificarPassword(form.password);
        authRepo.save(auth);

        Docente docente = new Docente();
        docente.usuario = usuario;
        docente.escuela = escuela;
        docente.codigoDocente = form.codigoDocente;
        docente.tipoContrato = TipoContrato.fromDescripcion(form.tipoContrato);
        docente.especialidad = form.especialidad;
        docente.fechaIncorporacion = fechaActualSiVacia(form.fechaIncorporacion);
        return docenteRepo.save(docente);
    }

    @Transactional
    public Docente actualizarDocente(Integer id, DocenteForm form) {
        Docente docente = obtenerDocente(id);
        Usuario usuario = docente.usuario;

        validarDocumento(form.numDoc, usuario.idUsuario);
        validarCorreo(form.correo, usuario.idUsuario);

        TipoDocumento tipoDoc = tipoDocRepo.findById(form.idTipoDoc)
            .orElseThrow(() -> new RuntimeException("Tipo de documento no válido"));
        Rol rol = rolRepo.findById(1)
            .orElseThrow(() -> new RuntimeException("Rol Docente no encontrado"));
        Escuela escuela = obtenerEscuelaOpcional(form.idEscuela);

        usuario.nombre = form.nombre;
        usuario.apellido = form.apellido;
        usuario.tipoDoc = tipoDoc;
        usuario.numDoc = form.numDoc;
        usuario.celular = form.celular;
        usuario.genero = form.genero;
        usuario.rol = rol;
        usuarioRepo.save(usuario);

        UsuarioAuth auth = obtenerCredencial(usuario);
        auth.correoU = form.correo;
        actualizarPasswordSiCorresponde(auth, form.password);
        authRepo.save(auth);

        docente.escuela = escuela;
        docente.codigoDocente = form.codigoDocente;
        docente.tipoContrato = TipoContrato.fromDescripcion(form.tipoContrato);
        docente.especialidad = form.especialidad;
        if (form.fechaIncorporacion != null && !form.fechaIncorporacion.isBlank()) {
            docente.fechaIncorporacion = parsearFecha(form.fechaIncorporacion);
        }
        return docenteRepo.save(docente);
    }

    @Transactional(readOnly = true)
    public List<Docente> listarDocentes() {
        return docenteRepo.findAll();
    }

    @Transactional(readOnly = true)
    public Docente obtenerDocente(Integer id) {
        return docenteRepo.findById(id)
            .orElseThrow(() -> new RuntimeException("Docente no encontrado."));
    }

    @Transactional
    public void eliminarDocente(Integer id) {
        actualizarEstadoDocente(id, false);
    }

    @Transactional
    public Docente actualizarEstadoDocente(Integer id, boolean activo) {
        Docente docente = obtenerDocente(id);
        Usuario usuario = docente.usuario;
        actualizarEstadoUsuario(usuario, activo);
        docente.usuario.estado = usuario.estado;
        return docente;
    }

    @Transactional
    public Administrativo registerAdministrativo(AdministrativoForm form) {
        validarDocumento(form.numDoc, null);
        validarCorreo(form.correo, null);
        validarPasswordObligatoria(form.password);

        TipoDocumento tipoDoc = tipoDocRepo.findById(form.idTipoDoc)
            .orElseThrow(() -> new RuntimeException("Tipo de documento no válido"));
        Rol rol = rolRepo.findById(form.idRol)
            .orElseThrow(() -> new RuntimeException("Rol Administrativo no válido"));
        Escuela escuela = obtenerEscuelaOpcional(form.idEscuela);

        Usuario usuario = new Usuario();
        usuario.nombre = form.nombre;
        usuario.apellido = form.apellido;
        usuario.tipoDoc = tipoDoc;
        usuario.numDoc = form.numDoc;
        usuario.celular = form.celular;
        usuario.genero = form.genero;
        usuario.rol = rol;
        usuario.estado = 1;
        usuario.fechaRegistro = java.time.LocalDateTime.now();
        usuarioRepo.save(usuario);

        UsuarioAuth auth = new UsuarioAuth();
        auth.usuario = usuario;
        auth.correoU = form.correo;
        auth.password = codificarPassword(form.password);
        authRepo.save(auth);

        Administrativo administrativo = new Administrativo();
        administrativo.usuario = usuario;
        administrativo.escuela = escuela;
        administrativo.turno = Turno.fromDescripcion(form.turno);
        administrativo.extension = form.extension;
        administrativo.fechaIncorporacion = fechaActualSiVacia(form.fechaIncorporacion);
        return administrativoRepo.save(administrativo);
    }

    @Transactional(readOnly = true)
    public List<Administrativo> listarAdministrativos() {
        return administrativoRepo.findAll();
    }

    @Transactional(readOnly = true)
    public Administrativo obtenerAdministrativo(Integer id) {
        return administrativoRepo.findById(id)
            .orElseThrow(() -> new RuntimeException("Administrativo no encontrado."));
    }

    @Transactional
    public void eliminarAdministrativo(Integer id) {
        actualizarEstadoAdministrativo(id, false);
    }

    @Transactional
    public Administrativo actualizarEstadoAdministrativo(Integer id, boolean activo) {
        Administrativo administrativo = obtenerAdministrativo(id);
        Usuario usuario = administrativo.usuario;
        actualizarEstadoUsuario(usuario, activo);
        administrativo.usuario.estado = usuario.estado;
        return administrativo;
    }
    
    @Transactional
    public Administrativo actualizarAdministrativo(Integer id, AdministrativoForm form) {
        Administrativo administrativo = obtenerAdministrativo(id);
        Usuario usuario = administrativo.usuario;

        validarDocumento(form.numDoc, usuario.idUsuario);
        validarCorreo(form.correo, usuario.idUsuario);

        TipoDocumento tipoDoc = tipoDocRepo.findById(form.idTipoDoc)
            .orElseThrow(() -> new RuntimeException("Tipo de documento no válido"));
        Rol rol = rolRepo.findById(form.idRol)
            .orElseThrow(() -> new RuntimeException("Rol Administrativo no válido"));
        Escuela escuela = obtenerEscuelaOpcional(form.idEscuela);

        usuario.nombre = form.nombre;
        usuario.apellido = form.apellido;
        usuario.tipoDoc = tipoDoc;
        usuario.numDoc = form.numDoc;
        usuario.celular = form.celular;
        usuario.genero = form.genero;
        usuario.rol = rol;
        usuarioRepo.save(usuario);

        UsuarioAuth auth = obtenerCredencial(usuario);
        auth.correoU = form.correo;
        actualizarPasswordSiCorresponde(auth, form.password);
        authRepo.save(auth);

        administrativo.escuela = escuela;
        administrativo.turno = Turno.fromDescripcion(form.turno);
        administrativo.extension = form.extension;
        if (form.fechaIncorporacion != null && !form.fechaIncorporacion.isBlank()) {
            administrativo.fechaIncorporacion = parsearFecha(form.fechaIncorporacion);
        }
        return administrativoRepo.save(administrativo);
    }
    
}
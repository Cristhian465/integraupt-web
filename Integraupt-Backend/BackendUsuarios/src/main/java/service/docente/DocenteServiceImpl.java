package service.docente;

import dto.DocenteForm;
import java.util.List;
import model.Docente;
import model.Escuela;
import model.Rol;
import model.TipoContrato;
import model.TipoDocumento;
import model.Usuario;
import model.UsuarioAuth;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import repository.DocenteRepository;
import repository.EscuelaRepository;
import repository.RolRepository;
import repository.TipoDocumentoRepository;
import repository.UsuarioAuthRepository;
import repository.UsuarioRepository;
import service.common.UsuarioServiceHelper;

@Service
public class DocenteServiceImpl extends UsuarioServiceHelper implements DocenteService {

    private final DocenteRepository docenteRepo;

    public DocenteServiceImpl(
        UsuarioRepository usuarioRepo,
        UsuarioAuthRepository authRepo,
        TipoDocumentoRepository tipoDocRepo,
        RolRepository rolRepo,
        EscuelaRepository escuelaRepo,
        DocenteRepository docenteRepo
    ) {
        super(usuarioRepo, authRepo, tipoDocRepo, rolRepo, escuelaRepo);
        this.docenteRepo = docenteRepo;
    }

    @Override
    @Transactional
    public Docente registrar(DocenteForm form) {
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

    @Override
    @Transactional
    public Docente actualizar(Integer id, DocenteForm form) {
        Docente docente = obtener(id);
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

    @Override
    @Transactional(readOnly = true)
    public List<Docente> listar() {
        return docenteRepo.findAll();
    }

    @Override
    @Transactional(readOnly = true)
    public Docente obtener(Integer id) {
        return docenteRepo.findById(id)
            .orElseThrow(() -> new RuntimeException("Docente no encontrado."));
    }

    @Override
    @Transactional
    public Docente actualizarEstado(Integer id, boolean activo) {
        Docente docente = obtener(id);
        Usuario usuario = docente.usuario;
        actualizarEstadoUsuario(usuario, activo);
        docente.usuario.estado = usuario.estado;
        return docente;
    }

    @Override
    @Transactional
    public void eliminar(Integer id) {
        actualizarEstado(id, false);
    }
}
package service.estudiante;

import dto.EstudianteForm;
import java.util.List;
import model.Escuela;
import model.Estudiante;
import model.Rol;
import model.TipoDocumento;
import model.Usuario;
import model.UsuarioAuth;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import repository.EscuelaRepository;
import repository.EstudianteRepository;
import repository.RolRepository;
import repository.TipoDocumentoRepository;
import repository.UsuarioAuthRepository;
import repository.UsuarioRepository;
import service.common.UsuarioServiceHelper;

@Service
public class EstudianteServiceImpl extends UsuarioServiceHelper implements EstudianteService {

    private final EstudianteRepository estudianteRepo;

    public EstudianteServiceImpl(
        UsuarioRepository usuarioRepo,
        UsuarioAuthRepository authRepo,
        TipoDocumentoRepository tipoDocRepo,
        RolRepository rolRepo,
        EscuelaRepository escuelaRepo,
        EstudianteRepository estudianteRepo
    ) {
        super(usuarioRepo, authRepo, tipoDocRepo, rolRepo, escuelaRepo);
        this.estudianteRepo = estudianteRepo;
    }

    @Override
    @Transactional
    public Estudiante registrar(EstudianteForm form) {
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

    @Override
    @Transactional(readOnly = true)
    public List<Estudiante> listar() {
        return estudianteRepo.findAll();
    }

    @Override
    @Transactional(readOnly = true)
    public Estudiante obtener(Integer id) {
        return estudianteRepo.findById(id)
            .orElseThrow(() -> new RuntimeException("Estudiante no encontrado."));
    }

    @Override
    @Transactional
    public Estudiante actualizar(Integer id, EstudianteForm form) {
        Estudiante estudiante = obtener(id);
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

    @Override
    @Transactional
    public Estudiante actualizarEstado(Integer id, boolean activo) {
        Estudiante estudiante = obtener(id);
        Usuario usuario = estudiante.usuario;
        actualizarEstadoUsuario(usuario, activo);
        estudiante.usuario.estado = usuario.estado;
        return estudiante;
    }

    @Override
    @Transactional
    public void eliminar(Integer id) {
        actualizarEstado(id, false);
    }
}
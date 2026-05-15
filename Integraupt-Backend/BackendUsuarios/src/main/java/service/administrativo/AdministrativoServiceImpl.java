package service.administrativo;

import dto.AdministrativoForm;
import java.util.List;
import model.Administrativo;
import model.Escuela;
import model.Rol;
import model.TipoDocumento;
import model.Turno;
import model.Usuario;
import model.UsuarioAuth;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import repository.AdministrativoRepository;
import repository.EscuelaRepository;
import repository.RolRepository;
import repository.TipoDocumentoRepository;
import repository.UsuarioAuthRepository;
import repository.UsuarioRepository;
import service.common.UsuarioServiceHelper;

@Service
public class AdministrativoServiceImpl extends UsuarioServiceHelper implements AdministrativoService {

    private final AdministrativoRepository administrativoRepo;

    public AdministrativoServiceImpl(
        UsuarioRepository usuarioRepo,
        UsuarioAuthRepository authRepo,
        TipoDocumentoRepository tipoDocRepo,
        RolRepository rolRepo,
        EscuelaRepository escuelaRepo,
        AdministrativoRepository administrativoRepo
    ) {
        super(usuarioRepo, authRepo, tipoDocRepo, rolRepo, escuelaRepo);
        this.administrativoRepo = administrativoRepo;
    }

    @Override
    @Transactional
    public Administrativo registrar(AdministrativoForm form) {
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

    @Override
    @Transactional
    public Administrativo actualizar(Integer id, AdministrativoForm form) {
        Administrativo administrativo = obtener(id);
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

    @Override
    @Transactional(readOnly = true)
    public List<Administrativo> listar() {
        return administrativoRepo.findAll();
    }

    @Override
    @Transactional(readOnly = true)
    public Administrativo obtener(Integer id) {
        return administrativoRepo.findById(id)
            .orElseThrow(() -> new RuntimeException("Administrativo no encontrado."));
    }

    @Override
    @Transactional
    public Administrativo actualizarEstado(Integer id, boolean activo) {
        Administrativo administrativo = obtener(id);
        Usuario usuario = administrativo.usuario;
        actualizarEstadoUsuario(usuario, activo);
        administrativo.usuario.estado = usuario.estado;
        return administrativo;
    }

    @Override
    @Transactional
    public void eliminar(Integer id) {
        actualizarEstado(id, false);
    }
}
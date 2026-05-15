package service.common;

import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.Base64;
import java.util.Optional;
import model.Escuela;
import model.Usuario;
import model.UsuarioAuth;
import repository.EscuelaRepository;
import repository.RolRepository;
import repository.TipoDocumentoRepository;
import repository.UsuarioAuthRepository;
import repository.UsuarioRepository;

public abstract class UsuarioServiceHelper {

    protected final UsuarioRepository usuarioRepo;
    protected final UsuarioAuthRepository authRepo;
    protected final TipoDocumentoRepository tipoDocRepo;
    protected final RolRepository rolRepo;
    protected final EscuelaRepository escuelaRepo;

    protected UsuarioServiceHelper(
        UsuarioRepository usuarioRepo,
        UsuarioAuthRepository authRepo,
        TipoDocumentoRepository tipoDocRepo,
        RolRepository rolRepo,
        EscuelaRepository escuelaRepo
    ) {
        this.usuarioRepo = usuarioRepo;
        this.authRepo = authRepo;
        this.tipoDocRepo = tipoDocRepo;
        this.rolRepo = rolRepo;
        this.escuelaRepo = escuelaRepo;
    }

    protected void validarDocumento(String numDoc, Integer idUsuarioActual) {
        Optional<Usuario> existenteOpt = usuarioRepo.findByNumDoc(numDoc);
        if (existenteOpt.isPresent() &&
            (idUsuarioActual == null || !existenteOpt.get().idUsuario.equals(idUsuarioActual))) {
            throw new RuntimeException("El documento ya está registrado.");
        }
    }

    protected void validarCorreo(String correo, Integer idUsuarioActual) {
        Optional<UsuarioAuth> existenteOpt = authRepo.findByCorreoU(correo);
        if (existenteOpt.isPresent() &&
            (idUsuarioActual == null || !existenteOpt.get().usuario.idUsuario.equals(idUsuarioActual))) {
            throw new RuntimeException("El correo ya está registrado.");
        }
    }

    protected void validarPasswordObligatoria(String password) {
        if (password == null || password.isBlank()) {
            throw new RuntimeException("La contraseña es obligatoria.");
        }
    }

    protected String codificarPassword(String password) {
        return Base64.getEncoder().encodeToString(password.getBytes());
    }

    protected void actualizarPasswordSiCorresponde(UsuarioAuth auth, String password) {
        if (password != null && !password.isBlank()) {
            auth.password = codificarPassword(password);
        }
    }

    protected Usuario actualizarEstadoUsuario(Usuario usuario, boolean activo) {
        usuario.estado = activo ? 1 : 0;
        return usuarioRepo.save(usuario);
    }

    protected UsuarioAuth obtenerCredencial(Usuario usuario) {
        Optional<UsuarioAuth> authOpt = authRepo.findByUsuario(usuario);
        if (authOpt.isEmpty()) {
            throw new RuntimeException("No se encontraron credenciales para el usuario indicado.");
        }
        return authOpt.get();
    }

    protected LocalDate parsearFecha(String fecha) {
        try {
            return LocalDate.parse(fecha, DateTimeFormatter.ISO_DATE);
        } catch (Exception e) {
            throw new RuntimeException("Formato de fecha inválido. Utiliza AAAA-MM-DD.");
        }
    }

    protected LocalDate fechaActualSiVacia(String fecha) {
        return (fecha == null || fecha.isBlank()) ? LocalDate.now() : parsearFecha(fecha);
    }

    protected Escuela obtenerEscuelaOpcional(Integer idEscuela) {
        if (idEscuela == null) {
            return null;
        }
        return escuelaRepo.findById(idEscuela)
            .orElseThrow(() -> new RuntimeException("Escuela no válida"));
    }
}
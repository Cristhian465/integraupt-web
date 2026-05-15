package repository;

import model.Usuario;
import model.UsuarioAuth;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.Optional;

public interface UsuarioAuthRepository extends JpaRepository<UsuarioAuth, Integer> {

    // CORREGIDO: Usar Optional y correoU (no correo)
    Optional<UsuarioAuth> findByCorreoU(String correoU); // ← Para login

    // CORREGIDO: Usar Optional
    Optional<UsuarioAuth> findByUsuario(Usuario usuario);
    
    // CORREGIDO: Para verificar si correo existe
    boolean existsByCorreoU(String correoU);
}
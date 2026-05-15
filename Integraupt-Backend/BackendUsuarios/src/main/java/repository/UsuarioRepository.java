package repository;

import model.Usuario;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.Optional;

public interface UsuarioRepository extends JpaRepository<Usuario, Integer> {

    // MEJORADO: Usar Optional para evitar NullPointerException
    Optional<Usuario> findByNumDoc(String numDoc);
    
    // ÚTIL: Para verificar si documento ya existe
    boolean existsByNumDoc(String numDoc);
    
    // ÚTIL: Buscar por rol específico
    Optional<Usuario> findByNumDocAndRol_IdRol(String numDoc, Integer idRol);
}
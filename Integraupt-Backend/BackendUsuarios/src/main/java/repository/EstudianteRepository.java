package repository;

import model.Estudiante;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.Optional;

public interface EstudianteRepository extends JpaRepository<Estudiante, Integer> {

    // ÚTIL: Buscar estudiante por código
    Optional<Estudiante> findByCodigo(String codigo);
    
    // ÚTIL: Verificar si código ya existe
    boolean existsByCodigo(String codigo);
    
    // ÚTIL: Buscar por usuario
    Optional<Estudiante> findByUsuario_IdUsuario(Integer idUsuario);
}
package repository;

import model.Docente;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.Optional;

public interface DocenteRepository extends JpaRepository<Docente, Integer> {

    // ÚTIL: Buscar docente por código
    Optional<Docente> findByCodigoDocente(String codigoDocente);
    
    // ÚTIL: Verificar si código ya existe
    boolean existsByCodigoDocente(String codigoDocente);
    
    // ÚTIL: Buscar por usuario
    Optional<Docente> findByUsuario_IdUsuario(Integer idUsuario);
}
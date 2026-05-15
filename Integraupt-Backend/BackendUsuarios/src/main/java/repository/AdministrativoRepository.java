package repository;

import java.util.List;
import model.Administrativo;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.Optional;

public interface AdministrativoRepository extends JpaRepository<Administrativo, Integer> {

    // ÚTIL: Buscar por usuario
    Optional<Administrativo> findByUsuario_IdUsuario(Integer idUsuario);
    
    // ÚTIL: Buscar administrativos por turno
    List<Administrativo> findByTurno(String turno);
}
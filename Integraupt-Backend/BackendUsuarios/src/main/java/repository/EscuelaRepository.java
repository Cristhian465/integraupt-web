package repository;

import model.Escuela;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.Optional;

public interface EscuelaRepository extends JpaRepository<Escuela, Integer> {
    Optional<Escuela> findByNombre(String nombre);
}
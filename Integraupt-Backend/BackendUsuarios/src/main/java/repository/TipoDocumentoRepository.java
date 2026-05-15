package repository;

import model.TipoDocumento;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.Optional;

public interface TipoDocumentoRepository extends JpaRepository<TipoDocumento, Integer> {
    Optional<TipoDocumento> findByAbreviatura(String abreviatura);
}
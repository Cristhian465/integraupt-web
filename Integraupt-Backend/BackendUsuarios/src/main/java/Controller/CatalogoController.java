package controller;

import java.util.List;
import model.Escuela;
import model.Rol;
import model.TipoDocumento;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;
import repository.EscuelaRepository;
import repository.RolRepository;
import repository.TipoDocumentoRepository;

@RestController
@RequestMapping("/api/catalogos")
public class CatalogoController {

    private final TipoDocumentoRepository tipoDocumentoRepository;
    private final RolRepository rolRepository;
    private final EscuelaRepository escuelaRepository;

    public CatalogoController(
        TipoDocumentoRepository tipoDocumentoRepository,
        RolRepository rolRepository,
        EscuelaRepository escuelaRepository
    ) {
        this.tipoDocumentoRepository = tipoDocumentoRepository;
        this.rolRepository = rolRepository;
        this.escuelaRepository = escuelaRepository;
    }

    @GetMapping("/tipos-documento")
    public List<TipoDocumento> obtenerTiposDocumento() {
        return tipoDocumentoRepository.findAll();
    }

    @GetMapping("/roles")
    public List<Rol> obtenerRoles() {
        return rolRepository.findAll();
    }

    @GetMapping("/escuelas")
    public List<Escuela> obtenerEscuelas() {
        return escuelaRepository.findAll();
    }
}
package controller;

import dto.AdministrativoForm;
import dto.EstadoUsuarioForm;
import java.util.List;
import model.Administrativo;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import service.administrativo.AdministrativoService;

@RestController
@RequestMapping("/api/administrativos")
public class AdministrativoController {

    private final AdministrativoService service;

    public AdministrativoController(AdministrativoService service) {
        this.service = service;
    }

    @GetMapping
    public List<Administrativo> listar() {
        return service.listar();
    }

    @GetMapping("/{id}")
    public ResponseEntity<Administrativo> obtener(@PathVariable Integer id) {
        try {
            Administrativo administrativo = service.obtener(id);
            return ResponseEntity.ok(administrativo);
        } catch (RuntimeException e) {
            return ResponseEntity.notFound().build();
        }
    }

    @PostMapping
    @ResponseStatus(HttpStatus.CREATED)
    public Administrativo crear(@RequestBody AdministrativoForm form) {
        return service.registrar(form);
    }

    @PutMapping("/{id}")
    public ResponseEntity<Administrativo> actualizar(@PathVariable Integer id, @RequestBody AdministrativoForm form) {
        try {
            Administrativo administrativo = service.actualizar(id, form);
            return ResponseEntity.ok(administrativo);
        } catch (RuntimeException e) {
            return ResponseEntity.notFound().build();
        }
    }

    @PatchMapping("/{id}/estado")
    public ResponseEntity<Administrativo> actualizarEstado(@PathVariable Integer id, @RequestBody EstadoUsuarioForm form) {
        if (form.activo == null) {
            return ResponseEntity.badRequest().build();
        }
        try {
            Administrativo administrativo = service.actualizarEstado(id, form.activo);
            return ResponseEntity.ok(administrativo);
        } catch (RuntimeException e) {
            return ResponseEntity.notFound().build();
        }
    }

    @DeleteMapping("/{id}")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void eliminar(@PathVariable Integer id) {
        service.eliminar(id);
    }
}

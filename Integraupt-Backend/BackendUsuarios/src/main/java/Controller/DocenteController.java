package controller;

import dto.DocenteForm;
import dto.EstadoUsuarioForm;
import java.util.List;
import model.Docente;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import service.docente.DocenteService;

@RestController
@RequestMapping("/api/docentes")
public class DocenteController {

    private final DocenteService service;

    public DocenteController(DocenteService service) {
        this.service = service;
    }

    @GetMapping
    public List<Docente> listar() {
        return service.listar();
    }

    @GetMapping("/{id}")
    public ResponseEntity<Docente> obtener(@PathVariable Integer id) {
        try {
            Docente docente = service.obtener(id);
            return ResponseEntity.ok(docente);
        } catch (RuntimeException e) {
            return ResponseEntity.notFound().build();
        }
    }

    @PostMapping
    @ResponseStatus(HttpStatus.CREATED)
    public Docente crear(@RequestBody DocenteForm form) {
        return service.registrar(form);
    }

    @PutMapping("/{id}")
    public ResponseEntity<Docente> actualizar(@PathVariable Integer id, @RequestBody DocenteForm form) {
        try {
            Docente docente = service.actualizar(id, form);
            return ResponseEntity.ok(docente);
        } catch (RuntimeException e) {
            return ResponseEntity.notFound().build();
        }
    }

    @PatchMapping("/{id}/estado")
    public ResponseEntity<Docente> actualizarEstado(@PathVariable Integer id, @RequestBody EstadoUsuarioForm form) {
        if (form.activo == null) {
            return ResponseEntity.badRequest().build();
        }
        try {
            Docente docente = service.actualizarEstado(id, form.activo);
            return ResponseEntity.ok(docente);
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

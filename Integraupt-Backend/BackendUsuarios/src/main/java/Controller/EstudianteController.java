package controller;

import dto.EstadoUsuarioForm;
import dto.EstudianteForm;
import java.util.List;
import model.Estudiante;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import service.estudiante.EstudianteService;

@RestController
@RequestMapping("/api/estudiantes")
public class EstudianteController {

    private final EstudianteService service;

    public EstudianteController(EstudianteService service) {
        this.service = service;
    }

    @GetMapping
    public List<Estudiante> listar() {
        return service.listar();
    }

    @GetMapping("/{id}")
    public ResponseEntity<Estudiante> obtener(@PathVariable Integer id) {
        try {
            Estudiante estudiante = service.obtener(id);
            return ResponseEntity.ok(estudiante);
        } catch (RuntimeException e) {
            return ResponseEntity.notFound().build();
        }
    }

    @PostMapping
    @ResponseStatus(HttpStatus.CREATED)
    public Estudiante crear(@RequestBody EstudianteForm form) {
        return service.registrar(form);
    }

    @PutMapping("/{id}")
    public ResponseEntity<Estudiante> actualizar(@PathVariable Integer id, @RequestBody EstudianteForm form) {
        try {
            Estudiante estudiante = service.actualizar(id, form);
            return ResponseEntity.ok(estudiante);
        } catch (RuntimeException e) {
            return ResponseEntity.notFound().build();
        }
    }

    @PatchMapping("/{id}/estado")
    public ResponseEntity<Estudiante> actualizarEstado(@PathVariable Integer id, @RequestBody EstadoUsuarioForm form) {
        if (form.activo == null) {
            return ResponseEntity.badRequest().build();
        }
        try {
            Estudiante estudiante = service.actualizarEstado(id, form.activo);
            return ResponseEntity.ok(estudiante);
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

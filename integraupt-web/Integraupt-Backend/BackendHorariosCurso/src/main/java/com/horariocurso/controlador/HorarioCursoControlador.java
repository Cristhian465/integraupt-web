package com.horariocurso.controlador;

import com.horariocurso.dto.HorarioCursoRequest;
import com.horariocurso.dto.HorarioCursoResponse;
import com.horariocurso.dto.catalogo.HorarioMetaResponse;
import com.horariocurso.servicio.CatalogoServicio;
import com.horariocurso.servicio.HorarioCursoServicio;
import jakarta.validation.Valid;
import java.util.List;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/horarios")
@Validated
public class HorarioCursoControlador {

    private final HorarioCursoServicio horarioCursoServicio;
    private final CatalogoServicio catalogoServicio;

    public HorarioCursoControlador(HorarioCursoServicio horarioCursoServicio,
            CatalogoServicio catalogoServicio) {
        this.horarioCursoServicio = horarioCursoServicio;
        this.catalogoServicio = catalogoServicio;
    }

    @GetMapping
    public List<HorarioCursoResponse> listar() {
        return horarioCursoServicio.listar();
    }

    @GetMapping("/{id}")
    public HorarioCursoResponse obtenerPorId(@PathVariable Integer id) {
        return horarioCursoServicio.buscarPorId(id);
    }

    @PostMapping
    public ResponseEntity<HorarioCursoResponse> crear(@Valid @RequestBody HorarioCursoRequest request) {
        HorarioCursoResponse response = horarioCursoServicio.crear(request);
        return ResponseEntity.status(HttpStatus.CREATED).body(response);
    }

    @PutMapping("/{id}")
    public HorarioCursoResponse actualizar(@PathVariable Integer id,
            @Valid @RequestBody HorarioCursoRequest request) {
        return horarioCursoServicio.actualizar(id, request);
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Integer id) {
        horarioCursoServicio.eliminar(id);
        return ResponseEntity.noContent().build();
    }

    @GetMapping("/meta")
    public HorarioMetaResponse obtenerMeta() {
        return catalogoServicio.obtenerMeta();
    }
}

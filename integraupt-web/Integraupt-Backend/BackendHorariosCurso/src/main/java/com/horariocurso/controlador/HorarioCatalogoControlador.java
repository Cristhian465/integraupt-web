package com.horariocurso.controlador;

import com.horariocurso.dto.catalogo.BloqueCatalogoDto;
import com.horariocurso.dto.catalogo.CursoCatalogoDto;
import com.horariocurso.dto.catalogo.DocenteCatalogoDto;
import com.horariocurso.dto.catalogo.EspacioCatalogoDto;
import com.horariocurso.servicio.CatalogoServicio;
import java.util.List;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/horarios/catalogos")
public class HorarioCatalogoControlador {

    private final CatalogoServicio catalogoServicio;

    public HorarioCatalogoControlador(CatalogoServicio catalogoServicio) {
        this.catalogoServicio = catalogoServicio;
    }

    @GetMapping("/cursos")
    public List<CursoCatalogoDto> listarCursos() {
        return catalogoServicio.listarCursos();
    }

    @GetMapping("/docentes")
    public List<DocenteCatalogoDto> listarDocentes() {
        return catalogoServicio.listarDocentes();
    }

    @GetMapping("/espacios")
    public List<EspacioCatalogoDto> listarEspacios() {
        return catalogoServicio.listarEspacios();
    }

    @GetMapping("/bloques")
    public List<BloqueCatalogoDto> listarBloques() {
        return catalogoServicio.listarBloques();
    }
}

package com.horariocurso.servicio;

import com.horariocurso.dto.catalogo.BloqueCatalogoDto;
import com.horariocurso.dto.catalogo.CursoCatalogoDto;
import com.horariocurso.dto.catalogo.DocenteCatalogoDto;
import com.horariocurso.dto.catalogo.EspacioCatalogoDto;
import com.horariocurso.dto.catalogo.HorarioMetaResponse;
import com.horariocurso.repositorio.CatalogoRepositorio;
import java.util.List;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
@Transactional(readOnly = true)
public class CatalogoServicio {

    private final CatalogoRepositorio catalogoRepositorio;

    public CatalogoServicio(CatalogoRepositorio catalogoRepositorio) {
        this.catalogoRepositorio = catalogoRepositorio;
    }

    public List<CursoCatalogoDto> listarCursos() {
        return catalogoRepositorio.listarCursos();
    }

    public List<DocenteCatalogoDto> listarDocentes() {
        return catalogoRepositorio.listarDocentes();
    }

    public List<EspacioCatalogoDto> listarEspacios() {
        return catalogoRepositorio.listarEspacios();
    }

    public List<BloqueCatalogoDto> listarBloques() {
        return catalogoRepositorio.listarBloques();
    }

    public HorarioMetaResponse obtenerMeta() {
        HorarioMetaResponse response = new HorarioMetaResponse();
        response.setCursos(listarCursos());
        response.setDocentes(listarDocentes());
        response.setEspacios(listarEspacios());
        response.setBloques(listarBloques());
        return response;
    }
}

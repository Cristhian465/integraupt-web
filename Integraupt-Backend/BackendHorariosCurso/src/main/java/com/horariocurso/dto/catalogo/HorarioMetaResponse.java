package com.horariocurso.dto.catalogo;

import java.util.List;

public class HorarioMetaResponse {

    private List<CursoCatalogoDto> cursos;
    private List<DocenteCatalogoDto> docentes;
    private List<EspacioCatalogoDto> espacios;
    private List<BloqueCatalogoDto> bloques;

    public List<CursoCatalogoDto> getCursos() {
        return cursos;
    }

    public void setCursos(List<CursoCatalogoDto> cursos) {
        this.cursos = cursos;
    }

    public List<DocenteCatalogoDto> getDocentes() {
        return docentes;
    }

    public void setDocentes(List<DocenteCatalogoDto> docentes) {
        this.docentes = docentes;
    }

    public List<EspacioCatalogoDto> getEspacios() {
        return espacios;
    }

    public void setEspacios(List<EspacioCatalogoDto> espacios) {
        this.espacios = espacios;
    }

    public List<BloqueCatalogoDto> getBloques() {
        return bloques;
    }

    public void setBloques(List<BloqueCatalogoDto> bloques) {
        this.bloques = bloques;
    }
}

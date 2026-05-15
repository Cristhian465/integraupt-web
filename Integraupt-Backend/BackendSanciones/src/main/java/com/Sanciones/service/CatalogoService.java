package com.Sanciones.service;

import com.Sanciones.model.Escuela;
import com.Sanciones.model.Facultad;
import com.Sanciones.repository.EscuelaRepository;
import com.Sanciones.repository.FacultadRepository;
import org.springframework.stereotype.Service;

import java.util.List;

@Service
public class CatalogoService {

    private final FacultadRepository facultadRepository;
    private final EscuelaRepository escuelaRepository;

    public CatalogoService(FacultadRepository facultadRepository, EscuelaRepository escuelaRepository) {
        this.facultadRepository = facultadRepository;
        this.escuelaRepository = escuelaRepository;
    }

    public List<Facultad> obtenerFacultades() {
        return facultadRepository.findAllByOrderByNombreAsc();
    }

    public List<Escuela> obtenerEscuelas(Long facultadId) {
        if (facultadId != null) {
            return escuelaRepository.findByFacultadIdOrderByNombreAsc(facultadId);
        }
        return escuelaRepository.findAllByOrderByNombreAsc();
    }
}
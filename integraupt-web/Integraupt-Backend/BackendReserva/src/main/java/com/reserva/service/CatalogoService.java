package com.reserva.service;

import com.reserva.model.Escuela;
import com.reserva.model.Facultad;
import com.reserva.repository.EscuelaRepository;
import com.reserva.repository.FacultadRepository;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.ArrayList;
import java.util.List;

@Service
@Transactional(readOnly = true)
public class CatalogoService {

    private final FacultadRepository facultadRepository;
    private final EscuelaRepository escuelaRepository;

    public CatalogoService(FacultadRepository facultadRepository, EscuelaRepository escuelaRepository) {
        this.facultadRepository = facultadRepository;
        this.escuelaRepository = escuelaRepository;
    }

    public List<Facultad> listarFacultades() {
        return new ArrayList<>(facultadRepository.findAllOrdenados());
    }

    public List<Escuela> listarEscuelas(Integer facultadId) {
        if (facultadId == null) {
            return new ArrayList<>(escuelaRepository.findAllOrdenados());
        }
        return new ArrayList<>(escuelaRepository.findByFacultadIdOrderByNombreAsc(facultadId));
    }
}
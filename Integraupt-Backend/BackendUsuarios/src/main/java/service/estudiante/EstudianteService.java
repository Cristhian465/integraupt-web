package service.estudiante;

import java.util.List;
import dto.EstudianteForm;
import model.Estudiante;

public interface EstudianteService {
    Estudiante registrar(EstudianteForm form);
    List<Estudiante> listar();
    Estudiante obtener(Integer id);
    Estudiante actualizar(Integer id, EstudianteForm form);
    Estudiante actualizarEstado(Integer id, boolean activo);
    void eliminar(Integer id);
}
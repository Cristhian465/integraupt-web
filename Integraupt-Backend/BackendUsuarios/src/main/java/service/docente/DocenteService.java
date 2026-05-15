package service.docente;

import java.util.List;
import dto.DocenteForm;
import model.Docente;

public interface DocenteService {
    Docente registrar(DocenteForm form);
    List<Docente> listar();
    Docente obtener(Integer id);
    Docente actualizar(Integer id, DocenteForm form);
    Docente actualizarEstado(Integer id, boolean activo);
    void eliminar(Integer id);
}
package service.administrativo;

import java.util.List;
import dto.AdministrativoForm;
import model.Administrativo;

public interface AdministrativoService {
    Administrativo registrar(AdministrativoForm form);
    List<Administrativo> listar();
    Administrativo obtener(Integer id);
    Administrativo actualizar(Integer id, AdministrativoForm form);
    Administrativo actualizarEstado(Integer id, boolean activo);
    void eliminar(Integer id);
}
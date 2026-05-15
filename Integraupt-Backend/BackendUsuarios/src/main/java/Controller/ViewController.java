package Controller;

import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.GetMapping;

@Controller
public class ViewController {

    @GetMapping("/")
    public String index() {
        return "index";
    }

    @GetMapping("/ui/estudiantes")
    public String estudiantes() {
        return "crud-estudiante";
    }

    @GetMapping("/ui/docentes")
    public String docentes() {
        return "crud-docente";
    }

    @GetMapping("/ui/administrativos")
    public String administrativos() {
        return "crud-administrativo";
    }

}
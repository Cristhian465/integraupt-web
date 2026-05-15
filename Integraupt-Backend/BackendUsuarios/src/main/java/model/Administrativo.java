package model;

import jakarta.persistence.*;
import java.time.LocalDate;

@Entity
@Table(name = "administrativo")
public class Administrativo {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdAdministrativo")
    public Integer idAdministrativo;

    @OneToOne
    @JoinColumn(name = "IdUsuario", nullable = false, unique = true)
    public Usuario usuario;

    @ManyToOne
    @JoinColumn(name = "Escuela")
    public Escuela escuela;
    
    @Column(name = "Turno")
    public Turno turno;

    @Column(name = "Extension", length = 10)
    public String extension;

    @Column(name = "FechaIncorporacion", nullable = false)
    public LocalDate fechaIncorporacion;
}
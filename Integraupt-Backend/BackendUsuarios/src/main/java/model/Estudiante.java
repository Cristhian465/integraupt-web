package model;

import jakarta.persistence.*;

@Entity
@Table(name = "estudiante")
public class Estudiante {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdEstudiante")
    public Integer idEstudiante;

    @OneToOne
    @JoinColumn(name = "IdUsuario", nullable = false, unique = true)
    public Usuario usuario;

    @ManyToOne
    @JoinColumn(name = "Escuela", nullable = false)
    public Escuela escuela;

    @Column(name = "Codigo", nullable = false, unique = true, length = 20)
    public String codigo;
}
package model;

import jakarta.persistence.*;

@Entity
@Table(name = "escuela")
public class Escuela {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdEscuela")
    public Integer idEscuela;

    @ManyToOne
    @JoinColumn(name = "IdFacultad", nullable = false)
    public Facultad facultad;

    @Column(name = "Nombre", nullable = false, length = 50)
    public String nombre;
}
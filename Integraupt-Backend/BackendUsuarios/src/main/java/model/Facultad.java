package model;

import jakarta.persistence.*;

@Entity
@Table(name = "facultad")
public class Facultad {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdFacultad")
    public Integer idFacultad;

    @Column(name = "Nombre", nullable = false, unique = true, length = 50)
    public String nombre;

    @Column(name = "Abreviatura", unique = true, length = 10)
    public String abreviatura;
}
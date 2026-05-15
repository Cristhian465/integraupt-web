package model;

import jakarta.persistence.*;

@Entity
@Table(name = "tipodocumento")
public class TipoDocumento {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdTipoDoc")
    public Integer idTipoDoc;

    @Column(name = "Nombre", nullable = false, unique = true, length = 50)
    public String nombre;

    @Column(name = "Abreviatura", nullable = false, unique = true, length = 10)
    public String abreviatura;
}
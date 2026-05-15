package model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "usuario")
public class Usuario {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdUsuario")
    public Integer idUsuario;

    @Column(name = "Nombre", nullable = false, length = 30)
    public String nombre;

    @Column(name = "Apellido", nullable = false, length = 30)
    public String apellido;

    @ManyToOne
    @JoinColumn(name = "TipoDoc", nullable = false)
    public TipoDocumento tipoDoc;

    @Column(name = "NumDoc", nullable = false, unique = true, length = 20)
    public String numDoc;

    @ManyToOne
    @JoinColumn(name = "Rol", nullable = false)
    public Rol rol;

    @Column(name = "Celular", length = 11)
    public String celular;

    @Column(name = "Genero")
    public Boolean genero;

    @Column(name = "Estado", nullable = false)
    public Integer estado = 1;

    @Column(name = "FechaRegistro")
    public LocalDateTime fechaRegistro;

    @OneToOne(mappedBy = "usuario")
    @JsonIgnoreProperties(value = {"usuario", "ultimoLogin"}, allowSetters = true)
    public UsuarioAuth auth;
}
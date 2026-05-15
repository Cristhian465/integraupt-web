package model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "usuario_auth")
public class UsuarioAuth {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdAuth")
    public Integer idAuth;

    @OneToOne
    @JoinColumn(name = "IdUsuario", nullable = false, unique = true)
    @JsonIgnoreProperties("auth")
    public Usuario usuario;

    @Column(name = "CorreoU", nullable = false, unique = true, length = 30)
    public String correoU;

    @Column(name = "Password", nullable = false, length = 255)
    public String password;

    @Column(name = "UltimoLogin")
    public LocalDateTime ultimoLogin;

    // ELIMINA estas líneas porque no existen en la base de datos:
    // @Column(name = "TokenRecuperacion", length = 255)
    // public String tokenRecuperacion;

    // @Column(name = "TokenExpiracion")
    // public LocalDateTime tokenExpiracion;
}
package model;

import jakarta.persistence.*;
import java.time.LocalDate;

@Entity
@Table(name = "docente")
public class Docente {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdDocente")
    public Integer idDocente;

    @OneToOne(fetch = FetchType.EAGER)
    @JoinColumn(name = "IdUsuario", nullable = false, unique = true)
    public Usuario usuario;

    @Column(name = "CodigoDocente", nullable = false, unique = true, length = 20)
    public String codigoDocente;

    @ManyToOne(fetch = FetchType.EAGER)
    @JoinColumn(name = "Escuela")
    public Escuela escuela;

    @Column(name = "TipoContrato", nullable = false, length = 50)
    public TipoContrato tipoContrato;

    @Column(name = "Especialidad", length = 100)
    public String especialidad;

    @Column(name = "FechaIncorporacion", nullable = false)
    public LocalDate fechaIncorporacion;

    // Constructores
    public Docente() {}

    public Docente(Usuario usuario, String codigoDocente, Escuela escuela, 
                  TipoContrato tipoContrato, String especialidad, LocalDate fechaIncorporacion) {
        this.usuario = usuario;
        this.codigoDocente = codigoDocente;
        this.escuela = escuela;
        this.tipoContrato = tipoContrato;
        this.especialidad = especialidad;
        this.fechaIncorporacion = fechaIncorporacion;
    }

    // Getters y Setters
    public Integer getIdDocente() {
        return idDocente;
    }

    public void setIdDocente(Integer idDocente) {
        this.idDocente = idDocente;
    }

    public Usuario getUsuario() {
        return usuario;
    }

    public void setUsuario(Usuario usuario) {
        this.usuario = usuario;
    }

    public String getCodigoDocente() {
        return codigoDocente;
    }

    public void setCodigoDocente(String codigoDocente) {
        this.codigoDocente = codigoDocente;
    }

    public Escuela getEscuela() {
        return escuela;
    }

    public void setEscuela(Escuela escuela) {
        this.escuela = escuela;
    }

    public TipoContrato getTipoContrato() {
        return tipoContrato;
    }

    public void setTipoContrato(TipoContrato tipoContrato) {
        this.tipoContrato = tipoContrato;
    }

    public String getEspecialidad() {
        return especialidad;
    }

    public void setEspecialidad(String especialidad) {
        this.especialidad = especialidad;
    }

    public LocalDate getFechaIncorporacion() {
        return fechaIncorporacion;
    }

    public void setFechaIncorporacion(LocalDate fechaIncorporacion) {
        this.fechaIncorporacion = fechaIncorporacion;
    }

    @Override
    public String toString() {
        return "Docente{" +
                "idDocente=" + idDocente +
                ", codigoDocente='" + codigoDocente + '\'' +
                ", tipoContrato=" + tipoContrato +
                ", especialidad='" + especialidad + '\'' +
                ", fechaIncorporacion=" + fechaIncorporacion +
                '}';
    }
}
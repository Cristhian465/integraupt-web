package com.auditoria.modelo;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.LocalDateTime;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

@Getter
@Setter
@NoArgsConstructor
@Entity
@Table(name = "auditoriareserva")
public class AuditoriaReserva {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdAudit")
    private Integer idAudit;

    @Column(name = "IdReserva", nullable = false)
    private Integer idReserva;

    @Column(name = "EstadoAnterior", nullable = false)
    private String estadoAnterior;

    @Column(name = "EstadoNuevo", nullable = false)
    private String estadoNuevo;

    @Column(name = "FechaCambio", nullable = false, insertable = false, updatable = false)
    private LocalDateTime fechaCambio;

    @Column(name = "UsuarioCambio")
    private Integer usuarioCambio;
}

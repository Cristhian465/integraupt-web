package com.integraupt.qr.model;

import java.io.Serial;
import java.io.Serializable;
import java.time.Instant;

import com.integraupt.qr.dto.ReservaInfo;

    public class ReservaQrData implements Serializable {

        @Serial
        private static final long serialVersionUID = 1L;

        private final String token;
        private final ReservaInfo reserva;
        private final Instant generadoEn;

        public ReservaQrData(String token, ReservaInfo reserva, Instant generadoEn) {
            this.token = token;
            this.reserva = reserva;
            this.generadoEn = generadoEn;
        }

        public String getToken() {
            return token;
        }

        public ReservaInfo getReserva() {
            return reserva;
        }

        public Instant getGeneradoEn() {
            return generadoEn;
        }
    }
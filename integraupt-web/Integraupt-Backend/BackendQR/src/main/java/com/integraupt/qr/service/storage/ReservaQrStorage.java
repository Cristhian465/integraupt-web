package com.integraupt.qr.service.storage;

import java.util.Optional;

import com.integraupt.qr.model.ReservaQrData;

public interface ReservaQrStorage {

    void save(ReservaQrData data);

    Optional<ReservaQrData> findByToken(String token);

    Optional<ReservaQrData> findByReservaId(Long reservaId);
}
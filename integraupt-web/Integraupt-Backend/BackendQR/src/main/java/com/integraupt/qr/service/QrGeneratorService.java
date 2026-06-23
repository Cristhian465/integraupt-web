package com.integraupt.qr.service;

import java.io.ByteArrayOutputStream;
import java.nio.charset.StandardCharsets;
import java.util.Base64;

import org.springframework.stereotype.Component;

import com.google.zxing.BarcodeFormat;
import com.google.zxing.EncodeHintType;
import com.google.zxing.MultiFormatWriter;
import com.google.zxing.client.j2se.MatrixToImageWriter;
import com.google.zxing.common.BitMatrix;

import java.util.EnumMap;
import java.util.Map;

@Component
public class QrGeneratorService {

    private static final int DEFAULT_SIZE = 300;

    public String generarCodigo(String contenido) {
        try {
            Map<EncodeHintType, Object> hints = new EnumMap<>(EncodeHintType.class);
            hints.put(EncodeHintType.CHARACTER_SET, StandardCharsets.UTF_8.name());
            hints.put(EncodeHintType.MARGIN, 1);

            BitMatrix matrix = new MultiFormatWriter().encode(
                    contenido,
                    BarcodeFormat.QR_CODE,
                    DEFAULT_SIZE,
                    DEFAULT_SIZE,
                    hints);

            try (ByteArrayOutputStream stream = new ByteArrayOutputStream()) {
                MatrixToImageWriter.writeToStream(matrix, "PNG", stream);
                return Base64.getEncoder().encodeToString(stream.toByteArray());
            }
        } catch (Exception e) {
            throw new IllegalStateException("No fue posible generar el código QR", e);
        }
    }
}
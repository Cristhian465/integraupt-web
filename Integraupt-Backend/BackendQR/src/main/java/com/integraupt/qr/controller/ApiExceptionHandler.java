package com.integraupt.qr.controller;

import java.time.Instant;
import java.util.Collections;
import java.util.List;
import java.util.stream.Collectors;

import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.validation.FieldError;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.RestControllerAdvice;

import com.integraupt.qr.dto.ApiError;
import com.integraupt.qr.exception.ReservaQrNotFoundException;

@RestControllerAdvice
public class ApiExceptionHandler {

    @ExceptionHandler(ReservaQrNotFoundException.class)
    public ResponseEntity<ApiError> manejarNoEncontrado(ReservaQrNotFoundException ex) {
        ApiError error = new ApiError(
                Instant.now(),
                HttpStatus.NOT_FOUND.value(),
                HttpStatus.NOT_FOUND.getReasonPhrase(),
                Collections.singletonList(ex.getMessage()));
        return ResponseEntity.status(HttpStatus.NOT_FOUND).body(error);
    }

    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ResponseEntity<ApiError> manejarValidacion(MethodArgumentNotValidException ex) {
        List<String> mensajes = ex.getBindingResult()
                .getFieldErrors()
                .stream()
                .map(ApiExceptionHandler::formatearError)
                .collect(Collectors.toList());

        ApiError error = new ApiError(
                Instant.now(),
                HttpStatus.BAD_REQUEST.value(),
                HttpStatus.BAD_REQUEST.getReasonPhrase(),
                mensajes);
        return ResponseEntity.badRequest().body(error);
    }

    private static String formatearError(FieldError error) {
        return error.getField() + ": " + error.getDefaultMessage();
    }
}
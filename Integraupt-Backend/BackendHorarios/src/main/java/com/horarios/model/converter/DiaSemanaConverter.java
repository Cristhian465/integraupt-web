package com.horarios.model.converter;

import com.horarios.model.DiaSemana;
import jakarta.persistence.AttributeConverter;
import jakarta.persistence.Converter;

@Converter(autoApply = true)
public class DiaSemanaConverter implements AttributeConverter<DiaSemana, String> {

    @Override
    public String convertToDatabaseColumn(DiaSemana attribute) {
        return attribute != null ? attribute.getNombre() : null;
    }

    @Override
    public DiaSemana convertToEntityAttribute(String dbData) {
        return dbData != null ? DiaSemana.fromNombre(dbData) : null;
    }
}
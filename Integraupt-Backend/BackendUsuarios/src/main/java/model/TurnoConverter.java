package model;

import jakarta.persistence.AttributeConverter;
import jakarta.persistence.Converter;

@Converter(autoApply = true)
public class TurnoConverter implements AttributeConverter<Turno, String> {

    @Override
    public String convertToDatabaseColumn(Turno attribute) {
        if (attribute == null) {
            return null;
        }
        return attribute.getDescripcion();
    }

    @Override
    public Turno convertToEntityAttribute(String dbData) {
        if (dbData == null || dbData.trim().isEmpty()) {
            return null;
        }
        return Turno.fromDescripcion(dbData.trim());
    }
}
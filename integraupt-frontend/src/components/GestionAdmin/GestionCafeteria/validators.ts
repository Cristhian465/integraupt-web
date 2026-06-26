import type { ProductoFormValues } from "./types";

export const validateProductoValues = (values: ProductoFormValues): string[] => {
  const errors: string[] = [];

  if (!values.nombre.trim()) {
    errors.push("El nombre es obligatorio.");
  }

  const precio = Number(values.precio);
  if (!Number.isFinite(precio) || precio <= 0) {
    errors.push("El precio debe ser un numero mayor a cero.");
  }

  const stock = Number(values.stock);
  if (!Number.isFinite(stock) || stock < 0) {
    errors.push("El stock debe ser un numero igual o mayor a cero.");
  }

  return errors;
};

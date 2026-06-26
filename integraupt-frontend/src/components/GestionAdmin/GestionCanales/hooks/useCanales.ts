import { useCallback, useEffect, useState } from "react";
import {
  agregarMiembros,
  cambiarEstadoCanal,
  createCanal,
  fetchCanales,
  quitarMiembro,
  updateCanal
} from "../canalesService";
import type { Canal, CanalPayload, EstadoCanal } from "../types";

export const useCanales = (usuarioId: number | null) => {
  const [canales, setCanales] = useState<Canal[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadCanales = useCallback(async () => {
    if (usuarioId == null) return;

    setLoading(true);
    setError(null);
    try {
      const data = await fetchCanales(usuarioId, true);
      setCanales(data);
    } catch (loadError) {
      setError(loadError instanceof Error ? loadError.message : "No se pudieron cargar los canales.");
      setCanales([]);
    } finally {
      setLoading(false);
    }
  }, [usuarioId]);

  useEffect(() => {
    void loadCanales();
  }, [loadCanales]);

  const saveCanal = useCallback(
    async (payload: CanalPayload, editingId?: number) => {
      const result = editingId
        ? await updateCanal(editingId, payload.nombre, payload.descripcion, payload.color, payload.fotoUrl)
        : await createCanal(payload);
      await loadCanales();
      return result;
    },
    [loadCanales]
  );

  const cambiarEstado = useCallback(
    async (id: number, estado: EstadoCanal) => {
      const result = await cambiarEstadoCanal(id, estado);
      await loadCanales();
      return result;
    },
    [loadCanales]
  );

  const addMiembros = useCallback(
    async (id: number, miembros: number[]) => {
      const result = await agregarMiembros(id, miembros);
      await loadCanales();
      return result;
    },
    [loadCanales]
  );

  const removeMiembro = useCallback(
    async (id: number, idUsuario: number) => {
      await quitarMiembro(id, idUsuario);
      await loadCanales();
    },
    [loadCanales]
  );

  return { canales, loading, error, loadCanales, saveCanal, cambiarEstado, addMiembros, removeMiembro };
};

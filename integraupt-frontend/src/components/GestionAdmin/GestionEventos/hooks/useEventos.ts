import { useCallback, useEffect, useState } from "react";
import type { Evento, EventoPayload, EstadoEvento } from "../types";
import {
  cambiarEstadoEvento,
  createEvento,
  fetchEventos,
  updateEvento,
  type EventoFiltros
} from "../eventosService";

interface StatusState {
  loading: boolean;
  error: string | null;
}

export const useEventos = (filtros: EventoFiltros) => {
  const [eventos, setEventos] = useState<Evento[]>([]);
  const [{ loading, error }, setStatus] = useState<StatusState>({
    loading: false,
    error: null
  });

  const loadEventos = useCallback(async () => {
    setStatus((prev) => ({ ...prev, loading: true }));
    try {
      const data = await fetchEventos(filtros);
      setEventos(data);
      setStatus({ loading: false, error: null });
      return data;
    } catch (loadError) {
      const message =
        loadError instanceof Error ? loadError.message : "No se pudieron cargar los eventos.";
      setStatus({ loading: false, error: message });
      throw loadError;
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [JSON.stringify(filtros)]);

  useEffect(() => {
    void loadEventos();
  }, [loadEventos]);

  const saveEvento = useCallback(async (payload: EventoPayload, id?: number, imagen?: File | null) => {
    const result = id ? await updateEvento(id, payload, imagen) : await createEvento(payload, imagen);

    setEventos((prev) => {
      if (id) {
        return prev.map((evento) => (evento.id === id ? result : evento));
      }
      return [result, ...prev];
    });

    return result;
  }, []);

  const cambiarEstado = useCallback(async (id: number, estado: EstadoEvento) => {
    const result = await cambiarEstadoEvento(id, estado);
    setEventos((prev) => prev.map((evento) => (evento.id === id ? result : evento)));
    return result;
  }, []);

  return {
    eventos,
    loading,
    error,
    loadEventos,
    saveEvento,
    cambiarEstado
  };
};

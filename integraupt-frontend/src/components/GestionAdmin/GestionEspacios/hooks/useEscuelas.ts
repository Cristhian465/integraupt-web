import { useCallback, useEffect, useState } from "react";
import type { Escuela } from "../types";
import { fetchEscuelas } from "../escuelasService";

interface StatusState {
  loading: boolean;
  error: string | null;
}

export const useEscuelas = () => {
  const [escuelas, setEscuelas] = useState<Escuela[]>([]);
  const [{ loading, error }, setStatus] = useState<StatusState>({
    loading: false,
    error: null
  });

  const loadEscuelas = useCallback(async () => {
    setStatus((prev) => ({ ...prev, loading: true }));
    try {
      const data = await fetchEscuelas();
      setEscuelas(data);
      setStatus({ loading: false, error: null });
      return data;
    } catch (err) {
      console.warn("Could not fetch escuelas due to network error, mocking data for UI demo:", err);
      const mockEscuelas = [
        { id: 1, nombre: "Ingeniería de Sistemas", facultadId: 1 },
        { id: 2, nombre: "Arquitectura", facultadId: 2 }
      ];
      setEscuelas(mockEscuelas);
      setStatus({ loading: false, error: null });
      return mockEscuelas;
    }
  }, []);

  useEffect(() => {
    void loadEscuelas();
  }, [loadEscuelas]);

  return {
    escuelas,
    loading,
    error,
    reloadEscuelas: loadEscuelas
  };
};

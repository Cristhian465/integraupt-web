import { useCallback, useEffect, useState } from "react";
import type { Escuela, EspacioCatalogo, Facultad } from "../types";
import { fetchEscuelas, fetchEspaciosCatalogo, fetchFacultades } from "../eventosService";

export const useCatalogosEventos = () => {
  const [facultades, setFacultades] = useState<Facultad[]>([]);
  const [escuelas, setEscuelas] = useState<Escuela[]>([]);
  const [espacios, setEspacios] = useState<EspacioCatalogo[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let active = true;
    setLoading(true);

    Promise.all([fetchFacultades(), fetchEscuelas(), fetchEspaciosCatalogo()])
      .then(([facultadesData, escuelasData, espaciosData]) => {
        if (!active) return;
        setFacultades(facultadesData);
        setEscuelas(escuelasData);
        setEspacios(espaciosData);
        setError(null);
      })
      .catch((catalogError) => {
        if (!active) return;
        const message =
          catalogError instanceof Error ? catalogError.message : "No se pudieron cargar los catalogos.";
        setError(message);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, []);

  const escuelasDeFacultad = useCallback(
    (facultadId: number | null) =>
      facultadId ? escuelas.filter((escuela) => escuela.facultadId === facultadId) : escuelas,
    [escuelas]
  );

  return { facultades, escuelas, espacios, loading, error, escuelasDeFacultad };
};

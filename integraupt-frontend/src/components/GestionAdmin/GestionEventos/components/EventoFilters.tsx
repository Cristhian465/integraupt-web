import React from "react";
import { RefreshCw, Search } from "lucide-react";
import type { Escuela, Facultad } from "../types";

interface EventoFiltersProps {
  search: string;
  facultadId: string;
  escuelaId: string;
  tipoEvento: string;
  estado: string;
  facultades: Facultad[];
  escuelas: Escuela[];
  onSearchChange: (value: string) => void;
  onFacultadChange: (value: string) => void;
  onEscuelaChange: (value: string) => void;
  onTipoChange: (value: string) => void;
  onEstadoChange: (value: string) => void;
  onReload: () => void;
  total: number;
  filtered: number;
  loading: boolean;
}

export const EventoFilters: React.FC<EventoFiltersProps> = ({
  search,
  facultadId,
  escuelaId,
  tipoEvento,
  estado,
  facultades,
  escuelas,
  onSearchChange,
  onFacultadChange,
  onEscuelaChange,
  onTipoChange,
  onEstadoChange,
  onReload,
  total,
  filtered,
  loading
}) => {
  const escuelasFiltradas = facultadId
    ? escuelas.filter((escuela) => `${escuela.facultadId}` === facultadId)
    : escuelas;

  return (
    <div className="evento-filters">
      <div className="evento-filters-left">
        <div className="evento-search-box">
          <Search size={16} />
          <input
            type="text"
            maxLength={50}
            placeholder="Buscar por titulo"
            value={search}
            onChange={(event) => onSearchChange(event.target.value)}
          />
        </div>

        <select
          value={facultadId}
          onChange={(event) => {
            onFacultadChange(event.target.value);
            onEscuelaChange("");
          }}
        >
          <option value="">Todas las facultades</option>
          {facultades.map((facultad) => (
            <option key={facultad.id} value={`${facultad.id}`}>
              {facultad.nombre}
            </option>
          ))}
        </select>

        <select value={escuelaId} onChange={(event) => onEscuelaChange(event.target.value)}>
          <option value="">Todas las escuelas</option>
          {escuelasFiltradas.map((escuela) => (
            <option key={escuela.id} value={`${escuela.id}`}>
              {escuela.nombre}
            </option>
          ))}
        </select>

        <select value={tipoEvento} onChange={(event) => onTipoChange(event.target.value)}>
          <option value="">Todos los tipos</option>
          <option value="charla">Charla</option>
          <option value="taller">Taller</option>
          <option value="cultural">Cultural</option>
          <option value="academico">Academico</option>
        </select>

        <select value={estado} onChange={(event) => onEstadoChange(event.target.value)}>
          <option value="">Todos los estados</option>
          <option value="borrador">Borrador</option>
          <option value="publicado">Publicado</option>
          <option value="en_curso">En curso</option>
          <option value="finalizado">Finalizado</option>
          <option value="cancelado">Cancelado</option>
        </select>
      </div>

      <div className="evento-filters-right">
        <p className="evento-filters-count">
          {filtered} de {total} eventos
        </p>
        <button type="button" onClick={onReload} disabled={loading} className="evento-refresh-button">
          <RefreshCw size={16} />
          {loading ? "Actualizando..." : "Sincronizar"}
        </button>
      </div>
    </div>
  );
};

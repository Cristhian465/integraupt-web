import React, { useEffect, useState } from "react";
import { Search, UserPlus, X } from "lucide-react";
import { buscarUsuarios } from "../services/canalesService";
import type { UsuarioBusqueda } from "../types";

interface UsuarioPickerProps {
  selected: UsuarioBusqueda[];
  onAdd: (usuario: UsuarioBusqueda) => void;
  onRemove: (idUsuario: number) => void;
}

export const UsuarioPicker: React.FC<UsuarioPickerProps> = ({ selected, onAdd, onRemove }) => {
  const [query, setQuery] = useState("");
  const [resultados, setResultados] = useState<UsuarioBusqueda[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const term = query.trim();
    if (term.length < 2) {
      setResultados([]);
      return;
    }

    const timeout = setTimeout(() => {
      setLoading(true);
      buscarUsuarios(term)
        .then(setResultados)
        .catch(() => setResultados([]))
        .finally(() => setLoading(false));
    }, 300);

    return () => clearTimeout(timeout);
  }, [query]);

  const selectedIds = new Set(selected.map((u) => u.id));

  return (
    <div className="canal-usuario-picker">
      <div className="canal-search-box">
        <Search size={16} />
        <input
          type="text"
          placeholder="Buscar por nombre o documento..."
          value={query}
          onChange={(event) => setQuery(event.target.value)}
        />
      </div>

      {loading && <p className="canal-usuario-picker-hint">Buscando...</p>}

      {resultados.length > 0 && (
        <ul className="canal-usuario-picker-results">
          {resultados.map((usuario) => (
            <li key={usuario.id}>
              <span>
                {usuario.nombre} {usuario.esAdmin ? "(Admin)" : usuario.esDocente ? "(Docente)" : "(Estudiante)"}
              </span>
              <button
                type="button"
                disabled={selectedIds.has(usuario.id)}
                onClick={() => onAdd(usuario)}
                title="Agregar"
              >
                <UserPlus size={14} />
              </button>
            </li>
          ))}
        </ul>
      )}

      {selected.length > 0 && (
        <div className="canal-usuario-picker-selected">
          {selected.map((usuario) => (
            <span key={usuario.id} className="canal-chip">
              {usuario.nombre}
              <button type="button" onClick={() => onRemove(usuario.id)} aria-label={`Quitar ${usuario.nombre}`}>
                <X size={12} />
              </button>
            </span>
          ))}
        </div>
      )}
    </div>
  );
};

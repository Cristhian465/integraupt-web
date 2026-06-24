import React, { useState, useEffect } from 'react';
import { X, Plus, Trash2, Calculator } from 'lucide-react';
import '../../../../styles/PromedioScreen.css';

interface Disciplina {
  id: string;
  nombre: string;
  nota: string;
  porcentaje: string;
}

interface DisciplinasModalProps {
  unidadIndex: number;
  notaObjetivo: number | null;
  isOpen: boolean;
  onClose: () => void;
  onApply: (notaCalculada: number) => void;
}

const generateId = () => Math.random().toString(36).substr(2, 9);

export const DisciplinasModal: React.FC<DisciplinasModalProps> = ({
  unidadIndex,
  notaObjetivo,
  isOpen,
  onClose,
  onApply
}) => {
  const [disciplinas, setDisciplinas] = useState<Disciplina[]>([
    { id: generateId(), nombre: 'Evaluación 1', nota: '', porcentaje: '50' },
    { id: generateId(), nombre: 'Evaluación 2', nota: '', porcentaje: '50' }
  ]);
  const [notaFinal, setNotaFinal] = useState<number | null>(null);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);

  const updateDisciplina = (id: string, field: keyof Disciplina, value: string) => {
    if (field === 'nota' && value !== '') {
      const num = parseFloat(value);
      if (isNaN(num) || num < 0 || num > 20) return;
    }
    if (field === 'porcentaje' && value !== '') {
      const num = parseFloat(value);
      if (isNaN(num) || num < 0 || num > 100) return;
    }
    
    setDisciplinas(prev =>
      prev.map(d => (d.id === id ? { ...d, [field]: value } : d))
    );
  };

  const addDisciplina = () => {
    if (disciplinas.length >= 10) return;
    const newId = generateId();
    setDisciplinas(prev => [
      ...prev,
      { id: newId, nombre: `Evaluación ${prev.length + 1}`, nota: '', porcentaje: '' }
    ]);
  };

  const removeDisciplina = (id: string) => {
    if (disciplinas.length <= 1) return;
    setDisciplinas(prev => prev.filter(d => d.id !== id));
  };

  // Calcular promedio automáticamente cuando cambian las disciplinas
  useEffect(() => {
    setErrorMsg(null);

    const porcentajesVacios = disciplinas.some(d => d.porcentaje.trim() === '');
    if (porcentajesVacios) {
      setNotaFinal(null);
      return;
    }

    let suma = 0;
    let sumaPorcentajes = 0;

    for (const d of disciplinas) {
      const valNota = d.nota === '' ? 0 : parseFloat(d.nota);
      const valPorcentaje = parseFloat(d.porcentaje);
      if (isNaN(valPorcentaje)) {
        setNotaFinal(null);
        return;
      }
      suma += (valNota * valPorcentaje) / 100;
      sumaPorcentajes += valPorcentaje;
    }

    if (Math.abs(sumaPorcentajes - 100) > 0.5) {
      setNotaFinal(null);
      setErrorMsg(`Los porcentajes suman ${sumaPorcentajes.toFixed(1)}%. Deben sumar 100%.`);
    } else {
      setNotaFinal(parseFloat(suma.toFixed(2)));
    }
  }, [disciplinas]);

  if (!isOpen) return null;

  const handleApply = () => {
    if (notaFinal === null) return;

    // Validar porcentajes suman 100
    const sumaPorcentajes = disciplinas.reduce((sum, d) => sum + (parseFloat(d.porcentaje) || 0), 0);
    if (Math.abs(sumaPorcentajes - 100) > 0.5) {
      setErrorMsg(`Los porcentajes deben sumar 100%. Actualmente: ${sumaPorcentajes.toFixed(1)}%.`);
      return;
    }

    onApply(notaFinal);
  };

  return (
    <div className="modal-overlay" onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}>
      <div className="modal-content disciplinas-modal">
        <div className="modal-header">
          <h3 className="modal-title">Detalle - Unidad {unidadIndex + 1}</h3>
          <button className="modal-close-btn" onClick={onClose}><X size={24} /></button>
        </div>

        <div className="modal-body">
          <p className="modal-description">Calcula tu nota exacta promediando las evaluaciones o disciplinas de esta unidad.</p>
          
          {notaObjetivo !== null && (
            <div className="disciplinas-meta-indicator">
              <span className="meta-label">Meta de esta unidad:</span>
              <span className="meta-value">{notaObjetivo}</span>
            </div>
          )}

          <div className="disciplinas-list">
            {disciplinas.map((d) => (
              <div key={d.id} className="disciplina-item">
                <input
                  type="text"
                  placeholder="Nombre"
                  value={d.nombre}
                  onChange={(e) => updateDisciplina(d.id, 'nombre', e.target.value)}
                  className="disciplina-input-name"
                />
                <input
                  type="number"
                  placeholder="Nota"
                  step="0.01"
                  min="0"
                  max="20"
                  value={d.nota}
                  onChange={(e) => updateDisciplina(d.id, 'nota', e.target.value)}
                  className="disciplina-input-val"
                />
                <div className="disciplina-input-peso-wrapper">
                  <input
                    type="number"
                    placeholder="Peso"
                    step="1"
                    min="0"
                    max="100"
                    value={d.porcentaje}
                    onChange={(e) => updateDisciplina(d.id, 'porcentaje', e.target.value)}
                    className="disciplina-input-val disciplina-input-peso"
                  />
                  <span className="disciplina-suffix">%</span>
                </div>
                <button
                  onClick={() => removeDisciplina(d.id)}
                  disabled={disciplinas.length <= 1}
                  className="promedio-remove-btn disciplina-remove-btn"
                >
                  <Trash2 size={18} />
                </button>
              </div>
            ))}
          </div>

          <button onClick={addDisciplina} disabled={disciplinas.length >= 10} className="promedio-add-btn disciplina-add-btn">
            <Plus size={16} /> Agregar
          </button>

          {errorMsg && (
            <div className="disciplinas-error-msg">
              {errorMsg}
            </div>
          )}

          <div className="disciplinas-footer">
            <div className="disciplinas-resultado">
              <span className="resultado-label">PROMEDIO:</span>
              <span className="resultado-valor">{notaFinal !== null ? notaFinal : '--'}</span>
              {notaObjetivo !== null && notaFinal !== null && (
                <span className={`resultado-match ${Math.abs(notaFinal - notaObjetivo) < 0.01 ? 'match-exact' : 'match-diff'}`}>
                  {Math.abs(notaFinal - notaObjetivo) < 0.01 
                    ? '✓ Coincide con la meta' 
                    : `Diferencia: ${(notaFinal - notaObjetivo).toFixed(2)}`}
                </span>
              )}
            </div>
            <button 
              className="promedio-calc-btn disciplinas-apply-btn" 
              disabled={notaFinal === null}
              onClick={handleApply}
            >
              <Calculator size={18} /> Aplicar
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

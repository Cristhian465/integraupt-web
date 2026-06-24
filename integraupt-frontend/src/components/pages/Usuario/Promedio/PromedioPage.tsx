import React, { useState, useMemo } from 'react';
import { Calculator, Plus, Trash2, ChevronRight, FileText } from 'lucide-react';
import { Navbar } from '../Navbar';
import { getPromedioApiUrl } from '../../../../utils/apiConfig';
import { DisciplinasModal } from './DisciplinasModal';

import '../../../../styles/PromedioScreen.css';

interface PromedioPageProps {
  user: {
    id: string;
    email: string;
    user_metadata: {
      name: string;
      codigo?: string;
    };
  };
  onNavigateToInicio: () => void;
  onNavigateToServicios: () => void;
  onNavigateToPerfil: () => void;
  onLogout?: () => void;
  isLoggingOut?: boolean;
}

interface Unidad {
  id: string;
  nota: string;
  porcentaje: string;
}

interface BackendResponse {
  acumulado: number;
  porcentajeFaltante: number;
  notaNecesaria: number;
  posible: boolean;
  aprobado: boolean;
  mensaje: string;
}

const generateId = () => Math.random().toString(36).substr(2, 9);

export const PromedioPage: React.FC<PromedioPageProps> = ({
  user,
  onNavigateToInicio,
  onNavigateToServicios,
  onNavigateToPerfil,
  onLogout,
  isLoggingOut = false,
}) => {
  const [unidades, setUnidades] = useState<Unidad[]>([
    { id: generateId(), nota: '', porcentaje: '50' },
    { id: generateId(), nota: '', porcentaje: '50' }
  ]);
  const [resultado, setResultado] = useState<BackendResponse | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [activeModalUnitId, setActiveModalUnitId] = useState<string | null>(null);

  const displayName = useMemo(() => {
    return user.user_metadata.name?.trim() || user.user_metadata.codigo || 'Usuario';
  }, [user.user_metadata]);

  const addUnidad = () => {
    if (unidades.length >= 5) return;
    const newId = generateId();
    setUnidades(prev => [...prev, { id: newId, nota: '', porcentaje: '' }]);
  };

  const removeUnidad = (id: string) => {
    if (unidades.length <= 1) return;
    setUnidades(prev => prev.filter(u => u.id !== id));
  };

  const updateUnidad = (id: string, field: keyof Unidad, value: string) => {
    if (field === 'nota' && value !== '') {
      const num = parseFloat(value);
      if (isNaN(num) || num < 0 || num > 20) return;
    }
    if (field === 'porcentaje' && value !== '') {
      const num = parseFloat(value);
      if (isNaN(num) || num < 0 || num > 100) return;
    }
    setUnidades(prev =>
      prev.map(u => (u.id === id ? { ...u, [field]: value } : u))
    );
  };

  const handleCalcular = async () => {
    setError(null);
    
    // Validar que los porcentajes no estén vacíos
    const porcentajesVacios = unidades.some(u => u.porcentaje.trim() === '');
    if (porcentajesVacios) {
      setError('Todos los porcentajes deben tener un valor asignado.');
      return;
    }

    // Validar suma de porcentajes
    const sumaPorcentajes = unidades.reduce((sum, u) => sum + (parseFloat(u.porcentaje) || 0), 0);
    if (Math.abs(sumaPorcentajes - 100) > 0.01) {
      setError(`La suma de los porcentajes debe ser exactamente 100%. Actualmente es ${sumaPorcentajes.toFixed(2)}%.`);
      return;
    }

    const payload = unidades.map(u => ({
      nota: u.nota.trim() !== '' ? parseFloat(u.nota) : null,
      porcentaje: parseFloat(u.porcentaje)
    }));

    setIsLoading(true);
    try {
      const response = await fetch(getPromedioApiUrl('/api/promedio/calcular'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ unidades: payload }),
      });

      if (!response.ok) throw new Error('Error al conectar con el servidor.');

      const data = await response.json();
      setResultado(data);
    } catch (err: any) {
      setError(err.message || 'Error al calcular.');
    } finally {
      setIsLoading(false);
    }
  };

  // Obtener la nota objetivo para el modal: si la unidad ya tiene nota, esa es la meta
  const getNotaObjetivo = (unitId: string): number | null => {
    const unidad = unidades.find(u => u.id === unitId);
    if (unidad && unidad.nota.trim() !== '') {
      return parseFloat(unidad.nota);
    }
    // Si el backend devolvió notaNecesaria y la unidad no tiene nota
    if (resultado && unidad && unidad.nota.trim() === '' && resultado.notaNecesaria > 0) {
      return resultado.notaNecesaria;
    }
    return null;
  };

  return (
    <div className="servicios-container">
      <Navbar
        displayName={displayName}
        userCode={user.user_metadata.codigo}
        currentPage="servicios"
        onNavigateToInicio={onNavigateToInicio}
        onNavigateToServicios={onNavigateToServicios}
        onNavigateToPerfil={onNavigateToPerfil}
        onLogout={onLogout}
        isLoggingOut={isLoggingOut}
      />

      <main className="servicios-main">
        <section className="home-welcome-card" style={{ background: 'linear-gradient(135deg, #ec4899 0%, #be185d 100%)', boxShadow: '0 25px 45px -30px rgba(236, 72, 153, 0.9)' }}>
          <div>
            <p className="home-welcome-date text-white opacity-80" style={{ color: 'white', opacity: 0.8 }}>Gestión Académica</p>
            <h1 className="home-title text-white" style={{ color: 'white' }}>Calcula tu Promedio</h1>
            <p className="home-subtitle text-white opacity-90" style={{ color: 'white', opacity: 0.9 }}>
              Ingresa tus unidades, asigna sus porcentajes y descubre qué nota necesitas para aprobar.
            </p>
          </div>
          <div className="home-welcome-avatar" style={{ border: '1px solid rgba(255,255,255,0.3)', background: 'rgba(255,255,255,0.2)' }}>
            <Calculator size={44} color="white" />
          </div>
        </section>

        <div className="promedio-section">
          <div className="promedio-form-container">
            <div className="promedio-header-flex">
            <h2 className="promedio-title-section">Unidades del Curso</h2>
            <button
              onClick={addUnidad}
              disabled={unidades.length >= 5}
              className="promedio-add-btn"
            >
              <Plus size={18} /> Agregar Unidad
            </button>
          </div>

          <div className="promedio-unit-list">
            {unidades.map((unidad, index) => (
              <div key={unidad.id} className="promedio-unit-item">
                <div className="promedio-input-group">
                  <label className="promedio-label">Unidad {index + 1}</label>
                  <div className="promedio-input-wrapper">
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      max="20"
                      placeholder="Ej. 14.5"
                      value={unidad.nota}
                      onChange={(e) => updateUnidad(unidad.id, 'nota', e.target.value)}
                      className="promedio-input"
                    />
                    <span className="promedio-input-suffix">Nota</span>
                  </div>
                  {resultado && unidad.nota === '' && resultado.notaNecesaria > 0 && (
                    <div className="promedio-nota-requerida" style={{ fontSize: '11px', color: '#d97706', marginTop: '4px', fontWeight: 600 }}>
                      Necesitas: {resultado.notaNecesaria}
                    </div>
                  )}
                </div>
                
                <div className="promedio-input-group">
                  <label className="promedio-label" style={{ color: 'transparent' }}>%</label>
                  <div className="promedio-input-wrapper">
                    <input
                      type="number"
                      step="1"
                      min="0"
                      max="100"
                      placeholder="Ej. 30"
                      value={unidad.porcentaje}
                      onChange={(e) => updateUnidad(unidad.id, 'porcentaje', e.target.value)}
                      className="promedio-input"
                    />
                    <span className="promedio-input-suffix">% Peso</span>
                  </div>
                </div>

                <div style={{ marginLeft: 'auto', display: 'flex', gap: '8px' }}>
                  <button
                    onClick={() => setActiveModalUnitId(unidad.id)}
                    className="promedio-details-btn"
                    title="Calcular disciplinas"
                    style={{ background: '#fef3c7', color: '#d97706', border: '1px solid #fde68a', padding: '10px', borderRadius: '12px', cursor: 'pointer', transition: 'all 0.2s', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                  >
                    <FileText size={22} />
                  </button>
                  <button
                    onClick={() => removeUnidad(unidad.id)}
                    disabled={unidades.length <= 1}
                    className="promedio-remove-btn"
                    title="Eliminar unidad"
                  >
                    <Trash2 size={22} />
                  </button>
                </div>
              </div>
            ))}
          </div>

          {error && (
            <div className="promedio-error-msg">
              {error}
            </div>
          )}

          <div className="promedio-action-row">
            <button
              onClick={handleCalcular}
              disabled={isLoading}
              className="promedio-calc-btn"
            >
              {isLoading ? 'Calculando...' : 'Calcular Promedio'} <ChevronRight size={22} />
            </button>
          </div>
        </div>

        {/* Panel de resultados - SIEMPRE visible */}
        <div className="promedio-results-container">
          <div className="promedio-results-grid">
            <div className={`promedio-result-card ${!resultado ? 'promedio-result-placeholder' : ''}`}>
              <p className="promedio-result-label">Promedio Acumulado</p>
              <p className="promedio-result-value">{resultado ? resultado.acumulado : '--'}</p>
              <p className="promedio-result-subtext">de un total de 20</p>
            </div>

            <div className={`promedio-result-card ${
              !resultado 
                ? 'promedio-result-placeholder' 
                : resultado.posible && !resultado.aprobado 
                  ? 'highlight-warning' 
                  : resultado.aprobado 
                    ? 'highlight-success' 
                    : 'highlight-danger'
            }`}>
              <p className="promedio-result-label">
                {resultado 
                  ? (resultado.aprobado ? '¡Aprobado!' : 'Nota Necesaria') 
                  : 'Nota Necesaria'}
              </p>
              <p className="promedio-result-value">
                {resultado 
                  ? (resultado.aprobado ? '✓' : resultado.notaNecesaria) 
                  : '--'}
              </p>
              <p className="promedio-result-subtext">
                {resultado 
                  ? (resultado.aprobado ? 'Ya pasaste el curso' : `en el ${resultado.porcentajeFaltante}% restante`) 
                  : 'Calcula para ver resultado'}
              </p>
            </div>

            <div className={`promedio-result-card ${!resultado ? 'promedio-result-placeholder' : ''}`}>
              <p className="promedio-result-label">Estado</p>
              <p className="promedio-status-text">{resultado ? resultado.mensaje : 'Sin calcular aún'}</p>
            </div>
          </div>
        </div>
        
        {activeModalUnitId && (
          <DisciplinasModal
            unidadIndex={unidades.findIndex(u => u.id === activeModalUnitId)}
            notaObjetivo={getNotaObjetivo(activeModalUnitId)}
            isOpen={true}
            onClose={() => setActiveModalUnitId(null)}
            onApply={(notaCalculada) => {
              updateUnidad(activeModalUnitId, 'nota', notaCalculada.toString());
              setActiveModalUnitId(null);
            }}
          />
        )}
        </div>
      </main>
    </div>
  );
};

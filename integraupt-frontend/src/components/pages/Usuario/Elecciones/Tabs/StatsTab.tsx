import React, { useEffect, useState } from 'react';
import { BarChart3, Loader2, AlertTriangle, Users, PieChart } from 'lucide-react';
import { getEleccionesApiUrl } from '../../../../../utils/apiConfig';

interface StatsTabProps {
  electionId: number;
}

export const StatsTab: React.FC<StatsTabProps> = ({ electionId }) => {
  const [stats, setStats] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const response = await fetch(getEleccionesApiUrl(`/api/elecciones/${electionId}/resultados`));
        if (!response.ok) throw new Error('Error al cargar estadísticas');
        const data = await response.json();
        setStats(data);
      } catch (err: any) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, [electionId]);

  if (loading) {
    return (
      <div className="stats-loading">
        <Loader2 className="spinner icon-mr loading-icon" size={32} />
        <p>Cargando resultados en tiempo real...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="stats-error">
        <AlertTriangle size={20} className="icon-mr" />
        {error}
      </div>
    );
  }

  const renderPieChart = (data: any[]) => {
    let cumulativePercent = 0;
    const sorted = [...data].sort((a, b) => b.votes - a.votes);
    const totalVotes = sorted.reduce((sum, item) => sum + item.votes, 0);
    
    if (totalVotes === 0) {
      return (
        <svg viewBox="0 0 32 32" className="pie-chart-svg empty-pie"></svg>
      );
    }

    return (
      <svg viewBox="0 0 32 32" className="pie-chart-svg">
        {sorted.map(party => {
          const sliceValue = party.percentage;
          if (sliceValue === 0) return null;
          
          const strokeDasharray = `${sliceValue} 100`;
          const strokeDashoffset = -cumulativePercent;
          cumulativePercent += sliceValue;
          
          return (
            <circle
              key={party.id === null ? 'blanco' : party.id}
              r="16"
              cx="16"
              cy="16"
              fill="transparent"
              stroke={party.color}
              strokeWidth="32"
              strokeDasharray={strokeDasharray}
              strokeDashoffset={strokeDashoffset}
              className="pie-slice"
            />
          );
        })}
      </svg>
    );
  };

  const renderCargoStats = (title: string, data: any[]) => {
    const sorted = [...data].sort((a, b) => b.votes - a.votes);
    
    return (
      <div className="stats-cargo-card">
        <div className="stats-cargo-left">
          <h4 className="stats-cargo-title">{title}</h4>
          <div className="stats-bars">
            {sorted.map(party => (
              <div key={party.id === null ? 'blanco' : party.id} className="stat-row">
                <div className="stat-label">
                  <span className="party-dot shadow-sm" style={{ backgroundColor: party.color }}></span>
                  <span className="party-name">{party.name}</span>
                  <span className="party-votes">{party.votes} votos</span>
                </div>
                <div className="stat-bar-bg shadow-inner">
                  <div 
                    className="stat-bar-fill" 
                    style={{ width: `${party.percentage}%`, backgroundColor: party.color }}
                  ></div>
                </div>
                <span className="stat-percent">{party.percentage}%</span>
              </div>
            ))}
          </div>
        </div>
        
        <div className="stats-cargo-right">
          <h5 className="pie-title"><PieChart size={18} className="icon-mr" /> Distribución de Votos</h5>
          <div className="pie-container">
            {renderPieChart(sorted)}
          </div>
        </div>
      </div>
    );
  };

  return (
    <div className="stats-tab">
      <div className="stats-intro">
        <h3><BarChart3 size={24} className="icon-mr text-blue" /> Resultados Electorales en Vivo</h3>
        <p className="stats-total-box">
          Total de votos escrutados: <strong>{stats.total_votes}</strong> <Users size={16} className="icon-mr-left" />
        </p>
      </div>

      <div className="stats-grid">
        {renderCargoStats('Asamblea Universitaria', stats.results.asamblea)}
        {renderCargoStats('Consejo Universitario', stats.results.consejo_uni)}
        {renderCargoStats('Consejos de Facultad', stats.results.consejo_fac)}
      </div>
    </div>
  );
};

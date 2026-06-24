import React, { useState, useEffect } from 'react';
import { ArrowLeft, Bus } from 'lucide-react';
import { Navbar } from '../Navbar';
import '../../../../styles/BurraScreen.css';
import { MapRoute } from './MapRoute';

interface BurraPageProps {
  user: {
    id: string;
    email: string;
    user_metadata: {
      name: string;
      avatar_url: string;
      codigo?: string;
    };
  };
  onNavigateToInicio: () => void;
  onNavigateToServicios: () => void;
  onNavigateToPerfil: () => void;
  onLogout?: () => void;
  isLoggingOut?: boolean;
}

const SCHEDULES = [
  { hours: 8, minutes: 30 },
  { hours: 9, minutes: 10 },
  { hours: 21, minutes: 40 },
];

export const BurraPage: React.FC<BurraPageProps> = ({
  user,
  onNavigateToInicio,
  onNavigateToServicios,
  onNavigateToPerfil,
  onLogout,
  isLoggingOut = false
}) => {
  const displayName = user.user_metadata.name?.trim() || user.user_metadata.codigo?.trim() || 'Usuario';
  const [activeTab, setActiveTab] = useState<'A' | 'B'>('A');
  const [busStatus, setBusStatus] = useState<Record<string, string>>({});

  useEffect(() => {
    const checkStatus = () => {
      const now = new Date();
      const currentH = now.getHours();
      const currentM = now.getMinutes();
      const currentTotalMins = currentH * 60 + currentM;

      const newStatus: Record<string, string> = {};

      SCHEDULES.forEach((sched, index) => {
        const schedTotalMins = sched.hours * 60 + sched.minutes;
        const diff = currentTotalMins - schedTotalMins;

        let statusText = 'Fuera de servicio';
        if (diff >= -10 && diff < 0) {
          statusText = 'En curso';
        } else if (diff === 0) {
          statusText = 'En espera';
        } else if (diff > 0 && diff <= 10) {
          statusText = 'En ruta';
        } else if (diff < -10 && diff >= -60) {
          statusText = 'Próximo';
        }

        const timeStr = `${sched.hours.toString().padStart(2, '0')}:${sched.minutes.toString().padStart(2, '0')}`;
        newStatus[timeStr] = statusText;
      });

      setBusStatus(newStatus);
    };

    checkStatus();
    const interval = setInterval(checkStatus, 10000); // Check every 10 seconds
    return () => clearInterval(interval);
  }, []);

  return (
    <div className="burra-container">
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
      <main className="burra-main">
        <div className="burra-header-section">
          <button
            type="button"
            className="back-button"
            onClick={onNavigateToServicios}
          >
            <ArrowLeft size={20} />
            Volver a Servicios
          </button>
          <h1 className="burra-title">Burra UPT - Rutas y Horarios</h1>
          <p className="burra-subtitle">Monitorea el estado y la ubicación de los buses universitarios.</p>
        </div>

        <div className="burra-tabs">
          <button 
            className={`burra-tab ${activeTab === 'A' ? 'active' : ''}`}
            onClick={() => setActiveTab('A')}
          >
            Ruta A (Z6B 954)
          </button>
          <button 
            className={`burra-tab ${activeTab === 'B' ? 'active' : ''}`}
            onClick={() => setActiveTab('B')}
          >
            Ruta B (UK 3013)
          </button>
        </div>

        <div className="burra-content">
          <div className="burra-info-panel">
            <div className="bus-card">
              <div className="bus-icon-wrapper">
                <Bus size={40} color="white" />
              </div>
              <div className="bus-details">
                <h3>{activeTab === 'B' ? 'BUS RUTA B' : 'BUS RUTA A'}</h3>
                <div className="placa">Placa: {activeTab === 'B' ? 'UK 3013' : 'Z6B 954'}</div>
              </div>
            </div>

            <div className="recorrido-info">
              <h4>Recorrido</h4>
              <p>
                {activeTab === 'B' 
                  ? 'Ida: UPT, Óvalo Pocollay, Av. Jorge Chávez, Av. Leguía, Óvalo Túpac Amaru, Av. Jorge Basadre Oeste, Av. Luis Basadre F., Óvalo Callao, Av. Grau, Av. Cuzco, Óvalo Cusco.'
                  : 'Ida: UPT por Av. Basadre, Jorge Basadre G., Av. Basadre Forero, Calle Alto de Lima, Av. San Martín, Plaza Zela (Fin).'}
              </p>
              <h4>Retorno</h4>
              <p>
                {activeTab === 'B'
                  ? 'Av. Jorge Basadre, UPT.'
                  : 'Calle General Vizquerra, Av. Leguía, Calle Cajamarca, Av. Jorge Basadre, UPT.'}
              </p>
            </div>

            <div className="horarios-lista">
              <h4>Horarios y Estado en Vivo</h4>
              {SCHEDULES.map((sched) => {
                const timeStr = `${sched.hours.toString().padStart(2, '0')}:${sched.minutes.toString().padStart(2, '0')}`;
                const status = busStatus[timeStr] || 'Fuera de servicio';
                
                let statusClass = 'status-inactive';
                if (status === 'En curso') statusClass = 'status-curso';
                else if (status === 'En espera') statusClass = 'status-espera';
                else if (status === 'En ruta') statusClass = 'status-ruta';

                return (
                  <div key={timeStr} className="horario-item">
                    <span className="horario-time">{timeStr} hrs</span>
                    <span className={`horario-status ${statusClass}`}>{status}</span>
                  </div>
                );
              })}
            </div>
          </div>

          <div className="burra-map-panel">
            <MapRoute routeType={activeTab} />
          </div>
        </div>
      </main>
    </div>
  );
};

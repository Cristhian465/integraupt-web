import { type KeyboardEvent, useCallback, useMemo } from 'react';
import {
  AlertTriangle,
  ArrowLeft,
  MonitorSmartphone,
  Home,
  LayoutGrid,
  NotebookPen,
  UserRound,
  LogOut,
  LibraryBig,
  Brain,
  Bus,
  Trophy,
  HeartPulse,
  Calculator
} from 'lucide-react';
import '../../../../styles/ServiciosScreen.css';
import { Navbar } from '../Navbar';

interface ServiciosPageProps {
  user: {
    id: string;
    email: string;
    user_metadata: {
      name: string;
      avatar_url: string;
      role?: string;
      login_type?: string;
      codigo?: string;
      escuelaId?: number;
      escuelaNombre?: string;
    };
  };
  onNavigateToInicio: () => void;
  onNavigateToServicios: () => void;
  onNavigateToPerfil: () => void;
  onNavigateToReservas: () => void;
  onNavigateToIncidencias: () => void;
  onNavigateToPsicologia: () => void;
  onNavigateToBurra: () => void;
  onNavigateToOlimpiadas: () => void;
  onNavigateToPoliclinico: () => void;
  onNavigateToPromedio: () => void;
  onLogout?: () => void;
  isLoggingOut?: boolean;
}

const getDisplayName = (name?: string, codigo?: string): string => {
  const trimmedName = name?.trim();
  if (trimmedName) {
    return trimmedName;
  }

  const trimmedCodigo = codigo?.trim();
  if (trimmedCodigo) {
    return trimmedCodigo;
  }

  return 'Usuario';
};

export const ServiciosPage: React.FC<ServiciosPageProps> = ({
  user,
  onNavigateToInicio,
  onNavigateToServicios,
  onNavigateToPerfil,
  onNavigateToReservas,
  onNavigateToIncidencias,
  onNavigateToPsicologia,
  onNavigateToBurra,
  onNavigateToOlimpiadas,
  onNavigateToPoliclinico,
  onNavigateToPromedio,
  onLogout,
  isLoggingOut = false
}) => {
  const displayName = useMemo(
    () => getDisplayName(user.user_metadata.name, user.user_metadata.codigo),
    [user.user_metadata.codigo, user.user_metadata.name],
  );

  const buildCardHandlers = useCallback(
    (action: () => void) => ({
      onClick: action,
      onKeyDown: (event: KeyboardEvent<HTMLElement>) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          action();
        }
      },
    }),
    [],
  );

  // const reservasHandlers = buildCardHandlers(onNavigateToReservas);
  // const incidenciasHandlers = buildCardHandlers(onNavigateToIncidencias);

  // Función placeholder para botones no implementados
  const handleNotImplemented = () => {
    console.log('Funcionalidad no implementada');
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
      <main className="servicios-main" aria-labelledby="servicios-title">
        <section className="home-welcome-card">
          <div>
            <p className="home-welcome-date">Servicios Disponibles</p>
            <h1 className="home-title">Gestión de Servicios</h1>
            <p className="home-subtitle">
              Hola {displayName}, selecciona el servicio que deseas gestionar.
            </p>
          </div>
          <div className="home-welcome-avatar" aria-hidden="true">
            <NotebookPen size={44} />
          </div>
        </section>

        <section className="servicios-menu-grid" aria-label="Servicios disponibles">
          <article
            className="servicios-menu-card servicios-menu-card-espacios"
            role="article"
            tabIndex={0}
          >
            <div className="servicios-menu-icon-container">
              <div className="servicios-menu-icon servicios-menu-icon-espacios">
                <MonitorSmartphone className="servicios-menu-icon-svg" aria-hidden="true" />
              </div>
            </div>
            <h2 className="servicios-menu-card-title">Reserva de espacios</h2>
            <p className="servicios-menu-card-description">
              Reserva laboratorios y aulas para tus actividades académicas.
            </p>
            <div className="servicios-menu-card-badge">Laboratorios / Aulas</div>
            <div className="servicios-menu-card-actions">
              <button
                type="button"
                className="servicios-menu-card-button servicios-menu-card-button-espacios"
                onClick={(event) => {
                  onNavigateToReservas();
                }}
              >
                Gestionar reservas
              </button>
            </div>
          </article>

          <article
            className="servicios-menu-card servicios-menu-card-incidencias"
            role="article"
            tabIndex={0}
          >
            <div className="servicios-menu-icon-container">
              <div className="servicios-menu-icon servicios-menu-icon-incidencias">
                <AlertTriangle className="servicios-menu-icon-svg" aria-hidden="true" />
              </div>
            </div>
            <h2 className="servicios-menu-card-title">Incidencias y reportes</h2>
            <p className="servicios-menu-card-description">
              Registra incidencias, da seguimiento a sus estados y revisa tus reportes.
            </p>
            <div className="servicios-menu-card-badge servicios-menu-card-badge-incidencias">
              Atención a incidencias
            </div>
            <div className="servicios-menu-card-actions">
              <button
                type="button"
                className="servicios-menu-card-button servicios-menu-card-button-incidencias"
                onClick={(event) => {
                  onNavigateToIncidencias();
                }}
              >
                Gestionar incidencias
              </button>
            </div>
          </article>

          <article
            className="servicios-menu-card servicios-menu-card-psicologia"
            role="article"
            tabIndex={0}
          >
            <div className="servicios-menu-icon-container">
              <div className="servicios-menu-icon servicios-menu-icon-psicologia">
                <Brain className="servicios-menu-icon-svg" aria-hidden="true" />
              </div>
            </div>
            <h2 className="servicios-menu-card-title">Citas de Psicología</h2>
            <p className="servicios-menu-card-description">
              Agenda y da seguimiento a tus citas con el área de psicología.
            </p>
            <div className="servicios-menu-card-badge servicios-menu-card-badge-psicologia">
              Bienestar estudiantil
            </div>
            <div className="servicios-menu-card-actions">
              <button
                type="button"
                className="servicios-menu-card-button servicios-menu-card-button-psicologia"
                onClick={(event) => {
                  onNavigateToPsicologia();
                }}
              >
                Agendar cita
              </button>
            </div>
          </article>

          <article
            className="servicios-menu-card servicios-menu-card-burra"
            role="article"
            tabIndex={0}
          >
            <div className="servicios-menu-icon-container">
              <div className="servicios-menu-icon servicios-menu-icon-burra">
                <Bus className="servicios-menu-icon-svg" aria-hidden="true" />
              </div>
            </div>
            <h2 className="servicios-menu-card-title">Burra UPT</h2>
            <p className="servicios-menu-card-description">
              Revisa los recorridos y horarios de los buses universitarios de la UPT.
            </p>
            <div className="servicios-menu-card-badge servicios-menu-card-badge-burra">
              Transporte Estudiantil
            </div>
            <div className="servicios-menu-card-actions">
              <button
                type="button"
                className="servicios-menu-card-button servicios-menu-card-button-burra"
                onClick={() => onNavigateToBurra()}
              >
                Ver rutas y horarios
              </button>
            </div>
          </article>

          <article
            className="servicios-menu-card servicios-menu-card-olimpiadas"
            role="article"
            tabIndex={0}
          >
            <div className="servicios-menu-icon-container">
              <div className="servicios-menu-icon servicios-menu-icon-olimpiadas">
                <Trophy className="servicios-menu-icon-svg" aria-hidden="true" />
              </div>
            </div>
            <h2 className="servicios-menu-card-title">Olimpiadas Interfacultades</h2>
            <p className="servicios-menu-card-description">
              Consulta disciplinas, resultados, fixture y el estado de inscripción de tu facultad.
            </p>
            <div className="servicios-menu-card-badge servicios-menu-card-badge-olimpiadas">
              Competencia interfacultades
            </div>
            <div className="servicios-menu-card-actions">
              <button
                type="button"
                className="servicios-menu-card-button servicios-menu-card-button-olimpiadas"
                onClick={(event) => {
                  onNavigateToOlimpiadas();
                }}
              >
                Ver Olimpiadas
              </button>
            </div>
          </article>

          <article
            className="servicios-menu-card servicios-menu-card-policlinico"
            role="article"
            tabIndex={0}
          >
            <div className="servicios-menu-icon-container">
              <div className="servicios-menu-icon servicios-menu-icon-policlinico">
                <HeartPulse className="servicios-menu-icon-svg" aria-hidden="true" />
              </div>
            </div>
            <h2 className="servicios-menu-card-title">Policlínico UPT</h2>
            <p className="servicios-menu-card-description">
              Agenda citas médicas, de enfermería o de tópico de emergencia.
            </p>
            <div className="servicios-menu-card-badge servicios-menu-card-badge-policlinico">
              Salud estudiantil
            </div>
            <div className="servicios-menu-card-actions">
              <button
                type="button"
                className="servicios-menu-card-button servicios-menu-card-button-policlinico"
                onClick={(event) => {
                  onNavigateToPoliclinico();
                }}
              >
                Agendar cita
              </button>
            </div>
          </article>

          <article
            className="servicios-menu-card servicios-menu-card-promedio"
            role="article"
            tabIndex={0}
          >
            <div className="servicios-menu-icon-container">
              <div className="servicios-menu-icon servicios-menu-icon-promedio">
                <Calculator className="servicios-menu-icon-svg" aria-hidden="true" />
              </div>
            </div>
            <h2 className="servicios-menu-card-title">Calcula tu Promedio</h2>
            <p className="servicios-menu-card-description">
              Calcula tu promedio actual y lo que necesitas sacar para aprobar.
            </p>
            <div className="servicios-menu-card-badge servicios-menu-card-badge-promedio">
              Gestión Académica
            </div>
            <div className="servicios-menu-card-actions">
              <button
                type="button"
                className="servicios-menu-card-button servicios-menu-card-button-promedio"
                onClick={(event) => {
                  onNavigateToPromedio();
                }}
              >
                Calcular notas
              </button>
            </div>
          </article>
        </section>
      </main>
    </div>
  );
};
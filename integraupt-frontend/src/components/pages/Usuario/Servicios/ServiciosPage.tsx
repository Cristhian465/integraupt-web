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
  Calculator,
  Dumbbell,
  GraduationCap,
  Coffee,
  CalendarPlus,
  MessageSquare
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
  onNavigateToGimnasio: () => void;
  onNavigateToPoliclinico: () => void;
  onNavigateToPromedio: () => void;
  onNavigateToAulaVirtual: () => void;
  onNavigateToCafeteria: () => void;
  onNavigateToEventos: () => void;
  onNavigateToCanales: () => void;
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
  onNavigateToGimnasio,
  onNavigateToPoliclinico,
  onNavigateToPromedio,
  onNavigateToAulaVirtual,
  onNavigateToCafeteria,
  onNavigateToEventos,
  onNavigateToCanales,
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
            className="servicios-menu-card servicios-menu-card-eventos"
            role="article"
            tabIndex={0}
          >
            <div className="servicios-menu-icon-container">
              <div className="servicios-menu-icon servicios-menu-icon-eventos">
                <CalendarPlus className="servicios-menu-icon-svg" aria-hidden="true" />
              </div>
            </div>
            <h2 className="servicios-menu-card-title">Eventos UPT</h2>
            <p className="servicios-menu-card-description">
              Descubre charlas, talleres y actividades de tu facultad e inscribete.
            </p>
            <div className="servicios-menu-card-badge servicios-menu-card-badge-eventos">
              Charlas y talleres
            </div>
            <div className="servicios-menu-card-actions">
              <button
                type="button"
                className="servicios-menu-card-button servicios-menu-card-button-eventos"
                onClick={(event) => {
                  onNavigateToEventos();
                }}
              >
                Ver Eventos
              </button>
            </div>
          </article>

          <article
            className="servicios-menu-card servicios-menu-card-gimnasio"
            role="article"
            tabIndex={0}
          >
            <div className="servicios-menu-icon-container">
              <div className="servicios-menu-icon servicios-menu-icon-gimnasio">
                <Dumbbell className="servicios-menu-icon-svg" aria-hidden="true" />
              </div>
            </div>
            <h2 className="servicios-menu-card-title">Gimnasio UPT</h2>
            <p className="servicios-menu-card-description">
              Registra tu asistencia y haz seguimiento a tus sesiones de gimnasio.
            </p>
            <div className="servicios-menu-card-badge servicios-menu-card-badge-gimnasio">
              Deporte y Salud
            </div>
            <div className="servicios-menu-card-actions">
              <button
                type="button"
                className="servicios-menu-card-button servicios-menu-card-button-gimnasio"
                onClick={(event) => {
                  onNavigateToGimnasio();
                }}
              >
                Ver Gimnasio
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
            className="servicios-menu-card servicios-menu-card-cafeteria"
            role="article"
            tabIndex={0}
          >
            <div className="servicios-menu-icon-container">
              <div className="servicios-menu-icon servicios-menu-icon-cafeteria">
                <Coffee className="servicios-menu-icon-svg" aria-hidden="true" />
              </div>
            </div>
            <h2 className="servicios-menu-card-title">Cafeteria UPT</h2>
            <p className="servicios-menu-card-description">
              Explora el menu de tu facultad, realiza tu pedido y pagalo por Yape.
            </p>
            <div className="servicios-menu-card-badge servicios-menu-card-badge-cafeteria">
              Servicios de alimentacion
            </div>
            <div className="servicios-menu-card-actions">
              <button
                type="button"
                className="servicios-menu-card-button servicios-menu-card-button-cafeteria"
                onClick={(event) => {
                  onNavigateToCafeteria();
                }}
              >
                Realizar pedido
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
          
          <article
            className="servicios-menu-card servicios-menu-card-aulavirtual"
            role="article"
            tabIndex={0}
          >
            <div className="servicios-menu-icon-container">
              <div className="servicios-menu-icon servicios-menu-icon-aulavirtual">
                <GraduationCap className="servicios-menu-icon-svg" aria-hidden="true" />
              </div>
            </div>
            <h2 className="servicios-menu-card-title">Aula Virtual</h2>
            <p className="servicios-menu-card-description">
              Conecta tu cuenta del Aula Virtual y revisa tus cursos, notas y próximos eventos.
            </p>
            <div className="servicios-menu-card-badge servicios-menu-card-badge-aulavirtual">
              Moodle UPT
            </div>
            <div className="servicios-menu-card-actions">
              <button
                type="button"
                className="servicios-menu-card-button servicios-menu-card-button-aulavirtual"
                onClick={(event) => {
                  onNavigateToAulaVirtual();
                }}
              >
                Ver Aula Virtual
              </button>
            </div>
          </article>

          <article
            className="servicios-menu-card servicios-menu-card-canales"
            role="article"
            tabIndex={0}
          >
            <div className="servicios-menu-icon-container">
              <div className="servicios-menu-icon servicios-menu-icon-canales">
                <MessageSquare className="servicios-menu-icon-svg" aria-hidden="true" />
              </div>
            </div>
            <h2 className="servicios-menu-card-title">Canales</h2>
            <p className="servicios-menu-card-description">
              Mantente al día con los anuncios y conversaciones de tu facultad, escuela o curso.
            </p>
            <div className="servicios-menu-card-badge servicios-menu-card-badge-canales">
              Comunicación institucional
            </div>
            <div className="servicios-menu-card-actions">
              <button
                type="button"
                className="servicios-menu-card-button servicios-menu-card-button-canales"
                onClick={(event) => {
                  onNavigateToCanales();
                }}
              >
                Ver canales
              </button>
            </div>
          </article>
        </section>
      </main>
    </div>
  );
};
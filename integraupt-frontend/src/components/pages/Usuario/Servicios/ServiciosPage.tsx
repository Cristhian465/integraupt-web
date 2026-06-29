import { useMemo, useState } from 'react';
import {
  AlertTriangle,
  MonitorSmartphone,
  NotebookPen,
  Brain,
  Bus,
  Trophy,
  HeartPulse,
  Calculator,
  Dumbbell,
  GraduationCap,
  Coffee,
  Vote,
  CalendarPlus,
  MessageSquare,
  BookOpen,
  Search
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
  onNavigateToElecciones: () => void;
  onNavigateToEventos: () => void;
  onNavigateToCanales: () => void;
  onNavigateToSilabo?: () => void;
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
  onNavigateToElecciones,
  onNavigateToEventos,
  onNavigateToCanales,
  onNavigateToSilabo,
  onLogout,
  isLoggingOut = false
}) => {
  const displayName = useMemo(
    () => getDisplayName(user.user_metadata.name, user.user_metadata.codigo),
    [user.user_metadata.codigo, user.user_metadata.name],
  );

  const [searchTerm, setSearchTerm] = useState('');

  const rol = (user.user_metadata?.role ?? '').toLowerCase();
  const esDocente = !['estudiante', 'student', 'alumno'].includes(rol);

  const normalizeText = (text: string) => {
    return text
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
  };

  const levenshtein = (a: string, b: string) => {
    if (a.length === 0) return b.length;
    if (b.length === 0) return a.length;
    const matrix = Array(b.length + 1).fill(null).map(() => Array(a.length + 1).fill(null));
    for (let i = 0; i <= a.length; i += 1) matrix[0][i] = i;
    for (let j = 0; j <= b.length; j += 1) matrix[j][0] = j;
    for (let j = 1; j <= b.length; j += 1) {
      for (let i = 1; i <= a.length; i += 1) {
        const indicator = a[i - 1] === b[j - 1] ? 0 : 1;
        matrix[j][i] = Math.min(
          matrix[j][i - 1] + 1,
          matrix[j - 1][i] + 1,
          matrix[j - 1][i - 1] + indicator
        );
      }
    }
    return matrix[b.length][a.length];
  };

  const matchSearch = (text: string, synonyms: string = '') => {
    if (!searchTerm.trim()) return true;
    
    const fullTargetText = `${text} ${synonyms}`;
    const normalizedTarget = normalizeText(fullTargetText);
    const searchWords = normalizeText(searchTerm).trim().split(/\s+/);
    const targetWords = normalizedTarget.split(/\s+/);
    
    return searchWords.every(searchWord => {
      if (normalizedTarget.includes(searchWord)) return true;
      
      if (searchWord.length < 4) return false;
      return targetWords.some(targetWord => {
        if (Math.abs(targetWord.length - searchWord.length) > 2) return false;
        const maxErrores = searchWord.length >= 6 ? 2 : 1;
        return levenshtein(searchWord, targetWord) <= maxErrores;
      });
    });
  };

  const academicaGroup = 'Gestión Académica y Aprendizaje';
  const showAulaVirtual = matchSearch(`${academicaGroup} Aula Virtual Moodle UPT Conecta tu cuenta`, 'cursos tareas examenes notas virtual');
  const showEspacios = matchSearch(`${academicaGroup} Reserva de espacios Laboratorios Aulas`, 'computadoras separar ambiente sala estudio');
  const showPromedio = matchSearch(`${academicaGroup} Calcula tu Promedio`, 'calculadora notas aprobar pasar ponderado matematica');
  const showSilabo = esDocente && matchSearch(`${academicaGroup} Gestión de Sílabo Seguimiento Curricular temas dictado en clase`, 'cursos profesor avance plan estudios');
  const showAcademica = showAulaVirtual || showEspacios || showPromedio || showSilabo;

  const saludGroup = 'Salud y Bienestar';
  const showPoliclinico = matchSearch(`${saludGroup} Policlínico UPT Salud estudiantil citas médicas`, 'doctor medico enfermeria topico pastillas emergencia hospital clinica');
  const showPsicologia = matchSearch(`${saludGroup} Citas de Psicología Bienestar estudiantil`, 'psicologo terapia mental bienestar emocional consejeria ayuda');
  const showSalud = showPoliclinico || showPsicologia;

  const recreacionGroup = 'Vida Universitaria y Recreación';
  const showGimnasio = matchSearch(`${recreacionGroup} Gimnasio UPT Deporte y Salud`, 'gym pesas maquinas entrenar fit ejercicio fisico');
  const showOlimpiadas = matchSearch(`${recreacionGroup} Olimpiadas Interfacultades Competencia`, 'deportes futbol voley basquet cachimbos torneo campeonato');
  const showEventos = matchSearch(`${recreacionGroup} Eventos UPT Charlas y talleres`, 'congresos seminarios certificados conferencia ponencias');
  const showRecreacion = showGimnasio || showOlimpiadas || showEventos;

  const campusGroup = 'Servicios del Campus';
  const showBurra = matchSearch(`${campusGroup} Burra UPT Transporte Estudiantil`, 'bus paradero ruta pasaje movilidad micro autobus');
  const showCafeteria = matchSearch(`${campusGroup} Cafeteria UPT Servicios de alimentacion`, 'comida almuerzo menu kiosko yape hambre snack restaurante');
  const showIncidencias = matchSearch(`${campusGroup} Incidencias y reportes Atención a incidencias`, 'quejas problemas fallas malogrado ayuda soporte reclamos');
  const showCampus = showBurra || showCafeteria || showIncidencias;

  const comunidadGroup = 'Comunidad y Participación';
  const showCanales = matchSearch(`${comunidadGroup} Canales Comunicación institucional`, 'foros chat mensajes anuncios grupos redes');
  const showElecciones = matchSearch(`${comunidadGroup} Elecciones UPT Participación Estudiantil`, 'votar candidatos representantes tercio asamblea politica');
  const showComunidad = showCanales || showElecciones;


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

        <div className="servicios-search-container">
          <Search className="servicios-search-icon" size={20} />
          <input
            type="text"
            className="servicios-search-input"
            placeholder="Buscar servicios (ej. Aula Virtual, Sílabo, Notas...)"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            aria-label="Buscar servicios"
          />
        </div>

        <div className="servicios-categories-container">
          {showAcademica && (
            <section className="servicios-category" aria-label="Gestión Académica y Aprendizaje">
              <h2 className="servicios-category-title">📚 Gestión Académica y Aprendizaje</h2>
              <div className="servicios-menu-grid">
                {showAulaVirtual && (
                  <article className="servicios-menu-card servicios-menu-card-aulavirtual" role="article" tabIndex={0}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-aulavirtual">
                        <GraduationCap className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Aula Virtual</h2>
                    <p className="servicios-menu-card-description">
                      Conecta tu cuenta del Aula Virtual y revisa tus cursos, notas y próximos eventos.
                    </p>
                    <div className="servicios-menu-card-badge servicios-menu-card-badge-aulavirtual">Moodle UPT</div>
                    <div className="servicios-menu-card-actions">
                      <button type="button" className="servicios-menu-card-button servicios-menu-card-button-aulavirtual" onClick={() => onNavigateToAulaVirtual()}>
                        Ver Aula Virtual
                      </button>
                    </div>
                  </article>
                )}
                {showEspacios && (
                  <article className="servicios-menu-card servicios-menu-card-espacios" role="article" tabIndex={0}>
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
                      <button type="button" className="servicios-menu-card-button servicios-menu-card-button-espacios" onClick={() => onNavigateToReservas()}>
                        Gestionar reservas
                      </button>
                    </div>
                  </article>
                )}
                {showPromedio && (
                  <article className="servicios-menu-card servicios-menu-card-promedio" role="article" tabIndex={0}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-promedio">
                        <Calculator className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Calcula tu Promedio</h2>
                    <p className="servicios-menu-card-description">
                      Calcula tu promedio actual y lo que necesitas sacar para aprobar.
                    </p>
                    <div className="servicios-menu-card-badge servicios-menu-card-badge-promedio">Gestión Académica</div>
                    <div className="servicios-menu-card-actions">
                      <button type="button" className="servicios-menu-card-button servicios-menu-card-button-promedio" onClick={() => onNavigateToPromedio()}>
                        Calcular notas
                      </button>
                    </div>
                  </article>
                )}
                {onNavigateToSilabo && showSilabo && (
                  <article className="servicios-menu-card servicios-menu-card-silabo" role="article" tabIndex={0}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-silabo">
                        <BookOpen className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Gestión de Sílabo</h2>
                    <p className="servicios-menu-card-description">
                      Organiza y registra el avance de tus clases según los temas del sílabo.
                    </p>
                    <div className="servicios-menu-card-badge servicios-menu-card-badge-silabo">Seguimiento Curricular</div>
                    <div className="servicios-menu-card-actions">
                      <button
                        type="button"
                        className="servicios-menu-card-button servicios-menu-card-button-silabo"
                        onClick={() => onNavigateToSilabo?.()}
                      >
                        Ver mi sílabo
                      </button>
                    </div>
                  </article>
                )}
              </div>
            </section>
          )}

          {showSalud && (
            <section className="servicios-category" aria-label="Salud y Bienestar">
              <h2 className="servicios-category-title">🏥 Salud y Bienestar</h2>
              <div className="servicios-menu-grid">
                {showPoliclinico && (
                  <article className="servicios-menu-card servicios-menu-card-policlinico" role="article" tabIndex={0}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-policlinico">
                        <HeartPulse className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Policlínico UPT</h2>
                    <p className="servicios-menu-card-description">
                      Agenda citas médicas, de enfermería o de tópico de emergencia.
                    </p>
                    <div className="servicios-menu-card-badge servicios-menu-card-badge-policlinico">Salud estudiantil</div>
                    <div className="servicios-menu-card-actions">
                      <button type="button" className="servicios-menu-card-button servicios-menu-card-button-policlinico" onClick={() => onNavigateToPoliclinico()}>
                        Agendar cita
                      </button>
                    </div>
                  </article>
                )}
                {showPsicologia && (
                  <article className="servicios-menu-card servicios-menu-card-psicologia" role="article" tabIndex={0}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-psicologia">
                        <Brain className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Citas de Psicología</h2>
                    <p className="servicios-menu-card-description">
                      Agenda y da seguimiento a tus citas con el área de psicología.
                    </p>
                    <div className="servicios-menu-card-badge servicios-menu-card-badge-psicologia">Bienestar estudiantil</div>
                    <div className="servicios-menu-card-actions">
                      <button type="button" className="servicios-menu-card-button servicios-menu-card-button-psicologia" onClick={() => onNavigateToPsicologia()}>
                        Agendar cita
                      </button>
                    </div>
                  </article>
                )}
              </div>
            </section>
          )}

          {showRecreacion && (
            <section className="servicios-category" aria-label="Vida Universitaria y Recreación">
              <h2 className="servicios-category-title">⚽ Vida Universitaria y Recreación</h2>
              <div className="servicios-menu-grid">
                {showGimnasio && (
                  <article className="servicios-menu-card servicios-menu-card-gimnasio" role="article" tabIndex={0}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-gimnasio">
                        <Dumbbell className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Gimnasio UPT</h2>
                    <p className="servicios-menu-card-description">
                      Registra tu asistencia y haz seguimiento a tus sesiones de gimnasio.
                    </p>
                    <div className="servicios-menu-card-badge servicios-menu-card-badge-gimnasio">Deporte y Salud</div>
                    <div className="servicios-menu-card-actions">
                      <button type="button" className="servicios-menu-card-button servicios-menu-card-button-gimnasio" onClick={() => onNavigateToGimnasio()}>
                        Ver Gimnasio
                      </button>
                    </div>
                  </article>
                )}
                {showOlimpiadas && (
                  <article className="servicios-menu-card servicios-menu-card-olimpiadas" role="article" tabIndex={0}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-olimpiadas">
                        <Trophy className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Olimpiadas Interfacultades</h2>
                    <p className="servicios-menu-card-description">
                      Consulta disciplinas, resultados, fixture y el estado de inscripción de tu facultad.
                    </p>
                    <div className="servicios-menu-card-badge servicios-menu-card-badge-olimpiadas">Competencia interfacultades</div>
                    <div className="servicios-menu-card-actions">
                      <button type="button" className="servicios-menu-card-button servicios-menu-card-button-olimpiadas" onClick={() => onNavigateToOlimpiadas()}>
                        Ver Olimpiadas
                      </button>
                    </div>
                  </article>
                )}
                {showEventos && (
                  <article className="servicios-menu-card servicios-menu-card-eventos" role="article" tabIndex={0}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-eventos">
                        <CalendarPlus className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Eventos UPT</h2>
                    <p className="servicios-menu-card-description">
                      Descubre charlas, talleres y actividades de tu facultad e inscribete.
                    </p>
                    <div className="servicios-menu-card-badge servicios-menu-card-badge-eventos">Charlas y talleres</div>
                    <div className="servicios-menu-card-actions">
                      <button type="button" className="servicios-menu-card-button servicios-menu-card-button-eventos" onClick={() => onNavigateToEventos()}>
                        Ver Eventos
                      </button>
                    </div>
                  </article>
                )}
              </div>
            </section>
          )}

          {showCampus && (
            <section className="servicios-category" aria-label="Servicios del Campus">
              <h2 className="servicios-category-title">🚌 Servicios del Campus</h2>
              <div className="servicios-menu-grid">
                {showBurra && (
                  <article className="servicios-menu-card servicios-menu-card-burra" role="article" tabIndex={0}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-burra">
                        <Bus className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Burra UPT</h2>
                    <p className="servicios-menu-card-description">
                      Revisa los recorridos y horarios de los buses universitarios de la UPT.
                    </p>
                    <div className="servicios-menu-card-badge servicios-menu-card-badge-burra">Transporte Estudiantil</div>
                    <div className="servicios-menu-card-actions">
                      <button type="button" className="servicios-menu-card-button servicios-menu-card-button-burra" onClick={() => onNavigateToBurra()}>
                        Ver rutas y horarios
                      </button>
                    </div>
                  </article>
                )}
                {showCafeteria && (
                  <article className="servicios-menu-card servicios-menu-card-cafeteria" role="article" tabIndex={0}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-cafeteria">
                        <Coffee className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Cafeteria UPT</h2>
                    <p className="servicios-menu-card-description">
                      Explora el menu de tu facultad, realiza tu pedido y pagalo por Yape.
                    </p>
                    <div className="servicios-menu-card-badge servicios-menu-card-badge-cafeteria">Servicios de alimentacion</div>
                    <div className="servicios-menu-card-actions">
                      <button type="button" className="servicios-menu-card-button servicios-menu-card-button-cafeteria" onClick={() => onNavigateToCafeteria()}>
                        Realizar pedido
                      </button>
                    </div>
                  </article>
                )}
                {showIncidencias && (
                  <article className="servicios-menu-card servicios-menu-card-incidencias" role="article" tabIndex={0}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-incidencias">
                        <AlertTriangle className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Incidencias y reportes</h2>
                    <p className="servicios-menu-card-description">
                      Registra incidencias, da seguimiento a sus estados y revisa tus reportes.
                    </p>
                    <div className="servicios-menu-card-badge servicios-menu-card-badge-incidencias">Atención a incidencias</div>
                    <div className="servicios-menu-card-actions">
                      <button type="button" className="servicios-menu-card-button servicios-menu-card-button-incidencias" onClick={() => onNavigateToIncidencias()}>
                        Gestionar incidencias
                      </button>
                    </div>
                  </article>
                )}
              </div>
            </section>
          )}

          {showComunidad && (
            <section className="servicios-category" aria-label="Comunidad y Participación">
              <h2 className="servicios-category-title">🗣️ Comunidad y Participación</h2>
              <div className="servicios-menu-grid">
                {showCanales && (
                  <article className="servicios-menu-card servicios-menu-card-canales" role="article" tabIndex={0}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-canales">
                        <MessageSquare className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Canales</h2>
                    <p className="servicios-menu-card-description">
                      Mantente al día con los anuncios y conversaciones de tu facultad, escuela o curso.
                    </p>
                    <div className="servicios-menu-card-badge servicios-menu-card-badge-canales">Comunicación institucional</div>
                    <div className="servicios-menu-card-actions">
                      <button type="button" className="servicios-menu-card-button servicios-menu-card-button-canales" onClick={() => onNavigateToCanales()}>
                        Ver canales
                      </button>
                    </div>
                  </article>
                )}
                {showElecciones && (
                  <article className="servicios-menu-card servicios-menu-card-elecciones" role="article" tabIndex={0} style={{ borderColor: '#1e3a8a' }}>
                    <div className="servicios-menu-icon-container">
                      <div className="servicios-menu-icon servicios-menu-icon-elecciones" style={{ background: 'linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%)', color: '#ffffff', boxShadow: '0 4px 6px -1px rgba(30, 58, 138, 0.3)' }}>
                        <Vote className="servicios-menu-icon-svg" aria-hidden="true" />
                      </div>
                    </div>
                    <h2 className="servicios-menu-card-title">Elecciones UPT</h2>
                    <p className="servicios-menu-card-description">
                      Vota por tus representantes estudiantiles y consulta los resultados de las elecciones.
                    </p>
                    <div className="servicios-menu-card-badge" style={{ backgroundColor: '#1e3a8a', color: '#ffffff', border: 'none' }}>Participación Estudiantil</div>
                    <div className="servicios-menu-card-actions">
                      <button type="button" className="servicios-menu-card-button" style={{ background: 'linear-gradient(90deg, #1e40af 0%, #1e3a8a 100%)', color: 'white', border: 'none', boxShadow: '0 4px 6px -1px rgba(30, 58, 138, 0.4)' }} onClick={() => onNavigateToElecciones()}>
                        Ver Elecciones
                      </button>
                    </div>
                  </article>
                )}
              </div>
            </section>
          )}
        </div>
      </main>
    </div>
  );
};
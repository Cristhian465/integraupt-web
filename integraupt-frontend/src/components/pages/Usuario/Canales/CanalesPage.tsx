import { useCallback, useEffect, useMemo, useState } from 'react';
import { AlertCircle, MessageSquare, Plus, RefreshCw } from 'lucide-react';
import { Navbar } from '../Navbar';
import { ChatWindow } from './components/ChatWindow';
import { CrearCanalModal } from './components/CrearCanalModal';
import { fetchMisCanales } from './services/canalesService';
import type { Canal } from './types';
import '../../../../styles/CanalesScreen.css';

interface CanalesPageProps {
  user: {
    id: string;
    email: string;
    sessionToken?: string;
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
  onLogout?: () => void;
  isLoggingOut?: boolean;
}

const getUserDisplayName = (name?: string, codigo?: string): string => {
  const trimmedName = name?.trim();
  if (trimmedName) return trimmedName;

  const trimmedCodigo = codigo?.trim();
  if (trimmedCodigo) return trimmedCodigo;

  return 'Usuario';
};

export const CanalesPage: React.FC<CanalesPageProps> = ({
  user,
  onNavigateToInicio,
  onNavigateToServicios,
  onNavigateToPerfil,
  onLogout,
  isLoggingOut = false
}) => {
  const [canales, setCanales] = useState<Canal[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [selectedCanalId, setSelectedCanalId] = useState<number | null>(null);
  const [crearModalOpen, setCrearModalOpen] = useState(false);

  const userId = useMemo(() => {
    const parsed = Number.parseInt(user.id, 10);
    return Number.isNaN(parsed) ? null : parsed;
  }, [user.id]);

  const isDocente = useMemo(() => {
    const role = user.user_metadata.role?.trim().toLowerCase() ?? '';
    return role === 'profesor';
  }, [user.user_metadata.role]);

  const displayName = useMemo(
    () => getUserDisplayName(user.user_metadata.name, user.user_metadata.codigo),
    [user.user_metadata.codigo, user.user_metadata.name]
  );

  const loadCanales = useCallback(async () => {
    if (userId == null) return;

    setLoading(true);
    setError(null);
    try {
      const data = await fetchMisCanales(userId);
      setCanales(data);
    } catch (loadError) {
      setError(loadError instanceof Error ? loadError.message : 'No se pudieron cargar tus canales.');
      setCanales([]);
    } finally {
      setLoading(false);
    }
  }, [userId]);

  useEffect(() => {
    void loadCanales();
  }, [loadCanales]);

  useEffect(() => {
    if (selectedCanalId == null && canales.length > 0) {
      setSelectedCanalId(canales[0].id);
    }
  }, [canales, selectedCanalId]);

  const selectedCanal = useMemo(
    () => canales.find((canal) => canal.id === selectedCanalId) ?? null,
    [canales, selectedCanalId]
  );

  const handleCanalCreado = (canal: Canal) => {
    setCrearModalOpen(false);
    setCanales((prev) => [canal, ...prev]);
    setSelectedCanalId(canal.id);
  };

  return (
    <div className="canales-container">
      <Navbar
        displayName={displayName}
        userCode={user.user_metadata.codigo}
        currentPage="canales"
        onNavigateToInicio={onNavigateToInicio}
        onNavigateToServicios={onNavigateToServicios}
        onNavigateToPerfil={onNavigateToPerfil}
        onLogout={onLogout}
        isLoggingOut={isLoggingOut}
      />

      <main className="canales-main">
        <section className="home-welcome-card">
          <div>
            <p className="home-welcome-date">Canales</p>
            <h1 className="home-title">Tus canales de comunicación</h1>
            <p className="home-subtitle">
              Mantente al día con los anuncios y conversaciones de tu facultad, escuela o curso.
            </p>
          </div>
          <div className="home-welcome-avatar" aria-hidden="true">
            <MessageSquare size={44} />
          </div>
        </section>

        {error && (
          <div className="gestion-canales-alert error">
            <AlertCircle size={16} />
            <span>{error}</span>
          </div>
        )}

        <div className="canales-layout">
          <aside className="canales-list-panel">
            <div className="canales-list-header">
              <h2>Mis canales</h2>
              <div className="canales-list-actions">
                <button type="button" className="canal-icon-button" onClick={loadCanales} title="Actualizar" disabled={loading}>
                  <RefreshCw size={16} />
                </button>
                {isDocente && (
                  <button type="button" className="canal-icon-button primary" onClick={() => setCrearModalOpen(true)} title="Crear canal">
                    <Plus size={16} />
                  </button>
                )}
              </div>
            </div>

            <div className="canales-list">
              {loading && canales.length === 0 && <p className="canal-usuario-picker-hint">Cargando canales...</p>}
              {!loading && canales.length === 0 && (
                <p className="canal-usuario-picker-hint">Aún no perteneces a ningún canal.</p>
              )}
              {canales.map((canal) => (
                <button
                  type="button"
                  key={canal.id}
                  className={`canales-list-item ${canal.id === selectedCanalId ? 'active' : ''}`}
                  onClick={() => setSelectedCanalId(canal.id)}
                >
                  <span className="canales-list-item-name">{canal.nombre}</span>
                  {canal.estado === 'archivado' && <span className="canales-list-item-badge">Archivado</span>}
                </button>
              ))}
            </div>
          </aside>

          {userId != null && <ChatWindow canal={selectedCanal} usuarioId={userId} esDocente={isDocente} />}
        </div>
      </main>

      {userId != null && (
        <CrearCanalModal
          open={crearModalOpen}
          creadorId={userId}
          onClose={() => setCrearModalOpen(false)}
          onCreated={handleCanalCreado}
        />
      )}
    </div>
  );
};

import React from 'react';
import { ExternalLink, Info, Download, PlayCircle } from 'lucide-react';

interface VoteTabProps {
  user: any;
  parties: any[];
  onVoteSubmit: (selections: any) => Promise<any>;
  existingVote: any;
  isVoting: boolean;
  voteError: string | null;
  electionEnded?: boolean;
}

export const VoteTab: React.FC<VoteTabProps> = ({ existingVote, electionEnded }) => {
  return (
    <div className="vote-tab-formal" style={{ maxWidth: '1200px', margin: '0 auto', padding: '0 1rem' }}>
      <div className="vote-header-formal" style={{ textAlign: 'center', marginBottom: '2rem' }}>
        <h3 style={{ fontSize: '2rem', fontWeight: 'bold', color: '#1e293b', marginBottom: '0.5rem' }}>Sufragio Oficial UPT</h3>
        <p style={{ color: '#64748b', fontSize: '1.1rem' }}>El proceso electoral se realiza a través de la plataforma oficial de la Universidad Privada de Tacna.</p>
      </div>

      <div style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))',
        gap: '2rem',
        alignItems: 'stretch'
      }}>
        {/* Left Column: Portal Information */}
        <div className="vote-portal-card" style={{
          background: 'white',
          borderRadius: '16px',
          padding: '3rem 2rem',
          textAlign: 'center',
          boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center'
        }}>
          <div style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: '64px', height: '64px', borderRadius: '50%', background: '#e0f2fe', color: '#0284c7', marginBottom: '1.5rem' }}>
            <Info size={32} />
          </div>
          <h4 style={{ fontSize: '1.5rem', fontWeight: 'bold', color: '#1e293b', marginBottom: '1rem' }}>Portal Oficial de Elecciones</h4>
          <p style={{ color: '#64748b', marginBottom: '2.5rem', lineHeight: '1.6' }}>
            Para emitir tu voto, serás redirigido al sistema oficial de elecciones de la Universidad Privada de Tacna. Necesitarás tus credenciales institucionales para ingresar.
          </p>
          <div style={{ display: 'flex', gap: '1rem', justifyContent: 'center', flexWrap: 'wrap', width: '100%' }}>
            {electionEnded ? (
              <span 
                style={{
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '0.75rem',
                  background: '#94a3b8',
                  color: 'white',
                  padding: '1rem 1.5rem',
                  borderRadius: '8px',
                  fontWeight: 'bold',
                  fontSize: '1rem',
                  flex: '1',
                  justifyContent: 'center',
                  minWidth: '220px',
                  cursor: 'not-allowed'
                }}
              >
                Se terminaron las votaciones
              </span>
            ) : (
              <a 
                href="https://net.upt.edu.pe/elecciones/weblogin/login.php"
                target="_blank"
                rel="noopener noreferrer"
                style={{
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '0.75rem',
                  background: '#0ea5e9',
                  color: 'white',
                  padding: '1rem 1.5rem',
                  borderRadius: '8px',
                  fontWeight: 'bold',
                  textDecoration: 'none',
                  fontSize: '1rem',
                  transition: 'all 0.2s',
                  boxShadow: '0 4px 6px -1px rgba(14, 165, 233, 0.3)',
                  flex: '1',
                  justifyContent: 'center',
                  minWidth: '220px'
                }}
                onMouseOver={(e) => (e.currentTarget.style.transform = 'translateY(-2px)')}
                onMouseOut={(e) => (e.currentTarget.style.transform = 'translateY(0)')}
              >
                Ingresa aquí para votar <ExternalLink size={18} />
              </a>
            )}
            <a 
              href="/GUIA_RAPIDA_Elecciones.pdf"
              download="GUIA_RAPIDA_Elecciones.pdf"
              style={{
                display: 'inline-flex',
                alignItems: 'center',
                gap: '0.75rem',
                background: '#f1f5f9',
                color: '#475569',
                padding: '1rem 1.5rem',
                borderRadius: '8px',
                fontWeight: 'bold',
                textDecoration: 'none',
                fontSize: '1rem',
                transition: 'all 0.2s',
                border: '1px solid #cbd5e1',
                flex: '1',
                justifyContent: 'center',
                minWidth: '160px'
              }}
              onMouseOver={(e) => (e.currentTarget.style.transform = 'translateY(-2px)')}
              onMouseOut={(e) => (e.currentTarget.style.transform = 'translateY(0)')}
            >
              Instrucciones <Download size={18} />
            </a>
          </div>
        </div>

        {/* Right Column: Video Tutorial */}
        <div className="vote-video-card" style={{
          background: 'white',
          borderRadius: '16px',
          padding: '2.5rem',
          boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
          display: 'flex',
          flexDirection: 'column'
        }}>
          <h5 style={{ fontSize: '1.25rem', fontWeight: 'bold', color: '#1e293b', marginBottom: '1.5rem', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
            <PlayCircle size={24} className="text-blue-600" /> Tutorial: ¿Cómo votar paso a paso?
          </h5>
          <div style={{ borderRadius: '12px', overflow: 'hidden', boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)', flexGrow: 1, display: 'flex', alignItems: 'center', background: '#000' }}>
            <video 
              src="/Proceso%20Sufragio.mp4" 
              controls 
              autoPlay 
              muted 
              loop 
              style={{ width: '100%', display: 'block' }} 
            />
          </div>
        </div>
      </div>
    </div>
  );
};

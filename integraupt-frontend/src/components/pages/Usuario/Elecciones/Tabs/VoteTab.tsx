import React, { useState } from 'react';
import { Vote, CheckCircle, AlertTriangle, Info, Loader2 } from 'lucide-react';

interface VoteTabProps {
  user: any;
  parties: any[];
  onVoteSubmit: (selections: any) => Promise<any>;
  existingVote: any;
  isVoting: boolean;
  voteError: string | null;
  electionEnded?: boolean;
  onVoteAgain?: () => void;
}

export const VoteTab: React.FC<VoteTabProps> = ({ user, parties = [], onVoteSubmit, existingVote, isVoting, voteError, electionEnded, onVoteAgain }) => {
  const [selections, setSelections] = useState<Record<string, string | number>>({});
  const [showConfirm, setShowConfirm] = useState(false);
  const [startedVoting, setStartedVoting] = useState(false);

  const safeParties = Array.isArray(parties) ? parties : [];

  const handleSelect = (category: string, partyId: string | number) => {
    setSelections(prev => ({ ...prev, [category]: partyId }));
  };

  const isFormComplete = selections['asamblea'] !== undefined && selections['consejo_uni'] !== undefined && selections['consejo_fac'] !== undefined;

  const handleSubmit = async () => {
    const payload = {
      asamblea: selections['asamblea'] === 'blanco' ? null : selections['asamblea'],
      consejo_uni: selections['consejo_uni'] === 'blanco' ? null : selections['consejo_uni'],
      consejo_fac: selections['consejo_fac'] === 'blanco' ? null : selections['consejo_fac'],
    };
    await onVoteSubmit(payload);
  };

  if (existingVote) {
    const getPartyName = (partyData: any, partyDataCamel: any) => {
      if (partyData) return partyData.name;
      if (partyDataCamel) return partyDataCamel.name;
      return 'Voto en Blanco';
    };

    return (
      <div className="vote-success-container fade-in">
        <div className="vote-success-card" style={{ maxWidth: '600px', width: '100%', margin: '0 auto', textAlign: 'center' }}>
          <CheckCircle size={64} className="text-green-600 mx-auto" style={{ marginBottom: '1rem', color: '#16a34a' }} />
          <h2 style={{ fontSize: '2rem', fontWeight: 'bold', color: '#16a34a', marginBottom: '0.5rem' }}>¡Ya Votaste!</h2>
          <p style={{ color: '#475569', marginBottom: '2rem' }}>Tu voto ha sido registrado exitosamente en el sistema.</p>

          <div style={{ background: '#f8fafc', padding: '1.5rem', borderRadius: '12px', border: '1px solid #e2e8f0', width: '100%', textAlign: 'left' }}>
            <h4 style={{ marginBottom: '1rem', borderBottom: '1px solid #cbd5e1', paddingBottom: '0.5rem', color: '#1e293b' }}>Resumen de Votación</h4>

            <div style={{ marginBottom: '1rem' }}>
              <div style={{ fontSize: '0.85rem', color: '#64748b', fontWeight: 'bold', textTransform: 'uppercase' }}>Asamblea Universitaria</div>
              <div style={{ fontSize: '1.1rem', fontWeight: '600', color: '#0f172a' }}>
                {getPartyName(existingVote.asamblea_party, existingVote.asambleaParty)}
              </div>
            </div>

            <div style={{ marginBottom: '1rem' }}>
              <div style={{ fontSize: '0.85rem', color: '#64748b', fontWeight: 'bold', textTransform: 'uppercase' }}>Consejo Universitario</div>
              <div style={{ fontSize: '1.1rem', fontWeight: '600', color: '#0f172a' }}>
                {getPartyName(existingVote.consejo_uni_party, existingVote.consejoUniParty)}
              </div>
            </div>

            <div style={{ marginBottom: '1.5rem' }}>
              <div style={{ fontSize: '0.85rem', color: '#64748b', fontWeight: 'bold', textTransform: 'uppercase' }}>Consejo de Facultad</div>
              <div style={{ fontSize: '1.1rem', fontWeight: '600', color: '#0f172a' }}>
                {getPartyName(existingVote.consejo_fac_party, existingVote.consejoFacParty)}
              </div>
            </div>

            <div style={{ display: 'flex', justifyContent: 'space-between', borderTop: '1px solid #cbd5e1', paddingTop: '1rem', flexWrap: 'wrap', gap: '1rem' }}>
              <div>
                <div style={{ fontSize: '0.75rem', color: '#64748b' }}>Fecha y Hora</div>
                <div style={{ fontWeight: '600', fontSize: '0.9rem' }}>
                  {existingVote.created_at || existingVote.timestamp ? new Date(existingVote.created_at || existingVote.timestamp).toLocaleString() : 'Registrado'}
                </div>
              </div>
              <div style={{ textAlign: 'left' }}>
                <div style={{ fontSize: '0.75rem', color: '#64748b' }}>Código de Verificación</div>
                <div style={{ fontWeight: 'bold', fontSize: '1.1rem', color: '#2563eb', letterSpacing: '2px' }}>
                  {existingVote.verification_code || 'VOTO-CONFIRMADO'}
                </div>
              </div>
            </div>

            {onVoteAgain && (
              <div style={{ marginTop: '2rem', textAlign: 'center' }}>
                <button
                  onClick={onVoteAgain}
                  style={{
                    background: '#f1f5f9',
                    color: '#475569',
                    border: '1px solid #cbd5e1',
                    padding: '0.75rem 1.5rem',
                    borderRadius: '8px',
                    fontWeight: 'bold',
                    cursor: 'pointer'
                  }}
                >
                  Votar de Nuevo (Modo Pruebas)
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    );
  }



  const renderCategory = (title: string, categoryKey: string) => (
    <div style={{ marginBottom: '3rem' }}>
      <h3 className="cargo-title">{title}</h3>
      <div className="options-grid-simple">
        {safeParties.map(party => (
          <div
            key={party.id}
            className={`voting-option-simple ${selections[categoryKey] === party.id ? 'selected' : ''}`}
            onClick={() => handleSelect(categoryKey, party.id)}
            style={{ '--party-color': party.color || '#2563eb' } as any}
          >
            {party.logo_url ? (
              <img src={party.logo_url} alt={party.name || 'Partido'} className="option-logo-small" />
            ) : (
              <div className="blank-logo-small" style={{ borderColor: party.color }}>
                <span style={{ fontSize: '1.2rem', fontWeight: 'bold', color: party.color }}>{(party.name || 'P').substring(0, 2).toUpperCase()}</span>
              </div>
            )}
            <div className="option-name-simple">{party.name || 'Partido'}</div>

            {selections[categoryKey] === party.id && (
              <div className="selection-overlay-simple">
                <div className="small-cross-container">
                  <div className="small-cross-line" style={{ transform: 'rotate(45deg)' }}></div>
                  <div className="small-cross-line" style={{ transform: 'rotate(-45deg)' }}></div>
                </div>
              </div>
            )}
          </div>
        ))}

        {/* Voto en blanco */}
        <div
          className={`voting-option-simple ${selections[categoryKey] === 'blanco' ? 'selected' : ''}`}
          onClick={() => handleSelect(categoryKey, 'blanco')}
          style={{ '--party-color': '#94a3b8' } as any}
        >
          <div className="blank-logo-small" style={{ background: '#f8fafc', borderColor: '#cbd5e1' }}>
            <span style={{ color: '#94a3b8', fontSize: '1rem' }}>⚪</span>
          </div>
          <div className="option-name-simple" style={{ color: '#64748b' }}>Voto en Blanco</div>

          {selections[categoryKey] === 'blanco' && (
            <div className="selection-overlay-simple">
              <div className="small-cross-container">
                <div className="small-cross-line" style={{ transform: 'rotate(45deg)', background: '#64748b' }}></div>
                <div className="small-cross-line" style={{ transform: 'rotate(-45deg)', background: '#64748b' }}></div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );

  if (!startedVoting && !existingVote) {
    return (
      <div className="vote-tab-formal fade-in" style={{ maxWidth: '700px', margin: '0 auto', padding: '2rem 1rem', textAlign: 'center' }}>
        <div style={{ background: 'white', borderRadius: '16px', padding: '3rem', boxShadow: '0 10px 25px -5px rgba(0,0,0,0.1)', border: '1px solid #e2e8f0' }}>
          <div style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: '80px', height: '80px', borderRadius: '50%', background: '#eff6ff', color: '#2563eb', marginBottom: '1.5rem' }}>
            <Vote size={40} />
          </div>
          <h2 style={{ fontSize: '2.5rem', fontWeight: 'bold', color: '#0f172a', marginBottom: '1rem' }}>¿Desea sufragar?</h2>
          <p style={{ color: '#475569', fontSize: '1.2rem', marginBottom: '3rem', lineHeight: '1.6' }}>
            Estás a punto de iniciar tu proceso de sufragio. Recuerda que tu voto es secreto e irreversible. Asegúrate de conocer a tus candidatos antes de continuar.
          </p>
          <button 
            className="btn-sufragar-strict"
            onClick={() => setStartedVoting(true)}
            style={{ fontSize: '1.2rem', padding: '1.2rem 3rem' }}
          >
            Sí, iniciar proceso de votación
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="vote-tab-formal fade-in" style={{ maxWidth: '900px', margin: '0 auto', padding: '0 1rem' }}>
      <div style={{ background: '#f8fafc', borderLeft: '4px solid #2563eb', padding: '1.5rem', borderRadius: '8px', marginBottom: '2.5rem' }}>
        <h4 style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', color: '#1e3a8a', margin: '0 0 0.5rem 0' }}>
          <Info size={20} /> Instrucciones de Votación
        </h4>
        <p style={{ margin: 0, color: '#475569', lineHeight: '1.5' }}>
          Selecciona una opción para cada categoría. Tu voto es secreto e irreversible.
          Al marcar una opción, aparecerá una cruz (X) roja confirmando tu elección.
        </p>
      </div>

      {renderCategory('Asamblea Universitaria', 'asamblea')}
      {renderCategory('Consejo Universitario', 'consejo_uni')}
      {renderCategory(`Consejo de Facultad (${user?.user_metadata?.faculty || user?.user_metadata?.escuelaNombre || 'FAING'})`, 'consejo_fac')}

      {voteError && (
        <div className="vote-error" style={{ marginBottom: '1.5rem' }}>
          <AlertTriangle size={20} />
          {voteError}
        </div>
      )}

      {!showConfirm ? (
        <button
          className="btn-sufragar-strict"
          disabled={!isFormComplete || isVoting}
          onClick={() => setShowConfirm(true)}
        >
          Revisar y Emitir Voto
        </button>
      ) : (
        <div style={{ background: 'white', padding: '2rem', borderRadius: '12px', boxShadow: '0 10px 25px -5px rgba(0,0,0,0.1)', border: '1px solid #e2e8f0', marginTop: '2rem' }}>
          <h3 style={{ margin: '0 0 1rem 0', color: '#0f172a', textAlign: 'center' }}>¿Confirmas tu voto?</h3>
          <p style={{ color: '#64748b', textAlign: 'center', marginBottom: '2rem' }}>Verifica tus selecciones antes de enviar. Esta acción no se puede deshacer.</p>

          <div style={{ display: 'flex', gap: '1rem', flexWrap: 'wrap' }}>
            <button
              className="btn-cancel"
              onClick={() => setShowConfirm(false)}
              disabled={isVoting}
              style={{ flex: 1, minWidth: '150px' }}
            >
              Modificar
            </button>
            <button
              className="btn-confirm"
              onClick={handleSubmit}
              disabled={isVoting}
              style={{ flex: 1, display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '0.5rem', minWidth: '150px' }}
            >
              {isVoting ? <Loader2 className="spinner" size={20} /> : <Vote size={20} />}
              {isVoting ? 'Procesando...' : 'Sí, Emitir Voto'}
            </button>
          </div>
        </div>
      )}
    </div>
  );
};

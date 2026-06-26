import React, { useState, Component, ErrorInfo } from 'react';
import { Info, BookOpen, Target, Shield, CheckCircle2, ChevronDown, ChevronUp, AlertCircle, Users, Building, GraduationCap, FileText, IdCard } from 'lucide-react';
import { partiesData } from '../../../../../data/candidatos';
import type { PartyData, Candidate } from '../../../../../data/candidatos';

class ErrorBoundary extends Component<{children: React.ReactNode}, {hasError: boolean, error: Error | null}> {
  constructor(props: {children: React.ReactNode}) {
    super(props);
    this.state = { hasError: false, error: null };
  }
  static getDerivedStateFromError(error: Error) {
    return { hasError: true, error };
  }
  componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error("ErrorBoundary caught an error", error, errorInfo);
  }
  render() {
    if (this.state.hasError) {
      return <div style={{padding: '2rem', background: '#fee2e2', color: '#991b1b', borderRadius: '1rem'}}>
        <h2>Error Crítico en InfoTab</h2>
        <pre>{this.state.error?.toString()}</pre>
        <pre>{this.state.error?.stack}</pre>
      </div>;
    }
    return this.props.children;
  }
}

export const InfoTab: React.FC = () => {
  const [expandedParty, setExpandedParty] = useState<string | null>(null);
  const [activeSubTab, setActiveSubTab] = useState<Record<string, 'plan' | 'list'>>({});
  const [activeCandidateType, setActiveCandidateType] = useState<Record<string, 'asamblea' | 'consejoUni' | 'consejoFac'>>({});
  const [activeFaculty, setActiveFaculty] = useState<Record<string, string>>({});

  const toggleExpand = (party: string) => {
    setExpandedParty(expandedParty === party ? null : party);
    if (expandedParty !== party && !activeSubTab[party]) {
      setActiveSubTab(prev => ({ ...prev, [party]: 'plan' }));
      setActiveCandidateType(prev => ({ ...prev, [party]: 'asamblea' }));
      setActiveFaculty(prev => ({ ...prev, [party]: 'FAING' }));
    }
  };

  const getPartyTagClass = (id: string) => {
    switch(id) {
      case 'feut': return 'tag-purple';
      case 'fuerza': return 'tag-green';
      case 'reu': return 'tag-blue';
      default: return 'tag-blue';
    }
  };

  const renderCandidateList = (title: string, candidates: Candidate[], icon: React.ReactNode) => {
    if (!candidates || candidates.length === 0) {
      return (
        <div className="candidate-group">
           <h6 className="candidate-group-title">{icon} {title}</h6>
           <p style={{padding: '1rem', color: '#64748b'}}>No se encontraron candidatos para esta lista.</p>
        </div>
      );
    }
    return (
      <div className="candidate-group">
        <h6 className="candidate-group-title">{icon} {title}</h6>
        <div className="candidates-grid-table">
          {candidates.map((c, idx) => (
            <div key={idx} className="candidate-table-row">
              <div className="c-number">{c.number}</div>
              <div className="c-info">
                <strong>{c.name}</strong>
                <span><GraduationCap size={14} className="icon-mr"/> {c.school}</span>
              </div>
              <div className="c-meta">
                <span className="c-dni"><IdCard size={14} className="icon-mr"/> {c.dni}</span>
                <span className="c-code"><FileText size={14} className="icon-mr"/> {c.code}</span>
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  };
  return (
    <ErrorBoundary>
      <div className="info-tab-formal">
        <div className="info-header-formal">
          <Info size={32} className="info-icon-formal" />
          <div className="info-header-text">
            <h3>Proceso de Elecciones Generales 2026-2027</h3>
            <p>Conozca las propuestas institucionales, los problemas que buscan resolver y las listas oficiales de candidatos de cada agrupación.</p>
          </div>
        </div>

        <div className="formal-party-list">
          {partiesData ? Object.values(partiesData).sort((a,b) => (a.listNumber || '').localeCompare(b.listNumber || '')).map((party) => (
            <div key={party.id} className="formal-party-card">
              <div className="formal-party-main">
                <div className="formal-party-logo">
                  <img src={`/logos/${party.id}.jpg`} alt={`Logo ${party.name}`} />
                </div>
                <div className="formal-party-summary">
                  <span className={`formal-tag ${getPartyTagClass(party.id)}`}>LISTA {party.listNumber}</span>
                  <h4>{party.name}</h4>
                  <p className="formal-motto">{party.motto}</p>
                  <button 
                    className="btn-expand-info"
                    onClick={() => toggleExpand(party.id)}
                  >
                    {expandedParty === party.id ? (
                      <><ChevronUp size={16} className="icon-mr" /> Ocultar Información</>
                    ) : (
                      <><ChevronDown size={16} className="icon-mr" /> Ver Propuestas y Candidatos</>
                    )}
                  </button>
                </div>
              </div>
              
              {expandedParty === party.id && (
                <div className="formal-party-details">
                  <div className="party-subtabs">
                    <button 
                      className={`subtab-btn ${activeSubTab[party.id] === 'plan' ? 'active' : ''}`}
                      onClick={() => setActiveSubTab(prev => ({ ...prev, [party.id]: 'plan' }))}
                    >
                      <BookOpen size={16} className="icon-mr" /> Plan
                    </button>
                    <button 
                      className={`subtab-btn ${activeSubTab[party.id] === 'list' ? 'active' : ''}`}
                      onClick={() => setActiveSubTab(prev => ({ ...prev, [party.id]: 'list' }))}
                    >
                      <Users size={16} className="icon-mr" /> Lista de Candidatos
                    </button>
                  </div>

                  <div className="subtab-content">
                    {activeSubTab[party.id] === 'plan' && (
                      <div className="plan-content fade-in">
                        <div className="detail-section">
                          <h5><Target size={18} className="icon-mr" /> Propuesta Institucional e Información</h5>
                          <p style={{whiteSpace: 'pre-line', lineHeight: '1.6', color: '#475569'}}>{party.proposalContent}</p>
                        </div>
                      </div>
                    )}

                    {activeSubTab[party.id] === 'list' && (
                      <div className="list-content fade-in">
                        
                        <div className="candidate-type-tabs" style={{ display: 'flex', gap: '0.5rem', marginBottom: '1.5rem', flexWrap: 'wrap' }}>
                          <button 
                            className={`subtab-btn ${activeCandidateType[party.id] === 'asamblea' ? 'active' : ''}`}
                            onClick={() => setActiveCandidateType(prev => ({ ...prev, [party.id]: 'asamblea' }))}
                          >
                            Asamblea Universitaria
                          </button>
                          <button 
                            className={`subtab-btn ${activeCandidateType[party.id] === 'consejoUni' ? 'active' : ''}`}
                            onClick={() => setActiveCandidateType(prev => ({ ...prev, [party.id]: 'consejoUni' }))}
                          >
                            Consejo Universitario
                          </button>
                          <button 
                            className={`subtab-btn ${activeCandidateType[party.id] === 'consejoFac' ? 'active' : ''}`}
                            onClick={() => setActiveCandidateType(prev => ({ ...prev, [party.id]: 'consejoFac' }))}
                          >
                            Consejo de Facultad
                          </button>
                        </div>

                        {activeCandidateType[party.id] === 'asamblea' && (
                          <div className="fade-in">
                            {renderCandidateList('Asamblea Universitaria', party.asamblea, <Building size={18} />)}
                          </div>
                        )}

                        {activeCandidateType[party.id] === 'consejoUni' && (
                          <div className="fade-in">
                            {renderCandidateList('Consejo Universitario', party.consejoUni, <Building size={18} />)}
                          </div>
                        )}

                        {activeCandidateType[party.id] === 'consejoFac' && (
                          <div className="fade-in">
                            <div className="faculty-tabs" style={{ display: 'flex', gap: '0.5rem', marginBottom: '1rem', flexWrap: 'wrap' }}>
                              {party.consejoFac.map(cf => (
                                <button 
                                  key={cf.facultyName}
                                  className={`faculty-btn ${activeFaculty[party.id] === cf.facultyName ? 'active' : ''}`}
                                  style={{
                                    padding: '0.4rem 0.8rem',
                                    borderRadius: '20px',
                                    border: '1px solid #cbd5e1',
                                    background: activeFaculty[party.id] === cf.facultyName ? '#1e293b' : 'white',
                                    color: activeFaculty[party.id] === cf.facultyName ? 'white' : '#475569',
                                    fontSize: '0.85rem',
                                    cursor: 'pointer',
                                    transition: 'all 0.2s'
                                  }}
                                  onClick={() => setActiveFaculty(prev => ({ ...prev, [party.id]: cf.facultyName }))}
                                >
                                  {cf.facultyName}
                                </button>
                              ))}
                            </div>
                            
                            {party.consejoFac.map((cf, idx) => (
                              activeFaculty[party.id] === cf.facultyName ? (
                                <div key={idx} className="fade-in">
                                  {renderCandidateList(`Consejo de Facultad - ${cf.facultyName}`, cf.candidates, <Building size={18} />)}
                                </div>
                              ) : null
                            ))}
                          </div>
                        )}

                      </div>
                    )}
                  </div>
                </div>
              )}
            </div>
          )) : null}
        </div>
      </div>
    </ErrorBoundary>
  );
};

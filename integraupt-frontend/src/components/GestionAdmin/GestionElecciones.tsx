import React, { useState, useEffect } from 'react';
import { Plus, Power, FileDown, AlertTriangle, Vote, CheckCircle2, ChevronRight, Save, X, Search, Printer, Shield, Edit, Trash2 } from 'lucide-react';
import { partiesData as initialPartiesData } from '../../data/candidatos';
import type { PartyData, Candidate, FacultyCouncil } from '../../data/candidatos';

interface GestionEleccionesProps {
  onAuditLog: (message: string, detail?: string) => void;
}

export const GestionElecciones: React.FC<GestionEleccionesProps> = ({ onAuditLog }) => {
  // Replace remote API state with local CRUD state initialized from candidatos.ts
  const [elections, setElections] = useState<PartyData[]>([]);
  const [loading, setLoading] = useState(true);
  
  // Wizard States
  const [showNewForm, setShowNewForm] = useState(false);
  const [wizardStep, setWizardStep] = useState(1);
  const [isEditing, setIsEditing] = useState(false);
  const [editingId, setEditingId] = useState('');
  
  // Wizard Step 1: Datos Generales
  const [partyId, setPartyId] = useState('');
  const [partyName, setPartyName] = useState('');
  const [partyListNumber, setPartyListNumber] = useState('');
  const [partyMotto, setPartyMotto] = useState('');
  const [partyProposals, setPartyProposals] = useState('');
  const [partyPhoto, setPartyPhoto] = useState<File | null>(null);

  // Wizard Step 2: Candidatos
  const [asamblea, setAsamblea] = useState<Candidate[]>([]);
  const [consejoUniv, setConsejoUniv] = useState<Candidate[]>([]);
  const [consejoFac, setConsejoFac] = useState<FacultyCouncil[]>([]);
  
  // Candidates Form Fields
  const [candNumber, setCandNumber] = useState('');
  const [candName, setCandName] = useState('');
  const [candDni, setCandDni] = useState('');
  const [candCode, setCandCode] = useState('');
  
  // Faculty filtering for Consejo de Facultad
  const [selectedFaculty, setSelectedFaculty] = useState('');
  const [candSchool, setCandSchool] = useState('');
  
  useEffect(() => {
    // Initialize data from local mock
    setLoading(true);
    setTimeout(() => {
      setElections(Object.values(initialPartiesData));
      setLoading(false);
    }, 400);
  }, []);

  const handleExportPDF = () => {
    onAuditLog('Exportación de Estadísticas', 'PDF generado');
    window.print();
  };

  const resetForm = () => {
    setPartyId('');
    setPartyName('');
    setPartyListNumber('');
    setPartyMotto('');
    setPartyProposals('');
    setPartyPhoto(null);
    setAsamblea([]);
    setConsejoUniv([]);
    setConsejoFac([]);
    setIsEditing(false);
    setEditingId('');
    setWizardStep(1);
  };

  const handleCreateNew = () => {
    resetForm();
    setShowNewForm(true);
  };

  const handleEdit = (party: PartyData) => {
    setPartyId(party.id);
    setPartyName(party.name);
    setPartyListNumber(party.listNumber);
    setPartyMotto(party.motto);
    setPartyProposals(party.proposalContent);
    setAsamblea([...party.asamblea]);
    setConsejoUniv([...party.consejoUni]);
    setConsejoFac(JSON.parse(JSON.stringify(party.consejoFac))); // deep copy
    
    setIsEditing(true);
    setEditingId(party.id);
    setShowNewForm(true);
    setWizardStep(1);
  };

  const handleDelete = (id: string, name: string) => {
    if (window.confirm(`¿Estás seguro de eliminar la lista ${name}?`)) {
      setElections(prev => prev.filter(e => e.id !== id));
      onAuditLog('Lista eliminada', `Partido: ${name}`);
    }
  };

  const handleSaveList = () => {
    const newList: PartyData = {
      id: partyId || partyName.toLowerCase().replace(/\s+/g, '-'),
      name: partyName,
      listNumber: partyListNumber,
      motto: partyMotto,
      proposalContent: partyProposals,
      asamblea,
      consejoUni: consejoUniv,
      consejoFac
    };

    if (isEditing) {
      setElections(prev => prev.map(e => e.id === editingId ? newList : e));
      onAuditLog('Lista Actualizada', `Partido: ${partyName}`);
    } else {
      setElections(prev => [...prev, newList]);
      onAuditLog('Nueva Lista Registrada', `Partido: ${partyName}`);
    }
    
    setShowNewForm(false);
    resetForm();
  };

  const addCandidate = (type: 'asamblea' | 'univ' | 'fac') => {
    if (!candName || !candDni || !candCode || !candNumber) {
      alert("Por favor completa Número, Nombre, DNI y Código del candidato.");
      return;
    }

    const newCand: Candidate = {
      number: candNumber,
      name: candName,
      school: type === 'fac' ? candSchool : (candSchool || 'N/A'),
      dni: candDni,
      code: candCode
    };

    if (type === 'asamblea') {
      setAsamblea([...asamblea, newCand]);
    } else if (type === 'univ') {
      setConsejoUniv([...consejoUniv, newCand]);
    } else if (type === 'fac') {
      if (!selectedFaculty) {
        alert("Selecciona una facultad para el Consejo de Facultad.");
        return;
      }
      setConsejoFac(prev => {
        const existing = prev.find(f => f.facultyName === selectedFaculty);
        if (existing) {
          return prev.map(f => f.facultyName === selectedFaculty ? { ...f, candidates: [...f.candidates, newCand] } : f);
        } else {
          return [...prev, { facultyName: selectedFaculty, candidates: [newCand] }];
        }
      });
    }

    // Reset fields
    setCandNumber('');
    setCandName('');
    setCandDni('');
    setCandCode('');
    setCandSchool('');
  };

  const deleteCandidate = (type: 'asamblea' | 'univ' | 'fac', index: number, facultyName?: string) => {
    if (type === 'asamblea') {
      setAsamblea(asamblea.filter((_, i) => i !== index));
    } else if (type === 'univ') {
      setConsejoUniv(consejoUniv.filter((_, i) => i !== index));
    } else if (type === 'fac' && facultyName) {
      setConsejoFac(prev => prev.map(f => {
        if (f.facultyName === facultyName) {
          return { ...f, candidates: f.candidates.filter((_, i) => i !== index) };
        }
        return f;
      }).filter(f => f.candidates.length > 0)); // Remove faculty if empty
    }
  };

  const renderWizardStep1 = () => (
    <div className="wizard-step" style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
      <h4 style={{ color: '#1e3a8a', fontWeight: 'bold', fontSize: '1.2rem', marginBottom: '1rem' }}>1. Datos Generales de la Lista</h4>
      
      <div style={{ display: 'flex', gap: '2rem' }}>
        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <div style={{ display: 'flex', gap: '1rem' }}>
            <div style={{ flex: 1 }}>
              <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600' }}>ID Interno</label>
              <input type="text" value={partyId} onChange={e => setPartyId(e.target.value)} style={{ width: '100%', padding: '0.75rem', borderRadius: '0.5rem', border: '1px solid #cbd5e1', background: isEditing ? '#f1f5f9' : 'white' }} readOnly={isEditing} placeholder="Ej: feut-upt" />
            </div>
            <div style={{ width: '100px' }}>
              <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600' }}>Número</label>
              <input type="text" value={partyListNumber} onChange={e => setPartyListNumber(e.target.value)} style={{ width: '100%', padding: '0.75rem', borderRadius: '0.5rem', border: '1px solid #cbd5e1' }} placeholder="Ej: 01" />
            </div>
          </div>
          <div>
            <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600' }}>Nombre Representativo (Agrupación)</label>
            <input type="text" value={partyName} onChange={e => setPartyName(e.target.value)} style={{ width: '100%', padding: '0.75rem', borderRadius: '0.5rem', border: '1px solid #cbd5e1' }} placeholder="Ej: FEUT UPT" />
          </div>
          <div>
            <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600' }}>Lema</label>
            <input type="text" value={partyMotto} onChange={e => setPartyMotto(e.target.value)} style={{ width: '100%', padding: '0.75rem', borderRadius: '0.5rem', border: '1px solid #cbd5e1' }} placeholder="Ej: 'Sangre Nueva'" />
          </div>
          <div>
            <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600' }}>Plan de Gobierno (Propuestas)</label>
            <textarea value={partyProposals} onChange={e => setPartyProposals(e.target.value)} rows={6} style={{ width: '100%', padding: '0.75rem', borderRadius: '0.5rem', border: '1px solid #cbd5e1' }} placeholder="Describe las propuestas aquí..."></textarea>
          </div>
        </div>
        
        <div style={{ width: '250px' }}>
          <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600' }}>Foto del Símbolo/Logo</label>
          <div style={{ border: '2px dashed #cbd5e1', borderRadius: '0.5rem', height: '150px', display: 'flex', alignItems: 'center', justifyContent: 'center', backgroundColor: '#f8fafc', cursor: 'pointer' }} onClick={() => document.getElementById('logo-upload')?.click()}>
            {partyPhoto ? <span style={{ color: '#10b981', fontWeight: 'bold' }}>Imagen Seleccionada</span> : <span style={{ color: '#94a3b8' }}>Click para subir imagen</span>}
          </div>
          <input id="logo-upload" type="file" style={{ display: 'none' }} onChange={e => setPartyPhoto(e.target.files ? e.target.files[0] : null)} accept="image/*" />
        </div>
      </div>
      
      <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: '1rem' }}>
        <button onClick={() => setWizardStep(2)} disabled={!partyName} style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', background: '#1e3a8a', color: 'white', padding: '0.75rem 1.5rem', borderRadius: '0.5rem', border: 'none', fontWeight: 'bold', cursor: partyName ? 'pointer' : 'not-allowed', opacity: partyName ? 1 : 0.5 }}>
          Siguiente Paso <ChevronRight size={18} />
        </button>
      </div>
    </div>
  );

  const CandidateInputForm = ({ onAdd }: { onAdd: () => void }) => (
    <div style={{ display: 'grid', gridTemplateColumns: '80px 1fr 1fr 120px 120px 100px', gap: '0.5rem', marginBottom: '1rem', alignItems: 'end' }}>
      <div><label style={{fontSize:'0.8rem'}}>Núm.</label><input type="text" value={candNumber} onChange={e=>setCandNumber(e.target.value)} placeholder="01" style={{width:'100%', padding:'0.4rem', border:'1px solid #ccc', borderRadius:'4px'}} /></div>
      <div><label style={{fontSize:'0.8rem'}}>Nombre Completo</label><input type="text" value={candName} onChange={e=>setCandName(e.target.value)} placeholder="Ej: Juan Perez" style={{width:'100%', padding:'0.4rem', border:'1px solid #ccc', borderRadius:'4px'}} /></div>
      <div><label style={{fontSize:'0.8rem'}}>Escuela/Carrera</label><input type="text" value={candSchool} onChange={e=>setCandSchool(e.target.value)} placeholder="Ej: Derecho" style={{width:'100%', padding:'0.4rem', border:'1px solid #ccc', borderRadius:'4px'}} /></div>
      <div><label style={{fontSize:'0.8rem'}}>DNI</label><input type="text" value={candDni} onChange={e=>setCandDni(e.target.value)} placeholder="12345678" style={{width:'100%', padding:'0.4rem', border:'1px solid #ccc', borderRadius:'4px'}} /></div>
      <div><label style={{fontSize:'0.8rem'}}>Código UPT</label><input type="text" value={candCode} onChange={e=>setCandCode(e.target.value)} placeholder="20210000" style={{width:'100%', padding:'0.4rem', border:'1px solid #ccc', borderRadius:'4px'}} /></div>
      <button onClick={onAdd} style={{ background: '#3b82f6', color: 'white', border: 'none', padding: '0.5rem', borderRadius: '4px', cursor: 'pointer', height: '33px' }}>Añadir</button>
    </div>
  );

  const renderCandidateList = (list: Candidate[], type: 'asamblea'|'univ'|'fac', facultyName?: string) => (
    <div style={{ marginTop: '0.5rem', background: 'white', borderRadius: '4px', border: '1px solid #e2e8f0' }}>
      {list.length === 0 ? <p style={{padding:'0.5rem', color:'#94a3b8', fontSize:'0.9rem', margin:0}}>No hay candidatos registrados.</p> :
      <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '0.85rem' }}>
        <thead>
          <tr style={{ background: '#f8fafc', borderBottom: '1px solid #e2e8f0' }}>
            <th style={{ padding: '0.5rem', textAlign: 'left' }}>#</th>
            <th style={{ padding: '0.5rem', textAlign: 'left' }}>Nombre</th>
            <th style={{ padding: '0.5rem', textAlign: 'left' }}>DNI</th>
            <th style={{ padding: '0.5rem', textAlign: 'left' }}>Código</th>
            <th style={{ padding: '0.5rem', textAlign: 'left' }}>Escuela</th>
            <th style={{ padding: '0.5rem', textAlign: 'center' }}>Acción</th>
          </tr>
        </thead>
        <tbody>
          {list.map((c, i) => (
            <tr key={i} style={{ borderBottom: '1px solid #f1f5f9' }}>
              <td style={{ padding: '0.5rem', fontWeight:'bold' }}>{c.number}</td>
              <td style={{ padding: '0.5rem' }}>{c.name}</td>
              <td style={{ padding: '0.5rem', color: '#64748b' }}>{c.dni}</td>
              <td style={{ padding: '0.5rem', color: '#64748b' }}>{c.code}</td>
              <td style={{ padding: '0.5rem', color: '#64748b' }}>{c.school}</td>
              <td style={{ padding: '0.5rem', textAlign: 'center' }}>
                <button onClick={() => deleteCandidate(type, i, facultyName)} style={{ background: 'none', border: 'none', color: '#ef4444', cursor: 'pointer' }} title="Eliminar candidato"><Trash2 size={16}/></button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>}
    </div>
  );

  const renderWizardStep2 = () => (
    <div className="wizard-step" style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
      <h4 style={{ color: '#1e3a8a', fontWeight: 'bold', fontSize: '1.2rem' }}>2. Registro de Candidatos</h4>
      
      {/* Asamblea Universitaria */}
      <div style={{ background: '#f1f5f9', padding: '1rem', borderRadius: '0.5rem' }}>
        <h5 style={{ fontWeight: 'bold', marginBottom: '1rem', color:'#334155' }}>Asamblea Universitaria</h5>
        <CandidateInputForm onAdd={() => addCandidate('asamblea')} />
        {renderCandidateList(asamblea, 'asamblea')}
      </div>

      {/* Consejo Universitario */}
      <div style={{ background: '#f1f5f9', padding: '1rem', borderRadius: '0.5rem' }}>
        <h5 style={{ fontWeight: 'bold', marginBottom: '1rem', color:'#334155' }}>Consejo Universitario</h5>
        <CandidateInputForm onAdd={() => addCandidate('univ')} />
        {renderCandidateList(consejoUniv, 'univ')}
      </div>

      {/* Consejo de Facultad */}
      <div style={{ background: '#e0e7ff', padding: '1rem', borderRadius: '0.5rem', border: '1px solid #c7d2fe' }}>
        <h5 style={{ fontWeight: 'bold', color: '#3730a3', marginBottom: '0.5rem' }}>Consejo de Facultad</h5>
        
        <div style={{ marginBottom: '1rem' }}>
          <select value={selectedFaculty} onChange={e => setSelectedFaculty(e.target.value)} style={{ padding: '0.5rem', borderRadius: '0.25rem', border: '1px solid #cbd5e1', fontWeight: 'bold', width: '300px' }}>
            <option value="">-- Seleccione Facultad (Filtro) --</option>
            <option value="FAING">Facultad de Ingeniería (FAING)</option>
            <option value="FADE">Facultad de Derecho (FADE)</option>
            <option value="FAU">Facultad de Arquitectura (FAU)</option>
            <option value="FACEM">Facultad de Ciencias Empresariales (FACEM)</option>
            <option value="FACS">Facultad de Ciencias de la Salud (FACS)</option>
          </select>
        </div>

        {selectedFaculty && (
          <div style={{ background: 'white', padding: '1rem', borderRadius: '4px', border: '1px dashed #a5b4fc' }}>
            <p style={{fontSize:'0.85rem', color:'#4f46e5', marginBottom:'0.5rem', fontWeight:'bold'}}>Añadiendo a: {selectedFaculty}</p>
            <CandidateInputForm onAdd={() => addCandidate('fac')} />
          </div>
        )}
        
        <div style={{ marginTop: '1rem' }}>
          {consejoFac.map((fac, idx) => (
            <div key={idx} style={{ marginBottom: '1rem' }}>
              <h6 style={{ fontWeight: 'bold', color: '#4338ca', margin: '0 0 0.5rem 0' }}>{fac.facultyName}</h6>
              {renderCandidateList(fac.candidates, 'fac', fac.facultyName)}
            </div>
          ))}
        </div>
      </div>

      <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: '1rem' }}>
        <button onClick={() => setWizardStep(1)} style={{ background: '#f1f5f9', color: '#475569', padding: '0.75rem 1.5rem', borderRadius: '0.5rem', border: '1px solid #cbd5e1', fontWeight: 'bold', cursor: 'pointer' }}>
          Atrás
        </button>
        <button onClick={handleSaveList} style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', background: '#10b981', color: 'white', padding: '0.75rem 1.5rem', borderRadius: '0.5rem', border: 'none', fontWeight: 'bold', cursor: 'pointer' }}>
          <Save size={18} /> {isEditing ? 'Guardar Cambios' : 'Finalizar Registro'}
        </button>
      </div>
    </div>
  );

  return (
    <div style={{ padding: '1rem' }}>
      <style>{`
        @media print {
          body * { visibility: hidden; }
          .print-section, .print-section * { visibility: visible; }
          .print-section { position: absolute; left: 0; top: 0; width: 100%; }
          .no-print { display: none !important; }
        }
      `}</style>
      
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '2rem' }} className="no-print">
        <div>
          <h2 style={{ fontSize: '1.8rem', fontWeight: 'bold', color: '#1e293b', margin: 0, display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
            <Shield className="text-blue-900" /> Control de Procesos Electorales
          </h2>
          <p style={{ color: '#64748b', margin: 0, marginTop: '0.5rem' }}>Administra listas, planes de gobierno y candidatos del sistema.</p>
        </div>
        <div style={{ display: 'flex', gap: '1rem' }}>
          <button onClick={handleExportPDF} style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', background: '#f1f5f9', color: '#1e3a8a', border: '1px solid #cbd5e1', padding: '0.75rem 1.25rem', borderRadius: '0.5rem', fontWeight: 'bold', cursor: 'pointer' }}>
            <Printer size={18} /> Exportar Reporte
          </button>
          <button onClick={handleCreateNew} style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', background: 'linear-gradient(90deg, #1e40af 0%, #1e3a8a 100%)', color: 'white', border: 'none', padding: '0.75rem 1.25rem', borderRadius: '0.5rem', fontWeight: 'bold', cursor: 'pointer', boxShadow: '0 4px 6px -1px rgba(30, 58, 138, 0.4)' }}>
            <Plus size={18} /> Registrar Nueva Lista
          </button>
        </div>
      </div>

      {showNewForm && (
        <div className="no-print" style={{ background: 'white', padding: '2rem', borderRadius: '1rem', boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)', marginBottom: '2rem', border: '1px solid #e2e8f0', position: 'relative' }}>
          <button onClick={() => { setShowNewForm(false); resetForm(); }} style={{ position: 'absolute', top: '1rem', right: '1rem', background: 'none', border: 'none', cursor: 'pointer', color: '#94a3b8' }}>
            <X size={24} />
          </button>
          {wizardStep === 1 ? renderWizardStep1() : renderWizardStep2()}
        </div>
      )}

      <div className="print-section" style={{ background: 'white', borderRadius: '1rem', boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)', overflow: 'hidden', border: '1px solid #e2e8f0' }}>
        <div style={{ padding: '1.5rem', borderBottom: '1px solid #e2e8f0', background: '#f8fafc' }}>
          <h3 style={{ margin: 0, color: '#1e293b', fontWeight: 'bold' }}>Listas Políticas y Agrupaciones Inscritas</h3>
        </div>
        
        {loading ? (
          <div style={{ padding: '3rem', textAlign: 'center', color: '#64748b' }}>Cargando datos electorales...</div>
        ) : (
          <table style={{ width: '100%', borderCollapse: 'collapse', textAlign: 'left' }}>
            <thead>
              <tr style={{ background: '#f1f5f9', color: '#475569', fontSize: '0.9rem', textTransform: 'uppercase' }}>
                <th style={{ padding: '1rem 1.5rem', borderBottom: '2px solid #e2e8f0' }}>Lista</th>
                <th style={{ padding: '1rem 1.5rem', borderBottom: '2px solid #e2e8f0' }}>Agrupación</th>
                <th style={{ padding: '1rem 1.5rem', borderBottom: '2px solid #e2e8f0' }}>Lema</th>
                <th style={{ padding: '1rem 1.5rem', borderBottom: '2px solid #e2e8f0' }}>Total Candidatos</th>
                <th style={{ padding: '1rem 1.5rem', borderBottom: '2px solid #e2e8f0', textAlign: 'center' }} className="no-print">Acciones</th>
              </tr>
            </thead>
            <tbody>
              {elections.map((elec, idx) => {
                const totalCand = elec.asamblea.length + elec.consejoUni.length + elec.consejoFac.reduce((acc, f) => acc + f.candidates.length, 0);
                return (
                <tr key={elec.id} style={{ borderBottom: '1px solid #e2e8f0', background: idx % 2 === 0 ? 'white' : '#f8fafc' }}>
                  <td style={{ padding: '1rem 1.5rem', fontWeight: 'bold', color: '#0f172a', fontSize: '1.2rem' }}>{elec.listNumber}</td>
                  <td style={{ padding: '1rem 1.5rem', fontWeight: 'bold', color: '#1e3a8a' }}>{elec.name}</td>
                  <td style={{ padding: '1rem 1.5rem', color: '#64748b', fontStyle: 'italic' }}>{elec.motto}</td>
                  <td style={{ padding: '1rem 1.5rem', color: '#475569' }}>
                    <span style={{ display: 'inline-block', padding: '0.25rem 0.75rem', borderRadius: '9999px', background: '#e0e7ff', color: '#4338ca', fontSize: '0.85rem', fontWeight: 'bold' }}>
                      {totalCand} inscritos
                    </span>
                  </td>
                  <td style={{ padding: '1rem 1.5rem', textAlign: 'center' }} className="no-print">
                    <button onClick={() => handleEdit(elec)} style={{ background: 'none', border: 'none', color: '#3b82f6', cursor: 'pointer', marginRight: '0.5rem' }} title="Editar Lista">
                      <Edit size={20} />
                    </button>
                    <button onClick={() => handleDelete(elec.id, elec.name)} style={{ background: 'none', border: 'none', color: '#ef4444', cursor: 'pointer' }} title="Eliminar Lista">
                      <Trash2 size={20} />
                    </button>
                  </td>
                </tr>
              )})}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
};

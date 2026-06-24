import { getOlimpiadasApiUrl } from '../../../utils/apiConfig';
import type {
  DisciplinaCatalogo,
  Edicion,
  EdicionDisciplina,
  FacultadOption,
  InscripcionAdmin,
  Resultado,
  TablaPosicion,
} from './types';

const buildUrl = (path: string): string => getOlimpiadasApiUrl(`/api${path}`);

const parseErrorMessage = async (response: Response): Promise<string> => {
  try {
    const data = await response.json();
    if (typeof data?.message === 'string') {
      return data.message;
    }
  } catch {
    // Ignorado, se usa el mensaje por defecto
  }
  return 'Ocurrió un error inesperado al comunicarse con el servicio de olimpiadas.';
};

const request = async <T,>(path: string, init?: RequestInit): Promise<T> => {
  const response = await fetch(buildUrl(path), {
    headers: { 'Content-Type': 'application/json' },
    ...init,
  });
  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }
  return response.json();
};

export const fetchFacultades = (): Promise<FacultadOption[]> => request('/catalogos/facultades');

export const fetchDisciplinas = (): Promise<DisciplinaCatalogo[]> => request('/disciplinas');

export const crearDisciplina = (payload: Record<string, unknown>): Promise<DisciplinaCatalogo> =>
  request('/disciplinas', { method: 'POST', body: JSON.stringify(payload) });

export const actualizarDisciplina = (id: number, payload: Record<string, unknown>): Promise<DisciplinaCatalogo> =>
  request(`/disciplinas/${id}`, { method: 'PUT', body: JSON.stringify(payload) });

export const cambiarEstadoDisciplina = (id: number, estado: 'activa' | 'inactiva'): Promise<DisciplinaCatalogo> =>
  request(`/disciplinas/${id}/estado`, { method: 'PATCH', body: JSON.stringify({ estado }) });

export const fetchEdiciones = (): Promise<Edicion[]> => request('/ediciones');

export const crearEdicion = (payload: Record<string, unknown>): Promise<Edicion> =>
  request('/ediciones', { method: 'POST', body: JSON.stringify(payload) });

export const cambiarEstadoEdicion = (id: number, estado: string): Promise<Edicion> =>
  request(`/ediciones/${id}/estado`, { method: 'PATCH', body: JSON.stringify({ estado }) });

export const abrirInscripcionEdicion = (id: number, fechaCierreInscripcion?: string): Promise<Edicion> =>
  request(`/ediciones/${id}/inscripcion/abrir`, {
    method: 'PATCH',
    body: JSON.stringify({ fechaCierreInscripcion: fechaCierreInscripcion || null }),
  });

export const cerrarInscripcionEdicion = (id: number): Promise<Edicion> =>
  request(`/ediciones/${id}/inscripcion/cerrar`, { method: 'PATCH' });

export const fetchDisciplinasDeEdicion = (edicionId: number): Promise<EdicionDisciplina[]> =>
  request(`/ediciones/${edicionId}/disciplinas`);

export const vincularDisciplinaAEdicion = (
  edicionId: number,
  payload: Record<string, unknown>,
): Promise<EdicionDisciplina> =>
  request(`/ediciones/${edicionId}/disciplinas`, { method: 'POST', body: JSON.stringify(payload) });

export const cambiarEstadoVinculo = (
  edicionDisciplinaId: number,
  estado: 'activa' | 'inactiva',
): Promise<EdicionDisciplina> =>
  request(`/edicion-disciplinas/${edicionDisciplinaId}/estado`, {
    method: 'PATCH',
    body: JSON.stringify({ estado }),
  });

export const fetchParticipantes = (edicionDisciplinaId: number): Promise<InscripcionAdmin[]> =>
  request(`/edicion-disciplinas/${edicionDisciplinaId}/participantes`);

export const fetchFixture = (edicionDisciplinaId: number): Promise<Resultado[]> =>
  request(`/edicion-disciplinas/${edicionDisciplinaId}/fixture`);

export const fetchTabla = (edicionDisciplinaId: number): Promise<TablaPosicion[]> =>
  request(`/edicion-disciplinas/${edicionDisciplinaId}/tabla`);

export const crearResultado = (payload: Record<string, unknown>): Promise<Resultado> =>
  request('/resultados', { method: 'POST', body: JSON.stringify(payload) });

export const actualizarResultado = (id: number, payload: Record<string, unknown>): Promise<Resultado> =>
  request(`/resultados/${id}`, { method: 'PUT', body: JSON.stringify(payload) });

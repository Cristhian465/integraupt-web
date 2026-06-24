import { getPsicologiaApiUrl } from '../../../utils/apiConfig';
import type { CitaAdminResponse, CitaFiltros, PsicologoAdminResponse } from './types';

const buildUrl = (path: string): string => getPsicologiaApiUrl(`/api${path}`);

const parseErrorMessage = async (response: Response): Promise<string> => {
  try {
    const data = await response.json();
    if (typeof data?.error === 'string') {
      return data.error;
    }
    if (typeof data?.message === 'string') {
      return data.message;
    }
  } catch {
    // Ignorado, se usa el mensaje por defecto
  }

  return 'Ocurrió un error inesperado al comunicarse con el servicio de psicología.';
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

export const fetchCitasAdmin = (filtros: CitaFiltros = {}): Promise<CitaAdminResponse[]> => {
  const params = new URLSearchParams();
  if (filtros.estado) params.set('estado', filtros.estado);
  if (filtros.fecha) params.set('fecha', filtros.fecha);
  if (filtros.psicologoId) params.set('psicologoId', String(filtros.psicologoId));

  const query = params.toString();
  return request(`/admin/citas${query ? `?${query}` : ''}`);
};

export const cambiarEstadoCita = (id: number, estado: string): Promise<CitaAdminResponse> =>
  request(`/admin/citas/${id}/estado`, { method: 'PATCH', body: JSON.stringify({ estado }) });

export const fetchPsicologosAdmin = (): Promise<PsicologoAdminResponse[]> => request('/admin/psicologos');

export const crearPsicologo = (payload: { nombre: string; especialidad?: string }): Promise<PsicologoAdminResponse> =>
  request('/admin/psicologos', { method: 'POST', body: JSON.stringify(payload) });

export const actualizarPsicologo = (
  id: number,
  payload: Partial<{ nombre: string; especialidad: string; estado: boolean }>,
): Promise<PsicologoAdminResponse> =>
  request(`/admin/psicologos/${id}`, { method: 'PATCH', body: JSON.stringify(payload) });

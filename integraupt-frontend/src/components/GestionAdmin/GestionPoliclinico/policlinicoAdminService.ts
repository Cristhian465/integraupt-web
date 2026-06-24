import { getPoliclinicoApiUrl } from '../../../utils/apiConfig';
import type {
  CitaAdminResponse,
  CitaFiltros,
  MedicoAdminResponse,
  TipoAtencionAdminResponse,
} from './types';

const buildUrl = (path: string): string => getPoliclinicoApiUrl(`/api${path}`);

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

  return 'Ocurrió un error inesperado al comunicarse con el servicio del policlínico.';
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
  if (filtros.medicoId) params.set('medicoId', String(filtros.medicoId));
  if (filtros.tipoAtencionId) params.set('tipoAtencionId', String(filtros.tipoAtencionId));

  const query = params.toString();
  return request(`/admin/citas${query ? `?${query}` : ''}`);
};

export const cambiarEstadoCita = (id: number, estado: string): Promise<CitaAdminResponse> =>
  request(`/admin/citas/${id}/estado`, { method: 'PATCH', body: JSON.stringify({ estado }) });

export const fetchTiposAtencionAdmin = (): Promise<TipoAtencionAdminResponse[]> => request('/admin/tipos-atencion');

export const crearTipoAtencion = (nombre: string): Promise<TipoAtencionAdminResponse> =>
  request('/admin/tipos-atencion', { method: 'POST', body: JSON.stringify({ nombre }) });

export const actualizarTipoAtencion = (
  id: number,
  payload: Partial<{ nombre: string; estado: boolean }>,
): Promise<TipoAtencionAdminResponse> =>
  request(`/admin/tipos-atencion/${id}`, { method: 'PATCH', body: JSON.stringify(payload) });

export const fetchMedicosAdmin = (): Promise<MedicoAdminResponse[]> => request('/admin/medicos');

export const crearMedico = (payload: { nombre: string; tiposAtencionIds: number[] }): Promise<MedicoAdminResponse> =>
  request('/admin/medicos', { method: 'POST', body: JSON.stringify(payload) });

export const actualizarMedico = (
  id: number,
  payload: Partial<{ nombre: string; estado: boolean; tiposAtencionIds: number[] }>,
): Promise<MedicoAdminResponse> =>
  request(`/admin/medicos/${id}`, { method: 'PATCH', body: JSON.stringify(payload) });

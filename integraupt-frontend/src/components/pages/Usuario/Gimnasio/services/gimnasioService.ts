import { getGimnasioApiUrl } from '../../../../../utils/apiConfig';
import type { AsistenciaGimnasioResponse, EstadoSesionResponse } from './types';

const buildGimnasioUrl = (path: string): string => getGimnasioApiUrl(`/api${path}`);

const parseErrorMessage = async (response: Response): Promise<string> => {
  try {
    const data = await response.json();
    if (typeof data?.error === 'string') {
      return data.error;
    }
    if (typeof data?.message === 'string') {
      return data.message;
    }
  } catch (error) {
    // Ignored
  }
  return 'Ocurrió un error inesperado al comunicarse con el servicio de gimnasio.';
};

export const registrarIngreso = async (usuarioId: number): Promise<AsistenciaGimnasioResponse> => {
  const response = await fetch(buildGimnasioUrl('/ingreso'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ usuarioId }),
  });
  if (!response.ok) throw new Error(await parseErrorMessage(response));
  return response.json();
};

export const registrarSalida = async (usuarioId: number): Promise<AsistenciaGimnasioResponse> => {
  const response = await fetch(buildGimnasioUrl('/salida'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ usuarioId }),
  });
  if (!response.ok) throw new Error(await parseErrorMessage(response));
  return response.json();
};

export const obtenerEstadoSesion = async (usuarioId: number, signal?: AbortSignal): Promise<EstadoSesionResponse> => {
  const response = await fetch(buildGimnasioUrl(`/estado/${encodeURIComponent(usuarioId)}`), { signal });
  if (!response.ok) throw new Error(await parseErrorMessage(response));
  return response.json();
};

export const obtenerAsistencias = async (signal?: AbortSignal): Promise<AsistenciaGimnasioResponse[]> => {
  const response = await fetch(buildGimnasioUrl('/asistencias'), { signal });
  if (!response.ok) throw new Error(await parseErrorMessage(response));
  return response.json();
};

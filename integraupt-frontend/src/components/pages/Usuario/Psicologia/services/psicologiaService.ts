import { getPsicologiaApiUrl } from '../../../../../utils/apiConfig';
import type {
  BloqueDisponibleResponse,
  CitaPsicologiaResponse,
  PsicologoResponse,
  RegistrarCitaPayload,
} from '../types';

const buildPsicologiaUrl = (path: string): string => getPsicologiaApiUrl(`/api${path}`);

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
    // Ignorado, se usa el mensaje por defecto
  }

  return 'Ocurrió un error inesperado al comunicarse con el servicio de psicología.';
};

export const obtenerPsicologos = async (signal?: AbortSignal): Promise<PsicologoResponse[]> => {
  const response = await fetch(buildPsicologiaUrl('/psicologos'), { signal });

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

export const obtenerBloquesDisponibles = async (
  psicologoId: number,
  fecha: string,
  signal?: AbortSignal,
): Promise<BloqueDisponibleResponse[]> => {
  const response = await fetch(
    buildPsicologiaUrl(`/psicologos/${encodeURIComponent(psicologoId)}/bloques-disponibles?fecha=${encodeURIComponent(fecha)}`),
    { signal },
  );

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

export const obtenerCitasPorUsuario = async (
  usuarioId: number,
  signal?: AbortSignal,
): Promise<CitaPsicologiaResponse[]> => {
  const response = await fetch(
    buildPsicologiaUrl(`/citas?usuarioId=${encodeURIComponent(usuarioId)}`),
    { signal },
  );

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

export const registrarCita = async (
  payload: RegistrarCitaPayload,
): Promise<CitaPsicologiaResponse> => {
  const response = await fetch(buildPsicologiaUrl('/citas'), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

export const cancelarCita = async (
  citaId: number,
  usuarioId: number,
): Promise<CitaPsicologiaResponse> => {
  const response = await fetch(
    buildPsicologiaUrl(`/citas/${encodeURIComponent(citaId)}?usuarioId=${encodeURIComponent(usuarioId)}`),
    { method: 'DELETE' },
  );

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

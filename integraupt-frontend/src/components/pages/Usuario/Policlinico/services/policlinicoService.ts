import { getPoliclinicoApiUrl } from '../../../../../utils/apiConfig';
import type {
  BloqueDisponibleResponse,
  CitaPoliclinicoResponse,
  MedicoResponse,
  RegistrarCitaPayload,
  TipoAtencionResponse,
} from '../types';

const buildPoliclinicoUrl = (path: string): string => getPoliclinicoApiUrl(`/api${path}`);

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

  return 'Ocurrió un error inesperado al comunicarse con el servicio del policlínico.';
};

export const obtenerTiposAtencion = async (signal?: AbortSignal): Promise<TipoAtencionResponse[]> => {
  const response = await fetch(buildPoliclinicoUrl('/tipos-atencion'), { signal });

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

export const obtenerMedicosPorTipoAtencion = async (
  tipoAtencionId: number,
  signal?: AbortSignal,
): Promise<MedicoResponse[]> => {
  const response = await fetch(
    buildPoliclinicoUrl(`/medicos?tipoAtencionId=${encodeURIComponent(tipoAtencionId)}`),
    { signal },
  );

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

export const obtenerBloquesDisponibles = async (
  medicoId: number,
  fecha: string,
  signal?: AbortSignal,
): Promise<BloqueDisponibleResponse[]> => {
  const response = await fetch(
    buildPoliclinicoUrl(`/medicos/${encodeURIComponent(medicoId)}/bloques-disponibles?fecha=${encodeURIComponent(fecha)}`),
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
): Promise<CitaPoliclinicoResponse[]> => {
  const response = await fetch(
    buildPoliclinicoUrl(`/citas?usuarioId=${encodeURIComponent(usuarioId)}`),
    { signal },
  );

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

export const registrarCita = async (
  payload: RegistrarCitaPayload,
): Promise<CitaPoliclinicoResponse> => {
  const response = await fetch(buildPoliclinicoUrl('/citas'), {
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
): Promise<CitaPoliclinicoResponse> => {
  const response = await fetch(
    buildPoliclinicoUrl(`/citas/${encodeURIComponent(citaId)}?usuarioId=${encodeURIComponent(usuarioId)}`),
    { method: 'DELETE' },
  );

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

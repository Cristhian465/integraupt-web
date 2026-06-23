import { getOlimpiadasApiUrl } from '../../../../../utils/apiConfig';
import type {
  EdicionDisciplinaResponse,
  EdicionResponse,
  InscripcionMiaResponse,
  ResultadoResponse,
  TablaPosicionResponse,
} from '../types';

const buildOlimpiadasUrl = (path: string): string => getOlimpiadasApiUrl(`/api${path}`);

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

export const obtenerEdiciones = async (signal?: AbortSignal): Promise<EdicionResponse[]> => {
  const response = await fetch(buildOlimpiadasUrl('/ediciones'), { signal });
  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }
  return response.json();
};

export const obtenerEdicionActual = async (signal?: AbortSignal): Promise<EdicionResponse | null> => {
  const response = await fetch(buildOlimpiadasUrl('/ediciones/actual'), { signal });
  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }
  return response.json();
};

export const obtenerDisciplinasDeEdicion = async (
  edicionId: number,
  signal?: AbortSignal,
): Promise<EdicionDisciplinaResponse[]> => {
  const response = await fetch(buildOlimpiadasUrl(`/ediciones/${edicionId}/disciplinas`), { signal });
  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }
  return response.json();
};

export const obtenerFixture = async (
  edicionDisciplinaId: number,
  signal?: AbortSignal,
): Promise<ResultadoResponse[]> => {
  const response = await fetch(buildOlimpiadasUrl(`/edicion-disciplinas/${edicionDisciplinaId}/fixture`), { signal });
  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }
  return response.json();
};

export const obtenerTablaPosiciones = async (
  edicionDisciplinaId: number,
  signal?: AbortSignal,
): Promise<TablaPosicionResponse[]> => {
  const response = await fetch(buildOlimpiadasUrl(`/edicion-disciplinas/${edicionDisciplinaId}/tabla`), { signal });
  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }
  return response.json();
};

export const obtenerMisInscripciones = async (
  usuarioId: number,
  signal?: AbortSignal,
): Promise<InscripcionMiaResponse[]> => {
  const response = await fetch(
    buildOlimpiadasUrl(`/inscripciones/mias?usuarioId=${encodeURIComponent(usuarioId)}`),
    { signal },
  );
  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }
  return response.json();
};

export const inscribirse = async (payload: {
  edicionDisciplinaId: number;
  usuarioId: number;
  observaciones?: string;
}): Promise<InscripcionMiaResponse> => {
  const response = await fetch(buildOlimpiadasUrl('/inscripciones'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }
  return response.json();
};

export const cancelarInscripcion = async (inscripcionId: number, usuarioId: number): Promise<void> => {
  const response = await fetch(buildOlimpiadasUrl(`/inscripciones/${inscripcionId}/cancelar`), {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ usuarioId }),
  });
  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }
};

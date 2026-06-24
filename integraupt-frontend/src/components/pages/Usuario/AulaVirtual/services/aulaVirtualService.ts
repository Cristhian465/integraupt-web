import { getMoodleApiUrl } from '../../../../../utils/apiConfig';
import type {
  ConectarMoodlePayload,
  ConectarMoodleResponse,
  ConfirmarSsoPayload,
  CursoMoodleResponse,
  EstadoMoodleResponse,
  EventoMoodleResponse,
  IniciarSsoResponse,
  NotaMoodleResponse,
} from '../types';

const buildMoodleUrl = (path: string): string => getMoodleApiUrl(`/api${path}`);

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

  return 'Ocurrió un error inesperado al comunicarse con el Aula Virtual.';
};

export const obtenerEstado = async (
  usuarioId: number,
  signal?: AbortSignal,
): Promise<EstadoMoodleResponse> => {
  const response = await fetch(buildMoodleUrl(`/estado?usuarioId=${encodeURIComponent(usuarioId)}`), { signal });

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

export const conectarCuenta = async (
  payload: ConectarMoodlePayload,
): Promise<ConectarMoodleResponse> => {
  const response = await fetch(buildMoodleUrl('/conectar'), {
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

export const iniciarSso = async (
  usuarioId: number,
  signal?: AbortSignal,
): Promise<IniciarSsoResponse> => {
  const response = await fetch(buildMoodleUrl(`/sso/iniciar?usuarioId=${encodeURIComponent(usuarioId)}`), { signal });

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

export const confirmarSso = async (
  payload: ConfirmarSsoPayload,
): Promise<ConectarMoodleResponse> => {
  const response = await fetch(buildMoodleUrl('/sso/confirmar'), {
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

export const desconectarCuenta = async (usuarioId: number): Promise<void> => {
  const response = await fetch(buildMoodleUrl(`/desconectar?usuarioId=${encodeURIComponent(usuarioId)}`), {
    method: 'DELETE',
  });

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }
};

export const obtenerCursos = async (
  usuarioId: number,
  signal?: AbortSignal,
): Promise<CursoMoodleResponse[]> => {
  const response = await fetch(buildMoodleUrl(`/cursos?usuarioId=${encodeURIComponent(usuarioId)}`), { signal });

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

export const obtenerNotas = async (
  usuarioId: number,
  cursoId: number,
  signal?: AbortSignal,
): Promise<NotaMoodleResponse[]> => {
  const response = await fetch(
    buildMoodleUrl(`/cursos/${encodeURIComponent(cursoId)}/notas?usuarioId=${encodeURIComponent(usuarioId)}`),
    { signal },
  );

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

export const obtenerEventos = async (
  usuarioId: number,
  signal?: AbortSignal,
): Promise<EventoMoodleResponse[]> => {
  const response = await fetch(buildMoodleUrl(`/eventos?usuarioId=${encodeURIComponent(usuarioId)}`), { signal });

  if (!response.ok) {
    throw new Error(await parseErrorMessage(response));
  }

  return response.json();
};

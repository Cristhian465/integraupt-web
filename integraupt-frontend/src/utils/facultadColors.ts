const FACULTAD_COLORS: Record<string, string> = {
  FAING: '#2563eb',
  'FACULTAD DE INGENIERÍA': '#2563eb',
  FADE: '#b91c1c',
  'FACULTAD DE DERECHO Y CIENCIAS POLÍTICAS': '#b91c1c',
  FACEM: '#15803d',
  'FACULTAD DE CIENCIAS EMPRESARIALES': '#15803d',
  FAEDCOH: '#a16207',
  'FACULTAD DE EDUCACIÓN, CIENCIAS DE LA COMUNICACIÓN': '#a16207',
  FACSA: '#0f766e',
  'FACULTAD DE CIENCIAS DE LA SALUD': '#0f766e',
  FAU: '#7c3aed',
  'FACULTAD DE ARQUITECTURA Y URBANISMO': '#7c3aed',
};

const DEFAULT_COLOR = '#64748b';

export const facultadColor = (abreviaturaOrNombre?: string | null): string => {
  if (!abreviaturaOrNombre) return DEFAULT_COLOR;
  return FACULTAD_COLORS[abreviaturaOrNombre.trim().toUpperCase()] ?? DEFAULT_COLOR;
};

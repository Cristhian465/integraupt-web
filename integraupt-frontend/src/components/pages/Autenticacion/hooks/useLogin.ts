import { useState, useCallback, useEffect } from 'react';
import type { LoginType, BackendSession } from '../types';
import { generateCaptcha } from '../captchaUtils';
import { autenticacionService } from '../services/autenticacionService';

export const useLogin = (onLoginSuccess?: (session: BackendSession) => void) => {
  const [selectedType, setSelectedType] = useState<LoginType | null>(null);
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [captcha, setCaptcha] = useState<string>(() => generateCaptcha());
  const [captchaInput, setCaptchaInput] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [infoMessage, setInfoMessage] = useState<string | null>(null);

  const refreshCaptcha = useCallback(() => {
    setCaptcha(generateCaptcha());
    setCaptchaInput('');
  }, []);

  const handleSelectType = (type: LoginType) => {
    setSelectedType(type);
    setIdentifier('');
    setPassword('');
    setCaptcha(generateCaptcha());
    setCaptchaInput('');
    setError(null);
    setInfoMessage(null);
  };

  const handleBack = () => {
    setSelectedType(null);
    setIdentifier('');
    setPassword('');
    setCaptcha(generateCaptcha());
    setCaptchaInput('');
    setError(null);
    setInfoMessage(null);
  };

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (!selectedType) return;

    if (captchaInput.trim().toUpperCase() !== captcha) {
      setError('El código de seguridad es incorrecto. Intenta nuevamente.');
      refreshCaptcha();
      return;
    }

    setLoading(true);
    setError(null);
    setInfoMessage(null);

    try {
      const data = await autenticacionService.login(identifier, password, selectedType);

      if (!data?.success) {
        throw new Error(data?.message ?? 'Credenciales inválidas.');
      }

      const perfil = data.perfil;
      const token = data.token?.trim();

      if (!perfil) {
        throw new Error('No se recibió el perfil del usuario.');
      }

      if (!token) {
        throw new Error('No se recibió el token de sesión. Intenta nuevamente.');
      }

      const session: BackendSession = { token, perfil };
      setInfoMessage(data.message ?? 'Inicio de sesión exitoso. Redirigiendo...');

      setTimeout(() => {
        if (onLoginSuccess) onLoginSuccess(session);
      }, 1000);
    } catch (err) {
      const message =
        err instanceof Error
          ? err.message
          : 'Ocurrió un error inesperado durante el inicio de sesión.';
      setError(message);
      refreshCaptcha();
    } finally {
      setLoading(false);
    }
  };

  const handleGoogleSignIn = useCallback(() => {
    console.log('Redirigiendo a autenticación con Google...');
    setLoading(true);
    setError(null);
    window.location.href = 'http://localhost:8081/api/auth/google/redirect';
  }, []);

  useEffect(() => {
    const handleGoogleCallback = async () => {
      const urlParams = new URLSearchParams(window.location.search);
      const token = urlParams.get('token');
      const authError = urlParams.get('error');
      
      if (authError) {
        let errorMsg = 'Error en la autenticación con Google.';
        if (authError === 'unauthorized_google') {
          errorMsg = 'Tu cuenta de Google no está autorizada o no tienes perfil activo.';
        }
        setError(errorMsg);
        window.history.replaceState({}, '', window.location.pathname);
        return;
      }

      if (token) {
        setLoading(true);
        setError(null);

        try {
          const data = await autenticacionService.validateToken(token);
          
          if (!data?.success || !data?.perfil) {
            throw new Error(data?.message || 'Error validando sesión de Google');
          }

          const session: BackendSession = {
            token,
            perfil: data.perfil
          };

          setInfoMessage('Autenticación con Google exitosa. Redirigiendo...');
          
          setTimeout(() => {
            if (onLoginSuccess) onLoginSuccess(session);
          }, 1000);

        } catch (err) {
          const message = err instanceof Error 
            ? err.message 
            : 'Error durante la validación del inicio de sesión con Google';
          setError(message);
        } finally {
          setLoading(false);
          // Limpiar URL
          window.history.replaceState({}, '', window.location.pathname);
        }
      }
    };

    handleGoogleCallback();
  }, [onLoginSuccess]);

  return {
    selectedType,
    identifier,
    password,
    captcha,
    captchaInput,
    error,
    loading,
    infoMessage,
    setIdentifier,
    setPassword,
    setCaptchaInput,
    refreshCaptcha,
    handleSelectType,
    handleBack,
    handleSubmit,
    handleGoogleSignIn,
  };
};
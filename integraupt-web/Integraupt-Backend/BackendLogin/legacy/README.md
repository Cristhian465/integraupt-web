# login-sad-php

API de autenticación traducida de Spring Boot a PHP puro.

## Estructura del proyecto

```
login-sad-php/
├── autoload.php              # PSR-style autoloader manual
├── config/
│   ├── database.php          # Conexión PDO a MariaDB
│   ├── cors.php              # Headers CORS
│   └── router.php            # Tabla de rutas
├── src/
│   ├── Controller/
│   │   └── AuthController.php
│   ├── Dto/
│   │   ├── LoginRequest.php
│   │   ├── LoginResponse.php
│   │   ├── LogoutRequest.php
│   │   ├── PerfilResponse.php
│   │   └── SessionValidationRequest.php
│   ├── Repository/
│   │   ├── AdministrativoRepository.php
│   │   ├── EstudianteRepository.php
│   │   └── UsuarioAuthRepository.php
│   └── Service/
│       └── AuthService.php
├── public/
│   ├── index.php             # Front controller (única entrada pública)
│   └── .htaccess             # Rewrite rules para Apache
└── nginx.conf.example        # Configuración de referencia para Nginx
```

## Configuración

### 1. Base de datos

Edita `config/database.php` y ajusta:

```php
private static string $host     = 'localhost';
private static string $dbname   = 'sisintupt';
private static string $username = 'root';
private static string $password = '';
```

### 2. Servidor web

**Apache** — apunta el DocumentRoot a la carpeta `public/`. El `.htaccess` ya está listo.

**Nginx** — usa el archivo `nginx.conf.example` como plantilla.

**PHP built-in** (desarrollo):
```bash
php -S localhost:8081 -t public
```

### 3. CORS

Edita `config/cors.php` para agregar o quitar orígenes permitidos:

```php
private static array $allowedOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
];
```

---

## Endpoints

| Método | Ruta                  | Descripción                        |
|--------|-----------------------|------------------------------------|
| POST   | /api/auth/login       | Iniciar sesión                     |
| POST   | /api/auth/validate    | Validar token de sesión            |
| POST   | /api/auth/logout      | Cerrar sesión                      |
| GET    | /api/auth/health      | Estado del servicio                |

---

## Ejemplos de petición

### Login
```json
POST /api/auth/login
{
  "codigoOEmail": "2021001@upt.pe",
  "password": "mi_contraseña",
  "tipoLogin": "academic"
}
```

### Validate
```json
POST /api/auth/validate
{
  "token": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
}
```

### Logout
```json
POST /api/auth/logout
{
  "usuarioId": 5,
  "token": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
}
```

---

## Notas de lógica

- La contraseña se almacena en la BD en **Base64** (igual que en el proyecto Java original).
- La sesión expira por **inactividad de 20 minutos** (SESSION_INACTIVITY_MINUTES).
- Roles académicos: `1` (Docente), `2` (Estudiante).
- Roles administrativos: `3`, `4`.
- Un usuario no puede tener dos sesiones simultáneas (devuelve HTTP 409).

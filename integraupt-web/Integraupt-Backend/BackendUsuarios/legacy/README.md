# SAD – API REST en PHP

Conversión del proyecto Spring Boot (Java) a PHP puro con PDO + MariaDB.

## Requisitos
- PHP 8.1+
- MySQL / MariaDB (`sisintupt`)
- Apache con `mod_rewrite` (o usar el servidor embebido de PHP)

## Configurar base de datos
Editar `config/Database.php`:
```php
private string $host     = 'localhost';
private string $dbname   = 'sisintupt';
private string $username = 'root';
private string $password = '';
```

## Iniciar (dev)
```bash
php -S localhost:8092 index.php
```

---

## Estructura del proyecto
```
sad-php/
├── index.php                         ← Entrada, autoload, CORS
├── .htaccess                         ← Rewrite para Apache
│
├── config/
│   ├── Database.php                  ← Singleton PDO
│   └── App.php                       ← CORS, json(), error(), getBody()
│
├── models/
│   ├── Usuario.php
│   ├── Estudiante.php
│   ├── Docente.php
│   └── Administrativo.php
│
├── services/
│   ├── common/
│   │   └── UsuarioServiceHelper.php  ← Lógica base compartida (abstract)
│   ├── estudiante/
│   │   └── EstudianteService.php
│   ├── docente/
│   │   └── DocenteService.php
│   ├── administrativo/
│   │   └── AdministrativoService.php
│   └── CatalogoService.php
│
├── controllers/
│   ├── EstudianteController.php
│   ├── DocenteController.php
│   ├── AdministrativoController.php
│   └── CatalogoController.php
│
└── routes/
    └── Router.php                    ← Despacha método+URI → controlador
```

---

## Endpoints

### Estudiantes
| Método | URI | Acción |
|--------|-----|--------|
| GET    | /api/estudiantes         | Listar todos |
| GET    | /api/estudiantes/{id}    | Obtener uno  |
| POST   | /api/estudiantes         | Crear        |
| PUT    | /api/estudiantes/{id}    | Actualizar   |
| PATCH  | /api/estudiantes/{id}/estado | Activar/desactivar |
| DELETE | /api/estudiantes/{id}    | Desactivar (soft-delete) |

### Docentes
| Método | URI | Acción |
|--------|-----|--------|
| GET    | /api/docentes         | Listar todos |
| GET    | /api/docentes/{id}    | Obtener uno  |
| POST   | /api/docentes         | Crear        |
| PUT    | /api/docentes/{id}    | Actualizar   |
| PATCH  | /api/docentes/{id}/estado | Activar/desactivar |
| DELETE | /api/docentes/{id}    | Desactivar (soft-delete) |

### Administrativos
| Método | URI | Acción |
|--------|-----|--------|
| GET    | /api/administrativos         | Listar todos |
| GET    | /api/administrativos/{id}    | Obtener uno  |
| POST   | /api/administrativos         | Crear        |
| PUT    | /api/administrativos/{id}    | Actualizar   |
| PATCH  | /api/administrativos/{id}/estado | Activar/desactivar |
| DELETE | /api/administrativos/{id}    | Desactivar (soft-delete) |

### Catálogos (solo GET)
| URI | Retorna |
|-----|---------|
| /api/catalogos/tipos-documento | Lista de tipos de documento |
| /api/catalogos/roles           | Lista de roles |
| /api/catalogos/escuelas        | Lista de escuelas con facultad |

---

## Body de ejemplo – Crear estudiante (POST /api/estudiantes)
```json
{
  "nombre": "Juan",
  "apellido": "Pérez",
  "idTipoDoc": 1,
  "numDoc": "12345678",
  "celular": "987654321",
  "genero": true,
  "correo": "juan@example.com",
  "password": "segura123",
  "idEscuela": 2,
  "codigo": "2024001"
}
```

## Body de ejemplo – Crear docente (POST /api/docentes)
```json
{
  "nombre": "María",
  "apellido": "López",
  "idTipoDoc": 1,
  "numDoc": "87654321",
  "celular": "912345678",
  "genero": false,
  "correo": "maria@example.com",
  "password": "segura456",
  "idEscuela": 1,
  "codigoDocente": "DOC-2024-001",
  "tipoContrato": "Tiempo Completo",
  "especialidad": "Matemáticas",
  "fechaIncorporacion": "2024-03-01"
}
```

## Body de ejemplo – Actualizar estado (PATCH /{recurso}/{id}/estado)
```json
{ "activo": false }
```

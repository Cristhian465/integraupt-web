# Plan de Implementación Exageradamente Extenso: Módulo "Burra UPT"

Este documento detalla exhaustivamente todos los pasos y componentes necesarios para implementar el nuevo servicio "Burra UPT" dentro del sistema IntegraUPT, abarcando Frontend, Backend e Infraestructura (Docker), asegurando la escalabilidad, la integración visual y el correcto funcionamiento en toda la plataforma.

---

## 1. Análisis de Requerimientos y Diseño

### 1.1 Objetivo General
Añadir un nuevo módulo al apartado de "Servicios" llamado "Burra UPT", el cual permitirá a los estudiantes visualizar las rutas de los dos buses universitarios, así como sus horarios y estado en tiempo real.

### 1.2 Rutas y Vehículos
Se gestionarán dos vehículos en dos rutas independientes:
*   **Ruta B - Bus Placa UK 3013**
    *   **Recorrido de Ida:** UPT, Óvalo Pocollay, Av. Jorge Chávez, Av. Leguía, Óvalo Túpac Amaru, Av. Jorge Basadre Oeste, Av. Luis Basadre F., Óvalo Callao, Av. Grau, Av. Cuzco, Óvalo Cusco.
    *   **Retorno:** Av. Jorge Basadre, UPT.
*   **Ruta A - Bus 02 Placa Z6B 954**
    *   **Recorrido de Ida:** UPT por Av. Basadre, Jorge Basadre G., Av. Basadre Forero, Calle Alto de Lima, Av. San Martín, Plaza Zela (Fin de la ruta).
    *   **Retorno:** Calle General Vizquerra, Av. Leguía, Calle Cajamarca, Av. Jorge Basadre, UPT.

### 1.3 Horarios y Estados "En Vivo"
Ambos buses operan exclusivamente en 3 horarios:
*   **08:30 hrs**
*   **09:10 hrs**
*   **21:40 hrs**

**Reglas de Estado:**
*   `T - 10 min` a `T - 1 min` (ej. 08:20 a 08:29): **"En curso"**
*   `T` (ej. 08:30): **"En espera"**
*   `T + 1 min` a `T + 10 min` (ej. 08:31 a 08:40): **"En ruta"**

### 1.4 Frontend (React)
*   Integración perfecta y armoniosa con el diseño de `IntegraUPT` usando los CSS existentes (o nuevos equivalentes).
*   Se usará la librería Open Source **Leaflet** (mediante `react-leaflet`) para dibujar las dos rutas gratuitamente con mapas de OpenStreetMap.
*   El botón de la vista "Servicios" será idéntico a "Citas de Psicología" o "Reservas de espacios", utilizando el icono del Bus y una paleta de colores acorde.

### 1.5 Backend (Laravel)
*   Se creará un nuevo microservicio llamado `servicio-burra`.
*   Tendrá su propia base de datos, migraciones y controladores si se requiere escalabilidad, aunque los datos principales iniciales (rutas y horarios) pueden manejarse estáticamente o con un endpoint inicial de configuración.
*   Se expondrá en el puerto `8094` (comprobaremos que no esté en uso).

### 1.6 Infraestructura (Docker Compose)
*   Se agregará `servicio-burra` al `docker-compose.yml`.
*   Se integrará a la red virtual de integraupt y a la dependencia del frontend.

---

## 2. Desarrollo del Backend: Microservicio `servicio-burra`

### Paso 2.1: Estructura del Directorio
1.  Duplicaremos la estructura base de un servicio limpio (ej. `servicio-psicologia`) y lo renombraremos a `servicio-burra`.
2.  Eliminaremos controladores, modelos y migraciones antiguas para dejar un lienzo en blanco.
3.  Modificaremos el `.env.docker` para que el nombre de la app sea `Servicio Burra`.

### Paso 2.2: Creación de Endpoints (API)
Se creará un endpoint principal:
*   `GET /api/burra/status`: Devolverá las dos rutas, sus coordenadas, y la lógica horaria para calcular los estados. Opcionalmente, la lógica del reloj ("en curso", "en espera") puede manejarse 100% en el Frontend para no saturar al servidor con peticiones, usando la hora del cliente o solo obteniendo la hora del servidor una vez.

### Paso 2.3: Configuración de Docker
En el archivo `docker-compose.yml`, añadiremos el siguiente bloque:
```yaml
  servicio-burra:
    build:
      context: ./Integraupt-Backend/servicio-burra
      dockerfile: ../docker/laravel.Dockerfile
      args: { PHP_VERSION: "8.2" }
    env_file: ./Integraupt-Backend/servicio-burra/.env.docker
    depends_on: [servicio-usuarios]
    ports: ["8094:8000"]
    networks: [integraupt]
```
Además, en la sección de dependencias del `frontend`, añadiremos `- servicio-burra`.

---

## 3. Desarrollo del Frontend: Interfaz y Lógica

### Paso 3.1: Actualización del menú `ServiciosPage.tsx`
1.  Importar el icono `Bus` desde `lucide-react`.
2.  Añadir una nueva tarjeta `<article>` con la clase `servicios-menu-card-burra`.
3.  Establecer el título "Burra UPT" y el subtítulo con la descripción de las dos rutas universitarias.
4.  Llamar a un evento `onNavigateToBurra()` en el botón "Ver horarios y rutas".

### Paso 3.2: Creación de `BurraPage.tsx`
Se creará un componente completo y modular en `src/components/pages/Usuario/Burra/`.
*   **Estructura:**
    *   **Navbar superior** con breadcrumbs idénticos al resto de la aplicación.
    *   **Tabs (Pestañas) o Tarjetas lado a lado** para dividir Ruta A y Ruta B.
*   **Visualización de Rutas:**
    *   Instalaremos `leaflet` y `react-leaflet`.
    *   Usaremos `Polyline` con un arreglo de coordenadas (latitud/longitud) tomadas manualmente a partir de las imágenes referenciales.
    *   El mapa tendrá control de zoom bloqueado si lo deseamos totalmente estático, pero permitir movimiento es ideal para ver las calles.
*   **Lógica de Tiempos (El Reloj en tiempo real):**
    *   Crearemos un Hook personalizado `useBusStatus(horarios)` que se ejecutará en un `setInterval` cada 10 segundos.
    *   Evaluar la hora local y arrojar el estado correspondiente:
        *   `En curso` (color naranja/animado)
        *   `En espera` (color verde/parpadeante)
        *   `En ruta` (color azul/movimiento)
        *   `Inactivo` (color gris)
*   **Diseño Visual:**
    *   Tarjeta con la ilustración de un Bus y su placa decorativa al costado.
    *   CSS personalizado en `BurraScreen.css` con las animaciones de estado en el timeline.

### Paso 3.3: Actualización de Estados en `Dashboard.tsx`
1.  Ampliar el estado `activeView` para incluir `'burra'`.
2.  Pasar la propiedad `onNavigateToBurra` y renderizar `BurraPage` cuando `activeView === 'burra'`.

---

## 4. Pruebas y Control de Calidad

1.  **Prueba de Arranque Docker:** Ejecutar `docker-compose up --build -d` para verificar que el nuevo microservicio y el puerto `8094` funcionen sin conflictos con la base de datos o el frontend.
2.  **Verificación de Diseño (UI/UX):** Comprobar que en Responsive (Móvil y Escritorio) el mapa no desborde la pantalla y las tarjetas se apilen correctamente.
3.  **Simulación de Reloj (Mock Time):** Cambiaremos la hora de la computadora temporalmente a las `08:25`, `08:30`, `08:35` y `08:45` para comprobar que la lógica de estados ("En curso", "En ruta", etc.) cambie dinámicamente en pantalla sin necesidad de recargar la página.

---
**Este plan garantiza la creación de un sistema robusto, totalmente libre de servicios de pago y acoplado perfectamente al estilo de código actual de IntegraUPT.**

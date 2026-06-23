FROM php:8.2-cli

# Instalar extensiones de PHP necesarias para conexión a base de datos
RUN docker-php-ext-install pdo pdo_mysql

# Argumento para saber qué carpeta de backend copiar
ARG SERVICE_DIR
WORKDIR /app

# Copiar el proyecto
COPY ${SERVICE_DIR} .

# El comando por defecto (se sobreescribe en docker-compose si es necesario)
CMD ["php", "-S", "0.0.0.0:8000", "-t", "."]

<?php
$config = require __DIR__ . '/config/database.php';
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";

try {
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
    CREATE TABLE IF NOT EXISTS facultad (
        IdFacultad INT AUTO_INCREMENT PRIMARY KEY,
        Nombre VARCHAR(100) NOT NULL,
        Estado VARCHAR(50) DEFAULT 'Activo'
    );

    CREATE TABLE IF NOT EXISTS escuela (
        IdEscuela INT AUTO_INCREMENT PRIMARY KEY,
        IdFacultad INT NOT NULL,
        Nombre VARCHAR(100) NOT NULL,
        Estado VARCHAR(50) DEFAULT 'Activo'
    );

    CREATE TABLE IF NOT EXISTS espacio (
        IdEspacio INT AUTO_INCREMENT PRIMARY KEY,
        Escuela INT NOT NULL,
        Nombre VARCHAR(100) NOT NULL,
        Codigo VARCHAR(50),
        Tipo VARCHAR(50),
        Capacidad INT,
        Estado VARCHAR(50) DEFAULT 'Disponible'
    );

    CREATE TABLE IF NOT EXISTS usuario (
        IdUsuario INT AUTO_INCREMENT PRIMARY KEY,
        Nombre VARCHAR(100) NOT NULL,
        Apellido VARCHAR(100) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS usuario_auth (
        IdUsuario INT PRIMARY KEY,
        CorreoU VARCHAR(100) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS bloqueshorarios (
        IdBloque INT AUTO_INCREMENT PRIMARY KEY,
        Nombre VARCHAR(50),
        HoraInicio TIME,
        HoraFinal TIME
    );

    CREATE TABLE IF NOT EXISTS cursos (
        IdCurso INT AUTO_INCREMENT PRIMARY KEY,
        Nombre VARCHAR(100) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS reserva_gestion (
        IdGestion INT AUTO_INCREMENT PRIMARY KEY,
        IdReserva INT NOT NULL,
        UsuarioGestion INT NOT NULL,
        Accion VARCHAR(50),
        Motivo VARCHAR(255),
        Comentarios TEXT,
        FechaGestion DATETIME
    );
    ";
    
    $pdo->exec($sql);
    echo "Todas las tablas creadas correctamente.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

<?php
$config = require __DIR__ . '/config/database.php';
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";

try {
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "CREATE TABLE IF NOT EXISTS reserva (
        IdReserva INT AUTO_INCREMENT PRIMARY KEY,
        usuario INT NOT NULL,
        espacio INT NOT NULL,
        bloque INT NOT NULL,
        curso INT NOT NULL,
        fechaReserva DATE NOT NULL,
        fechaSolicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        DescripcionUso TEXT,
        CantidadEstudiantes INT NOT NULL,
        Estado VARCHAR(50) DEFAULT 'Pendiente'
    );";
    
    $pdo->exec($sql);
    echo "Tabla 'reserva' creada correctamente.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

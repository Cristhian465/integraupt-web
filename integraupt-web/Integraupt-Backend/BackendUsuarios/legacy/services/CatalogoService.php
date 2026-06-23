<?php

namespace Services;

use Config\Database;

class CatalogoService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function tiposDocumento(): array
    {
        return $this->db->query(
            'SELECT IdTipoDoc AS idTipoDoc, Nombre AS nombre, Abreviatura AS abreviatura
             FROM tipodocumento ORDER BY Nombre'
        )->fetchAll();
    }

    public function roles(): array
    {
        return $this->db->query(
            'SELECT IdRol AS idRol, Nombre AS nombre FROM rol ORDER BY Nombre'
        )->fetchAll();
    }

    public function escuelas(): array
    {
        return $this->db->query(
            'SELECT e.IdEscuela AS idEscuela, e.Nombre AS nombre,
                    f.IdFacultad AS idFacultad, f.Nombre AS facultadNombre
             FROM escuela e
             LEFT JOIN facultad f ON e.IdFacultad = f.IdFacultad
             ORDER BY e.Nombre'
        )->fetchAll();
    }
}

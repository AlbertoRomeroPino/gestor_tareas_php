<?php

class Database
{
    public static function conectar()
    {
        $db = __DIR__ . '/data/gestor_tareas.db';

        try {
            $pdo = new PDO('sqlite', $db);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $db;
        } catch (PDOException $excepcion) {
            die("Error fatal de conexión: " . $excepcion->getMessage());
        }
    }
}

?>
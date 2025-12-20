<?php

class Database
{
    public static function conectar()
    {
        $db = __DIR__ . '/../data/gestor_tareas.db';

        try {
            $pdo = new PDO('sqlite:'. $db);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           
            return $pdo;
        } catch (PDOException $excepcion) {
            die("Error fatal de conexión: " . $excepcion->getMessage());
        }
    }
}

?>
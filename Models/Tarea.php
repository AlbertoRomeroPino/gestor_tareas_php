<?php

require_once __DIR__ . '/../Config/Database.php';
class Tareas
{
    private $db;
    private $id;
    private $titulo;
    private $descripcion;
    private $estado;
    private $usuario_id;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function findAllByUserId($usuario_id)
{
    // Añadimos ORDER BY id DESC para que las nuevas salgan primero
    $sql = "SELECT * FROM tareas WHERE usuario_id = :usuario_id ORDER BY id DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function findById($id)
    {
        $sql = "SELECT * FROM tareas WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByTitulo($titulo)
    {
        $sql = "SELECT * FROM tareas WHERE titulo LIKE :titulo";
        $stmt = $this->db->prepare($sql);

        $parametro = "%" . $titulo . "%";

        $stmt->execute([':titulo' => $parametro]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByEstado($estado)
    {
        $sql = "SELECT * FROM tareas WHERE estado = :estado";
        $stmt = $this->db->prepare($sql);

        $stmt->execute([':estado' => $estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTarea($titulo, $estado, $descripcion, $usuario_id)
    {

        $sql = "INSERT INTO tareas (titulo, estado, descripcion, usuario_id) VALUES (:titulo, :estado, :descripcion, :usuario_id)";
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute([
                ':titulo' => $titulo,
                ':estado' => $estado,
                ':descripcion' => $descripcion,
                ':usuario_id' => $usuario_id
            ]);
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateTarea($id, $titulo, $estado, $descripcion)
    {
        $sql = ("UPDATE tareas SET 
                titulo = :titulo, 
                descripcion = :descripcion, 
                estado = :estado 
            WHERE id = :id");
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute([
                ':titulo' => $titulo,
                ':estado' => $estado,
                ':descripcion' => $descripcion,
                ':id' => $id
            ]);
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function deleteTarea($id)
    {
        $sql = "DELETE FROM tareas WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
?>
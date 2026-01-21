<?php

require_once __DIR__ . '/../Config/Database.php';

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function findByUsername($user)
    {
        $sql = "SELECT * FROM usuarios WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':username', $user);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function create($user, $pass)
    {
        $passHash = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (username, password) VALUES (:username, :password)";
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute([
                ':username' => $user,
                ':password' => $passHash
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
<?php

session_start();
require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login($username, $password, $remember = false)
    {
        $user = $this->userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            if ($remember) {
                setcookie('user_login', $user['username'], time() + (86400 * 30), "/");
            }

            header("Location: ../Views/tablero.php");
            exit();
        } else {
            header("Location: ../Views/auth/login.php?error=credenciales_incorrectas");
            exit;
        }
    }

    public function register($username, $password)
    {
        if ($this->userModel->create($username, $password)) {
            header("Location: ../Views/auth/login.php?success=registrado");
            exit();
        } else {
            header("Location: ../Views/auth/register.php?error=fallo_registro");
            exit();
        }
    }






}

?>
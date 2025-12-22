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

            header("Location: ../Views/layouts/tablero.php");
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

    public function logout()
    {
        session_destroy();

        if (isset($_COOKIE['user_login'])) {
            setcookie('user_login', '', time() - 3600, "/");
        }

        header("Location: ../Views/auth/login.php");
        exit();
    }

    public function checkCookie()
    {
        if (isset($_SESSION['user_id'])) {
            return;
        }

        if (isset($_COOKIE['user_login'])) {
            $username = $_COOKIE['user_login'];
            $user = $this->userModel->findByUsername($username);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();

    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $remembre = isset($_POST['remember']);

        $auth->login($_POST['username'], $_POST['password'], $remembe);
    }

    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $auth->register($_POST['username'], $_POST['password']);
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new AuthController();
    $auth->logout();
}
?>
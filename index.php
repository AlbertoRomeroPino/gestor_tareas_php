<?php


// 1. Iniciamos la sesión para poder leer si el usuario ya entró
session_start();

// 2. Importamos el controlador para usar la lógica de cookies (Autologin)
require_once __DIR__ . '/Controllers/AuthController.php';

$auth = new AuthController();

// 3. Verificamos si existe la cookie "user_login" para loguearlo automáticamente
// Esta es la función que explicamos que dura 30 días
$auth->checkCookie();

// 4. Lógica de redirección (El filtro)
if (isset($_SESSION['user_id'])) {
    // Si la sesión existe, lo mandamos a la vista del tablero
    header("Location: Views/tablero.php");
} else {
    // Si no hay sesión, lo mandamos a la pantalla de acceso
    header("Location: Views/auth/login.php");
}

// Es buena práctica poner exit() después de un header Location
exit();
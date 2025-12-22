<?php
// index.php (En la raíz del proyecto)

// 1. Iniciamos la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Importamos el controlador
// Usamos __DIR__ para que la ruta sea absoluta y no falle
require_once __DIR__ . '/Controllers/AuthController.php';

$auth = new AuthController();

// 3. Verificamos la cookie "recordarme"
$auth->checkCookie();

// 4. Lógica de redirección
if (isset($_SESSION['user_id'])) {
    // Si la sesión existe, vamos al tablero
    // Asegúrate de que esta ruta sea correcta según tu estructura de carpetas
    header("Location: Views/layouts/tablero.php");
} else {
    // Si no hay sesión, vamos al login
    header("Location: Views/auth/login.php");
}
exit();
?>
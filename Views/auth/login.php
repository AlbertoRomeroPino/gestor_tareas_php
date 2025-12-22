<?php
// 1. Iniciamos sesión y verificamos si ya está dentro
session_start();
if (isset($_SESSION['user_id'])) {
    // Si ya está logueado, lo mandamos directo al tablero
    header("Location: ../layouts/tablero.php");
    exit();
}

// 2. Configuración de la página
$pageTitle = "Acceso - Gestor de Tareas";

// 3. AQUÍ ESTÁ EL TRUCO: Definimos el CSS específico para el login.
// Al definir esta variable, el header.php la leerá y cargará este archivo.
$extraCss = '/public/CSS/login.css';

// 4. Incluimos el header (que contiene el menú y la apertura del body)
// Ajusta la ruta si tu carpeta layouts está en otro nivel, pero esto debería funcionar según tu estructura.
include __DIR__ . '/../layouts/header.php'; 
?>

<div class="container" id="container">
    
    <div class="form-container sign-up-container">
        <form action="../../Controllers/AuthController.php" method="POST">
            <h1>Crear Cuenta</h1>
            <input type="hidden" name="action" value="register">
            
            <input type="text" name="username" placeholder="Nombre de usuario" required />
            <input type="password" name="password" placeholder="Contraseña" required />
            
            <button type="submit">Registrarse</button>
        </form>
    </div>

    <div class="form-container sign-in-container">
        <form action="../../Controllers/AuthController.php" method="POST">
            <h1>Iniciar Sesión</h1>
            
            <?php if (isset($_GET['error'])): ?>
                <span class="error-text">
                    <?php
                        if ($_GET['error'] == 'credenciales_incorrectas') echo "Usuario o contraseña incorrectos";
                        if ($_GET['error'] == 'usuario_existe') echo "Ese usuario ya está registrado";
                        if ($_GET['error'] == 'registro_fallido') echo "Error al registrarse";
                    ?>
                </span>
            <?php endif; ?>

            <input type="hidden" name="action" value="login">
            
            <input type="text" name="username" placeholder="Usuario" required />
            <input type="password" name="password" placeholder="Contraseña" required />
            
            <div class="remember-container">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Recordarme</label>
            </div>

            <button type="submit">Entrar</button>
        </form>
    </div>

    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>¡Hola de nuevo!</h1>
                <p>Para mantenerte conectado con nosotros, inicia sesión con tus datos personales.</p>
                <button class="ghost" id="signIn">Iniciar Sesión</button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>¡Bienvenido!</h1>
                <p>Ingresa tus datos personales y comienza a organizar tus tareas hoy mismo.</p>
                <button class="ghost" id="signUp">Registrarse</button>
            </div>
        </div>
    </div>
</div>

<script>
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');

    signUpButton.addEventListener('click', () => {
        container.classList.add("right-panel-active");
    });

    signInButton.addEventListener('click', () => {
        container.classList.remove("right-panel-active");
    });
</script>

</body>
</html>
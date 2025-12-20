<?php
// Iniciamos sesión para verificar si el usuario ya entró
session_start();

// Si ya existe una sesión activa, lo mandamos directo al tablero
if (isset($_SESSION['user_id'])) {
    header("Location: ../tablero.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Gestor de Tareas</title>
    <link rel="stylesheet" href="../../public/CSS/login.css">
</head>
<body>

    <div class="container" id="container">
        
        <div class="form-container sign-up-container">
            <form action="../../Controllers/AuthController.php" method="POST">
                <h1>CREAR CUENTA</h1>
                <input type="hidden" name="action" value="register">
                <input type="text" name="username" placeholder="Nombre de usuario" required />
                <input type="password" name="password" placeholder="Contraseña" required />
                <button type="submit">REGISTRARSE</button>
            </form>
        </div>

        <div class="form-container sign-in-container">
            <form action="../../Controllers/AuthController.php" method="POST">
                <h1>BIENVENIDO</h1>
                
                <?php if (isset($_GET['error'])): ?>
                    <span class="error-text">
                        <?php
                            if ($_GET['error'] == 'credenciales_incorrectas') echo "Datos inválidos";
                            if ($_GET['error'] == 'usuario_existe') echo "El usuario ya existe";
                        ?>
                    </span>
                <?php endif; ?>

                <input type="hidden" name="action" value="login">
                <input type="text" name="username" placeholder="Usuario" required />
                <input type="password" name="password" placeholder="Contraseña" required />
                
                <div class="remember-container">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Recordarme por 30 días</label>
                </div>

                <button type="submit">ENTRAR</button>
            </form>
        </div>

        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>¡HOLA!</h1>
                    <p>Si ya tienes una cuenta, inicia sesión con tus datos personales aquí.</p>
                    <button class="ghost" id="signIn">INICIAR SESIÓN</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>¡BIENVENIDO!</h1>
                    <p>Regístrate para empezar a gestionar tus tareas de forma eficiente.</p>
                    <button class="ghost" id="signUp">REGISTRARSE</button>
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
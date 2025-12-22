<?php
// Aseguramos que la sesión esté activa (necesario para mostrar enlaces condicionales)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL para recursos estáticos (calcula la raíz del proyecto eliminando /Views/...)
$baseUrl = preg_replace('#/Views.*$#', '', dirname($_SERVER['SCRIPT_NAME']));
if ($baseUrl === '\\' || $baseUrl === '.') { $baseUrl = ''; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($pageTitle) ? $pageTitle : "Gestor de Tareas"; ?></title>
    
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/public/CSS/header.css">

    <?php if (isset($extraCss) && !empty($extraCss)): ?>
        <link rel="stylesheet" href="<?php echo $baseUrl . $extraCss; ?>">
    <?php endif; ?>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <header class="site-header">
        <div class="logo-container">
            <a href="<?php echo $baseUrl; ?>/index.php" class="logo-link" title="Volver al inicio"></a>
            <div class="bat"></div>
        </div>

        <nav>
            <ul class="nav-links">
                <li><a href="<?php echo $baseUrl; ?>/index.php">Inicio</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo $baseUrl; ?>/Views/layouts/tablero.php">Mi Tablero</a></li>
                    <li>
                        <a href="<?php echo $baseUrl; ?>/Controllers/AuthController.php?action=logout" class="btn-action logout">
                            Cerrar Sesión
                        </a>
                    </li>
                <?php else: ?>
                    <li><a href="#">Servicios</a></li>
w                    <li><a href="<?php echo $baseUrl; ?>/Views/auth/login.php" class="btn-action">Ingresar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
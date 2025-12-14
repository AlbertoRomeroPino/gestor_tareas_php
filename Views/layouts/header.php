<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Tareas</title>
    
    <link rel="stylesheet" href="public/CSS/header.css">
</head>
<body>

    <header class="app-header">
        
        <div class="header-left">
            <div class="bat"></div> <h1 class="app-title">TaskBat</h1>
        </div>

        <div class="header-right">
             <?php if(isset($_SESSION['user_id'])): ?>
                <a href="index.php?action=logout">Salir</a>
            <?php else: ?>
                <a href="index.php?action=login">Entrar</a>
            <?php endif; ?>
        </div>

    </header>

    <main class="container">
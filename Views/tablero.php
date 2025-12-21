<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Carga el controlador de tareas (ruta relativa más robusta)
require_once __DIR__ . '/../Controllers/TareaController.php';
$controller = new TareasController();
$misTareas = $controller->index();

$pageTitle = "Mi Tablero";
// Ruta del CSS específico para esta página (se usará en header.php)
$extraCss = '/public/CSS/tablero.css';
// Incluye la cabecera desde la carpeta layouts
include __DIR__ . '/layouts/header.php'; 
?>

<div class="board-container">
    
    <div class="toolbar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="buscador" placeholder="Buscar en mis notas..." onkeyup="filtrarTareas()">
        </div>
        
        <button class="btn-new-note" onclick="document.getElementById('form-card').style.display='block'">
            <i class="fas fa-plus"></i> Nueva Nota
        </button>
    </div>

    <div id="form-card" class="postit-form" style="display:none;">
        <form action="../Controllers/TareaController.php" method="POST">
            <input type="hidden" name="action" value="crear">
            <input type="text" name="titulo" placeholder="Título de la tarea..." required>
            <textarea name="descripcion" placeholder="Detalles (opcional)..." rows="3"></textarea>
            <div class="form-buttons">
                <button type="submit" class="btn-save">Pegar Nota</button>
                <button type="button" class="btn-cancel" onclick="document.getElementById('form-card').style.display='none'">Cancelar</button>
            </div>
        </form>
    </div>

    <div class="corkboard" id="listaTareas">
        
        <?php if (count($misTareas) > 0): ?>
            <?php foreach ($misTareas as $tarea): ?>
                
                <div class="postit <?php echo $tarea['estado'] == 1 ? 'done' : ''; ?>">
                    <div class="pin"><i class="fas fa-thumbtack"></i></div>
                    
                    <h2 class="note-title"><?php echo htmlspecialchars($tarea['titulo']); ?></h2>
                    
                    <p class="note-body">
                        <?php echo htmlspecialchars($tarea['descripcion'] ?? ''); ?>
                    </p>
                    
                    <div class="note-footer">
                        <?php $fecha = $tarea['fecha_creacion'] ?? null; ?>
                        <span class="date"><?php echo $fecha ? date('d M', strtotime($fecha)) : ''; ?></span>
                        <div class="actions">
                            <a href="../Controllers/TareaController.php?action=eliminar&id=<?php echo $tarea['id']; ?>" 
                               class="btn-icon delete" 
                               onclick="return confirm('¿Arrancar esta nota?');">
                               <i class="fas fa-trash-alt"></i>
                            </a>
                            <a href="#" class="btn-icon check"><i class="fas fa-check"></i></a>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-message">
                <p>El tablero está vacío. ¡Empieza agregando una nota!</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
    function filtrarTareas() {
        // 1. Obtener texto del buscador
        let input = document.getElementById('buscador');
        let filter = input.value.toLowerCase();
        
        // 2. Obtener todas las notas
        let tablero = document.getElementById('listaTareas');
        let notas = tablero.getElementsByClassName('postit');

        // 3. Recorrer y ocultar/mostrar
        for (let i = 0; i < notas.length; i++) {
            let titulo = notas[i].getElementsByClassName('note-title')[0];
            let cuerpo = notas[i].getElementsByClassName('note-body')[0];
            
            if (titulo || cuerpo) {
                let textoTitulo = titulo.textContent || titulo.innerText;
                let textoCuerpo = cuerpo.textContent || cuerpo.innerText;

                if (textoTitulo.toLowerCase().indexOf(filter) > -1 || textoCuerpo.toLowerCase().indexOf(filter) > -1) {
                    notas[i].style.display = ""; // Mostrar
                } else {
                    notas[i].style.display = "none"; // Ocultar
                }
            }
        }
    }
</script>

<?php // Footer not implemented. Create Views/layouts/footer.php and include it here if needed. ?>
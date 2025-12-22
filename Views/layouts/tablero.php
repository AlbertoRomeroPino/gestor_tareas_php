<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// --- CONTROLADOR ---
// Ajusta la ruta si es necesario según tus carpetas, esto sube 2 niveles
require_once __DIR__ . '/../../Controllers/TareaController.php';
$controller = new TareasController();
$misTareas = $controller->index();

// --- CONFIGURACIÓN DE LA PÁGINA ---
$pageTitle = "Mi Tablero";

// AQUÍ ESTÁ LA CLAVE: Cargamos solo el CSS del tablero
$extraCss = '/public/CSS/tablero.css';

include __DIR__ . '/header.php'; 
?>

<link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap" rel="stylesheet">

<div class="board-container">
    
    <div class="toolbar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="buscador" placeholder="Buscar nota..." onkeyup="filtrarTareas()">
        </div>
        
        <button class="btn-new-note" onclick="nuevaTarea()">
            <i class="fas fa-plus"></i> Nueva Nota
        </button>
    </div>

    <div id="form-card" class="postit-form-overlay" style="display:none;">
        <div class="postit-form-content">
            <form action="../../Controllers/TareaController.php" method="POST" id="formTarea">
                <input type="hidden" name="action" id="formAction" value="crear">
                <input type="hidden" name="id" id="taskId" value="">

                <input type="text" name="titulo" id="taskTitle" placeholder="Título..." required autocomplete="off">
                <textarea name="descripcion" id="taskDesc" placeholder="Escribe los detalles aquí..." rows="4"></textarea>
                
                <div class="form-buttons">
                    <button type="submit" class="btn-save" id="btnSubmit">Pegar Nota</button>
                    <button type="button" class="btn-cancel" onclick="cerrarFormulario()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="corkboard" id="listaTareas">
        
        <?php if (!empty($misTareas)): ?>
            <?php foreach ($misTareas as $tarea): ?>
                
                <div class="postit">
                    <div class="pin"><i class="fas fa-thumbtack"></i></div>
                    
                    <h2 class="note-title"><?php echo htmlspecialchars($tarea['titulo']); ?></h2>
                    
                    <p class="note-body">
                        <?php echo nl2br(htmlspecialchars($tarea['descripcion'] ?? '')); ?>
                    </p>
                    
                    <div class="note-footer">
                        <span class="date">
                            <?php echo date('d M', strtotime($tarea['fecha_creacion'])); ?>
                        </span>
                        
                        <div class="actions">
                            <a href="#" class="btn-icon" 
                               onclick="editarTarea('<?php echo $tarea['id']; ?>', '<?php echo addslashes(htmlspecialchars($tarea['titulo'])); ?>', '<?php echo addslashes(htmlspecialchars($tarea['descripcion'] ?? '')); ?>')">
                               <i class="fas fa-pencil-alt"></i>
                            </a>

                            <a href="../../Controllers/TareaController.php?action=eliminar&id=<?php echo $tarea['id']; ?>" 
                               class="btn-icon delete" 
                               onclick="return confirm('¿Arrancar esta nota del tablero?');">
                               <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-message">
                <p>El tablero está vacío. ¡Crea tu primera nota!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // --- LÓGICA JS (No cambia nada de lo que ya tenías) ---
    function nuevaTarea() {
        document.getElementById('formTarea').reset();
        document.getElementById('formAction').value = 'crear';
        document.getElementById('taskId').value = ''; 
        document.getElementById('btnSubmit').innerText = 'Pegar Nota';
        document.getElementById('form-card').style.display = 'flex'; 
        document.getElementById('taskTitle').focus();
    }

    function editarTarea(id, titulo, descripcion) {
        document.getElementById('taskId').value = id;
        document.getElementById('taskTitle').value = titulo;
        document.getElementById('taskDesc').value = descripcion;
        document.getElementById('formAction').value = 'editar';
        document.getElementById('btnSubmit').innerText = 'Guardar Cambios';
        document.getElementById('form-card').style.display = 'flex';
    }

    function cerrarFormulario() {
        document.getElementById('form-card').style.display = 'none';
    }

    function filtrarTareas() {
        let input = document.getElementById('buscador');
        let filter = input.value.toLowerCase();
        let notas = document.getElementsByClassName('postit');

        for (let i = 0; i < notas.length; i++) {
            let titulo = notas[i].querySelector('.note-title').innerText.toLowerCase();
            let cuerpo = notas[i].querySelector('.note-body').innerText.toLowerCase();
            notas[i].style.display = (titulo.includes(filter) || cuerpo.includes(filter)) ? "" : "none";
        }
    }
</script>

</body>
</html>
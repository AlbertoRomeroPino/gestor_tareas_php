<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Carga el controlador
require_once __DIR__ . '/../../Controllers/TareaController.php';
$controller = new TareasController();
$misTareas = $controller->index();

$pageTitle = "Mi Tablero";
// Definimos el CSS extra para que header.php lo cargue
$extraCss = '<link rel="stylesheet" href="../../public/CSS/tablero.css">';
include __DIR__ . '/header.php'; 
?>

<link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap" rel="stylesheet">

<div class="board-container">
    
    <div class="toolbar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="buscador" placeholder="Buscar en mis notas..." onkeyup="filtrarTareas()">
        </div>
        
        <button class="btn-new-note" onclick="nuevaTarea()">
            <i class="fas fa-plus"></i> Nueva Nota
        </button>
    </div>

    <div id="form-card" class="postit-form" style="display:none;">
        <form action="../../Controllers/TareaController.php" method="POST" id="formTarea">
            
            <input type="hidden" name="action" id="formAction" value="crear">
            
            <input type="hidden" name="id" id="taskId" value="">

            <input type="text" name="titulo" id="taskTitle" placeholder="Título de la tarea..." required autocomplete="off">
            <textarea name="descripcion" id="taskDesc" placeholder="Detalles (opcional)..." rows="4"></textarea>
            
            <div class="form-buttons">
                <button type="submit" class="btn-save" id="btnSubmit">Pegar Nota</button>
                <button type="button" class="btn-cancel" onclick="cerrarFormulario()">Cancelar</button>
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
                        <?php echo nl2br(htmlspecialchars($tarea['descripcion'] ?? '')); ?>
                    </p>
                    
                    <div class="note-footer">
                        <span class="date">
                            <?php echo date('d M', strtotime($tarea['fecha_creacion'])); ?>
                        </span>
                        
                        <div class="actions">
                            <a href="#" class="btn-icon edit" 
                               onclick="editarTarea(
                                   '<?php echo $tarea['id']; ?>', 
                                   '<?php echo htmlspecialchars($tarea['titulo'], ENT_QUOTES); ?>', 
                                   '<?php echo htmlspecialchars($tarea['descripcion'] ?? '', ENT_QUOTES); ?>'
                               )">
                               <i class="fas fa-pencil-alt"></i>
                            </a>

                            <a href="../../Controllers/TareaController.php?action=eliminar&id=<?php echo $tarea['id']; ?>" 
                               class="btn-icon delete" 
                               onclick="return confirm('¿Arrancar esta nota del tablero?');">
                               <i class="fas fa-trash-alt"></i>
                            </a>
                            
                            <a href="#" class="btn-icon check"><i class="fas fa-check"></i></a>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-message">
                <i class="fas fa-sticky-note" style="font-size: 3rem; margin-bottom: 10px; display:block;"></i>
                <p>El tablero está vacío.<br>¡Haz clic en "Nueva Nota" para empezar!</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
    // 1. ABRIR PARA CREAR
    function nuevaTarea() {
        document.getElementById('formTarea').reset(); // Limpiar campos
        document.getElementById('formAction').value = 'crear'; // Modo crear
        document.getElementById('taskId').value = ''; 
        document.getElementById('btnSubmit').innerText = 'Pegar Nota'; // Texto botón
        document.getElementById('form-card').style.display = 'block'; // Mostrar
        document.getElementById('taskTitle').focus();
    }

    // 2. ABRIR PARA EDITAR (Recibe datos de la tarea)
    function editarTarea(id, titulo, descripcion) {
        document.getElementById('taskId').value = id;
        document.getElementById('taskTitle').value = titulo;
        document.getElementById('taskDesc').value = descripcion;
        
        document.getElementById('formAction').value = 'editar'; // Modo editar
        document.getElementById('btnSubmit').innerText = 'Guardar Cambios'; // Texto botón
        
        document.getElementById('form-card').style.display = 'block'; // Mostrar
    }

    // 3. CERRAR FORMULARIO
    function cerrarFormulario() {
        document.getElementById('form-card').style.display = 'none';
    }

    // 4. FILTRAR TAREAS (BUSCADOR)
    function filtrarTareas() {
        let input = document.getElementById('buscador');
        let filter = input.value.toLowerCase();
        let notas = document.getElementsByClassName('postit');

        for (let i = 0; i < notas.length; i++) {
            let titulo = notas[i].getElementsByClassName('note-title')[0].innerText;
            let cuerpo = notas[i].getElementsByClassName('note-body')[0].innerText;
            
            if (titulo.toLowerCase().includes(filter) || cuerpo.toLowerCase().includes(filter)) {
                notas[i].style.display = ""; 
            } else {
                notas[i].style.display = "none"; 
            }
        }
    }
</script>

</body>
</html>
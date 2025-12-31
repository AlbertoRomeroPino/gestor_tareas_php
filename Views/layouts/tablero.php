<?php
session_start();

// 1. Seguridad: Si no hay usuario, fuera.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// 2. Cargar Controlador
// Ajusta la ruta si es necesario. (Subimos 2 niveles desde Views/layouts)
require_once __DIR__ . '/../../Controllers/TareaController.php';

$controller = new TareasController();
$misTareas = $controller->index();

// 3. Configuración de la página
$pageTitle = "Mi Tablero";

// 4. Cargar CSS específico del Tablero
$extraCss = '/public/CSS/tablero.css';

// 5. Incluir Header común
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
                <textarea name="descripcion" id="taskDesc" placeholder="Escribe los detalles aquí..."
                    rows="4"></textarea>

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

                            <?php $nuevoEstado = ($tarea['estado'] == 1) ? 0 : 1; ?>

                            <a href="../../Controllers/TareaController.php?action=cambiar_estado&id=<?php echo $tarea['id']; ?>&estado=<?php echo $nuevoEstado; ?>"
                                class="btn-icon check"
                                title="<?php echo $tarea['estado'] == 1 ? 'Marcar como pendiente' : 'Marcar como completada'; ?>">

                                <?php if ($tarea['estado'] == 1): ?>
                                    <i class="fas fa-check-circle"></i>
                                <?php else: ?>
                                    <i class="far fa-circle"></i>
                                <?php endif; ?>
                            </a>

                            <a href="#" class="btn-icon" onclick="editarTarea(
                                   '<?php echo $tarea['id']; ?>', 
                                   '<?php echo addslashes(htmlspecialchars($tarea['titulo'])); ?>', 
                                   '<?php echo addslashes(htmlspecialchars($tarea['descripcion'] ?? '')); ?>'
                               )">
                                <i class="fas fa-pencil-alt"></i>
                            </a>

                            <a href="../../Controllers/TareaController.php?action=eliminar&id=<?php echo $tarea['id']; ?>"
                                class="btn-icon delete" onclick="return confirm('¿Arrancar esta nota del tablero?');">
                                <i class="fas fa-trash-alt"></i>
                            </a>

                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-message">
                <p>El tablero está vacío.<br>¡Haz clic en "Nueva Nota"!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Abrir modal para crear
    function nuevaTarea() {
        document.getElementById('formTarea').reset();
        document.getElementById('formAction').value = 'crear';
        document.getElementById('taskId').value = '';
        document.getElementById('btnSubmit').innerText = 'Pegar Nota';
        document.getElementById('form-card').style.display = 'flex';
        document.getElementById('taskTitle').focus();
    }

    // Abrir modal para editar (rellena los datos)
    function editarTarea(id, titulo, descripcion) {
        document.getElementById('taskId').value = id;
        document.getElementById('taskTitle').value = titulo;
        document.getElementById('taskDesc').value = descripcion;
        document.getElementById('formAction').value = 'editar';
        document.getElementById('btnSubmit').innerText = 'Guardar Cambios';
        document.getElementById('form-card').style.display = 'flex';
    }

    // Cerrar modal
    function cerrarFormulario() {
        document.getElementById('form-card').style.display = 'none';
    }

    // Filtro del buscador en tiempo real
    function filtrarTareas() {
        let input = document.getElementById('buscador');
        let filter = input.value.toLowerCase();
        let notas = document.getElementsByClassName('postit');

        for (let i = 0; i < notas.length; i++) {
            let titulo = notas[i].querySelector('.note-title').innerText.toLowerCase();
            let cuerpo = notas[i].querySelector('.note-body').innerText.toLowerCase();
            // Muestra si coincide el título O la descripción
            notas[i].style.display = (titulo.includes(filter) || cuerpo.includes(filter)) ? "" : "none";
        }
    }
</script>

</body>

</html>
<?php
// 1. Iniciar sesión si no está iniciada (Fundamental para saber quién es el usuario)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Importar el modelo
require_once __DIR__ . '/../Models/Tarea.php';

class TareasController {

    public function __construct() {
        
        // --- RUTAS POST (Formularios) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'crear') {
                    $this->agregar();
                }
                elseif ($_POST['action'] === 'editar') {
                    // Necesitamos el ID para editar
                    $this->actualizar($_POST['id']);
                }
            }

        // --- RUTAS GET (Enlaces / URL) ---
        } elseif (isset($_GET['action'])) {
            
            // Verificamos que venga un ID para las acciones que lo requieren
            if (isset($_GET['id'])) {
                if ($_GET['action'] === 'eliminar') {
                    $this->eliminar($_GET['id']);
                }
                // Si quisieras una pantalla de "Editar Tarea", iría aquí
            }
        }
    }

    // --- 1. LISTAR TAREAS (INDEX) ---
    public function index() {
        $tareaModel = new Tareas();
        // Usamos el ID de la sesión para traer SOLO las tareas de este usuario
        return $tareaModel->findAllByUserId($_SESSION['user_id']);
    }

    // --- 2. AGREGAR TAREA ---
    public function agregar() {
        // Validación básica
        if (empty($_POST['titulo'])) {
            header("Location: ../Views/layouts/tablero.php?error=titulo_vacio");
            exit();
        }

        $titulo = trim($_POST['titulo']);
        $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
        $estado = 0; // Por defecto pendiente
        $usuario_id = $_SESSION['user_id'];

        $tareaModel = new Tareas();
        $tareaModel->createTarea($titulo, $estado, $descripcion, $usuario_id);

        header("Location: ../Views/layouts/tablero.php");
        exit();
    }

    // --- 3. ELIMINAR TAREA (CON SEGURIDAD) ---
    public function eliminar($id) {
        $tareaModel = new Tareas();
        
        // PASO 1: Buscar la tarea primero
        $tarea = $tareaModel->findById($id);

        // PASO 2: VERIFICACIÓN DE PROPIEDAD (SEGURIDAD)
        // Verificamos si la tarea existe Y si el usuario_id de la tarea coincide con el de la sesión
        if ($tarea && $tarea['usuario_id'] == $_SESSION['user_id']) {
            
            // Si es dueño, procedemos a borrar
            $tareaModel->deleteTarea($id);
            header("Location: ../Views/layouts/tablero.php?msg=tarea_eliminada");
        } else {
            // Si no es dueño o no existe, lo echamos (Seguridad)
            header("Location: ../Views/layouts/tablero.php?error=acceso_denegado");
        }
        exit();
    }

    // --- 4. ACTUALIZAR TAREA (CON SEGURIDAD) ---
    public function actualizar($id) {
        $tareaModel = new Tareas();
        
        // PASO 1: Buscar la tarea
        $tarea = $tareaModel->findById($id);

        // PASO 2: VERIFICACIÓN DE PROPIEDAD
        if ($tarea && $tarea['usuario_id'] == $_SESSION['user_id']) {
            
            $titulo = $_POST['titulo'];
            $descripcion = $_POST['descripcion'];
            // Si el checkbox no se marca, no se envía, así que asumimos 0, si se envía es 1
            $estado = isset($_POST['estado']) ? 1 : 0; 

            $tareaModel->updateTarea($id, $titulo, $estado, $descripcion);
            header("Location: ../Views/layouts/tablero.php?msg=tarea_actualizada");
        } else {
            header("Location: ../Views/layouts/tablero.php?error=acceso_denegado");
        }
        exit();
    }
}

// Lógica para instanciar si se llama directamente al archivo
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    new TareasController();
}
?>
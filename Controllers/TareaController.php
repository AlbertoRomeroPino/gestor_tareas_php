<?php
// 1. Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Importar el modelo
require_once __DIR__ . '/../Models/Tarea.php';

class TareasController {

    // --- PROPIEDAD PRIVADA PARA EL MODELO ---
    private $tareaModel;

    public function __construct() {
        
        // 1. INICIALIZAMOS EL MODELO (Igual que en AuthController)
        // Esto crea la conexión a la BD una sola vez al cargar el controlador
        $this->tareaModel = new Tareas();

        // 2. LÓGICA DE ENRUTAMIENTO
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
            }
        }
    }

    // --- 1. LISTAR TAREAS (INDEX) ---
    public function index() {
        // Ya no hacemos 'new Tareas()', usamos la propiedad de la clase
        return $this->tareaModel->findAllByUserId($_SESSION['user_id']);
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

        // Usamos la propiedad de la clase
        $this->tareaModel->createTarea($titulo, $estado, $descripcion, $usuario_id);

        header("Location: ../Views/layouts/tablero.php");
        exit();
    }

    // --- 3. ELIMINAR TAREA (CON SEGURIDAD) ---
    public function eliminar($id) {
        
        // PASO 1: Buscar la tarea primero usando la propiedad
        $tarea = $this->tareaModel->findById($id);

        // PASO 2: VERIFICACIÓN DE PROPIEDAD (SEGURIDAD)
        if ($tarea && $tarea['usuario_id'] == $_SESSION['user_id']) {
            
            // Si es dueño, procedemos a borrar
            $this->tareaModel->deleteTarea($id);
            header("Location: ../Views/layouts/tablero.php?msg=tarea_eliminada");
        } else {
            // Si no es dueño o no existe
            header("Location: ../Views/layouts/tablero.php?error=acceso_denegado");
        }
        exit();
    }

    // --- 4. ACTUALIZAR TAREA (CON SEGURIDAD) ---
    public function actualizar($id) {
        
        // PASO 1: Buscar la tarea
        $tarea = $this->tareaModel->findById($id);

        // PASO 2: VERIFICACIÓN DE PROPIEDAD
        if ($tarea && $tarea['usuario_id'] == $_SESSION['user_id']) {
            
            $titulo = $_POST['titulo'];
            $descripcion = $_POST['descripcion'];
            $estado = isset($_POST['estado']) ? 1 : 0; 

            $this->tareaModel->updateTarea($id, $titulo, $estado, $descripcion);
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
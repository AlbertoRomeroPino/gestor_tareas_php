 <?php
// 1. Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Importar el modelo
require_once __DIR__ . '/../Models/Tarea.php';

class TareasController {

    // Propiedad privada para el modelo
    private $tareaModel;

    public function __construct() {
        
        // Inicializamos el modelo una sola vez
        $this->tareaModel = new Tareas();

        // --- ENRUTAMIENTO (Router) ---

        // A. RUTAS POST (Formularios: Crear, Editar)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'crear') {
                    $this->agregar();
                }
                elseif ($_POST['action'] === 'editar') {
                    $this->actualizar($_POST['id']);
                }
            }

        // B. RUTAS GET (Enlaces: Eliminar, Cambiar Estado)
        } elseif (isset($_GET['action'])) {
            
            // Verificamos que venga un ID para las acciones que lo requieren
            if (isset($_GET['id'])) {
                
                // 1. Eliminar
                if ($_GET['action'] === 'eliminar') {
                    $this->eliminar($_GET['id']);
                }

                // 2. Cambiar Estado (El Tik)
                if ($_GET['action'] === 'cambiar_estado') {
                    // Si no viene el parámetro estado, asumimos 0
                    $nuevoEstado = isset($_GET['estado']) ? $_GET['estado'] : 0;
                    $this->cambiarEstado($_GET['id'], $nuevoEstado);
                }
            }
        }
    }

    // --- MÉTODOS DE LÓGICA ---

    // 1. LISTAR TAREAS (INDEX)
    public function index() {
        return $this->tareaModel->findAllByUserId($_SESSION['user_id']);
    }

    // 2. AGREGAR TAREA
    public function agregar() {
        if (empty($_POST['titulo'])) {
            header("Location: ../Views/layouts/tablero.php?error=titulo_vacio");
            exit();
        }

        $titulo = trim($_POST['titulo']);
        $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
        $estado = 0; 
        $usuario_id = $_SESSION['user_id'];

        $this->tareaModel->createTarea($titulo, $estado, $descripcion, $usuario_id);

        header("Location: ../Views/layouts/tablero.php");
        exit();
    }

    // 3. ELIMINAR TAREA
    public function eliminar($id) {
        $tarea = $this->tareaModel->findById($id);

        if ($tarea && $tarea['usuario_id'] == $_SESSION['user_id']) {
            $this->tareaModel->deleteTarea($id);
            header("Location: ../Views/layouts/tablero.php?msg=tarea_eliminada");
        } else {
            header("Location: ../Views/layouts/tablero.php?error=acceso_denegado");
        }
        exit();
    }

    // 4. ACTUALIZAR TAREA (Texto y Descripción)
    public function actualizar($id) {
        $tarea = $this->tareaModel->findById($id);

        if ($tarea && $tarea['usuario_id'] == $_SESSION['user_id']) {
            $titulo = $_POST['titulo'];
            $descripcion = $_POST['descripcion'];
            // Ojo: aquí mantenemos el estado que ya tenía, no lo reseteamos
            $estado = $tarea['estado']; 

            $this->tareaModel->updateTarea($id, $titulo, $estado, $descripcion);
            header("Location: ../Views/layouts/tablero.php?msg=tarea_actualizada");
        } else {
            header("Location: ../Views/layouts/tablero.php?error=acceso_denegado");
        }
        exit();
    }

    // 5. CAMBIAR ESTADO (Check/Uncheck) - LA FUNCIÓN NUEVA
    public function cambiarEstado($id, $nuevoEstado) {
        // 1. Buscamos la tarea para asegurar que es del usuario y obtener sus datos actuales
        $tarea = $this->tareaModel->findById($id);

        if ($tarea && $tarea['usuario_id'] == $_SESSION['user_id']) {
            // 2. Actualizamos SOLO el estado, manteniendo el título y descripción originales
            $this->tareaModel->updateTarea($id, $tarea['titulo'], $nuevoEstado, $tarea['descripcion']);
            
            // 3. Redirigimos al tablero
            header("Location: ../Views/layouts/tablero.php");
        } else {
            header("Location: ../Views/layouts/tablero.php?error=acceso_denegado");
        }
        exit();
    }
}

// --- INSTANCIACIÓN AUTOMÁTICA ---
// Esto es vital. Si falta esto, verás la PANTALLA EN BLANCO.
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    new TareasController();
}
?>
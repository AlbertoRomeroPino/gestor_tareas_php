# ./create_db

La creación de la base de datos la he hecho con PDO y la he almacenado en /data con el nombre gestor_tareas.db. También la base de datos utilizada va a ser SQlite.

### Parámetros de Conexión PDO

El script utiliza el modo de excepciones para el manejo de errores SQL:

| **Componente**                 | **Descripción**                                                                                |
| ------------------------------------ | ----------------------------------------------------------------------------------------------------- |
| **`$db->setAttribute`**      | Método para cambiar la configuración de la conexión.                                               |
| **`PDO::ATTR_ERRMODE`**      | Opción que controla el "Modo de Reporte de Errores".                                                 |
| **`PDO::ERRMODE_EXCEPTION`** | Valor que activa el "Modo Excepción". Permite capturar errores SQL críticos mediante `try/catch`. |

### Entidades a utilizar

Después de tener la conexión con la base de datos y crearla, metemos las 2 entidades que se van a almacenar en este proyecto:

Usuario y Tareas

```sql
CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL
    )
CREATE TABLE IF NOT EXISTS tareas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT NOT NULL,
    descripcion TEXT NOT NULL,
    estado TEXT DEFAULT 'pendiente',
    usuario_id INTEGER,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
)
```

Estas creaciones de entidades para la base de datos las insertamos en variables con el nombre de `$sqlUsuarios` y `$sqlTareas`

y usando una función de pdo lo ejecutamos con exec:

```php
$db->exec($sqlUsuarios);
$db->exec($sqlTareas);
```

### Creación del primer usuario

Al ejecutar el create_db.php en mi proyecto no solo crea la base de datos y las entidades sino que también crea el primer usuario llamado admin con contraseña 1234. Esto nunca es recomendable hacerlo porque si es filtrado se podría ver tanto el usuario como contraseña en texto plano y adicionalmente las contraseñas nunca se deben almacenar en texto plano sino hasheadas.

#### Paso 1 Comprobación de insertar

```php
$check = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

if ($check == 0) { 
    // ... Entramos aquí solo si la tabla está vacía
}
```

Lo que realiza son 3 acciones:

1. Comprueba el número de usuarios y te devuelve el  número .
2. `fetchColumn()` al solo recibir un número esta función de php coge directamente ese valor simple.
3. El resultado de la consulta sql y el resultado recibido de fetch lo almacena en `$check`.
4. Por último si el valor de `$check` es igual a 0 (Es decir que no hay usuario) se entra en la condición.

#### Paso 2 Seguridad e Inserción Blindada

Como seguridad vamos a hashear la contraseña con el código `password_Hash();`

```php
$passHash = password_hash("1234", PASSWORD_DEFAULT);
```

`password_hash` Esta función necesita un string (1234) y la convierte en una cadena larga y compleja.

`PASSWORD_DEFAULT` Esto hace que php utilice un algoritmo de encriptación más seguro.

---

Ahora ya teniendo la contraseña en la variable `$passHash` podemos empezar ya a hacer la inserción. La contraseña también puede hacerse después o entre esto pero así nos quitamos futuros problemas con hashearla y no se mete tanta explicación en este apartado.

```php
 $stmt = $db->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
        $stmt->execute(['admin', $passHash]);
```

Esta parte se divide en 2, una más compleja que la otra:

La primera línea lo que realiza es como una sentencia preparada. Voy a añadir un usuario y contraseña que todavía no tengo, pero se almacena en $stmt.

El significado de las `?` es principalmente donde se van a almacenar los datos que van a ser insertados.

La segunda línea es más simple: `$stmt` ejecuta la consulta que hemos creado con anterioridad pero con los datos que le pasas en el array. En este caso le pasas en la primera posición (Nombre) admin y en la segunda posición (Contraseña) `$password` y se ejecuta para almacenarse en la base de datos.

#### Paso 3 Pequeñas comprobaciones

Esta parte no es importante porque son simplemente imprimir por pantalla los datos almacenados en la base de datos y si todo ha ido bien mostraría por pantalla:

```php
echo "✅ Usuario creado por defecto.<br>";
        echo "User: <b>admin</b> <br> Pass: <b>1234</b><br><br>";
    }

    echo "✅ Tablas 'usuarios' y 'tareas' listas en SQLite.";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
```

Esto de todas formas en un proyecto serio no se debería hacer porque sería una vulnerabilidad, pero he preferido ponerlo para mostrar cosas si pasa el if.

# ./Config/Database.php

```php
public static function conectar()
    {
        $db = __DIR__ . '/../data/gestor_tareas.db';

        try {
            $pdo = new PDO('sqlite:'. $db);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
            return $pdo;
        } catch (PDOException $excepcion) {
            die("Error fatal de conexión: " . $excepcion->getMessage());
        }
    }
```

Esta clase gestiona la conexión a la base de datos SQLite utilizando PDO. Se implementa un bloque `try-catch` para el manejo robusto de excepciones y se configura la instancia con `PDO::FETCH_ASSOC` para obtener los resultados como arrays asociativos. El método retorna el objeto de conexión (`$pdo`) listo para su uso.

# ./Models/Tarea.php

Esta sección aborda la arquitectura de la clase en tres niveles: atributos, funciones y una explicación práctica del manejo de consultas SQL.

### Parte 1 Atributos

Gestiona la información y operaciones de las tareas del tablero.

**Infraestructura**

* **`$db`** : Instancia de la conexión a la base de datos (objeto `PDO`). Permite la ejecución de las consultas SQL.

**Atributos de la Entidad**

* **`$id`** : Identificador único numérico de la tarea (Primary Key).
* **`$titulo`** : Encabezado breve o nombre de la tarea.
* **`$descripcion`** : Texto extendido con los detalles de la actividad.
* **`$estado`** : Indicador del flujo de trabajo (ej. 'pendiente', 'en proceso', 'completado').
* **`$usuario_id`** : Clave foránea que vincula la tarea con su creador/propietario (relación con la entidad `User`).
* **`$fecha_creacion`**: fecha en la que se creo la tarea

### Parte 2 Funciones

* **`__construct`**: El constructor de la clase
* **`findAllByUserId`** : Recupera el listado completo de todas las tareas registradas en la base de datos.
* **`findByID`** : Obtiene los detalles de una tarea específica buscando por su identificador único (Primary Key).
* **`findByTitulo`** : Realiza una búsqueda de tareas cuyo título coincida parcial o totalmente con el término proporcionado.
* **`findByEstado`** : Filtra y devuelve las tareas que corresponden a un estado específico ('pendiente', 'realizado').
* **`createTarea`** : Inserta un nuevo registro de tarea en la base de datos con la información suministrada.
* **`updateTarea`** : Modifica los datos (título, descripción, estado) de una tarea existente identificada por su ID.
* **`deleteTarea`** : Elimina permanentemente el registro de una tarea específica de la base de datos.

### Parte 3 Explicación

Voy a explicar la **función** `createTarea`:

```php
public function createTarea($titulo, $estado, $descripcion, $usuario_id)
    {

        $sql = "INSERT INTO tareas (titulo, estado, descripcion, usuario_id) VALUES (:titulo, :estado, :descripcion, :usuario_id)";
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute([
                ':titulo' => $titulo,
                ':estado' => $estado,
                ':descripcion' => $descripcion,
                ':usuario_id' => $usuario_id
            ]);
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
```

Los pasos que **he realizado** para ejecutar un crear tarea:

1. **Definición SQL:** Se declara la consulta `INSERT` utilizando marcadores de posición (`:titulo`, `:estado`...) en lugar de variables directas para seguridad.
2. **Preparación:** Se invoca el método `prepare()` para convertir el SQL en un objeto `PDOStatement` optimizado y seguro.
3. **Control de Excepciones:** Se inicia un bloque `try-catch` para manejar cualquier error crítico durante la comunicación con la base de datos.
4. **Ejecución y Vinculación:** Se llama a `execute()` pasando un array que vincula los valores reales con los marcadores definidos anteriormente.
5. **Retorno de Estado:** La función finaliza devolviendo `true` si la inserción tuvo éxito o `false` (y registrando el error) si falló.

# ./Models/User.php

### Parte 1 Atributos

**Infraestructura**

* **`$db`** : Instancia de la conexión a la base de datos (objeto `PDO`). Permite la ejecución de las consultas SQL.

**Atributos de la Entidad**

* `$id`: Identificador único numérico de la tarea (Primary Key).
* `$username`: Nombre del usuario
* `$password`: Contraseña del usuario

### Parte 2 Funciones

* `findByUsername`: Sirve principalmente para comprobar un usuario cuando es insertado en el login
* `create`: Creamos un nuevo usuario

# ./Controller/AuthController.php

```php
session_start();
require_once __DIR__ . '/../Models/User.php';
```

`session_start();` Crea o recupera una memoria temporal en el servidor para el usuario actual. Sin esta la web olvidaria quien eres y perderia tus datos cada vez que cambias de pagina.

`require_once __DIR__ . '/../Models/User.php'` importamos el modelo user para poder hablar con la base de datos

Posterior a esto ya empieza la clase `AuthController`:

**Atributos de la Entidad y Contructor**

`$userModel`:  Es la entidad usuario que se conecta con la clase. Y el constructor es darle el valor a esta

**Login**

```php
public function login($username, $password, $remember = false) // 1
    {
        $user = $this->userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            // A. Guardamos sesión (Memoria RAM del servidor)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // B. Manejo de Cookie (Disco Duro del usuario)
            if ($remember) { // 2
                // time() es la hora actual en segundos.
                // 86400 son los segundos de un día.
                // Multiplicado por 30 = 1 mes de duración.
                setcookie('user_login', $user['username'], time() + (86400 * 30), "/"); // 3
            }

            header("Location: ../Views/tablero.php");
            exit();
        } 
        // ... (else error)
    }
```

Primero recibe 3 cosas:

* Nombre de usuario
* Contraseña
* remember (Cookie para guardar durante un tiempo el usuario )

y despues de recibir todo creamos un nuevo usuario buscando desde el nombre de usuario que se inserta.

Si el paso anterior no existiera el usuario daria un mensaje de error pero en cambio si existe el nombre de usuario y al mismo tiempo a puesto bien la contraseña entraria en el if.

Una vez dentro de la primera comprobación almacenamos el id de usuario y el nombre de usuario para la sesion y por ultimo antes de entrar a la pagina comprueba que si esta a true remember, crea una cookie para mantener almacenada la sesion de usuario para que se mantenga abierta durante 1 mes.

**Logout**

```php
public function logout()
    {
        // 1. Borramos la sesión del servidor
        session_destroy(); 

        // 2. Borramos la cookie del navegador
        if (isset($_COOKIE['user_login'])) {
            setcookie('user_login', '', time() - 3600, "/"); // 1
        }

        header("Location: ../Views/auth/login.php");
        exit();
    }
```

Esto es mucho mas simple que el login, empezamos destruyendo la sesion que teniamos con la información del usuario. Luego de borrar la sesion porsi en el caso de haber querido que se mantubiese la sesion abierta la borramos para que no de problemas y devolvemos a la pantalla del login

**checkCookie**

```php
public function checkCookie()
    {
        // 1. ¿Ya estás dentro? Si sí, no hago nada.
        if (isset($_SESSION['user_id'])) {
            return;
        }

        // 2. No estás dentro, pero... ¿tienes la etiqueta "user_login"?
        if (isset($_COOKIE['user_login'])) {
            $username = $_COOKIE['user_login'];
  
            // Busco en la BD si ese usuario existe
            $user = $this->userModel->findByUsername($username);
  
            if ($user) {
                // 3. ¡Bingo! Te restauro la sesión automáticamente
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
            }
        }
    }
```

En esta función lo primero que realizamos es comprobar si esta ya hay una sesion activa en memoria termina la función inmediatamente.

El segundo if verifica si existe la cookie `user_login`. Si existe, recuperamos el nombre de usuario que hay dentro de ella y le pedimos al Modelo que busque en la Base de Datos los datos completos de ese usuario.

El tercer if  lo que realiza es crear las sesiones o igualarlas para asi tener la información almacenada

**Register**

```php
public function register($username, $password)
    {
        if ($this->userModel->create($username, $password)) {
            header("Location: ../Views/auth/login.php?success=registrado");
            exit();
        } else {
            header("Location: ../Views/auth/register.php?error=fallo_registro");
            exit();
        }
    }
```

Primero recibe 2 cosas:

`$username`: nombre de usuario para crear

`$password` contraseña del usuario.

Lo que se realiza en esta función es simple se hace un if con la función de create de userModel y luego si da true de haber podido crearse te debuelve que se a creado en cambio si no se a podido crear da fallo

**Router Artesanal**

```php
// BLOQUE A: Peticiones POST (Datos que viajan ocultos: Login y Registro)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
  
    // CASO 1: LOGIN
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        // Truco del Checkbox
        $remember = isset($_POST['remember']);
  
        $auth->login($_POST['username'], $_POST['password'], $remember);
    }
  
    // CASO 2: REGISTRO
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $auth->register($_POST['username'], $_POST['password']);
    }
}

// BLOQUE B: Peticiones GET (Enlaces visibles: Logout)
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new AuthController();
    $auth->logout();
}
```

El primer if lo que realiza es comprobar que los datos vienen empaquetados de forma segura desde el formulario y si es correcto crea una variable AuthController

```php
if (isset($_POST['action']) && $_POST['action'] === 'login') {
        // ... Lógica de Login
    }

    if (isset($_POST['action']) && $_POST['action'] === 'register') {
       // ... Lógica de Registro
    }
```

Como el formulario envia todo junto, PHP necesita saber qué botón pulsaste

* Si el valor es `'login'`, llama a la función de entrar.
* Si el valor es `'register'`, llama a la función de registrarse.

```php
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new AuthController();
    $auth->logout();
}
```

Con este codigo lo unico que realiza es un logout

# ./Controller/TareaController.php

```php
// 1. Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Importar el modelo
require_once __DIR__ . '/../Models/Tarea.php';
```

Lo primero que realizo en TareaController es comprobar si hay una sesion iniciada si en el caso de que no este la sesion iniciada se inicia aqui.

Luego se importa el modelo que en este caso es tarea.php

**Atributos de la Entidad**

`$tareaModel`:  Es la entidad tarea que se conecta con la clase. Y el constructor es darle el valor a esta

**Contructor**

Este constructor actúa como el "cerebro" inicial del controlador. Se ejecuta automáticamente al instanciar la clase y su función principal es dirigir el tráfico dependiendo de cómo lleguen los datos (por formulario o por enlace).

```PHP
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
            elseif ($_POST['action'] === 'editar') {$this->actualizar($_POST['id']);
            }
        }

    // B. RUTAS GET (Enlaces: Eliminar, Cambiar Estado)
    } elseif (isset($_GET['action'])) {

    // Verificamos que venga un ID para las acciones que lo requieren
        if (isset($_GET['id'])) {

    // 1. Eliminar
            if ($_GET['action'] === 'eliminar') {$this->eliminar($_GET['id']);
            }

    // 2. Cambiar Estado (El Tik)
            if ($_GET['action'] === 'cambiar_estado') {
                // Si no viene el parámetro estado, asumimos 0$nuevoEstado = isset($_GET['estado']) ? $_GET['estado'] : 0;
                $this->cambiarEstado($_GET['id'], $nuevoEstado);
            }
        }
    }
}
```

1. Inicialización del Modelo
   Lo primero que realizamos es instanciar la clase Tareas.

```php
$this->tareaModel = new Tareas();
```

2. Gestión de Rutas POST (Formularios)
   Este bloque maneja las acciones que envían datos sensibles o grandes, como Crear y Editar.

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'crear') {
            $this->agregar();
        } elseif ($_POST['action'] === 'editar') {
            $this->actualizar($_POST['id']);
        }
    }
}
```

Filtro de Método: El primer if separa las peticiones. Si el método es POST, entramos aquí.

Validación: Comprobamos con isset si existe una variable action. Si es null, no hace nada.

Enrutamiento:

Si la acción es 'crear', llamamos a la función $this->agregar().

Si la acción es 'editar', llamamos a $this->actualizar() pasando el ID recibido.

3. Gestión de Rutas GET (Enlaces y Acciones Rápidas)
   Este bloque maneja acciones que se pueden realizar a través de la URL, como Eliminar o Cambiar Estado.

```php
} elseif (isset($_GET['action'])) {

    // Verificamos que venga un ID obligatorio
    if (isset($_GET['id'])) {

    // Acción 1: Eliminar
        if ($_GET['action'] === 'eliminar') {$this->eliminar($_GET['id']);
        }

    // Acción 2: Cambiar Estado
        if ($_GET['action'] === 'cambiar_estado') {$nuevoEstado = isset($_GET['estado']) ? $_GET['estado'] : 0;
            $this->cambiarEstado($_GET['id'], $nuevoEstado);
        }
    }
}
```

Flujo lógico:

Filtro Alternativo: Usamos elseif para capturar la petición solo si no fue POST y si existe $_GET['action']. Esto añade una capa de seguridad y orden.

Validación de ID: Antes de ejecutar nada, comprobamos isset($_GET['id']). Esto es crucial para evitar errores, ya que no podemos eliminar o modificar una tarea sin saber cuál es.

Enrutamiento Específico:

Si la acción es 'eliminar', ejecutamos el borrado.

Si la acción es 'cambiar_estado', recogemos el nuevo estado y actualizamos la tarea en la base de datos.

**index**

```php
// 1. LISTAR TAREAS (INDEX)
    public function index() {
        return $this->tareaModel->findAllByUserId($_SESSION['user_id']);
    }
```

Esto lo que hace es que cuando un usuario se registra ve directamente todas sus tareas en vez de todas las tareas en la aplicación.

**agregar**

```php
// 2. AGREGAR TAREA
public function agregar() {
    // 1. Validación
    if (empty($_POST['titulo'])) {
        header("Location: ../Views/layouts/tablero.php?error=titulo_vacio");
        exit();
    }

    // 2. Recogida y limpieza de datos
    $titulo = trim($_POST['titulo']);
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
    $estado = 0; 
    $usuario_id = $_SESSION['user_id'];

    // 3. Insertar en BD
    $this->tareaModel->createTarea($titulo, $estado, $descripcion, $usuario_id);

    // 4. Redirección
    header("Location: ../Views/layouts/tablero.php");
    exit();
}
```

Lo primero que realizo en esta función es una  **validación de seguridad** : compruebo si el campo `titulo` viene vacío. Si es así, expulsamos al usuario de la función y lo devolvemos al tablero con un mensaje de error, ya que no se pueden crear tareas sin nombre.

Si pasa la validación, preparamos las 4 variables necesarias para la base de datos:

* `$titulo`: Usamos `trim()` para limpiar espacios en blanco al principio y al final del texto que escribió el usuario.
* `$descripcion`: Comprobamos si existe y tiene contenido; si es así la limpiamos, y si no, le asignamos `null`.
* `$estado`: Se fuerza a `0`, ya que al crear una tarea nueva, por defecto siempre nace como "Pendiente" (o incompleta).
* `$usuario_id`: Recuperamos el ID de la memoria de sesión (`$_SESSION`) para asociar la tarea al usuario que está logueado en ese momento.

Finalmente, llamamos al método `createTarea` del Modelo pasándole estos datos limpios y redirigimos al usuario de vuelta al tablero para que vea su nueva tarea en la lista.

**eliminar**

```php
// 3. ELIMINAR TAREA
public function eliminar($id) {
    // 1. Búsqueda previa
    $tarea = $this->tareaModel->findById($id);

    // 2. Verificación de seguridad (Propiedad)
    if ($tarea && $tarea['usuario_id'] == $_SESSION['user_id']) {
    
        // 3. Borrado físico
        $this->tareaModel->deleteTarea($id);
        header("Location: ../Views/layouts/tablero.php?msg=tarea_eliminada");
    } else {
        // 4. Rechazo por seguridad
        header("Location: ../Views/layouts/tablero.php?error=acceso_denegado");
    }
    exit();
}
```

Esta función recibe un parámetro crítico: `$id`, que es el número identificador de la tarea que se quiere borrar.

Lo primero y más importante que hago aquí no es borrar, sino  **investigar** . Llamo a `findById($id)` para recuperar toda la información de esa tarea desde la base de datos.

Una vez tengo los datos de la tarea, aplicamos un **filtro de seguridad estricto** (el `if`):

* **¿Existe la tarea?** (`$tarea`): Verificamos que el ID sea real y no un número inventado.
* **¿Eres el dueño?** (`$tarea['usuario_id'] == $_SESSION['user_id']`): Esta es la clave. Comparamos el "ID del dueño de la tarea" (que viene de la base de datos) con el "ID del usuario conectado" (que está en la memoria de sesión).

 **Si ambas condiciones se cumplen** , procedemos a llamar a `deleteTarea` para borrarla definitivamente y redirigimos con un mensaje de éxito.

**Si alguna condición falla** (por ejemplo, si intentas borrar la tarea ID 50 cambiando la URL manualmente, pero esa tarea es de otro usuario), te expulsamos al tablero con un error de "acceso denegado". Esto evita que un usuario malintencionado borre datos ajenos.

**actualizar**

```php
// 4. ACTUALIZAR TAREA (Texto y Descripción)
public function actualizar($id) {
    // 1. Recuperar datos actuales
    $tarea = $this->tareaModel->findById($id);

    // 2. Verificación de seguridad (Propiedad)
    if ($tarea && $tarea['usuario_id'] == $_SESSION['user_id']) {
    
        // 3. Recogida de nuevos datos
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
    
        // 4. Preservar el estado anterior
        $estado = $tarea['estado']; 

        // 5. Guardar cambios
        $this->tareaModel->updateTarea($id, $titulo, $estado, $descripcion);
        header("Location: ../Views/layouts/tablero.php?msg=tarea_actualizada");
    } else {
        header("Location: ../Views/layouts/tablero.php?error=acceso_denegado");
    }
    exit();
}
```

Esta función se encarga de modificar el texto de una nota existente. Recibe el `$id` de la tarea que queremos editar.

El proceso es una mezcla entre la lógica de "Crear" y la de "Eliminar":

1. **Investigación Previa:** Al igual que en `eliminar`, primero buscamos la tarea en la base de datos con `findById($id)`. Esto es vital para saber de quién es la tarea y  cómo estaba antes .
2. **Filtro de Seguridad:** Comprobamos de nuevo que la tarea exista y que el usuario conectado (`$_SESSION`) sea el dueño legítimo.
3. **Recogida de Datos:** Obtenemos el nuevo título y descripción que vienen del formulario (el Post-it amarillo).
4. **Lógica del Estado (El detalle importante):**
   * Al contrario que al "Crear" (donde forzamos el estado a 0), aquí recuperamos el estado que la tarea **ya tenía** en la base de datos (`$tarea['estado']`).
   * **¿Por qué?** Porque si estás editando una falta de ortografía en una tarea que ya marcaste como "Completada", no quieres que se desmarque sola. Queremos que siga completada.
5. **Actualización:** Llamamos al método `updateTarea` del Modelo, que sobrescribe los datos viejos con los nuevos en la base de datos.

**cambiarEstado**

```php
// 5. CAMBIAR ESTADO (Check/Uncheck)
public function cambiarEstado($id, $nuevoEstado) {
    // 1. Recuperación de datos antiguos
    $tarea = $this->tareaModel->findById($id);

    // 2. Verificación de seguridad
    if ($tarea && $tarea['usuario_id'] == $_SESSION['user_id']) {
    
        // 3. Actualización "Quirúrgica"
        // Mantenemos el título y descripción que YA tenía, solo cambiamos el estado.
        $this->tareaModel->updateTarea($id, $tarea['titulo'], $nuevoEstado, $tarea['descripcion']);
    
        header("Location: ../Views/layouts/tablero.php");
    } else {
        header("Location: ../Views/layouts/tablero.php?error=acceso_denegado");
    }
    exit();
}
```

Esta función es la que se activa cuando haces clic en el botón de "Check". Recibe dos datos:

* `$id`: La tarea que queremos modificar.
* `$nuevoEstado`: El valor **0** (pendiente/false) o **1** (completada/true) al que queremos cambiar.

La lógica tiene un truco interesante en el  **Paso 3** :

Como la función `updateTarea` en el Modelo está diseñada para recibir **todos** los campos de golpe (título, estado y descripción), no podemos enviarle solo el estado nuevo. Si lo hiciéramos, borraríamos el título y la descripción.

Por eso seguimos esta estrategia:

1. **Investigamos (Paso 1):** Recuperamos la tarea completa de la base de datos para tener sus datos originales.
2. **Mezclamos (Paso 3):** Al llamar a actualizar, enviamos una mezcla:
   * El **Título Viejo** (`$tarea['titulo']`).
   * La **Descripción Vieja** (`$tarea['descripcion']`).
   * Pero incrustamos el **Estado Nuevo** (`$nuevoEstado`).

Así conseguimos el efecto de "solo cambiar el checkbox" sin perder la información de texto de la nota.

**Interruptor**

Este código sirve para que el archivo sepa si debe **ejecutarse solo** o esperar órdenes.

```php
// --- INSTANCIACIÓN AUTOMÁTICA ---
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    new TareasController();
}
```

El código hace una comparación simple entre **"Quién soy yo"** y  **"A quién han llamado"** .

1. **Si son iguales (Acceso Directo):**
   Significa que el navegador ha entrado directamente en este archivo.
   * **Acción:** Se ejecuta `new TareasController()`. El controlador se activa, procesa los datos y trabaja inmediatamente.
2. **Si son diferentes (Acceso Indirecto):**
   Significa que otro archivo (como tu web principal) ha incluido a este solo para leer sus funciones.
   * **Acción:** El código **NO** entra en el `if`. El controlador se carga en memoria pero se queda "dormido" esperando a que tú lo uses manualmente.

¿Por qué es obligatorio?

Si eliminas este bloque, el archivo se convierte en un simple almacén de código.

Cuando intentes enviar datos para guardar o borrar, el navegador abrirá el archivo, leerá el código y **terminará sin hacer nada** (pantalla en blanco), porque nadie dio la orden de  **"Empezar a trabajar"** .

# ./index.php

Este archivo es el **punto de entrada principal** de la aplicación. Actúa como un conserje que recibe al usuario, verifica si tiene permiso para pasar y lo envía a la habitación correcta (Login o Tablero).

### Infraestructura del Archivo

| **Componente**          | **Función**                                                |
| ----------------------------- | ----------------------------------------------------------------- |
| **`session_start()`** | Activa la memoria del servidor para reconocer al usuario.         |
| **`AuthController`**  | Clase encargada de toda la lógica de seguridad y acceso.         |
| **`checkCookie()`**   | Método que intenta recuperar sesiones antiguas mediante cookies. |

---

### Explicación Paso a Paso

He diseñado la lógica de este archivo en 4 pasos críticos para asegurar que nadie entre al sistema sin permiso:

#### Paso 1: Gestión de Sesión

**PHP**

```
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

Lo primero que hacemos es comprobar si ya existe una sesión. Si el servidor no tiene una iniciada, la creamos. Sin esto, no podríamos saber quién es el usuario que acaba de entrar a nuestra web.

#### Paso 2: Importación y Control

**PHP**

```
require_once __DIR__ . '/Controllers/AuthController.php';
$auth = new AuthController();
```

Importamos el controlador de autenticación usando rutas absolutas (`__DIR__`). Esto es una **buena práctica** porque evita que el archivo falle si movemos carpetas. Inmediatamente después, instanciamos el controlador para poder usar sus herramientas.

#### Paso 3: El "Auto-Login" (Cookies)

**PHP**

```
$auth->checkCookie();
```

Antes de decidir a dónde enviar al usuario, llamamos a `checkCookie()`. Esto revisa si el usuario marcó la casilla "Recordarme" en el pasado. Si la cookie existe, el sistema le devolverá su sesión de forma invisible y automática.

#### Paso 4: El Guardia de Tráfico (Redirección)

**PHP**

```
if (isset($_SESSION['user_id'])) {
    header("Location: Views/layouts/tablero.php");
} else {
    header("Location: Views/auth/login.php");
}
exit();
```

Finalmente, realizamos la comprobación definitiva:

1. **¿Hay un ID de usuario en la sesión?** Si la respuesta es sí (ya sea porque acaba de loguearse o por la cookie), lo mandamos directamente al  **Tablero** .
2. **¿No hay sesión?** Entonces lo mandamos al **Login** para que se identifique.
3. **`exit()`** : Usamos esta función para asegurar que el servidor deje de trabajar inmediatamente después de enviar la orden de redirección.

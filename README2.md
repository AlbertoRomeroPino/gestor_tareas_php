
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
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
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
 try {
            $pdo = new PDO('sqlite', $db);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $pdo;
        } catch (PDOException $excepcion) {
            die("Error fatal de conexión: " . $excepcion->getMessage());
        }
```

Esta clase gestiona la conexión a la base de datos SQLite utilizando PDO. Se implementa un bloque `try-catch` para el manejo robusto de excepciones y se configura la instancia con `PDO::FETCH_ASSOC` para obtener los resultados como arrays asociativos. El método retorna el objeto de conexión (`$pdo`) listo para su uso.

# ./Models/Tarea.php

La entidad usuario es mas simple a esta lo unico que tiene nuevo es password_hash que es para encriptar la contraseña

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

### Parte 2 Funciones

* **`findAll`** : Recupera el listado completo de todas las tareas registradas en la base de datos.
* **`findByID`** : Obtiene los detalles de una tarea específica buscando por su identificador único (Primary Key).
* **`findByTitulo`** : Realiza una búsqueda de tareas cuyo título coincida parcial o totalmente con el término proporcionado.
* **`findByEstado`** : Filtra y devuelve las tareas que corresponden a un estado específico (ej. 'pendiente', 'en proceso').
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

# ./controller/AuthController.php

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

Una vez dentro de la primera comprobación almacenamos el id de usuario y el nombre de usuario para la sesion y por ultimo antes de entrar a la pagina comprueba que si esta a true remember cree una cookie para mantener almacenada la sesion de usuario para que se mantenga abierta durante 1 mes.

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

# Views/auth/login

```php
<?php if (isset($_GET['error'])): ?>
                    <span class="error-text">
                        <?php
                            if ($_GET['error'] == 'credenciales_incorrectas') echo "Datos inválidos";
                            if ($_GET['error'] == 'usuario_existe') echo "El usuario ya existe";
                        ?>
                    </span>
                <?php endif; ?>
```

PHP revisa la URL del navegador. Si el controlador te redirigió con algo como `?error=usuario_existe`, este apartado se activa.

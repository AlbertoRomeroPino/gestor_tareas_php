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

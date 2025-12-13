# He empezado creando el create_db

La creación de la base de datos la e echo con PDO y la e almacenado en /data con el nombre gestor_tareas.db. Tambien la base de datos utilizada va a ser SQlite.

### Parámetros de Conexión PDO

El script utiliza el modo de excepciones para el manejo de errores SQL:

| Componente                           | Descripción                                                                                          |
| :----------------------------------- | :---------------------------------------------------------------------------------------------------- |
| **`$db->setAttribute`**      | Método para cambiar la configuración de la conexión.                                               |
| **`PDO::ATTR_ERRMODE`**      | Opción que controla el "Modo de Reporte de Errores".                                                 |
| **`PDO::ERRMODE_EXCEPTION`** | Valor que activa el "Modo Excepción". Permite capturar errores SQL críticos mediante `try/catch`. |

### Entidades a utilizar

Despues de tener la conexión con la base de datos y crearla metemos las 2 entidades que se van a almacenar en este proyecto:

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
        estado TEXT DEFAULT 'pendiente',
        usuario_id INTEGER,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )
```

Estas creaciones de entidades para la base de datos las insertamos en variables con el nombre de `$sqlUsuarios` y `$sqlTareas`

y usando una función de pdo lo ejecutamos com exec:

```sql
$db->exec($sqlUsuarios);
$db->exec($sqlTareas);
```

### Creación del primer usuario

Al ejecutar el create_db.php en mi proyecto no solo crea la base de datos y las entidades sino que tambien crea el primer usuario llamado admin con contraseña 1234. Esto nunca es recomendable hacerlo porque si es filtrado se podria ver tanto el usuario como contraseña en texto plano y adicionamente las contraseñas nunca se deben almacenar en texto plano sino haseadas.

#### Paso 1

```php
$check = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

if ($check == 0) { 
    // ... Entramos aquí solo si la tabla está vacía
}
```

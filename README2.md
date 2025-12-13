✨ Versión Pulida y Profesional
Aquí tienes tu texto corregido y mejorado. He mantenido tu estructura y tus explicaciones, pero con un tono más técnico y sin faltas.

Markdown

# Inicialización de la Base de Datos (`create_db.php`)

La creación de la base de datos se ha realizado utilizando **PDO** y **SQLite**. El archivo resultante se almacena en `/data` bajo el nombre `gestor_tareas.db`.

### Parámetros de Conexión PDO

El script utiliza el modo de excepciones para asegurar un manejo robusto de errores SQL:

| Componente                           | Descripción                                                                                                  |
| :----------------------------------- | :------------------------------------------------------------------------------------------------------------ |
| **`$db->setAttribute`**      | Método para cambiar la configuración de la conexión activa.                                                |
| **`PDO::ATTR_ERRMODE`**      | Opción que controla el "Modo de Reporte de Errores".                                                         |
| **`PDO::ERRMODE_EXCEPTION`** | Valor que activa el "Modo Excepción". Permite capturar errores SQL críticos mediante bloques `try/catch`. |

### Definición del Modelo de Datos (Entidades)

Una vez establecida la conexión, definimos las dos entidades principales del proyecto: **Usuario** y **Tareas**.

```sql
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS tareas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT NOT NULL,
    estado TEXT DEFAULT 'pendiente',
    usuario_id INTEGER,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

Estas sentencias SQL se almacenan en las variables `$sqlUsuarios` y `$sqlTareas`. Posteriormente, se ejecutan mediante el método `exec()` de PDO:

**PHP**

```
$db->exec($sqlUsuarios);
$db->exec($sqlTareas);
```

### Creación del Usuario Administrador (Seeding)

Al ejecutar `create_db.php`, el script no solo crea la estructura, sino que inserta un usuario de prueba (**admin** /  **1234** ).

> ⚠️ **Nota de Seguridad:** Crear usuarios por defecto con contraseñas conocidas no es recomendable en producción. Además, las contraseñas nunca deben guardarse en texto plano, sino hasheadas (cifradas).

#### Paso 1: Verificación de Existencia

Para evitar duplicados, primero comprobamos si ya existen usuarios:

**PHP**

```php
$check = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

if ($check == 0) { 
    // ... Entramos aquí solo si la tabla está vacía
}
```

El código realiza 3 acciones:

1. **Consulta:** Cuenta el número total de filas en la tabla `usuarios`.
2. **`fetchColumn()`:** Al recibir un único dato (el conteo), esta función recupera directamente el valor numérico.
3. **Condición:** Si `$check` es `0` (tabla vacía), procede a la inserción.

#### Paso 2: Seguridad e Inserción Blindada

Para la seguridad, ciframos la contraseña utilizando `password_hash()`:

**PHP**

```php
$passHash = password_hash("1234", PASSWORD_DEFAULT);
```

* **`password_hash`** : Convierte el string "1234" en un hash complejo y seguro.
* **`PASSWORD_DEFAULT`** : Indica a PHP que utilice el algoritmo de encriptación más fuerte disponible en la versión actual.

Finalmente, realizamos la inserción mediante  **Sentencias Preparadas** :

**PHP**

```php
$stmt = $db->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
$stmt->execute(['admin', $passHash]);
```

Este proceso se divide en dos fases:

1. **Preparación (`prepare`):** Se crea una plantilla SQL donde los signos `?` actúan como marcadores de posición para los datos futuros.
2. **Ejecución (`execute`):** Se envían los datos reales en un array. PHP asigna `'admin'` al primer `?` y `$passHash` al segundo `?`, ejecutando la consulta de forma segura contra inyecciones SQL.

#### Paso 3: Feedback de Ejecución

Si el proceso es exitoso, el script imprime los detalles en pantalla para confirmar la creación (solo útil en entorno de desarrollo):

**PHP**

```php
    echo "✅ Usuario creado por defecto.<br>";
    echo "User: <b>admin</b> <br> Pass: <b>1234</b><br><br>";
}
echo "✅ Tablas 'usuarios' y 'tareas' listas en SQLite.";
```

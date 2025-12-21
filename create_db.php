<?php
try {

  $db = new PDO('sqlite:' . __DIR__ . '/data/gestor_tareas.db');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $sqlUsuarios = "CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL
    )";
  $db->exec($sqlUsuarios);

  $sqlTareas = "CREATE TABLE IF NOT EXISTS tareas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT NOT NULL,
    descripcion TEXT NOT NULL,
    estado TEXT DEFAULT 'pendiente',
    usuario_id INTEGER,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
)";
  $db->exec($sqlTareas);

  $check = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

  if ($check == 0) {
    // IMPORTANTE: Nunca guardes contraseñas en texto plano. Usamos password_hash.
    // Usuario: admin
    // Contraseña: 1234
    $passHash = password_hash("1234", PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $passHash]);

    echo "✅ Usuario creado por defecto.<br>";
    echo "User: <b>admin</b> <br> Pass: <b>1234</b><br><br>";
  }

  echo "✅ Tablas 'usuarios' y 'tareas' listas en SQLite.";

} catch (PDOException $e) {
  echo "❌ Error: " . $e->getMessage();
}
?>
<?php
header('Content-Type: application/json');

// Datos de conexión de PostgreSQL en Render
$host = "dpg-dbkl8q3ncjis73elbltg-a";
$port = "5432";
$dbname = "db_auditoria";
$user = "db_auditoria_user";
$password_db = "nTqU7Fpo6WVUKE1HAuMParlupKFUYPph";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. CREAR LA TABLA AUTOMÁTICAMENTE SI NO EXISTE
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )");

    // 2. CREAR UN USUARIO DE PRUEBA AUTOMÁTICO (admin / 123456)
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = 'admin'");
    $stmtCheck->execute();
    if ($stmtCheck->fetchColumn() == 0) {
        $passwordHash = password_hash("123456", PASSWORD_BCRYPT);
        $stmtInsert = $pdo->prepare("INSERT INTO usuarios (username, password) VALUES ('admin', ?)");
        $stmtInsert->execute([$passwordHash]);
    }

    // Recibir datos JSON de la app .NET MAUI
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(["status" => "error", "mensaje" => "Faltan datos de acceso."]);
        exit;
    }

    // Consultar el usuario en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar contraseña cifrada
    if ($user_row && password_verify($password, $user_row['password'])) {
        echo json_encode(["status" => "success", "mensaje" => "Login exitoso"]);
    } else {
        echo json_encode(["status" => "error", "mensaje" => "Usuario o contraseña incorrectos."]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "mensaje" => "Error de conexión: " . $e->getMessage()]);
}
?>

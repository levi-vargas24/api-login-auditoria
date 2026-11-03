<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

$host = "sql312.infinityfree.com";
$usuario_db = "if0_43339706";
$password_db = "LostAngel2499";
$nombre_db = "if0_43339706_usuarios";

try {
    $conexion = new PDO("mysql:host=$host;dbname=$nombre_db;charset=utf8", $usuario_db, $password_db);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "mensaje" => "Error de conexión al servidor"]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$user = $input['username'] ?? '';
$pass = $input['password'] ?? '';

if (empty($user) || empty($pass)) {
    echo json_encode(["status" => "error", "mensaje" => "Faltan datos obligatorios"]);
    exit();
}

$stmt = $conexion->prepare("SELECT Id, Username, Password, NombreCompleto, Rol, activo FROM usuarios WHERE Username = :username");
$stmt->execute(['username' => $user]);
$usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuarioData) {
    echo json_encode(["status" => "error", "mensaje" => "Usuario no encontrado"]);
    exit();
}

if ($usuarioData['activo'] == 0) {
    echo json_encode(["status" => "bloqueado", "mensaje" => "Su cuenta ha sido desactivada."]);
    exit();
}

if (password_verify($pass, $usuarioData['Password'])) {
    echo json_encode([
        "status" => "exito",
        "mensaje" => "Inicio de sesión correcto",
        "username" => $usuarioData['Username'],
        "nombreCompleto" => $usuarioData['NombreCompleto'],
        "rol" => $usuarioData['Rol']
    ]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Contraseña incorrecta"]);
}
?>
<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'modular';

// 1. Conexión
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

// 2. Recoger y limpiar datos
$name     = trim($_POST['name']     ?? '');
$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']     ?? '';

// 3. Validaciones (las que ya tenías están bien)
if (empty($name) || empty($email) || empty($password)) {
    die('Todos los campos son obligatorios.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('El correo no tiene un formato válido.');
}
if (strlen($password) < 6) {
    die('La contraseña debe tener al menos 6 caracteres.');
}

// 5. Hashear la contraseña
$hash = password_hash($password, PASSWORD_DEFAULT);

// 7. Preparar INSERT
$stmt = $mysqli->prepare("INSERT INTO usuarios (name, email, password, token, status) VALUES (?, ?, ?, ?, 'pendiente') ON DUPLICATE KEY UPDATE name=?, password=?, token=?, status='pendiente'");
$stmt->bind_param('sssssss', $name, $email, $hash, $token, $name, $hash, $token);

?>
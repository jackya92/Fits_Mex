<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
// ----------------------------------

// 1. INCLUIR LA CONEXIÓN CENTRALIZADA
require_once 'conexion.php'; 

try {
    // 2. VALIDAR SESIÓN
    // Importante: Asegúrate de que 'id_usuario' sea la clave que usas en tu login
    $id_usuario = $_SESSION['id_usuario'] ?? null;

    if (!$id_usuario) {
        echo json_encode(["error" => "No hay sesión activa"]);
        exit;
    }

    // 3. CONSULTA SQL MEJORADA
    // Usamos prepare() para poder pasar el ID del usuario de forma segura 
    $sql = 'SELECT nom_usuario, fecha_creacion FROM usuario WHERE id_usuario = ?';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario]);

    // 4. OBTENER RESULTADOS COMO ARRAY ASOCIATIVO
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // 5. ENVIAR JSON
    // Si no hay resultados, fetchAll devuelve [] (un array vacío), 
    // lo cual NO rompe el .forEach en JS.
    echo json_encode($usuario);

} catch (PDOException $e) {
    // 6. MANEJO DE ERRORES (Enviamos un objeto con el error para depurar)
    http_response_code(500);
    echo json_encode([
        "error" => "Error de base de datos",
        "detalle" => $e->getMessage()
    ]);
}
?>
?>
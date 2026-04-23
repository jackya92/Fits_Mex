<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

$input = json_decode(file_get_contents('php://input'), true);

if (
    json_last_error() !== JSON_ERROR_NONE ||
    !isset($input['id_rutina']) ||
    !isset($input['id_ejercicio']) ||
    !isset($input['segundos'])
) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos'
    ]);
    exit;
}

$id_rutina = (int)$input['id_rutina'];
$id_ejercicio = (int)$input['id_ejercicio'];
$segundos = (int)$input['segundos'];

if ($segundos <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Segundos inválidos'
    ]);
    exit;
}

try {

    // 🔒 Validar usuario
    $id_usuario = $_SESSION['id_usuario'] ?? null;

    if (!$id_usuario) {
        echo json_encode([
            'success' => false,
            'message' => 'Sesión inválida'
        ]);
        exit;
    }

    // Validar que la rutina pertenece al usuario
    $stmt = $pdo->prepare("SELECT 1 FROM rutina WHERE id_rutina = ? AND id_usuario = ?");
    $stmt->execute([$id_rutina, $id_usuario]);

    if (!$stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'No autorizado'
        ]);
        exit;
    }

    // 🔥 UPDATE CORRECTO (tabla nueva)
    $sql = "
        UPDATE rutina_ejercicio 
        SET segundos = ?
        WHERE id_rutina = ? 
        AND id_ejercicio = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$segundos, $id_rutina, $id_ejercicio]);

    echo json_encode([
        'success' => true,
        'message' => 'Actualizado'
    ]);

} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage() // 🔥 DEBUG REAL
    ]);
}
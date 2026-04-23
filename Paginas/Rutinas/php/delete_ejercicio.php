<?php
// api_eliminar_ejercicio_rutina.php

header('Content-Type: application/json');
session_start();

require_once 'conexion.php'; // PDO ($pdo)

// ======================
// VALIDAR SESIÓN
// ======================
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado.'
    ]);
    exit;
}

// ======================
// OBTENER JSON
// ======================
$input = json_decode(file_get_contents('php://input'), true);

if (
    json_last_error() !== JSON_ERROR_NONE ||
    !isset($input['id_rutina']) ||
    !isset($input['id_ejercicio'])
) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos o inválidos.'
    ]);
    exit;
}

$id_rutina = (int)$input['id_rutina'];
$id_ejercicio = (int)$input['id_ejercicio'];
$id_usuario = (int)$_SESSION['id_usuario'];

// ======================
// VALIDAR QUE LA RUTINA ES DEL USUARIO
// ======================
$sql_check = "SELECT id_rutina 
              FROM rutina 
              WHERE id_rutina = ? AND id_usuario = ?";

$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute([$id_rutina, $id_usuario]);

if ($stmt_check->rowCount() === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permiso para modificar esta rutina.'
    ]);
    exit;
}

// ======================
// ELIMINAR EJERCICIO
// ======================
try {

    $pdo->beginTransaction();

    $sql = "DELETE FROM rutina_ejercicio
            WHERE id_rutina = ? AND id_ejercicio = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_rutina, $id_ejercicio]);

    $filas = $stmt->rowCount();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $filas > 0 
            ? 'Ejercicio eliminado correctamente.'
            : 'El ejercicio no estaba en la rutina.'
    ]);

} catch (PDOException $e) {

    $pdo->rollBack();

    error_log("Error al eliminar ejercicio: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor.'
    ]);
}
?>
<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_rutina_ejercicio']) || !isset($input['segundos'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$id = (int)$input['id_rutina_ejercicio'];
$segundos = (int)$input['segundos'];

if ($segundos <= 0) {
    echo json_encode(['success' => false, 'message' => 'Los segundos deben ser mayor a 0.']);
    exit;
}

try {

    $sql = "UPDATE rutina_ejercicio 
            SET segundos = ? 
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$segundos, $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró el registro o no hubo cambios.'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
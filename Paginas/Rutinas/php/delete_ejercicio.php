<?php
require_once 'conexion.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_rutina']) || !isset($data['id_ejercicio'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
    exit;
}

$id_rutina = $data['id_rutina'];
$id_ejercicio = $data['id_ejercicio'];

try {
    // Preparamos y ejecutamos la consulta DELETE
    $stmt = $pdo->prepare(
        "DELETE FROM rel_ejer_rutina_musculo WHERE id_ejercicio = ? AND id_rutina = ?"
    );
    $stmt->execute([$id_ejercicio, $id_rutina]);

    // Verificamos si se eliminó alguna fila para confirmar el éxito
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el ejercicio en la rutina.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
}
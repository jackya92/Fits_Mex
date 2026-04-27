<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$input = json_decode(file_get_contents('php://input'), true);

// Cambiamos la validación para que use lo que manda el JS
if (!isset($input['id_ejercicio']) || !isset($input['id_rutina'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$id_ejercicio = (int)$input['id_ejercicio'];
$id_rutina = (int)$input['id_rutina'];

try {
    // Borramos la fila que coincida con ambos IDs
    $sql = "DELETE FROM rutina_ejercicio WHERE id_ejercicio = ? AND id_rutina = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_ejercicio, $id_rutina]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Ejercicio eliminado de la rutina.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró el ejercicio en esta rutina.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de BD: ' . $e->getMessage()
    ]);
}
?>
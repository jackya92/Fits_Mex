<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_rutina_ejercicio'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$id = (int)$input['id_rutina_ejercicio'];

try {

    $sql = "DELETE FROM rutina_ejercicio WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Ejercicio eliminado correctamente.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró el registro.'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de BD: ' . $e->getMessage()
    ]);
}
?>
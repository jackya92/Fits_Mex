<?php
header('Content-Type: application/json');
require_once 'conexion.php'; 

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_rutina']) || !isset($input['id_ejercicio'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$id_rutina = (int)$input['id_rutina'];
$id_ejercicio = (int)$input['id_ejercicio'];
$default_segundos = 30;

try {

    // (Opcional) Validar que el ejercicio exista
    $stmt_check = $pdo->prepare("SELECT id_ejercicio FROM ejercicio WHERE id_ejercicio = ?");
    $stmt_check->execute([$id_ejercicio]);

    if (!$stmt_check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El ejercicio no existe.']);
        exit;
    }

    // Insertar directamente en rutina_ejercicio
    $sql = "INSERT INTO rutina_ejercicio (id_rutina, id_ejercicio, segundos, orden) 
            VALUES (?, ?, ?, NULL)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_rutina, $id_ejercicio, $default_segundos]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
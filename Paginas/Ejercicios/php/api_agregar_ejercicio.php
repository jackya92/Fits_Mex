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
$segundos = 30; // default

try {

    // 🔹 Obtener siguiente orden automático
    $stmtOrden = $pdo->prepare("
        SELECT COALESCE(MAX(orden),0) + 1 
        FROM rutina_ejercicio 
        WHERE id_rutina = ?
    ");
    $stmtOrden->execute([$id_rutina]);
    $orden = $stmtOrden->fetchColumn();

    // 🔹 Insertar ejercicio en rutina
    $sql = "INSERT INTO rutina_ejercicio (id_rutina, id_ejercicio, segundos, orden) 
            VALUES (?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_rutina, $id_ejercicio, $segundos, $orden]);

    echo json_encode([
        'success' => true,
        'message' => 'Ejercicio agregado correctamente.'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de BD: ' . $e->getMessage()
    ]);
}
?>
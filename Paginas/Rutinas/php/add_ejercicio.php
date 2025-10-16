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
$segundos_default = 30; 
$fecha_actual = date('Y-m-d');

// El único cambio está aquí:
$id_musculo_default = NULL; 

try {
    // Verificamos si ya existe para no duplicarlo
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM rel_ejer_rutina_musculo WHERE id_rutina = ? AND id_ejercicio = ?");
    $checkStmt->execute([$id_rutina, $id_ejercicio]);
    if ($checkStmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'El ejercicio ya está en la rutina.']);
        exit;
    }

    // Insertamos el nuevo registro en la tabla relacional
    $stmt = $pdo->prepare(
        "INSERT INTO rel_ejer_rutina_musculo (id_ejercicio, id_rutina, id_musculo, segundos, fecha) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$id_ejercicio, $id_rutina, $id_musculo_default, $segundos_default, $fecha_actual]);

    echo json_encode(['success' => true, 'message' => 'Ejercicio agregado.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al agregar: ' . $e->getMessage()]);
}
<?php
// add_ejercicio.php

header('Content-Type: application/json');
require_once 'conexion.php'; 

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_rutina']) || !isset($input['id_ejercicio'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$id_rutina = (int)$input['id_rutina'];
$id_ejercicio = (int)$input['id_ejercicio'];
$default_segundos = 30; // Valor por defecto

try {
    // 1. Obtener todos los IDs de músculos asociados a este ejercicio
    $stmt_musculos = $pdo->prepare("SELECT id_musculo FROM rel_ejer_musc WHERE id_ejercicio = ?");
    $stmt_musculos->execute([$id_ejercicio]);
    $musculos = $stmt_musculos->fetchAll(PDO::FETCH_COLUMN);

    if (empty($musculos)) {
        // Si el ejercicio no tiene músculos, aún se puede intentar agregarlo (como un 'descanso' o 'ejercicio genérico'),
        // pero la tabla rel_ejer_rutina_musculo requiere id_musculo.
        // Por consistencia en el diseño, devolvemos error si no hay músculos asociados.
         echo json_encode(['success' => false, 'message' => 'El ejercicio no tiene músculos asociados para ser agregado.']);
         exit;
    }

    $pdo->beginTransaction();
    
    // 2. Insertar una entrada en rel_ejer_rutina_musculo por cada músculo
    $sql = "INSERT INTO rel_ejer_rutina_musculo (id_rutina, id_ejercicio, id_musculo, segundos) 
            VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    foreach ($musculos as $id_musculo) {
        $stmt->execute([$id_rutina, $id_ejercicio, $id_musculo, $default_segundos]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
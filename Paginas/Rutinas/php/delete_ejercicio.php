<?php
// delete_ejercicio.php

header('Content-Type: application/json');
require_once 'conexion.php'; 

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_rutina']) || !isset($input['id_ejercicio'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$id_rutina = (int)$input['id_rutina'];
$id_ejercicio = (int)$input['id_ejercicio'];

try {
    // Elimina TODAS las entradas de músculos para este ejercicio en esta rutina.
    $sql = "DELETE FROM rel_ejer_rutina_musculo 
            WHERE id_rutina = ? AND id_ejercicio = ?";
    
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id_rutina, $id_ejercicio])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
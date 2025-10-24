<?php
// api_editar_segundos.php

header('Content-Type: application/json');
require_once 'conexion.php'; // Asegúrate de que 'conexion.php' esté aquí

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_rutina']) || !isset($input['id_ejercicio']) || !isset($input['segundos'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$id_rutina = $input['id_rutina'];
$id_ejercicio = $input['id_ejercicio'];
$segundos = (int)$input['segundos'];

if ($segundos <= 0) {
    echo json_encode(['success' => false, 'message' => 'Los segundos deben ser mayor a 0.']);
    exit;
}

try {
    // Esta consulta actualiza los segundos para TODAS las entradas de músculos
    // de este ejercicio en esta rutina.
    $sql = "UPDATE rel_ejer_rutina_musculo 
            SET segundos = ? 
            WHERE id_rutina = ? AND id_ejercicio = ?";
    
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$segundos, $id_rutina, $id_ejercicio])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
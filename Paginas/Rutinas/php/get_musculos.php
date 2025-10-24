<?php
// get_musculos.php

header('Content-Type: application/json');
require_once 'conexion.php'; 

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_rutina'])) {
    echo json_encode([]);
    exit;
}

$id_rutina = (int)$input['id_rutina'];

try {
    // Obtenemos los nombres de los músculos únicos usados en esta rutina
    $stmt_musculos = $pdo->prepare("
        SELECT DISTINCT m.nom_musculo
        FROM rel_ejer_rutina_musculo AS rel
        JOIN musculo AS m ON rel.id_musculo = m.id_musculo
        WHERE rel.id_rutina = ?
        ORDER BY m.nom_musculo
    ");
    $stmt_musculos->execute([$id_rutina]);
    $musculos = $stmt_musculos->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($musculos);

} catch (PDOException $e) {
    // En caso de error, devuelve un array vacío
    error_log("Error al obtener músculos: " . $e->getMessage());
    echo json_encode([]);
}
?>
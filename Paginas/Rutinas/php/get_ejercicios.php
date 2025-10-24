<?php
// get_ejercicios.php

header('Content-Type: application/json');
require_once 'conexion.php'; 

// El ID de rutina se recibe por GET para este listado
$id_rutina = isset($_GET['id_rutina']) ? (int)$_GET['id_rutina'] : 0;

if ($id_rutina === 0) {
    echo json_encode([]);
    exit;
}

try {
    // Selecciona todos los ejercicios que NO están presentes en la rutina actual.
    // Usamos el id_ejercicio para la subconsulta, aprovechando el GROUP BY implícito
    // en la llave primaria de rel_ejer_rutina_musculo.
    $sql = "
        SELECT 
            id_ejercicio, nom_ejercicio
        FROM 
            ejercicio
        WHERE 
            id_ejercicio NOT IN (
                SELECT DISTINCT id_ejercicio 
                FROM rel_ejer_rutina_musculo 
                WHERE id_rutina = ?
            )
        ORDER BY 
            nom_ejercicio
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_rutina]);
    $ejercicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($ejercicios);

} catch (PDOException $e) {
    // En caso de error, devuelve un array vacío
    error_log("Error al obtener ejercicios disponibles: " . $e->getMessage());
    echo json_encode([]);
}
?>
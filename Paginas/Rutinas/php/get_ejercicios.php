<?php
require_once 'conexion.php';
header('Content-Type: application/json');

// Obtenemos el ID de la rutina desde la URL para saber qué ejercicios excluir
$id_rutina_actual = isset($_GET['id_rutina']) ? (int)$_GET['id_rutina'] : 0;

try {
    // Seleccionamos todos los ejercicios que NO ESTÉN en la tabla relacional para la rutina actual
    $stmt = $pdo->prepare(
        "SELECT e.id_ejercicio, e.nom_ejercicio 
         FROM ejercicio e
         WHERE e.id_ejercicio NOT IN (
             SELECT rel.id_ejercicio 
             FROM rel_ejer_rutina_musculo rel 
             WHERE rel.id_rutina = ?
         )
         ORDER BY e.nom_ejercicio ASC"
    );

    $stmt->execute([$id_rutina_actual]);
    $ejercicios = $stmt->fetchAll();

    echo json_encode($ejercicios);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
<?php
require_once 'conexion.php';
$data = json_decode(file_get_contents("php://input"), true);
$id_rutina = $data['id_rutina'] ?? null;

if (!$id_rutina) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT DISTINCT m.nom_musculo
    FROM musculo AS m
    JOIN ejercicio AS e ON e.id_musculo = m.id_musculo
    JOIN rel_ejer_rutina_musculo AS rel ON rel.id_ejercicio = e.id_ejercicio
    WHERE rel.id_rutina = ?
");
$stmt->execute([$id_rutina]);
$musculos = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($musculos);

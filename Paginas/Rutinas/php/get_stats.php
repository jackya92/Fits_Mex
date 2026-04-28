<?php
session_start();
require_once 'conexion.php'; // Asegúrate de que este sea el nombre de tu archivo de conexión

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['events' => [], 'compliance' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = ['events' => [], 'compliance' => 0];

try {
    // 1. Obtener rutinas realizadas para el calendario
    $stmt = $pdo->prepare("SELECT nom_rutina as title, fecha_completada as start FROM rutinas_realizadas WHERE id_usuario = ?");
    $stmt->execute([$user_id]);
    $data['events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Calcular porcentaje para la gráfica (Ejemplo: hecho en la semana vs meta de 4)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rutinas_realizadas WHERE id_usuario = ? AND YEARWEEK(fecha_completada, 1) = YEARWEEK(CURDATE(), 1)");
    $stmt->execute([$user_id]);
    $hechas = $stmt->fetchColumn();
    
    $meta_semanal = 4; // Puedes ajustar tu meta aquí
    $data['compliance'] = ($hechas / $meta_semanal) * 100;

} catch (Exception $e) {
    // Error silencioso para no romper el JSON
}

header('Content-Type: application/json');
echo json_encode($data);
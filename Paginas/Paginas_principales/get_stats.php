<?php
session_start();
require_once '../Rutinas/php/conexion.php'; 
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 1;

try {
    // 1. Eventos para el calendario
    $stmt = $pdo->prepare("SELECT nom_rutina as title, fecha_completada as start FROM rutinas_realizadas WHERE id_usuario = ?");
    $stmt->execute([$user_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($events as &$e) { $e['color'] = '#607AFB'; }

    // 2. Gráfica: 100% si hizo algo hoy, 0% si no
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rutinas_realizadas WHERE id_usuario = ? AND fecha_completada = CURDATE()");
    $stmt->execute([$user_id]);
    $hizoHoy = $stmt->fetchColumn();
    $compliance = ($hizoHoy > 0) ? 100 : 0;

    // 3. Racha (Total del mes)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rutinas_realizadas WHERE id_usuario = ? AND MONTH(fecha_completada) = MONTH(CURDATE())");
    $stmt->execute([$user_id]);
    $streak = $stmt->fetchColumn();

    echo json_encode([
        'compliance' => $compliance,
        'events' => $events,
        'streak' => $streak
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
<?php
session_start();
// 1. Forzamos que se muestren errores si algo falla (solo para pruebas)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. CORRECCIÓN DE RUTA: Asegúrate que esté en la misma carpeta que get_stats.php
require_once '../Rutinas/php/conexion.php'; 

header('Content-Type: application/json');

// Si no hay sesión, usamos el ID 1 para que al menos veas datos de prueba
$user_id = $_SESSION['user_id'] ?? 1;

try {
    // Eventos para el calendario
    $stmt = $pdo->prepare("SELECT nom_rutina as title, fecha_completada as start FROM rutinas_realizadas WHERE id_usuario = ?");
    $stmt->execute([$user_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($events as &$e) { $e['color'] = '#607AFB'; }

    // Cálculo para la gráfica (Cumplimiento semanal)
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT fecha_completada) FROM rutinas_realizadas WHERE id_usuario = ? AND fecha_completada >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $stmt->execute([$user_id]);
    $realizadas = $stmt->fetchColumn();
    
    // Si haces 4 rutinas a la semana es el 100%
    $compliance = ($realizadas > 0) ? ($realizadas / 4) * 100 : 0;
    if($compliance > 100) $compliance = 100;

// OBTENER RACHA BÁSICA (Ejemplo: Total de rutinas realizadas este mes)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rutinas_realizadas WHERE id_usuario = ? AND MONTH(fecha_completada) = MONTH(CURDATE())");
    $stmt->execute([$user_id]);
    $streak = $stmt->fetchColumn();

    // IMPRIMIR JSON FINAL
    echo json_encode([
        'events' => $events,
        'compliance' => $compliance,
        'streak' => $streak
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

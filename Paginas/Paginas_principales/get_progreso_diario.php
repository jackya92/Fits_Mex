<?php
session_start();
require_once 'conexion.php';

// Asumimos usuario 1 si no hay sesión para pruebas, pero usa $_SESSION['user_id'] en producción
$user_id = $_SESSION['user_id'] ?? 1;

try {
    // Contar cuántas rutinas ha hecho el usuario hoy
    $sql = "SELECT COUNT(*) as total FROM rutinas_realizadas 
            WHERE id_usuario = ? AND fecha_completada = CURDATE()";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $resultado = $stmt->fetch();

    // Si hizo 1 o más, el progreso es 100, de lo contrario 0
    $progreso = ($resultado['total'] > 0) ? 100 : 0;

    echo json_encode(['progreso' => $progreso]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
<?php
// ============================================================
// get_ejercicios.php
// Obtiene ejercicios disponibles (NO agregados a la rutina)
// ============================================================

header('Content-Type: application/json');
require_once 'conexion.php'; 

// ============================================================
// 1. VALIDAR PARÁMETRO
// ============================================================
$id_rutina = isset($_GET['id_rutina']) ? (int)$_GET['id_rutina'] : 0;

if ($id_rutina <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID de rutina inválido.',
        'data' => []
    ]);
    exit;
}

try {
    // ============================================================
    // 2. CONSULTA: EJERCICIOS NO AGREGADOS A LA RUTINA
    // ============================================================
    $sql = "
        SELECT 
            e.id_ejercicio, 
            e.nom_ejercicio
        FROM ejercicio e
        WHERE e.id_ejercicio NOT IN (
            SELECT DISTINCT erm.id_ejercicio
            FROM rutina_ejercicio erm
            WHERE erm.id_rutina = ?
        )
        ORDER BY e.nom_ejercicio ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_rutina]);
    $ejercicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ============================================================
    // 3. RESPUESTA EXITOSA
    // ============================================================
    echo json_encode($ejercicios);

} catch (PDOException $e) {
    // ============================================================
    // 4. MANEJO DE ERRORES
    // ============================================================
    error_log("Error en get_ejercicios.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener ejercicios.',
        'data' => []
    ]);
}
?>
<?php
// ============================================================
// get_musculos.php
// Obtiene los músculos únicos usados en una rutina
// ============================================================

header('Content-Type: application/json');
require_once 'conexion.php'; 

// ============================================================
// 1. OBTENER DATOS (POST JSON)
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_rutina']) || (int)$input['id_rutina'] <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID de rutina inválido.',
        'data' => []
    ]);
    exit;
}

$id_rutina = (int)$input['id_rutina'];

try {
    // ============================================================
    // 2. CONSULTA DE MÚSCULOS ÚNICOS
    // ============================================================
    $sql = "
        SELECT DISTINCT m.nom_musculo
FROM rutina_ejercicio re
INNER JOIN ejercicio e 
    ON re.id_ejercicio = e.id_ejercicio
INNER JOIN rel_ejercicio_musculo rem 
    ON e.id_ejercicio = rem.id_ejercicio
INNER JOIN musculo m 
    ON rem.id_musculo = m.id_musculo
WHERE re.id_rutina = ?
ORDER BY m.nom_musculo ASC;
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_rutina]);

    $musculos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // ============================================================
    // 3. RESPUESTA EXITOSA
    // ============================================================
    echo json_encode($musculos);

} catch (PDOException $e) {
    // ============================================================
    // 4. MANEJO DE ERRORES
    // ============================================================
    error_log("Error en get_musculos.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener músculos.',
        'data' => []
    ]);
}
?>
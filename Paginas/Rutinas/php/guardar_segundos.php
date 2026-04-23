<?php
// ============================================================
// update_segundos.php
// Actualiza el tiempo (segundos) de un ejercicio en una rutina
// ============================================================

header('Content-Type: application/json');
require_once 'conexion.php';

// ============================================================
// 1. OBTENER DATOS (JSON)
// ============================================================
$data = json_decode(file_get_contents('php://input'), true);

// ============================================================
// 2. VALIDACIÓN
// ============================================================
if (
    !isset($data['id_ejercicio']) || !is_numeric($data['id_ejercicio']) ||
    !isset($data['id_rutina'])   || !is_numeric($data['id_rutina']) ||
    !isset($data['segundos'])    || !is_numeric($data['segundos'])
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos.'
    ]);
    exit;
}

$id_ejercicio = (int)$data['id_ejercicio'];
$id_rutina    = (int)$data['id_rutina'];
$segundos     = (int)$data['segundos'];

// Validación lógica
if ($id_ejercicio <= 0 || $id_rutina <= 0 || $segundos <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Valores fuera de rango.'
    ]);
    exit;
}

try {
    // ============================================================
    // 3. UPDATE
    // ============================================================
    $sql = "
        UPDATE rel_ejer_rutina_musculo 
        SET segundos = ?
        WHERE id_ejercicio = ? AND id_rutina = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$segundos, $id_ejercicio, $id_rutina]);

    // ============================================================
    // 4. VERIFICAR RESULTADO
    // ============================================================
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Tiempo actualizado correctamente.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se realizaron cambios (puede que ya tenga ese valor o no exista).'
        ]);
    }

} catch (PDOException $e) {
    error_log("Error en update_segundos.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor.'
    ]);
}
?>
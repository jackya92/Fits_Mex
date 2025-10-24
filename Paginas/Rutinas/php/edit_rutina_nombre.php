<?php
// edit_rutina_nombre.php
// API para actualizar el nombre de una rutina.

header('Content-Type: application/json');
require_once 'conexion.php'; // Asegúrate de que este archivo define la conexión PDO ($pdo)

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_rutina']) || !isset($input['new_name'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos (id_rutina o new_name).']);
    exit;
}

$id_rutina = (int)$input['id_rutina'];
$new_name = trim($input['new_name']);

if (empty($new_name) || strlen($new_name) < 3) {
    echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
    exit;
}

try {
    // 1. Verificar si el nombre ya existe para otra rutina
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM rutina WHERE nom_rutina = ? AND id_rutina != ?");
    $stmt_check->execute([$new_name, $id_rutina]);
    if ($stmt_check->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otra rutina con este nombre.']);
        exit;
    }

    // 2. Actualizar el nombre en la tabla 'rutina'
    $sql = "UPDATE rutina SET nom_rutina = ? WHERE id_rutina = ?";
    
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$new_name, $id_rutina])) {
        // Verificar si se afectó alguna fila (nombre cambiado)
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            // Esto ocurre si el nombre ya era el mismo o si el ID de rutina no existe
            echo json_encode(['success' => false, 'message' => 'Rutina no encontrada o el nombre no ha cambiado (es el mismo).']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta de actualización.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
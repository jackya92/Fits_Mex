<?php
// edit_rutina_nombre.php

session_start(); // 🔥 FALTABA ESTO

$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    echo json_encode([
        'success' => false,
        'message' => 'Sesión no válida.'
    ]);
    exit;
}

header('Content-Type: application/json');
require_once 'conexion.php';

// Leer JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validación de JSON
if (
    json_last_error() !== JSON_ERROR_NONE ||
    !isset($input['id_rutina']) || 
    !isset($input['new_name'])
) {
    echo json_encode([
        'success' => false, 
        'message' => 'Datos incompletos o inválidos.'
    ]);
    exit;
}

// Sanitizar datos
$id_rutina = (int)$input['id_rutina'];
$new_name = trim($input['new_name']);

// Validación del nombre
if (empty($new_name) || strlen($new_name) < 3) {
    echo json_encode([
        'success' => false, 
        'message' => 'El nombre debe tener al menos 3 caracteres.'
    ]);
    exit;
}

// Opcional: limitar longitud (recomendado)
if (strlen($new_name) > 100) {
    $new_name = substr($new_name, 0, 100);
}

try {

    // 🔒 (OPCIONAL PERO RECOMENDADO) validar que la rutina pertenece al usuario
    if (isset($_SESSION['id_usuario'])) {
        $stmt_owner = $pdo->prepare("SELECT COUNT(*) FROM rutina WHERE id_rutina = ? AND id_usuario = ?");
        $stmt_owner->execute([$id_rutina, $_SESSION['id_usuario']]);

        if ($stmt_owner->fetchColumn() == 0) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permiso para modificar esta rutina.'
            ]);
            exit;
        }
    }

    // 1. Verificar nombre duplicado (mismo usuario)
    $stmt_check = $pdo->prepare("
        SELECT COUNT(*) 
        FROM rutina 
        WHERE nom_rutina = ? 
        AND id_rutina != ?
        AND id_usuario = ?
    ");

    $stmt_check->execute([
        $new_name, 
        $id_rutina, 
        $_SESSION['id_usuario']
    ]);

    if ($stmt_check->fetchColumn() > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Ya tienes otra rutina con este nombre.'
        ]);
        exit;
    }

    // 2. Actualizar nombre
    $sql = "UPDATE rutina SET nom_rutina = ? WHERE id_rutina = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_name, $id_rutina]);

    // 3. Verificar resultado
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Nombre actualizado correctamente.'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'No hubo cambios (posiblemente es el mismo nombre).'
        ]);
    }

} catch (PDOException $e) {

    error_log("Error edit_rutina_nombre: " . $e->getMessage());

    echo json_encode([
        'success' => false, 
        'message' => 'Error de base de datos.'
    ]);
}
?>
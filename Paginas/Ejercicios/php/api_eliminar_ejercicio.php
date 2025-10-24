<?php
// Define el tipo de contenido como JSON
header('Content-Type: application/json');

// 1. OBTENER DATOS DE ENTRADA (JSON)
$input = json_decode(file_get_contents('php://input'), true);

// Validar que los datos JSON se recibieron correctamente
if (json_last_error() !== JSON_ERROR_NONE || 
    !isset($input['id_ejercicio']) || 
    !isset($input['id_rutina']) || 
    !isset($input['muscle_ids'])) {
        
    echo json_encode(['success' => false, 'message' => 'Error: Datos de entrada inválidos.']);
    exit;
}

$id_ejercicio = (int)$input['id_ejercicio'];
$id_rutina = (int)$input['id_rutina'];
$muscle_ids_string = $input['muscle_ids']; // Viene como "8,9,10"

// 2. CONFIGURACIÓN DE CONEXIÓN
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "modular";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}
$conn->set_charset("utf8");

// 3. PARSEAR LOS IDs DE MÚSCULOS
$muscle_ids = explode(',', $muscle_ids_string);
if (empty($muscle_ids) || empty($muscle_ids[0])) {
     echo json_encode(['success' => false, 'message' => 'Error: El ejercicio no tiene músculos asociados.']);
     $conn->close();
     exit;
}

// 4. INICIAR TRANSACCIÓN Y ELIMINAR
$conn->begin_transaction();

try {
    // Preparar la consulta UNA VEZ
    $sql = "DELETE FROM rel_ejer_rutina_musculo 
            WHERE id_ejercicio = ? AND id_rutina = ? AND id_musculo = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
         throw new Exception('Error al preparar la consulta: ' . $conn->error);
    }

    // Iterar por CADA músculo asociado al ejercicio
    foreach ($muscle_ids as $id_musculo) {
        if (!is_numeric($id_musculo)) continue; // Omitir valores no numéricos
        
        $id_musculo_int = (int)$id_musculo;
        
        // Vincular parámetros y ejecutar por cada músculo
        $stmt->bind_param("iii", $id_ejercicio, $id_rutina, $id_musculo_int);
        
        if (!$stmt->execute()) {
             // Si falla una eliminación, lanza una excepción para revertir todo
             throw new Exception('Error al eliminar músculo ID ' . $id_musculo_int . ': ' . $stmt->error);
        }
    }

    // Si todo fue bien, confirmar cambios
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Ejercicio eliminado correctamente.']);

} catch (Exception $e) {
    // Si algo falló, revertir cambios
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// 5. CERRAR CONEXIÓN
$stmt->close();
$conn->close();

?>
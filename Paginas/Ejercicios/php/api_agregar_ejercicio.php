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

// 2. CONFIGURACIÓN DE CONEXIÓN (La misma que en ejercicios.php)
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

// 3. DATOS ADICIONALES (Segundos y Fecha)
$segundos = 30; // Valor por defecto. (Lo has solicitado en Ver_Rutina)
$fecha = date('Y-m-d'); // Fecha de hoy

// 4. PARSEAR LOS IDs DE MÚSCULOS
$muscle_ids = explode(',', $muscle_ids_string);
if (empty($muscle_ids) || empty($muscle_ids[0])) {
     echo json_encode(['success' => false, 'message' => 'Error: El ejercicio no tiene músculos asociados.']);
     $conn->close();
     exit;
}

// 5. INICIAR TRANSACCIÓN E INSERTAR
$conn->begin_transaction();

try {
    // Preparar la consulta UNA VEZ
    // Usamos IGNORE para evitar errores si el ejercicio exacto (con el mismo músculo) ya está en la rutina.
    // Esto previene duplicados accidentales.
    $sql = "INSERT IGNORE INTO rel_ejer_rutina_musculo (id_ejercicio, id_rutina, id_musculo, segundos, fecha) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
         throw new Exception('Error al preparar la consulta: ' . $conn->error);
    }

    // Iterar por CADA músculo asociado al ejercicio
    foreach ($muscle_ids as $id_musculo) {
        if (!is_numeric($id_musculo)) continue; // Omitir valores no numéricos
        
        $id_musculo_int = (int)$id_musculo;
        
        // Vincular parámetros y ejecutar por cada músculo
        $stmt->bind_param("iiiis", $id_ejercicio, $id_rutina, $id_musculo_int, $segundos, $fecha);
        
        if (!$stmt->execute()) {
             // Si falla una inserción, lanza una excepción para revertir todo
             throw new Exception('Error al insertar músculo ID ' . $id_musculo_int . ': ' . $stmt->error);
        }
    }

    // Si todo fue bien, confirmar cambios
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Ejercicio agregado correctamente.']);

} catch (Exception $e) {
    // Si algo falló, revertir cambios
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// 6. CERRAR CONEXIÓN
$stmt->close();
$conn->close();

?>
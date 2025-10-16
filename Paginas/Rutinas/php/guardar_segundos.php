<?php
// Requerimos el archivo de conexión a la base de datos
require_once 'conexion.php';

// Leemos los datos JSON que nos envió el JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Preparamos la respuesta que enviaremos de vuelta
header('Content-Type: application/json');

// --- Validación de datos ---
// Verificamos que recibimos los datos necesarios y que son números
if (
    !isset($data['id_ejercicio']) || !is_numeric($data['id_ejercicio']) ||
    !isset($data['id_rutina']) || !is_numeric($data['id_rutina']) ||
    !isset($data['segundos']) || !is_numeric($data['segundos'])
) {
    // Si faltan datos o no son válidos, enviamos un error
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit; // Detenemos la ejecución del script
}

// Asignamos los datos a variables para mayor claridad
$id_ejercicio = $data['id_ejercicio'];
$id_rutina = $data['id_rutina'];
$segundos = $data['segundos'];

try {
    // --- Preparamos la consulta SQL UPDATE ---
    // Actualizamos la tabla relacional donde el ID del ejercicio Y el ID de la rutina coincidan
    $stmt = $pdo->prepare(
        "UPDATE rel_ejer_rutina_musculo 
         SET segundos = ? 
         WHERE id_ejercicio = ? AND id_rutina = ?"
    );

    // Ejecutamos la consulta con los datos recibidos
    $stmt->execute([$segundos, $id_ejercicio, $id_rutina]);

    // Si todo fue bien, enviamos una respuesta de éxito
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    // Si ocurre un error en la base de datos, lo capturamos y enviamos un mensaje de error
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos: ' . $e->getMessage()]);
}
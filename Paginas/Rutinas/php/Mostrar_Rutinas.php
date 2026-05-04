<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once 'conexion.php'; 

try {
    $id_usuario = $_SESSION['id_usuario'] ?? null;

    if (!$id_usuario) {
        echo json_encode(["error" => "No hay sesión activa"]);
        exit;
    }

    // CONSULTA CORREGIDA: Incluye el promedio de dificultad
    $sql = '
       SELECT 
            r.id_rutina,
            r.nom_rutina, 
            r.color, 
            r.icono,
            IFNULL(AVG(e.nivel_dificultad), 0) AS promedio_dificultad,
            GROUP_CONCAT(DISTINCT m.nom_musculo SEPARATOR ", ") AS musculos,
            GROUP_CONCAT(DISTINCT h.nom_herramienta SEPARATOR ", ") AS herramientas 
        FROM rutina r
        LEFT JOIN rutina_ejercicio re ON r.id_rutina = re.id_rutina
        LEFT JOIN ejercicio e ON re.id_ejercicio = e.id_ejercicio
        LEFT JOIN rel_ejercicio_musculo rem ON e.id_ejercicio = rem.id_ejercicio
        LEFT JOIN musculo m ON rem.id_musculo = m.id_musculo
        LEFT JOIN rel_ejercicio_herramienta reh ON e.id_ejercicio = reh.id_ejercicio
        LEFT JOIN herramienta h ON reh.id_herramienta = h.id_herramienta
        WHERE r.id_usuario = ?
        GROUP BY r.id_rutina, r.nom_rutina, r.color, r.icono;
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario]);
    $rutinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rutinas);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error de base de datos",
        "detalle" => $e->getMessage()
    ]);
}
?>
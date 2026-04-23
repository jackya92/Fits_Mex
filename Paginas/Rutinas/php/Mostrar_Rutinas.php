<?php
session_start();

// --- Cabeceras para JSON y CORS ---
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
// ----------------------------------

// 1. INCLUIR LA CONEXIÓN CENTRALIZADA
require_once 'conexion.php'; 

try {
    // 2. VALIDAR SESIÓN
    // Importante: Asegúrate de que 'id_usuario' sea la clave que usas en tu login
    $id_usuario = $_SESSION['id_usuario'] ?? null;

    if (!$id_usuario) {
        echo json_encode(["error" => "No hay sesión activa"]);
        exit;
    }

    // 3. CONSULTA SQL MEJORADA
    // Usamos prepare() para poder pasar el ID del usuario de forma segura 
    $sql = '
        SELECT 
    r.id_rutina AS id,
    r.nom_rutina AS nombre, 
    r.color AS color_fondo, 
    r.icono AS icono_nombre,

    GROUP_CONCAT(DISTINCT m.nom_musculo SEPARATOR 
    ", ") AS musculos,
    GROUP_CONCAT(DISTINCT h.nom_herramienta SEPARATOR ", ") AS herramientas 

FROM rutina r

LEFT JOIN rutina_ejercicio re 
    ON r.id_rutina = re.id_rutina

LEFT JOIN ejercicio e 
    ON re.id_ejercicio = e.id_ejercicio

LEFT JOIN rel_ejercicio_musculo rem 
    ON e.id_ejercicio = rem.id_ejercicio

LEFT JOIN musculo m 
    ON rem.id_musculo = m.id_musculo

LEFT JOIN rel_ejercicio_herramienta reh 
    ON e.id_ejercicio = reh.id_ejercicio

LEFT JOIN herramienta h 
    ON reh.id_herramienta = h.id_herramienta

WHERE r.id_usuario = ?

GROUP BY
    r.id_rutina, r.nom_rutina, r.color, r.icono;
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario]);

    // 4. OBTENER RESULTADOS COMO ARRAY ASOCIATIVO
    // PDO::FETCH_ASSOC evita que los datos se dupliquen con índices numéricos
    $rutinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. ENVIAR JSON
    // Si no hay resultados, fetchAll devuelve [] (un array vacío), 
    // lo cual NO rompe el .forEach en JS.
    echo json_encode($rutinas);

} catch (PDOException $e) {
    // 6. MANEJO DE ERRORES (Enviamos un objeto con el error para depurar)
    http_response_code(500);
    echo json_encode([
        "error" => "Error de base de datos",
        "detalle" => $e->getMessage()
    ]);
}
?>
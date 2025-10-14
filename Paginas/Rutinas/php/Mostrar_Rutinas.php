<?php
// Configuración de la base de datos (¡AJUSTA LAS CREDENCIALES!)
$host = 'localhost';
$db   = 'modular'; // Base de datos identificada
$user = 'root'; // <<< CAMBIAR
$pass = ''; // <<< CAMBIAR
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// --- Cabeceras para JSON y CORS ---
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
// ------------------------------------

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Consulta SQL avanzada que obtiene nombre, color, icono, músculos y herramientas
    $sql = '
    SELECT 
        r.id_rutina AS id,
        r.nom_rutina AS nombre, 
        r.color AS color_fondo, 
        r.icono AS icono_nombre,
        GROUP_CONCAT(DISTINCT m.nom_musculo SEPARATOR \',\') AS musculos,
        GROUP_CONCAT(DISTINCT h.nom_herramiena SEPARATOR \',\') AS herramientas 
    FROM 
        rutina r
    LEFT JOIN
        rel_ejer_rutina_musculo AS rem ON r.id_rutina = rem.id_rutina
    LEFT JOIN
        musculo AS m ON rem.id_musculo = m.id_musculo
    LEFT JOIN
        ejercicio AS e ON rem.id_ejercicio = e.id_ejercicio 
    LEFT JOIN 
        rel_usu_herra_ejer AS rue ON e.id_ejercicio = rue.fk_ejercicio
    LEFT JOIN
        herramienta AS h ON rue.fk_herramienta = h.id_herramienta
    GROUP BY
        r.id_rutina, r.nom_rutina, r.color, r.icono
';


    $stmt = $pdo->query($sql);
    $rutinas = $stmt->fetchAll();

    // Devolver los datos en formato JSON
    echo json_encode($rutinas);

} catch (\PDOException $e) {
    // Manejo de errores de base de datos
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}

?>
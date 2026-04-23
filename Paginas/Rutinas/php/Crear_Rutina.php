<?php
session_start();

// ======================
// VALIDAR SESIÓN
// ======================
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.html");
    exit();
}

// ======================
// VALIDAR MÉTODO
// ======================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../Creacion_Rutina.html");
    exit();
}

// ======================
// CONEXIÓN
// ======================
require_once 'conexion.php'; // Usa PDO ($pdo)

// ======================
// OBTENER Y LIMPIAR DATOS
// ======================
$nombreRutina = isset($_POST['nombre_rutina']) 
    ? trim($_POST['nombre_rutina']) 
    : '';

$colorPortada = isset($_POST['color_portada']) 
    ? trim($_POST['color_portada']) 
    : '#afffebff';

$iconoRutina = isset($_POST['icono_rutina']) 
    ? trim($_POST['icono_rutina']) 
    : 'fitness_center';

// ======================
// VALIDACIONES
// ======================
if (empty($nombreRutina)) {
    header("Location: ../Creacion_Rutina.html?error=nombre_vacio");
    exit();
}

// Limitar longitud (seguridad)
$nombreRutina = substr($nombreRutina, 0, 100);

// Validar formato de color HEX (opcional pero recomendable)
if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $colorPortada)) {
    $colorPortada = '#afffebff';
}

// ======================
// INSERTAR RUTINA
// ======================
$sql = "INSERT INTO rutina (id_usuario, nom_rutina, color, icono) 
        VALUES (:id_usuario, :nombre, :color, :icono)";

try {

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id_usuario' => $_SESSION['id_usuario'],
        ':nombre'     => $nombreRutina,
        ':color'      => $colorPortada,
        ':icono'      => $iconoRutina
    ]);

    $idRutina = $pdo->lastInsertId();

    // ======================
    // VALIDAR INSERCIÓN
    // ======================
    if ($idRutina) {

        // 👉 OPCIONAL: guardar ID en sesión para usarlo después
        $_SESSION['ultima_rutina_creada'] = $idRutina;

        // Redirigir a ejercicios
        header("Location: ../../Ejercicios/php/ejercicios.php?success=rutina_creada");
        exit();

    } else {
        throw new Exception("No se generó ID de rutina.");
    }

} catch (PDOException $e) {

    error_log("Error DB (crear rutina): " . $e->getMessage());

    header("Location: ../Creacion_Rutina.html?error=db");
    exit();

} catch (Exception $e) {

    error_log("Error general: " . $e->getMessage());

    header("Location: ../Creacion_Rutina.html?error=general");
    exit();
}
?>
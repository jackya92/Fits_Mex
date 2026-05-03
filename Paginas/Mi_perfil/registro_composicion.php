<?php
// 1. Iniciar sesión para identificar al usuario
session_start();

// 2. Incluir la conexión a la base de datos (Archivo con PDO)
include("conexion.php"); 

// 3. Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../Paginas_cuentas/Index.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// 4. Verificar que los datos llegaron por el método POST[cite: 2]
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener y limpiar datos del formulario[cite: 2]
    $peso = isset($_POST['peso']) ? (float)$_POST['peso'] : 0;
    $grasa = isset($_POST['grasa']) ? (float)$_POST['grasa'] : 0;
    $masa_magra = isset($_POST['masa_magra']) ? (float)$_POST['masa_magra'] : 0;
    $fecha_actual = date("Y-m-d");

    // 5. Validación básica[cite: 2]
    if ($peso <= 0) {
        die("Error: El peso debe ser mayor a 0.");
    }

    try {
        // 6. Preparar la consulta SQL con PDO
        // Usamos marcadores de posición con nombre (:nombre) para mayor claridad
        $sql = "INSERT INTO medicion_corporal (id_usuario, peso, porcentaje_grasa, masa_magra, fecha) 
                VALUES (:id_usuario, :peso, :grasa, :masa_magra, :fecha)";
        
        $stmt = $pdo->prepare($sql); // Asumiendo que tu variable de conexión en conexion.php se llama $pdo

        // Vincular parámetros
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':peso', $peso);
        $stmt->bindParam(':grasa', $grasa);
        $stmt->bindParam(':masa_magra', $masa_magra);
        $stmt->bindParam(':fecha', $fecha_actual);

        if ($stmt->execute()) {
            // Éxito: Redirigir de vuelta al perfil[cite: 2]
            header("Location: Composicion_corp.php?registro=exitoso");
        } else {
            echo "Error al registrar el progreso.";
        }

    } catch (PDOException $e) {
        // Manejo de errores específico de PDO
        echo "Error de base de datos: " . $e->getMessage();
    }

} else {
    // Redirección si se accede ilegalmente al archivo[cite: 2]
    header("Location: Composicion_corp.php");
}
?>
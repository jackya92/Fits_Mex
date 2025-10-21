<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ================================================================
    // 1. INCLUIR LA CONEXIÓN CENTRALIZADA
    // ================================================================
    require_once 'conexion.php'; // 🔗 Usa la conexión común (PDO en $pdo)

    // ================================================================
    // 2. RECOGER Y SANEAR DATOS DEL FORMULARIO
    // ================================================================
    $nombreRutina = isset($_POST['nombre_rutina']) ? htmlspecialchars(substr(trim($_POST['nombre_rutina']), 0, 100)) : '';
    $colorPortada = isset($_POST['color_portada']) ? htmlspecialchars(trim($_POST['color_portada'])) : '#afffebff'; 
    $iconoRutina  = isset($_POST['icono_rutina']) ? htmlspecialchars(trim($_POST['icono_rutina'])) : 'fitness_center'; 

    // Validación básica
    if (empty($nombreRutina)) {
        header("Location: ../Creacion_Rutina.html?error=nombre_vacio");
        exit();
    }

    // ================================================================
    // 3. INSERTAR RUTINA Y OBTENER SU ID
    // ================================================================
    $sql = "INSERT INTO rutina (nom_rutina, color, icono) VALUES (?, ?, ?)";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombreRutina, $colorPortada, $iconoRutina]);
        $idRutina = $pdo->lastInsertId();

        // ================================================================
        // 4. REDIRECCIÓN DESPUÉS DE CREAR LA RUTINA
        // ================================================================
        if ($idRutina) {
            // Redirige a la página de ejercicios (puedes pasar el ID por GET)
            header("Location: ../../Ejercicios/php/ejercicios.php");
            exit();
        } else {
            die("Error al crear la rutina. ID no generado.");
        }

    } catch (PDOException $e) {
        error_log("Error de DB al crear rutina: " . $e->getMessage());
        die("Error al guardar la rutina. Por favor, verifica la configuración de tu tabla y DB."); 
    }

} else {
    // Si se accede sin método POST, redirigir al formulario
    header("Location: Creacion_Rutina.html"); 
    exit();
}
?>

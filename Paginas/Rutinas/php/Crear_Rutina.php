<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ===================================================================
    // 1. CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS (PDO)
    // ===================================================================

    // ** ATENCIÓN: Reemplaza estos valores con tus credenciales reales **
    $host = 'localhost';          // O la IP de tu servidor de base de datos
    $db   = 'modular'; // Nombre de tu DB
    $user = 'root';         // Tu usuario de MySQL
    $pass = '';        // Tu contraseña de MySQL
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Manejo de errores con excepciones
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Resultados como arrays asociativos
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
         $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
         // En un entorno de producción, nunca muestres el error, solo un mensaje genérico
         error_log("Error de conexión a la base de datos: " . $e->getMessage());
         die("Error de conexión al servidor de rutinas. Por favor, inténtalo más tarde.");
    }
    
    // ===================================================================
    // 2. RECOGER Y SANEAR DATOS
    // ===================================================================

    // Limitando el nombre a 100 caracteres y saneando
    $nombreRutina = isset($_POST['nombre_rutina']) ? htmlspecialchars(substr(trim($_POST['nombre_rutina']), 0, 100)) : '';
    $colorPortada = isset($_POST['color_portada']) ? htmlspecialchars(trim($_POST['color_portada'])) : '#afffebff'; 
    $iconoRutina = isset($_POST['icono_rutina']) ? htmlspecialchars(trim($_POST['icono_rutina'])) : 'fitness_center'; 

    // Validación básica
    if (empty($nombreRutina)) {
        header("Location: ../Creacion_Rutina.html?error=nombre_vacio");
        exit();
    }

    // ===================================================================
    // 3. REALIZAR el INSERT y OBTENER el ID
    // ===================================================================
    
    // El SQL utiliza marcadores de posición (?) para evitar inyección SQL (Prepared Statements)
    $sql = "INSERT INTO rutina (nom_rutina, color, icono) VALUES (?, ?, ?)";
    
    try {
        $stmt = $pdo->prepare($sql);
        
        // Ejecutar la declaración
        $stmt->execute([$nombreRutina, $colorPortada, $iconoRutina]);
        
        // Obtener el ID de la rutina recién creada
        $idRutina = $pdo->lastInsertId();

        // ===============================================================
        // 4. REDIRIGIR a la página para agregar ejercicios
        // ===============================================================
        
        if ($idRutina) {
            // Redirige pasando el ID de la rutina como parámetro GET
            header("Location: ../../Ejercicios/Lista_Ejercicios.html");
            exit();
        } else {
            // Esto solo ocurre si la inserción en la DB fue exitosa pero no se generó un ID
            die("Error al crear la rutina. ID no generado.");
        }

    } catch (PDOException $e) {
        // Manejo de errores de la base de datos
        error_log("Error de DB al crear rutina: " . $e->getMessage());
        die("Error al guardar la rutina. Por favor, verifica la configuración de tu tabla y DB."); 
    }

} else {
    // Si se accede sin POST, redirigir al formulario
    header("Location: Creacion_Rutina.html"); 
    exit();
}
?>
<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";  // Usuario por defecto de XAMPP
$password = "";      // Contraseña por defecto (vacía)
$dbname = "modular";  // Cambia por el nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar que se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y limpiar los datos del formulario
    $email = trim($_POST['correo']);
    $password = $_POST['contra'];
    $codigo_activacion = 0;
    $estado_activacion =0;
    $nivel = 0;
    $puntuacion =0;
    $n_puntuacion=0;


    
    // Validaciones básicas
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "El nombre es obligatorio";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El correo electrónico no es válido";
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres";
    }
    
    // Si no hay errores, insertar en la base de datos
    if (empty($errors)) {
        // Hashear la contraseña para seguridad
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Preparar la consulta SQL
        $sql = "INSERT INTO usuario (nombre, email, password) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            // Vincular parámetros
            $stmt->bind_param("sssss", $name, $email, $hashed_password, $birthdate, $gender);
            
            // Ejecutar la consulta
            if ($stmt->execute()) {
                // Éxito - redirigir al usuario
                header("Location: Inicio_Sesion.html?registro=exitoso");
                exit();
            } else {
                $errors[] = "Error al registrar el usuario: " . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $errors[] = "Error al preparar la consulta: " . $conn->error;
        }
    }
    
    // Si hay errores, mostrarlos
    if (!empty($errors)) {
        session_start();
        $_SESSION['errores'] = $errors;
        $_SESSION['datos_formulario'] = $_POST;
        header("Location: registro.html");
        exit();
    }
} else {
    // Si no es POST, redirigir al formulario
    header("Location: registro.html");
    exit();
}

$conn->close();
?>
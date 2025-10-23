<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['email'];
    $contra = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $codigo_activacion = md5(uniqid(rand(), true));
    $estado_activacion = 1; // activado directamente
    $nivel = 1;
    $puntuacion = 0;
    $n_puntuacion = 0;

    // Verificar si el correo ya existe
    $sql_check = "SELECT correo FROM usuario WHERE correo = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "El correo ya está registrado"]);
        exit;
    }

    $sql = "INSERT INTO usuario (correo, contra, codigo_activacion, estado_activacion, nivel, puntuacion, n_puntuacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdddd", $correo, $contra, $codigo_activacion, $estado_activacion, $nivel, $puntuacion, $n_puntuacion);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Usuario registrado correctamente"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al registrar usuario"]);
    }

    $stmt->close();
    $conn->close();
}
?>

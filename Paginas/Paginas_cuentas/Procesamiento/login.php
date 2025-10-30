<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['email'];
    $contra = $_POST['password'];

    $sql = "SELECT id_usuario, contra FROM usuario WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_usuario, $hash);
        $stmt->fetch();

        if (password_verify($contra, $hash)) {
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['correo'] = $correo;
            $_SESSION['password'] = $_POST['password'];

            echo json_encode(["status" => "success", "message" => "Inicio de sesión correcto"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Contraseña incorrecta"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    }

    $stmt->close();
    $conn->close();
}
?>

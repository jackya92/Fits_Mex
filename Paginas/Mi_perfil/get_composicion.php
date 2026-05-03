<?php
session_start();
include("conexion.php"); // Aquí se crea la variable $pdo

$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    die("Sesión no válida.");
}

try {
    // 🔹 Obtener datos del usuario
    $sql_usuario = "SELECT nom_usuario, fecha_creacion FROM usuario WHERE id_usuario = ?";
    $stmt = $pdo->prepare($sql_usuario);
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch();

    // 🔹 Último registro de progreso
    $sql_progreso = "SELECT peso, porcentaje_grasa, masa_magra, fecha 
                     FROM medicion_corporal 
                     WHERE id_usuario = ? 
                     ORDER BY fecha DESC LIMIT 1";
    $stmt2 = $pdo->prepare($sql_progreso);
    $stmt2->execute([$id_usuario]);
    $progreso = $stmt2->fetch() ?: ['peso' => 0, 'masa_magra' => 0, 'porcentaje_grasa' => 0];

    // 🔹 Datos para gráfica
    $sql_grafica = "SELECT peso, fecha FROM medicion_corporal WHERE id_usuario = ? ORDER BY fecha ASC";
    $stmt3 = $pdo->prepare($sql_grafica);
    $stmt3->execute([$id_usuario]);
    
    $pesos = [];
    $fechas = [];

    while($row = $stmt3->fetch()){
        $pesos[] = $row['peso'];
        $fechas[] = date("M", strtotime($row['fecha']));
    }

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
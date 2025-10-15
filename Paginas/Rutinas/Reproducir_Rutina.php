
<?php
// conexion.php
$host = 'localhost';
$dbname = 'modular';
$username = 'root';
$password = ''; // cambia si usas contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Modo de errores para depuración
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}


// ID de rutina (temporalmente fijo)
$id_rutina = 1;

// Obtener nombre de la rutina
$stmt = $pdo->prepare("SELECT nom_rutina FROM rutina WHERE id_rutina = ?");
$stmt->execute([$id_rutina]);
$rutina = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener ejercicios de la rutina
$query = "
    SELECT e.id_ejercicio, e.nom_ejercicio, e.ejemplo_ejer, rem.segundos
    FROM rel_ejer_rutina_musculo rem
    INNER JOIN ejercicio e ON e.id_ejercicio = rem.id_ejercicio
    WHERE rem.id_rutina = ?
    ORDER BY rem.id_ejercicio ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$id_rutina]);
$ejercicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($rutina['nom_rutina']); ?> | Fits - Mex</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-background-light font-display">

<div class="flex flex-col items-center justify-center min-h-screen px-6">

    <?php if (count($ejercicios) > 0): ?>
        <?php
            $actual = $ejercicios[0];
            $proximos = array_slice($ejercicios, 1);
        ?>
        <div class="text-center mb-6">
            <p class="text-sm uppercase tracking-widest text-primary font-semibold">
                Ejercicio Actual
            </p>
            <h1 class="text-5xl font-bold mt-2 text-gray-900">
                <?php echo htmlspecialchars($actual['nom_ejercicio']); ?>
            </h1>
        </div>

        <div class="aspect-[16/9] w-full max-w-3xl rounded-xl bg-cover bg-center mb-6"
            style='background-image: url("<?php echo htmlspecialchars($actual['ejemplo_ejer']); ?>");'>
        </div>

        <p class="text-center text-lg font-medium text-gray-700 mb-6">
            Tiempo: <?php echo $actual['segundos']; ?> segundos
        </p>

        <h3 class="text-lg font-bold mb-4 text-gray-900">Próximos</h3>
        <ul class="space-y-3">
            <?php foreach ($proximos as $p): ?>
                <li class="flex items-center gap-4 bg-gray-100 rounded-lg p-3">
                    <span class="material-symbols-outlined text-3xl text-primary">fitness_center</span>
                    <div>
                        <p class="font-semibold"><?php echo htmlspecialchars($p['nom_ejercicio']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo $p['segundos']; ?> seg</p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-center text-gray-500">No hay ejercicios en esta rutina.</p>
    <?php endif; ?>

</div>
</body>
</html>

?>
<?php
session_start();
// Configuración de la base de datos
$host = 'localhost';
$db   = 'fits_mex';
$user = 'root';
$pass = ''; // Tu contraseña de base de datos
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 1. Obtener ID del usuario desde la sesión
// Si no hay sesión, podrías redirigir al login o usar uno de prueba
$id_usuario = $_SESSION['id_usuario'] ?? 1;

// 2. Consulta para estadísticas generales (Totales)
$stmt_stats = $pdo->prepare("
    SELECT 
        COUNT(id_realizada) as total_rutinas,
        SUM(ejercicios_completados) as total_ejercicios,
        SUM(tiempo_total) as total_tiempo
    FROM rutinas_realizadas 
    WHERE id_usuario = ?
");
$stmt_stats->execute([$id_usuario]);
$stats = $stmt_stats->fetch();

// 3. Consulta para el listado de sesiones
$stmt_historial = $pdo->prepare("
    SELECT nom_rutina, fecha_completada, tiempo_total, ejercicios_completados 
    FROM rutinas_realizadas 
    WHERE id_usuario = ? 
    ORDER BY fecha_completada DESC
");
$stmt_historial->execute([$id_usuario]);
$rutinas = $stmt_historial->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fits-Mex - Mi Historial</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#607AFB",
                        "background-light": "#f5f6f8",
                        "background-dark": "#0f1323"
                    },
                    fontFamily: {
                        display: "Lexend"
                    }
                }
            }
        };
    </script>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200">
    <aside
        class="hidden md:flex flex-col w-64 bg-white dark:bg-black/20 border-r border-primary/20 min-h-screen fixed">
        <div class="flex items-center justify-center h-20 border-b px-6">
            <div class="flex items-center gap-3 bg-primary py-2 px-4 rounded-lg">
                <img class="h-10 w-10" src="../Logo_FitsMex.png" />
                <span class="text-2xl font-bold text-white">Fits - Mex</span>
            </div>
        </div>
        <!-- SIDEBAR -->
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="../Paginas_principales/Pag_Principal.html"
                class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-primary/10 hover:text-primary transition">
                <span class="material-symbols-outlined">home</span> Inicio
            </a>
            <a href="../../Paginas/Ejercicios/php/ejercicios.php"
                class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-primary/10 hover:text-primary transition">
                <span class="material-symbols-outlined">search</span> Explorar
            </a>
            <a href="../../Paginas/Rutinas/Lista_Rutinas.html"
                class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-primary/10 hover:text-primary transition">
                <span class="material-symbols-outlined">library_books</span> Mis rutinas
            </a>
        </nav>

        <div class="p-4 border-t border-primary/20 dark:border-primary/30">
            <a href="../../Paginas/Mi_perfil/Mi_Perfil.html"
                class="flex items-center gap-3 p-2 rounded-lg bg-primary/10 text-primary transition-all">
                <span class="material-symbols-outlined">account_circle</span>
                <span class="text-sm font-medium truncate">Mi Perfil</span>
            </a>
        </div>
    </aside>
    <div class="flex-1 md:ml-64 p-4 md:p-8 space-y-6 max-w-5xl mx-auto w-full">
        <main class="max-w-5xl mx-auto px-4 py-8 space-y-8 mb-20">

            <!-- Estadísticas Dinámicas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-black/20 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-white/5">
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Rutinas</p>
                    <h3 class="text-3xl font-bold text-primary"><?php echo $stats['total_rutinas'] ?? 0; ?></h3>
                </div>
                <div class="bg-white dark:bg-black/20 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-white/5">
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Ejercicios</p>
                    <h3 class="text-3xl font-bold text-green-500"><?php echo $stats['total_ejercicios'] ?? 0; ?></h3>
                </div>
                <div class="bg-white dark:bg-black/20 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-white/5">
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Segundos</p>
                    <h3 class="text-3xl font-bold text-orange-500"><?php echo $stats['total_tiempo'] ?? 0; ?></h3>
                </div>
            </div>

            <section class="space-y-4">
                <h2 class="text-lg font-bold flex items-center gap-2 px-2">
                    <span class="material-symbols-outlined text-primary">calendar_month</span>
                    Actividad Reciente
                </h2>

                <div class="space-y-4">
                    <?php if (count($rutinas) > 0): ?>
                        <?php foreach ($rutinas as $rutina): ?>
                            <div class="bg-white dark:bg-black/20 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-white/5">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                            <span class="material-symbols-outlined">exercise</span>
                                        </div>
                                        <div>
                                            <h4 class="font-bold"><?php echo htmlspecialchars($rutina['nom_rutina']); ?></h4>
                                            <p class="text-xs text-gray-500">
                                                <?php echo date("d M, Y", strtotime($rutina['fecha_completada'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] bg-green-100 dark:bg-green-500/20 text-green-600 px-2 py-1 rounded font-bold uppercase">Éxito</span>
                                </div>

                                <div class="grid grid-cols-2 bg-gray-50 dark:bg-white/5 rounded-xl p-3">
                                    <div class="text-center border-r border-gray-200 dark:border-white/10">
                                        <p class="text-[10px] text-gray-500 uppercase">Tiempo</p>
                                        <p class="font-bold text-sm"><?php echo $rutina['tiempo_total']; ?> seg</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-[10px] text-gray-500 uppercase">Ejercicios</p>
                                        <p class="font-bold text-sm"><?php echo $rutina['ejercicios_completados']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <span class="material-symbols-outlined text-6xl text-gray-300">history_toggle_off</span>
                            <p class="text-gray-500 mt-4">Aún no has completado ninguna rutina.</p>
                            <a href="../Rutinas/Lista_Rutinas.html" class="text-primary font-bold text-sm">¡Empieza ahora!</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

</body>

</html>
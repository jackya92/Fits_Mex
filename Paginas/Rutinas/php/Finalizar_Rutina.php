<?php
// ================================================================
// 1. OBTENER DATOS DE LA URL (VALIDADOS)
// ================================================================

$tiempo_total_segundos = isset($_GET['tiempo']) ? (int)$_GET['tiempo'] : 0;
$ejercicios_completados = isset($_GET['ejercicios']) ? (int)$_GET['ejercicios'] : 0;

// Evitar valores negativos
$tiempo_total_segundos = max(0, $tiempo_total_segundos);
$ejercicios_completados = max(0, $ejercicios_completados);

// ================================================================
// 2. FORMATEAR TIEMPO (MEJORADO)
// ================================================================

$minutos = floor($tiempo_total_segundos / 60);
$segundos = $tiempo_total_segundos % 60;

// Formato más limpio
if ($minutos > 0) {
    $tiempo_formateado = $minutos . " min " . $segundos . " seg";
} else {
    $tiempo_formateado = $segundos . " seg";
}

// ================================================================
// 3. MENSAJE DINÁMICO (UX PRO)
// ================================================================

if ($ejercicios_completados === 0) {
    $mensaje = "Inténtalo de nuevo, tú puedes 💪";
} elseif ($ejercicios_completados < 5) {
    $mensaje = "Buen comienzo, sigue así 🔥";
} else {
    $mensaje = "¡Excelente trabajo! Sigue con esa energía 🚀";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>¡Rutina Completada! - Fits-Mex</title>

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

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24;
}

/* Animación */
.fade-in {
    animation: fadeIn 0.6s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px);}
    to { opacity: 1; transform: translateY(0);}
}
</style>

</head>

<body class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200">

<div class="flex">

<!-- SIDEBAR -->
<aside class="hidden md:flex flex-col w-64 bg-white dark:bg-black/20 border-r border-primary/20 min-h-screen fixed">
    
    <div class="flex items-center justify-center h-20 border-b border-primary/20">
        <div class="flex items-center gap-3 bg-primary py-2 px-4 rounded-lg">
            <img class="h-10 w-10" src="../../Logo_FitsMex.png" />
            <span class="text-2xl font-bold text-white">Fits - Mex</span>
        </div>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-2">
        <a href="../../Paginas_principales/Pag_Principal.html" class="menu-link">
            <span class="material-symbols-outlined">home</span> Inicio
        </a>
        <a href="../../../Paginas/Ejercicios/php/ejercicios.php" class="menu-link">
            <span class="material-symbols-outlined">search</span> Explorar
        </a>
        <a href="../Lista_Rutinas.html" class="menu-link">
            <span class="material-symbols-outlined">library_books</span> Mis rutinas
        </a>
    </nav>
</aside>

<!-- CONTENIDO -->
<div class="flex-1 flex flex-col md:ml-64">

<main class="flex-grow flex items-center justify-center p-6">

<div class="max-w-xl w-full text-center fade-in">

    <!-- ICONO -->
    <div class="text-6xl mb-4">🎉</div>

    <!-- TITULO -->
    <h1 class="text-4xl font-extrabold mb-2">¡Rutina completada!</h1>

    <!-- MENSAJE DINÁMICO -->
    <p class="text-gray-600 dark:text-gray-300 mb-6">
        <?php echo $mensaje; ?>
    </p>

    <!-- TARJETAS -->
    <div class="grid grid-cols-2 gap-4">

        <div class="bg-white dark:bg-black/10 p-6 rounded-xl shadow border border-primary/20">
            <p class="text-sm text-gray-500">Tiempo total</p>
            <p class="text-2xl font-bold">
                <?php echo htmlspecialchars($tiempo_formateado); ?>
            </p>
        </div>

        <div class="bg-white dark:bg-black/10 p-6 rounded-xl shadow border border-primary/20">
            <p class="text-sm text-gray-500">Ejercicios</p>
            <p class="text-2xl font-bold">
                <?php echo htmlspecialchars($ejercicios_completados); ?>
            </p>
        </div>

    </div>

    <!-- BOTONES -->
    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">

        <a href="../../Paginas_principales/Pag_Principal.html"
           class="bg-primary text-white px-6 py-3 rounded-lg font-bold hover:scale-105 transition">
            Inicio
        </a>

        <a href="../Lista_Rutinas.html"
           class="bg-gray-200 dark:bg-gray-700 px-6 py-3 rounded-lg font-bold hover:scale-105 transition">
            Ver rutinas
        </a>

    </div>

</div>

</main>

</div>
</div>

</body>
</html>
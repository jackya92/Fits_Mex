<?php
// ==================================================================
// 1. OBTENER DATOS DE LA URL
// ==================================================================

// Recibimos el tiempo total en segundos y el número de ejercicios de la URL.
$tiempo_total_segundos = isset($_GET['tiempo']) ? (int)$_GET['tiempo'] : 0;
$ejercicios_completados = isset($_GET['ejercicios']) ? (int)$_GET['ejercicios'] : 0;

// ==================================================================
// 2. FORMATEAR DATOS
// ==================================================================

// Convertir segundos a un formato "XX min YY seg"
$minutos = floor($tiempo_total_segundos / 60);
$segundos = $tiempo_total_segundos % 60;
$tiempo_formateado = $minutos . " min " . $segundos . " seg";

// Si no se pasan datos (para pruebas), usamos los valores del archivo original.
if ($tiempo_total_segundos === 0) {
    $tiempo_formateado = "0";
    $ejercicios_completados = 0;
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>¡Rutina Completada! - Fits-Mex</title>

    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        // Configuración de Tailwind de Pag_Principal.html
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#607AFB", // Color de Pag_Principal
                        "background-light": "#f5f6f8",
                        "background-dark": "#0f1323"
                    },
                    fontFamily: {
                        display: "Lexend"
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        full: "9999px"
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200">

    <div class="flex">
        <aside
            class="hidden md:flex flex-col w-64 bg-white dark:bg-black/20 border-r border-primary/20 dark:border-primary/30 min-h-screen fixed top-0 left-0 bottom-0 z-40">
            <div class="flex items-center justify-center h-20 border-b border-primary/20 dark:border-primary/30 px-6">
                <div class="flex items-center gap-3 bg-primary py-2 px-4 rounded-lg dark:bg-primary/80">
                    <img alt="Fit Mex logo" class="h-10 w-10" src="../../Logo_FitsMex.png" />
                    <span class="text-2xl font-bold text-white">Fits - Mex</span>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="../../Paginas_principales/Pag_Principal.html"
                    class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10 hover:text-primary dark:hover:bg-primary/20 dark:hover:text-primary">
                    <span class="material-symbols-outlined">home</span>
                    <span>Inicio</span>
                </a>

                <a href="../../../Paginas/Ejercicios/php/ejercicios.php"
                    class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10 hover:text-primary dark:hover:bg-primary/20 dark:hover:text-primary">
                    <span class="material-symbols-outlined">search</span>
                    <span>Explorar</span>
                </a>

                <a href="../Lista_Rutinas.html"
                    class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10 hover:text-primary dark:hover:bg-primary/20 dark:hover:text-primary">
                    <span class="material-symbols-outlined">library_books</span>
                    <span>Mis rutinas</span>
                </a>
            </nav>

            <div class="p-4 border-t border-primary/20 dark:border-primary/30">
                <a href="../../Paginas/Perfil/Mi_Perfil.html"
                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-primary/10 dark:hover:bg-primary/20 cursor-pointer">
                    <span class="material-symbols-outlined">account_circle</span>
                    <span class="text-sm font-medium truncate">Mi Perfil</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-h-screen md:ml-64">
            
            <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-16 md:pb-8 flex items-center justify-center">
                <div class="w-full max-w-2xl px-4 text-center">
                    
                    <div class="mb-8">
                        <span class="text-6xl">🎉</span>
                        <h2 class="mt-4 text-4xl font-extrabold text-gray-900 dark:text-white">¡Felicidades!</h2>
                        <p class="mx-auto mt-2 max-w-md text-base text-gray-600 dark:text-gray-300">Has completado tu rutina. ¡Un paso más cerca de tus metas! Sigue con esa energía.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-xl bg-white dark:bg-black/10 p-6 shadow-lg border border-primary/20">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tiempo total</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($tiempo_formateado); ?>
                            </p>
                        </div>
                        
                        <div class="rounded-xl bg-white dark:bg-black/10 p-6 shadow-lg border border-primary/20">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ejercicios completados</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($ejercicios_completados); ?>
                            </p>
                        </div>
                    </div>

                    <div class="mt-10">
                        <a href="../../Paginas_principales/Pag_Principal.html" class="inline-flex h-12 cursor-pointer items-center justify-center rounded-lg bg-primary px-6 text-sm font-bold text-white shadow-lg transition-transform hover:scale-105">
                            <span>Volver al inicio</span>
                        </a>
                    </div>
                </div>
            </main>

            <nav
                class="md:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-black/20 border-t border-primary/20 dark:border-primary/30 flex justify-around py-2 z-50">

                <a class="flex flex-col items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-primary" 
                   href="../../Paginas_principales/Pag_Principal.html">
                    <span class="material-symbols-outlined">home</span>
                    <span class="text-xs font-medium">Inicio</span>
                </a>

                <a class="flex flex-col items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-primary"
                    href="../../Paginas/Ejercicios/php/ejercicios.php">
                    <span class="material-symbols-outlined">search</span>
                    <span class="text-xs font-medium">Explorar</span>
                </a>

                <a class="flex flex-col items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-primary"
                    href="../Lista_Rutinas.html">
                    <span class="material-symbols-outlined">library_books</span>
                    <span class="text-xs font-medium">Mis rutinas</span>
                </a>

                <a class="flex flex-col items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-primary"
                    href="../../Paginas/Perfil/Mi_Perfil.html">
                    <span class="material-symbols-outlined">account_circle</span>
                    <span class="text-xs font-medium">Perfil</span>
                </a>
            </nav>
        </div>
    </div>
</body>
</html>
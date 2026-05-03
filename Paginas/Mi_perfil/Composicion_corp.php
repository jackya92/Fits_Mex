<?php
// Incluimos la lógica de obtención de datos al principio del archivo
include("get_composicion.php");

// Verificación de datos para evitar errores si la base de datos está vacía
$peso_actual = $progreso['peso'] ?? 0;
$musculo_actual = $progreso['masa_magra'] ?? 0;
$grasa_actual = $progreso['porcentaje_grasa'] ?? 0;
$nombre_usuario = $usuario['nom_usuario'] ?? 'Usuario';
$pesos = $pesos ?? []; // Si no vienen de get_composicion.php, se vuelven un array vacío
$fechas = $fechas ?? [];
$registro_usuario = $usuario['fecha_creacion'] ?? date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fits-Mex - Mi Perfil</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

<body class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200 mb-20 md:mb-0">
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
    <div class="flex flex-col md:flex-row">
        <div class="flex-1 md:ml-64 p-4 md:p-8 space-y-6 max-w-5xl mx-auto w-full">


            <div class="bg-white dark:bg-black/20 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <div class="text-center sm:text-left">
                        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($nombre_usuario); ?></h2>
                        <p class="text-gray-500 text-sm">Miembro desde: <span class="font-medium"><?php echo date("d/m/Y", strtotime($registro_usuario)); ?></span></p>
                    </div>
                </div>

                <button onclick="toggleModal()" class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-primary/30">
                    <span class="material-symbols-outlined">add_circle</span>
                    Nuevo Registro
                </button>
            </div>

            <div class="bg-white dark:bg-black/20 rounded-2xl p-6 shadow-sm grid grid-cols-3 gap-2">
                <div class="flex flex-col items-center">
                    <p class="text-xl font-bold text-primary"><?php echo $peso_actual; ?> kg</p>
                    <p class="text-[10px] md:text-xs uppercase tracking-wider text-gray-400">Peso</p>
                </div>
                <div class="flex flex-col items-center border-x border-gray-100 dark:border-white/10">
                    <p class="text-xl font-bold text-primary"><?php echo $musculo_actual; ?> kg</p>
                    <p class="text-[10px] md:text-xs uppercase tracking-wider text-gray-400">Músculo</p>
                </div>
                <div class="flex flex-col items-center">
                    <p class="text-xl font-bold text-primary"><?php echo $grasa_actual; ?>%</p>
                    <p class="text-[10px] md:text-xs uppercase tracking-wider text-gray-400">Grasa</p>
                </div>
            </div>

            <div class="bg-white dark:bg-black/20 rounded-2xl p-6 shadow-sm">
                <h3 class="font-bold mb-4">Evolución de peso</h3>
                <div class="h-[250px] w-full">
                    <canvas id="grafica"></canvas>
                </div>
            </div>

            <div class="pb-10 md:pb-0">
                <a href="../Paginas_cuentas/Procesamiento/logout.php" class="flex items-center justify-center gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-500 font-bold border border-red-200 dark:border-red-500/20">
                    <span class="material-symbols-outlined">logout</span>
                    <span>Cerrar sesión</span>
                </a>
            </div>
            <div id="modalRegistro" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                <div class="bg-white dark:bg-[#1a1f35] w-full max-w-md rounded-2xl shadow-2xl overflow-hidden transform transition-all">
                    <div class="p-6 border-b border-gray-100 dark:border-white/10 flex justify-between items-center">
                        <h3 class="text-xl font-bold">Nuevo Registro Corporal</h3>
                        <button onclick="toggleModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <form action="registro_composicion.php" method="POST" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-500">Peso (kg)</label>
                            <input type="number" step="0.1" name="peso" required class="w-full rounded-xl border-gray-200 dark:bg-black/20 dark:border-white/10 focus:border-primary focus:ring-primary" placeholder="00.0">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1 text-gray-500">% Grasa</label>
                                <input type="number" step="0.1" name="grasa" required class="w-full rounded-xl border-gray-200 dark:bg-black/20 dark:border-white/10 focus:border-primary focus:ring-primary" placeholder="0%">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1 text-gray-500">Masa Magra (kg)</label>
                                <input type="number" step="0.1" name="masa_magra" required class="w-full rounded-xl border-gray-200 dark:bg-black/20 dark:border-white/10 focus:border-primary focus:ring-primary" placeholder="00.0">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-primary text-white font-bold py-3 rounded-xl hover:bg-primary/90 transition-all shadow-lg shadow-primary/20 mt-4">
                            Guardar Progreso
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Corrección de sintaxis PHP dentro de JS
        const pesos = <?php echo json_encode($pesos); ?>;
        const fechas = <?php echo json_encode($fechas); ?>;

        const ctx = document.getElementById('grafica');
        if (pesos.length > 0) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: fechas,
                    datasets: [{
                        label: 'Peso (kg)',
                        data: pesos,
                        borderColor: '#607AFB',
                        backgroundColor: 'rgba(96, 122, 251, 0.1)',
                        fill: true,
                        borderWidth: 3,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
        //MODAL
        function toggleModal() {
            const modal = document.getElementById('modalRegistro');
            modal.classList.toggle('hidden');
        }

        // Opcional: Cerrar el modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('modalRegistro');
            if (event.target == modal) {
                toggleModal();
            }
        }
    </script>
</body>

</html>
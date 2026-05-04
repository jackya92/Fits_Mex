<?php
// ==================================================================
// 1. CONEXIÓN Y OBTENCIÓN DE DATOS
// ==================================================================
require_once 'conexion.php'; // Usa la misma conexión PDO

// --- OBTENER ID DE RUTINA DESDE LA URL ---
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header('Location: ../Lista_Rutinas.html');
    exit();
}
$id_rutina = $_GET['id'];

// --- Obtener nombre de la rutina ---
$stmt = $pdo->prepare("SELECT nom_rutina FROM rutina WHERE id_rutina = ?");
$stmt->execute([$id_rutina]);
$rutina = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rutina) {
    die("Error: Rutina no encontrada.");
}

// --- Obtener ejercicios de la rutina (Query corregida con GROUP BY) ---
$query = "
SELECT 
    re.id AS id_rutina_ejercicio, -- 🔥 ESTE ES CLAVE
    e.id_ejercicio, 
    e.nom_ejercicio, 
    e.ejemplo_ejer, 
    re.segundos
FROM rutina_ejercicio re
INNER JOIN ejercicio e 
    ON e.id_ejercicio = re.id_ejercicio
WHERE re.id_rutina = ?
ORDER BY re.orden ASC;

";
$stmt = $pdo->prepare($query);
$stmt->execute([$id_rutina]);
$ejercicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Variables para la lógica de reproducción
$ejercicio_actual = null;
$ejercicios_proximos = [];
$total_ejercicios = count($ejercicios);
$progreso_actual = 0;
$segundos_actuales = 0;
$minutos_actuales = 0;

if ($total_ejercicios > 0) {
    $ejercicio_actual = $ejercicios[0];
    $ejercicios_proximos = array_slice($ejercicios, 1);
    // Progreso para el primer ejercicio
    $progreso_actual = (1 / $total_ejercicios) * 100;

    // Calcular minutos y segundos iniciales
    $segundos_totales = (int) $ejercicio_actual['segundos'];
    $minutos_actuales = floor($segundos_totales / 60);
    $segundos_actuales = $segundos_totales % 60;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($rutina['nom_rutina']); ?> | Fits - Mex</title>

    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
                <a href="../../Paginas/Ejercicios/php/ejercicios.php"
                    class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10 hover:text-primary dark:hover:bg-primary/20 dark:hover:text-primary">
                    <span class="material-symbols-outlined">search</span>
                    <span>Explorar</span>
                </a>
                <a href="../Lista_Rutinas.html"
                    class="flex items-center gap-3 px-4 py-2 text-sm font-medium bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary rounded-lg font-semibold">
                    <span class="material-symbols-outlined">library_books</span>
                    <span>Mis rutinas</span>
                </a>
            </nav>
            <div class="p-4 border-t border-primary/20 dark:border-primary/30">
                <a href="../../../Paginas/Mi_perfil/Mi_Perfil.html"
                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-primary/10 dark:hover:bg-primary/20 transition-all cursor-pointer">
                    <span class="material-symbols-outlined">account_circle</span>
                    <span class="text-sm font-medium truncate">Mi Perfil</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-h-screen md:ml-64">

            <main class="flex-1 flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">

                <?php if (empty($ejercicio_actual)): ?>
                    <div class="text-center">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Rutina Vacía</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">No hay ejercicios en
                            "<?php echo htmlspecialchars($rutina['nom_rutina']); ?>".</p>
                        <a href="Ver_Rutina.php?id=<?= $id_rutina ?>"
                            class="mt-6 inline-block bg-primary text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-opacity-90 transition-colors">
                            Volver y Agregar Ejercicios
                        </a>
                    </div>

                <?php else: ?>
                    <div class="w-full max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">

                        <div
                            class="lg:col-span-2 bg-white/5 dark:bg-black/10 p-6 rounded-xl shadow-lg border border-primary/20 dark:border-primary/30">
                            <div class="text-center mb-6">
                                <p class="text-sm uppercase tracking-widest text-primary font-semibold">Ejercicio Actual</p>
                                <h1 id="exercise-name" class="text-5xl font-bold mt-2 text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($ejercicio_actual['nom_ejercicio']); ?>
                                </h1>
                            </div>

                            <div id="exercise-image" class="aspect-[16/9] w-full rounded-xl bg-cover bg-center mb-6"
                                style='background-image: url(../../../ejemplos_ejercicios/<?php echo htmlspecialchars($ejercicio_actual['ejemplo_ejer']); ?>);'>
                            </div>

                            <div class="flex items-center justify-center gap-8 mb-4">
                                <div class="text-center">
                                    <p id="timer-minutes" class="text-5xl font-bold text-primary">
                                        <?php echo str_pad($minutos_actuales, 2, '0', STR_PAD_LEFT); ?>
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Minutos</p>
                                </div>
                                <p class="text-5xl font-bold text-primary">:</p>
                                <div class="text-center">
                                    <p id="timer-seconds" class="text-5xl font-bold text-primary">
                                        <?php echo str_pad($segundos_actuales, 2, '0', STR_PAD_LEFT); ?>
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Segundos</p>
                                </div>
                            </div>

                            <p id="timer-label"
                                class="text-center text-lg font-medium text-gray-700 dark:text-gray-300 mb-6">
                                Tiempo Restante
                            </p>

                            <div class="flex justify-center gap-4 flex-wrap">
                                <button id="start-routine-button"
                                    class="w-full py-4 px-6 bg-green-500 text-white font-bold rounded-lg shadow-md hover:bg-green-600 transition-colors mb-2">
                                    ¡Comenzar Rutina!
                                </button>

                                <button id="next-button"
                                    class="flex-1 py-3 px-6 bg-primary text-white font-bold rounded-lg shadow-md hover:bg-opacity-90 transition-colors hidden">
                                    Siguiente
                                </button>
                                <button id="pause-button"
                                    class="flex-1 py-3 px-6 bg-primary/20 dark:bg-primary/30 text-primary font-bold rounded-lg hover:bg-primary/30 dark:hover:bg-primary/40 transition-colors hidden">
                                    Pausar
                                </button>
                                <a id="finish-button" href="Finalizar_Rutina.php"
                                    class="flex-1 text-center py-3 px-6 bg-red-500/20 text-red-500 font-bold rounded-lg hover:bg-red-500/30 transition-colors">
                                    Terminar
                                </a>
                            </div>
                        </div>

                        <div class="flex flex-col gap-6">
                            <div
                                class="bg-white/5 dark:bg-black/10 p-6 rounded-xl shadow-lg border border-primary/20 dark:border-primary/30">
                                <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Progreso</h3>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div id="progress-bar" class="bg-primary h-2.5 rounded-full" style="width: 0%"></div>
                                </div>
                            </div>

                            <div
                                class="bg-white/5 dark:bg-black/10 p-6 rounded-xl shadow-lg flex-1 border border-primary/20 dark:border-primary/30">
                                <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Próximos</h3>
             <div id="next-exercises-list" class="space-y-3">
    <?php foreach ($ejercicios as $index => $ejercicio): ?>
        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-white/5 rounded-2xl">
            <div class="size-12 rounded-xl bg-gray-200 dark:bg-gray-800 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-gray-400">fitness_center</span>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($ejercicio['nom_ejercicio']) ?></h4>
                <p class="text-xs text-gray-500"><?= htmlspecialchars($ejercicio['segundos']) ?> segundos</p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
                                   
                                <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
               
            </main>
        </div>
    </div>
    <script>
        // --- DATOS Y ESTADO ---
        const allExercises = <?php echo json_encode($ejercicios); ?>;
        const routineId = "<?php echo $id_rutina; ?>";
        const totalExercises = allExercises.length;

        let currentExerciseIndex = 0;
        let timerInterval = null;
        let timeLeft = 0;
        let isPaused = false;

        let tiempoInicioRutina = null;
        let tiempoPausadoTotal = 0;
        let tiempoInicioPausa = null;
        let exercisesCompletedCount = 0;

        // --- ELEMENTOS ---
        const timerMinutesEl = document.getElementById('timer-minutes');
        const timerSecondsEl = document.getElementById('timer-seconds');
        const timerLabelEl = document.getElementById('timer-label');
        const exerciseNameEl = document.getElementById('exercise-name');
        const exerciseImageEl = document.getElementById('exercise-image');

        const pauseButton = document.getElementById('pause-button');
        const nextButton = document.getElementById('next-button');
        const startRoutineButton = document.getElementById('start-routine-button');
        const finishButton = document.getElementById('finish-button'); // Agregado para el evento click

        function updateTimerDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerMinutesEl.textContent = String(minutes).padStart(2, '0');
            timerSecondsEl.textContent = String(seconds).padStart(2, '0');
        }

        function startTimer() {
            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                if (!isPaused) {
                    if (timeLeft > 0) {
                        timeLeft--;
                        updateTimerDisplay();
                    } else {
                        nextExercise();
                    }
                }
            }, 1000);
        }

        function loadExercise(index) {
            // 1. Si ya se completaron todos, la barra llega al 100% y finaliza
            if (index >= totalExercises) {
                const progressBar = document.getElementById('progress-bar');
                if (progressBar) progressBar.style.width = "100%";

                finishRoutine();
                return;
            }

            const current = allExercises[index];

            // --- ESTO YA LO TENÍAS: Actualiza el ejercicio actual ---
            exerciseNameEl.textContent = current.nom_ejercicio;
            exerciseImageEl.style.backgroundImage = `url('../../../ejemplos_ejercicios/${current.ejemplo_ejer}')`;
            timeLeft = parseInt(current.segundos) || 0;
            updateTimerDisplay();

            // --- NUEVO: Actualizar la barra de progreso (Inicia en 0%) ---
            const progressPercentage = (index / totalExercises) * 100;
            const progressBar = document.getElementById('progress-bar');
            if (progressBar) {
                progressBar.style.width = progressPercentage + "%";
            }

            // --- NUEVO: Actualizar la lista de próximos ejercicios ---
            const nextExercisesContainer = document.getElementById('next-exercises-list');
            if (nextExercisesContainer) {
                nextExercisesContainer.innerHTML = ''; // Limpia la lista

                // Dibuja solo los que faltan (a partir del índice actual + 1)
               for (let i = index + 1; i < totalExercises; i++) {
    const nextEx = allExercises[i];
    nextExercisesContainer.innerHTML += `
        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-white/5 rounded-2xl">
            <div class="size-12 rounded-xl bg-gray-200 dark:bg-gray-800 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-gray-400">fitness_center</span>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-gray-800 dark:text-white">${nextEx.nom_ejercicio}</h4>
                <p class="text-xs text-gray-500">${nextEx.segundos} segundos</p>
            </div>
        </div>
    `;
}
                

                // Si ya está en el último ejercicio
                if (index + 1 >= totalExercises) {
                    nextExercisesContainer.innerHTML = '<p class="text-xs text-gray-500 text-center py-4">¡Último ejercicio!</p>';
                }
            }
            exercisesCompletedCount++;
            const ex = allExercises[index];
            exerciseNameEl.textContent = ex.nom_ejercicio;
            exerciseImageEl.style.backgroundImage = `url('../../../ejemplos_ejercicios/${ex.ejemplo_ejer}')`;

            timeLeft = parseInt(ex.segundos) || 0;
            updateTimerDisplay();
            startTimer();
        }

        function togglePause() {
            isPaused = !isPaused;

            if (isPaused) {
                tiempoInicioPausa = Date.now();
            } else {
                if (tiempoInicioPausa) {
                    tiempoPausadoTotal += (Date.now() - tiempoInicioPausa);
                    tiempoInicioPausa = null;
                }
            }

            pauseButton.textContent = isPaused ? 'Reanudar' : 'Pausar';
            timerLabelEl.textContent = isPaused ? 'Pausado' : 'Tiempo Restante';
        }

        function nextExercise() {
            clearInterval(timerInterval);
            currentExerciseIndex++;
            loadExercise(currentExerciseIndex);
        }

        function finishRoutine() {
            clearInterval(timerInterval);

            let segundosReales = 0;
            if (tiempoInicioRutina) {
                let tiempoFin = Date.now();
                if (isPaused && tiempoInicioPausa) {
                    tiempoPausadoTotal += (tiempoFin - tiempoInicioPausa);
                }
                segundosReales = Math.floor((tiempoFin - tiempoInicioRutina - tiempoPausadoTotal) / 1000);
            }

            window.location.href = `Finalizar_Rutina.php?id=${routineId}&tiempo=${segundosReales}&ejercicios=${exercisesCompletedCount}`;
        }

        // --- INICIALIZACIÓN ---
        document.addEventListener('DOMContentLoaded', () => {
            if (totalExercises > 0) {
                pauseButton.addEventListener('click', togglePause);
                nextButton.addEventListener('click', nextExercise);

                // Prevenir que el enlace de finalizar actúe antes de calcular el tiempo
                if (finishButton) {
                    finishButton.addEventListener('click', (e) => {
                        e.preventDefault();
                        finishRoutine();
                    });
                }

                if (startRoutineButton) {
                    startRoutineButton.addEventListener('click', () => {
                        tiempoInicioRutina = Date.now();

                        startRoutineButton.classList.add('hidden');
                        nextButton.classList.remove('hidden');
                        pauseButton.classList.remove('hidden');

                        loadExercise(0);
                    });
                }

                timeLeft = parseInt(allExercises[0].segundos);
                updateTimerDisplay();
                timerLabelEl.textContent = 'Listo para empezar';
            }
        });
    </script>
</body>

</html>
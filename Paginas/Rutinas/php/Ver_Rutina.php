<?php
// ==================================================================
// 1. CONEXIÓN Y CONSULTAS INICIALES
// ==================================================================
require_once 'conexion.php';

// ID de la rutina (puedes cambiarlo dinámicamente según lo necesites)
$id_rutina_actual = 1;

// --- Obtenemos el nombre de la rutina ---
$stmt_rutina = $pdo->prepare("SELECT nom_rutina FROM rutina WHERE id_rutina = ?");
$stmt_rutina->execute([$id_rutina_actual]);
$rutina = $stmt_rutina->fetch();
$nombre_rutina = $rutina ? $rutina['nom_rutina'] : 'Rutina Desconocida';

// --- Obtenemos los ejercicios ---
$stmt_ejercicios = $pdo->prepare("
    SELECT 
        ej.id_ejercicio AS id,
        ej.nom_ejercicio AS nombre, 
        rel.segundos
    FROM 
        rel_ejer_rutina_musculo AS rel
    JOIN 
        ejercicio AS ej ON rel.id_ejercicio = ej.id_ejercicio
    WHERE 
        rel.id_rutina = ?
    ORDER BY 
        rel.id_ejercicio
");
$stmt_ejercicios->execute([$id_rutina_actual]);
$ejercicios = $stmt_ejercicios->fetchAll();

// --- Obtenemos los músculos usados en esta rutina ---
$stmt_musculos = $pdo->prepare("
    SELECT DISTINCT m.nom_musculo
    FROM musculo AS m
    JOIN ejercicio AS e ON e.id_musculo = m.id_musculo
    JOIN rel_ejer_rutina_musculo AS rel ON rel.id_ejercicio = e.id_ejercicio
    WHERE rel.id_rutina = ?
");
$stmt_musculos->execute([$id_rutina_actual]);
$musculos = $stmt_musculos->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fits-Mex | <?= htmlspecialchars($nombre_rutina) ?></title>

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
                        "background-dark": "#0f1323",
                    },
                    fontFamily: {
                        display: "Lexend",
                    },
                },
            },
        };
    </script>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200">
<div class="flex">
    <aside class="hidden md:fixed md:flex flex-col w-64 h-screen top-0 left-0 z-40 bg-white dark:bg-black/20 border-r border-primary/20 dark:border-primary/30">
        <div class="flex items-center justify-center h-20 border-b border-primary/20 dark:border-primary/30 px-6">
            <div class="flex items-center gap-3 bg-primary py-2 px-4 rounded-lg dark:bg-primary/80">
                <img src="../../Logo_FitsMex.png" alt="Fit Mex logo" class="h-10 w-10" />
                <span class="text-2xl font-bold text-white">Fits-Mex</span>
            </div>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="/Paginas/Paginas_principales/Pag_Principal.html" class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary">
                <span class="material-symbols-outlined">home</span>
                <span>Inicio</span>
            </a>
            <a href="/Paginas/Ejercicios/Lista_Ejercicios.html" class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary">
                <span class="material-symbols-outlined">search</span>
                <span>Explorar</span>
            </a>
            <a href="Lista_Rutinas.html" class="flex items-center gap-3 px-4 py-2 text-sm font-medium bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary rounded-lg font-semibold">
                <span class="material-symbols-outlined">library_books</span>
                <span>Mis rutinas</span>
            </a>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-h-screen md:ml-64">
        <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-4xl">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        <?= htmlspecialchars($nombre_rutina) ?>
                    </h2>

                    <!-- TAGS DE MÚSCULOS (DINÁMICOS) -->
                    <div id="muscle-tags" class="flex flex-wrap gap-2 mt-4">
                        <?php if (empty($musculos)): ?>
                            <span class="px-3 py-1 text-sm font-medium rounded-full bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                Sin músculos asignados
                            </span>
                        <?php else: ?>
                            <?php foreach ($musculos as $musculo): ?>
                                <span class="muscle-tag px-3 py-1 text-sm font-medium rounded-full bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary">
                                    <?= htmlspecialchars($musculo) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex items-center gap-3 sm:gap-4">
                    <a title="Agregar ejercicio o descanso" href="#" class="flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white shadow-lg hover:opacity-90 transition-transform transform hover:scale-105">
                        <span class="material-symbols-outlined text-2xl">add</span>
                    </a>
                    <a title="Comenzar a ejercitarse" href="#" class="flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white shadow-lg hover:opacity-90 transition-transform transform hover:scale-105">
                        <span class="material-symbols-outlined text-2xl">play_arrow</span>
                    </a>
                </div>
            </div>

            <!-- TABLA DE EJERCICIOS -->
            <div class="bg-white dark:bg-black/10 rounded-xl shadow-lg border border-primary/20 dark:border-primary/30 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-primary/10 dark:bg-primary/20">
                            <tr>
                                <th class="px-6 py-3 font-medium text-gray-700 dark:text-gray-200 uppercase tracking-wider">Nombre de ejercicio</th>
                                <th class="px-6 py-3 font-medium text-gray-700 dark:text-gray-200 uppercase tracking-wider text-center">Segundos</th>
                                <th class="px-6 py-3"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary/10 dark:divide-primary/5" data-rutina-id="<?= htmlspecialchars($id_rutina_actual) ?>">
                            <?php if (empty($ejercicios)): ?>
                                <tr id="empty-routine-message">
                                    <td colspan="3" class="text-center px-6 py-4 text-gray-500 dark:text-gray-400">No hay ejercicios en esta rutina.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ejercicios as $ejercicio): ?>
                                    <tr data-id="<?= htmlspecialchars($ejercicio['id']) ?>">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($ejercicio['nombre']) ?></td>
                                        <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400"><?= htmlspecialchars($ejercicio['segundos']) ?></td>
                                        <td class="px-6 py-4 text-right relative">
                                            <button data-action="toggle-menu" class="text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors p-1 rounded-full hover:bg-primary/10">
                                                <span class="material-symbols-outlined text-base">edit</span>
                                            </button>
                                            <button class="delete-btn text-red-500/70 hover:text-red-500 dark:text-red-500/60 dark:hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-500/10 ml-2">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                            <div data-menu class="hidden absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-primary/20 dark:border-primary/30 p-3 z-10">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">Segundos</label>
                                                <input type="number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary dark:bg-gray-700 dark:text-white sm:text-sm" placeholder="Ej. 60">
                                                <button data-action="save-changes" class="mt-3 w-full bg-primary text-white py-1.5 px-3 rounded-md text-sm font-medium hover:opacity-90">Guardar</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- MODAL PARA AGREGAR EJERCICIO -->
<div id="add-exercise-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-background-dark rounded-xl shadow-lg p-6 w-full max-w-lg max-h-[80vh] flex flex-col">
        <div class="flex justify-between items-center border-b border-primary/20 pb-4 mb-4">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Agregar Ejercicio</h3>
            <button id="close-modal-btn" class="text-gray-500 hover:text-red-500">
                <span class="material-symbols-outlined text-3xl">close</span>
            </button>
        </div>
        <div id="exercise-list-container" class="overflow-y-auto space-y-3 pr-2"></div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.querySelector('tbody');
    const rutinaId = tableBody ? tableBody.dataset.rutinaId : null;
    const addExerciseModal = document.getElementById('add-exercise-modal');
    const openModalBtn = document.querySelector('a[title="Agregar ejercicio o descanso"]');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const exerciseListContainer = document.getElementById('exercise-list-container');

    // FUNCIONES AUXILIARES ==================================================
    async function updateMuscleTags() {
        try {
            const response = await fetch('get_musculos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_rutina: rutinaId })
            });
            const muscles = await response.json();
            const tagsContainer = document.getElementById('muscle-tags');
            tagsContainer.innerHTML = '';
            if (muscles.length === 0) {
                tagsContainer.innerHTML = `<span class="px-3 py-1 text-sm font-medium rounded-full bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Sin músculos asignados</span>`;
            } else {
                muscles.forEach(muscle => {
                    const span = document.createElement('span');
                    span.className = 'muscle-tag px-3 py-1 text-sm font-medium rounded-full bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary';
                    span.textContent = muscle;
                    tagsContainer.appendChild(span);
                });
            }
        } catch (error) {
            console.error('Error actualizando músculos:', error);
        }
    }

    async function loadAvailableExercises() {
        exerciseListContainer.innerHTML = '<p class="text-center text-gray-500">Cargando ejercicios...</p>';
        try {
            const response = await fetch(`get_ejercicios.php?id_rutina=${rutinaId}`);
            const exercises = await response.json();
            exerciseListContainer.innerHTML = '';
            if (exercises.length === 0) {
                exerciseListContainer.innerHTML = '<p class="text-center text-gray-500">No hay más ejercicios para agregar.</p>';
            } else {
                exercises.forEach(exercise => {
                    const el = document.createElement('div');
                    el.className = 'flex justify-between items-center p-3 bg-gray-50 dark:bg-black/20 rounded-lg';
                    el.innerHTML = `
                        <span class="font-medium text-gray-800 dark:text-gray-200">${exercise.nom_ejercicio}</span>
                        <button data-id="${exercise.id_ejercicio}" data-name="${exercise.nom_ejercicio}" class="add-btn bg-primary text-white text-sm font-bold py-1 px-3 rounded-md hover:opacity-90">Agregar</button>`;
                    exerciseListContainer.appendChild(el);
                });
            }
        } catch {
            exerciseListContainer.innerHTML = '<p class="text-center text-red-500">Error al cargar los ejercicios.</p>';
        }
    }

    function addNewExerciseRow(id, name) {
        const emptyRow = document.getElementById('empty-routine-message');
        if (emptyRow) emptyRow.remove();
        const tr = document.createElement('tr');
        tr.dataset.id = id;
        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">${name}</td>
            <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">30</td>
            <td class="px-6 py-4 text-right relative">
                <button data-action="toggle-menu" class="text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors p-1 rounded-full hover:bg-primary/10"><span class="material-symbols-outlined text-base">edit</span></button>
                <button class="delete-btn text-red-500/70 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-500/10 ml-2"><span class="material-symbols-outlined text-base">delete</span></button>
            </td>`;
        tableBody.appendChild(tr);
    }

    // EVENTOS ================================================================
    openModalBtn.addEventListener('click', e => { e.preventDefault(); addExerciseModal.classList.remove('hidden'); loadAvailableExercises(); });
    closeModalBtn.addEventListener('click', () => addExerciseModal.classList.add('hidden'));
    addExerciseModal.addEventListener('click', e => { if (e.target === addExerciseModal) addExerciseModal.classList.add('hidden'); });

    tableBody.addEventListener('click', async e => {
        const deleteBtn = e.target.closest('.delete-btn');
        const addBtn = e.target.closest('.add-btn');

        if (deleteBtn) {
            const row = deleteBtn.closest('tr');
            const ejercicioId = row.dataset.id;
            const ejercicioName = row.querySelector('td:first-child').textContent.trim();
            if (confirm(`¿Eliminar "${ejercicioName}" de la rutina?`)) {
                const response = await fetch('delete_ejercicio.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id_rutina: rutinaId, id_ejercicio: ejercicioId })
                });
                const result = await response.json();
                if (result.success) { row.remove(); updateMuscleTags(); }
            }
        }
    });

    exerciseListContainer.addEventListener('click', async e => {
        if (e.target.classList.contains('add-btn')) {
            const id = e.target.dataset.id;
            const name = e.target.dataset.name;
            const response = await fetch('add_ejercicio.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id_rutina: rutinaId, id_ejercicio: id })
            });
            const result = await response.json();
            if (result.success) {
                addNewExerciseRow(id, name);
                e.target.closest('.flex').remove();
                updateMuscleTags(); // 🔄 Actualiza los músculos
            }
        }
    });
});
</script>
</body>
</html>

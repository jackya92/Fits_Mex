<?php
// ==================================================================
// 1. CONEXIÓN Y MANEJO DEL ID
// ==================================================================
// Asegúrate de que 'conexion.php' contenga la lógica para conectar a la DB (usando PDO).
require_once 'conexion.php';

// --- RECIBIR EL ID DE LA URL ---
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header('Location: ../Lista_Rutinas.html');
    exit();
}

$id_rutina_actual = $_GET['id'];

// Establecer zona horaria y localización en español (México)
date_default_timezone_set('America/Mexico_City');
setlocale(LC_TIME, 'es_MX.UTF-8', 'es_MX', 'spanish');

// Obtener la fecha actual en formato largo
$fecha_de_hoy = strftime('%A, %d de %B de %Y');

// Asegurar que los nombres salgan con mayúscula inicial
$fecha_de_hoy = ucfirst($fecha_de_hoy);


// ==================================================================
// 3. CONSULTAS A LA BASE DE DATOS
// ==================================================================

// --- Obtenemos el nombre de la rutina ---
$stmt_rutina = $pdo->prepare("SELECT nom_rutina FROM rutina WHERE id_rutina = ?");
$stmt_rutina->execute([$id_rutina_actual]);
$rutina = $stmt_rutina->fetch();

if (!$rutina) {
    $nombre_rutina = 'Rutina No Encontrada';
    $ejercicios = [];
    $musculos = [];
} else {
    $nombre_rutina = $rutina['nom_rutina'];

    // --- Obtenemos los ejercicios ---
    $stmt_ejercicios = $pdo->prepare("
        SELECT 
            ej.id_ejercicio AS id,
            ej.nom_ejercicio AS nombre, 
            MAX(rel.segundos) AS segundos
        FROM 
            rel_ejer_rutina_musculo AS rel
        JOIN 
            ejercicio AS ej ON rel.id_ejercicio = ej.id_ejercicio
        WHERE 
            rel.id_rutina = ?
        GROUP BY
            ej.id_ejercicio, ej.nom_ejercicio
        ORDER BY 
            ej.nom_ejercicio
    ");
    $stmt_ejercicios->execute([$id_rutina_actual]);
    $ejercicios = $stmt_ejercicios->fetchAll(PDO::FETCH_ASSOC);

    // --- Obtenemos los músculos usados ---
    $stmt_musculos = $pdo->prepare("
        SELECT DISTINCT m.nom_musculo
        FROM rel_ejer_rutina_musculo AS rel
        JOIN musculo AS m ON rel.id_musculo = m.id_musculo
        WHERE rel.id_rutina = ?
    ");
    $stmt_musculos->execute([$id_rutina_actual]);
    $musculos = $stmt_musculos->fetchAll(PDO::FETCH_COLUMN);
}
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
            // ... (Configuración de Tailwind) ...
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
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24 }
        [data-menu].hidden { display: none; }
    </style>
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
                <a href="../../Paginas_principales/Pag_Principal.html" class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary">
                    <span class="material-symbols-outlined">home</span>
                    <span>Inicio</span>
                </a>
                <a href="../../../Paginas/Ejercicios/php/ejercicios.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary">
                    <span class="material-symbols-outlined">search</span>
                    <span>Explorar</span>
                </a>
                <a href="../Lista_Rutinas.html" class="flex items-center gap-3 px-4 py-2 text-sm font-medium bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary rounded-lg font-semibold">
                    <span class="material-symbols-outlined">library_books</span>
                    <span>Mis rutinas</span>
                </a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-h-screen md:ml-64">
            <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-4xl">

                <?php if ($nombre_rutina === 'Rutina No Encontrada'): ?>
                    <div class="text-center py-12">
                        <h2 class="text-4xl font-bold text-red-500 mb-4">Error 404</h2>
                        <p class="text-xl text-gray-600 dark:text-gray-400">La rutina solicitada no existe o fue eliminada.</p>
                        <a href="../Lista_Rutinas.html" class="mt-6 inline-block bg-primary text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-opacity-90 transition-colors">Volver a Mis Rutinas</a>
                    </div>
                <?php else: ?>
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-8">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 capitalize mb-1">
                                <?= htmlspecialchars($fecha_de_hoy) ?>
                            </p>
                            
                            <div id="routine-name-container">
                                <div id="routine-name-display-container" class="flex items-center gap-2 mb-2">
                                    <h2 id="routine-name-display" class="text-3xl font-bold text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($nombre_rutina) ?>
                                    </h2>
                                    <button id="edit-name-btn" title="Editar nombre de rutina" class="w-7 h-7 flex items-center justify-center rounded-full text-gray-500 dark:text-gray-400 hover:bg-primary/10 hover:text-primary transition-colors p-1">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                </div>
                                
                                <div id="routine-name-edit-container" class="hidden flex items-center gap-2 mb-2">
                                    <input id="routine-name-input" type="text" value="<?= htmlspecialchars($nombre_rutina) ?>"
                                        class="text-3xl font-bold border-b-2 border-primary bg-transparent text-gray-900 dark:text-white focus:outline-none w-full max-w-xs p-0 focus:ring-0">
                                    <button id="save-name-btn" title="Guardar nombre" class="w-7 h-7 flex items-center justify-center rounded-full bg-primary text-white hover:bg-primary/90 transition-colors p-1">
                                        <span class="material-symbols-outlined text-base">check</span>
                                    </button>
                                    <button id="cancel-name-btn" title="Cancelar edición" class="w-7 h-7 flex items-center justify-center rounded-full text-gray-500 dark:text-gray-400 hover:bg-red-500/10 hover:text-red-500 transition-colors p-1">
                                        <span class="material-symbols-outlined text-base">close</span>
                                    </button>
                                </div>
                            </div>
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
                            <button id="open-add-exercise-modal" title="Agregar ejercicio" class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/50 text-white shadow-lg hover:bg-primary transition-transform transform hover:scale-105">
                                <span class="material-symbols-outlined text-2xl">add</span>
                            </button>
                            <a title="Comenzar a ejercitarse" href="#" class="flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white shadow-lg hover:opacity-90 transition-transform transform hover:scale-105">
                                <span class="material-symbols-outlined text-2xl">play_arrow</span>
                            </a>
                        </div>
                    </div>
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
                                            <tr data-exercise-id="<?= htmlspecialchars($ejercicio['id']) ?>">
                                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($ejercicio['nombre']) ?></td>
                                                <td data-role="segundos-display" class="px-6 py-4 text-center text-gray-600 dark:text-gray-400"><?= htmlspecialchars($ejercicio['segundos']) ?></td>
                                                <td class="px-6 py-4 text-right relative">
                                                    
                                                    <button data-action="toggle-menu" class="text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors p-1 rounded-full hover:bg-primary/10">
                                                        <span class="material-symbols-outlined text-base">edit</span>
                                                    </button>
                                                    
                                                    <button data-action="delete-exercise" class="text-red-500/70 hover:text-red-500 dark:text-red-500/60 dark:hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-500/10 ml-2">
                                                        <span class="material-symbols-outlined text-base">delete</span>
                                                    </button>
                                                    
                                                    <div data-menu class="hidden absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-primary/20 dark:border-primary/30 p-3 z-10">
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">Segundos</label>
                                                        <input data-role="segundos-input" type="number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary dark:bg-gray-700 dark:text-white sm:text-sm" value="<?= htmlspecialchars($ejercicio['segundos']) ?>">
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
                <?php endif; ?>
            </main>
        </div>
    </div>

    <div id="add-exercise-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-background-dark rounded-xl shadow-lg p-6 w-full max-w-lg max-h-[80vh] flex flex-col">
            <div class="flex justify-between items-center border-b border-primary/20 pb-4 mb-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Agregar Ejercicio</h3>
                <button id="close-modal-btn" class="text-gray-500 hover:text-red-500">
                    <span class="material-symbols-outlined text-3xl">close</span>
                </button>
            </div>
            <div id="exercise-list-container" class="overflow-y-auto space-y-3 pr-2">
                <p class="text-center text-gray-500">Cargando ejercicios...</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const tableBody = document.querySelector('tbody');
            const rutinaId = tableBody ? tableBody.dataset.rutinaId : null;

            if (!rutinaId) return;

            // --- Nuevos elementos para edición de nombre ---
            const routineNameDisplay = document.getElementById('routine-name-display');
            const editNameBtn = document.getElementById('edit-name-btn');
            const routineNameEditContainer = document.getElementById('routine-name-edit-container');
            const routineNameDisplayContainer = document.getElementById('routine-name-display-container');
            const routineNameInput = document.getElementById('routine-name-input');
            const saveNameBtn = document.getElementById('save-name-btn');
            const cancelNameBtn = document.getElementById('cancel-name-btn');
            // ----------------------------------------------
            
            // --- Elementos del modal y CRUD de ejercicios ---
            const addExerciseModal = document.getElementById('add-exercise-modal');
            const openModalBtn = document.getElementById('open-add-exercise-modal');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const exerciseListContainer = document.getElementById('exercise-list-container');
            const muscleTagsContainer = document.getElementById('muscle-tags');
            const emptyRoutineMessage = document.getElementById('empty-routine-message');
            // ----------------------------------------------


            // --- LÓGICA DE UTILIDAD Y CÁLCULO DE MÚSCULOS (Se mantiene igual) ---
            function showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg z-[100] transform transition-transform duration-300 translate-x-full ${
                    type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
                }`;
                notification.textContent = message;
                
                document.body.appendChild(notification);
                
                setTimeout(() => { notification.classList.remove('translate-x-full'); }, 10);
                
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => {
                        if (document.body.contains(notification)) {
                            document.body.removeChild(notification);
                        }
                    }, 300);
                }, 3000);
            }

            async function updateMuscleTags() {
                try {
                    const response = await fetch('get_musculos.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_rutina: rutinaId })
                    });
                    const muscles = await response.json();
                    
                    muscleTagsContainer.innerHTML = '';
                    if (muscles.length === 0) {
                        muscleTagsContainer.innerHTML = `<span class="px-3 py-1 text-sm font-medium rounded-full bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Sin músculos asignados</span>`;
                    } else {
                        muscles.forEach(muscle => {
                            const span = document.createElement('span');
                            span.className = 'muscle-tag px-3 py-1 text-sm font-medium rounded-full bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary';
                            span.textContent = muscle;
                            muscleTagsContainer.appendChild(span);
                        });
                    }
                } catch (error) {
                    console.error('Error actualizando músculos:', error);
                }
            }
            // -------------------------------------------------------------------


            // --- 1. LÓGICA DE EDICIÓN DEL NOMBRE DE RUTINA (NUEVO) ---

            /** Alterna entre el modo de visualización y el modo de edición. */
            function toggleEditMode(isEditing) {
                if (isEditing) {
                    routineNameDisplayContainer.classList.add('hidden'); // Ocultar H2 y lápiz
                    routineNameEditContainer.classList.remove('hidden'); // Mostrar Input y botones
                    routineNameInput.focus();
                } else {
                    routineNameEditContainer.classList.add('hidden'); // Ocultar Input y botones
                    routineNameDisplayContainer.classList.remove('hidden'); // Mostrar H2 y lápiz
                }
            }

            /** Llama a la API para actualizar el nombre de la rutina. */
            async function updateRoutineName(newName) {
                const trimmedName = newName.trim();
                const currentName = routineNameDisplay.textContent.trim();

                if (trimmedName === currentName) {
                    showNotification("El nombre no ha cambiado.", 'error');
                    toggleEditMode(false);
                    return;
                }
                
                if (trimmedName.length < 3) {
                    showNotification("El nombre debe tener al menos 3 caracteres.", 'error');
                    routineNameInput.value = currentName; // Restaurar el nombre anterior
                    return;
                }

                try {
                    const response = await fetch('edit_rutina_nombre.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            id_rutina: rutinaId, 
                            new_name: trimmedName 
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        routineNameDisplay.textContent = trimmedName;
                        routineNameInput.value = trimmedName; // Sincronizar el valor del input
                        document.title = `Fits-Mex | ${trimmedName}`; // Actualizar el título de la página
                        showNotification(`Nombre de rutina actualizado a: "${trimmedName}"`);
                        toggleEditMode(false);
                    } else {
                        showNotification('Error al actualizar: ' + result.message, 'error');
                    }

                } catch (error) {
                    console.error('Error:', error);
                    showNotification('Error al comunicarse con el servidor', 'error');
                }
            }

            // Event Listeners para la edición de nombre
            editNameBtn.addEventListener('click', () => {
                routineNameInput.value = routineNameDisplay.textContent.trim(); // Asegurar que el input tenga el nombre actual
                toggleEditMode(true);
            });

            cancelNameBtn.addEventListener('click', () => {
                toggleEditMode(false);
            });

            saveNameBtn.addEventListener('click', () => {
                updateRoutineName(routineNameInput.value);
            });

            // Permite guardar al presionar Enter en el campo de entrada
            routineNameInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    updateRoutineName(routineNameInput.value);
                }
            });


            // --- 2. LÓGICA DEL MODAL (AGREGAR) (Se mantiene igual) ---

            async function loadAvailableExercises() {
                exerciseListContainer.innerHTML = '<p class="text-center text-primary animate-pulse">Cargando ejercicios...</p>';
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
                                <button data-id="${exercise.id_ejercicio}" data-name="${exercise.nom_ejercicio}" class="add-btn bg-primary text-white text-sm font-bold py-1 px-3 rounded-md hover:opacity-90 transition-colors">Agregar</button>`;
                            exerciseListContainer.appendChild(el);
                        });
                    }
                } catch (error) {
                    exerciseListContainer.innerHTML = '<p class="text-center text-red-500">Error al cargar los ejercicios.</p>';
                }
            }

            function addNewExerciseRow(id, name) {
                const emptyRow = document.getElementById('empty-routine-message');
                if (emptyRow) emptyRow.remove();
                
                const tr = document.createElement('tr');
                tr.dataset.exerciseId = id; 
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">${name}</td>
                    <td data-role="segundos-display" class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">30</td>
                    <td class="px-6 py-4 text-right relative">
                        <button data-action="toggle-menu" class="text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors p-1 rounded-full hover:bg-primary/10">
                            <span class="material-symbols-outlined text-base">edit</span>
                        </button>
                        <button data-action="delete-exercise" class="text-red-500/70 hover:text-red-500 dark:text-red-500/60 dark:hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-500/10 ml-2">
                            <span class="material-symbols-outlined text-base">delete</span>
                        </button>
                        <div data-menu class="hidden absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-primary/20 dark:border-primary/30 p-3 z-10">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">Segundos</label>
                            <input data-role="segundos-input" type="number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary dark:bg-gray-700 dark:text-white sm:text-sm" value="30">
                            <button data-action="save-changes" class="mt-3 w-full bg-primary text-white py-1.5 px-3 rounded-md text-sm font-medium hover:opacity-90">Guardar</button>
                        </div>
                    </td>`;
                tableBody.appendChild(tr);
            }

            // Eventos del Modal
            openModalBtn.addEventListener('click', () => {
                addExerciseModal.classList.remove('hidden');
                loadAvailableExercises();
            });

            closeModalBtn.addEventListener('click', () => addExerciseModal.classList.add('hidden'));

            addExerciseModal.addEventListener('click', e => {
                if (e.target === addExerciseModal) addExerciseModal.classList.add('hidden');
            });


            // --- 3. MANEJADORES PRINCIPALES (Delegación de Eventos y CRUD de ejercicios) (Se mantiene igual) ---
            
            tableBody.addEventListener('click', async function(e) {
                
                // --- TOGGLE MENU (EDITAR) ---
                const toggleBtn = e.target.closest('[data-action="toggle-menu"]');
                if (toggleBtn) {
                    document.querySelectorAll('[data-menu]').forEach(menu => {
                        if (menu !== toggleBtn.nextElementSibling) { menu.classList.add('hidden'); }
                    });
                    const menu = toggleBtn.closest('td').querySelector('[data-menu]');
                    if (menu) { menu.classList.toggle('hidden'); }
                }
                
                // --- SAVE CHANGES (GUARDAR SEGUNDOS) ---
                const saveBtn = e.target.closest('[data-action="save-changes"]');
                if (saveBtn) {
                    const row = saveBtn.closest('tr');
                    const ejercicioId = row.dataset.exerciseId;
                    const menu = saveBtn.closest('[data-menu]');
                    const input = menu.querySelector('[data-role="segundos-input"]');
                    const nuevosSegundos = parseInt(input.value);

                    if (nuevosSegundos && nuevosSegundos > 0) {
                        await handleSecondsUpdate(ejercicioId, rutinaId, nuevosSegundos, row, menu);
                    } else {
                        showNotification("Por favor, introduce un número válido de segundos (> 0).", 'error');
                        input.value = row.querySelector('[data-role="segundos-display"]').textContent;
                    }
                }

                // --- DELETE EXERCISE (ELIMINAR) ---
                const deleteBtn = e.target.closest('[data-action="delete-exercise"]');
                if (deleteBtn) {
                    const row = deleteBtn.closest('tr');
                    const ejercicioId = row.dataset.exerciseId;
                    const ejercicioName = row.querySelector('td:first-child').textContent.trim();

                    if (confirm(`¿Estás seguro de que quieres eliminar "${ejercicioName}" de esta rutina?`)) {
                        await handleExerciseDelete(ejercicioId, rutinaId, row);
                    }
                }
            });

            exerciseListContainer.addEventListener('click', async function(e) {
                const addBtn = e.target.closest('.add-btn');

                if (addBtn) {
                    const id = addBtn.dataset.id;
                    const name = addBtn.dataset.name;
                    addBtn.disabled = true;
                    addBtn.textContent = '...';

                    try {
                        const response = await fetch('add_ejercicio.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_rutina: rutinaId, id_ejercicio: id })
                        });

                        const result = await response.json();

                        if (result.success) {
                            showNotification(`"${name}" agregado.`);
                            addNewExerciseRow(id, name);
                            addBtn.closest('.flex').remove();
                            updateMuscleTags();
                            
                            if (exerciseListContainer.children.length === 0) { addExerciseModal.classList.add('hidden'); }

                        } else {
                            showNotification('Error al agregar: ' + result.message, 'error');
                            addBtn.disabled = false;
                            addBtn.textContent = 'Agregar';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showNotification('Error al comunicarse con el servidor', 'error');
                        addBtn.disabled = false;
                        addBtn.textContent = 'Agregar';
                    }
                }
            });
            

            // --- 4. FUNCIONES DE API (CRUD) (Se mantiene igual) ---

            async function handleSecondsUpdate(ejercicioId, rutinaId, segundos, rowElement, menuElement) {
                try {
                    const response = await fetch('edit_ejercicio_segundos.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ id_rutina: rutinaId, id_ejercicio: ejercicioId, segundos: segundos })
                    });
                    const result = await response.json();
                    if (result.success) {
                        rowElement.querySelector('[data-role="segundos-display"]').textContent = segundos;
                        menuElement.classList.add('hidden');
                        showNotification('Segundos actualizados.');
                    } else { showNotification('Error al actualizar segundos: ' + result.message, 'error'); }
                } catch (error) { showNotification('Error de conexión al actualizar.', 'error'); }
            }

            async function handleExerciseDelete(ejercicioId, rutinaId, rowElement) {
                try {
                    const response = await fetch('delete_ejercicio.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ id_rutina: rutinaId, id_ejercicio: ejercicioId })
                    });
                    const result = await response.json();

                    if (result.success) {
                        rowElement.remove();
                        updateMuscleTags();
                        showNotification('Ejercicio eliminado de la rutina.');
                        if (tableBody.children.length === 0) {
                            tableBody.innerHTML = '<tr id="empty-routine-message"><td colspan="3" class="text-center px-6 py-4 text-gray-500 dark:text-gray-400">No hay ejercicios en esta rutina.</td></tr>';
                        }
                    } else { showNotification('Error al eliminar: ' + result.message, 'error'); }
                } catch (error) { showNotification('Error de conexión al eliminar.', 'error'); }
            }

        });
    </script>
</body>
</html>
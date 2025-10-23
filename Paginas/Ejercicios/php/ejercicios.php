<?php
// ============================================================
// CONFIGURACIÓN DE CONEXIÓN A BASE DE DATOS
// ============================================================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "modular";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// ============================================================
// CONSULTA DE EJERCICIOS CON BÚSQUEDA
// ============================================================
$busqueda = "";
$resultadosEncontrados = true;

// Verificar si se ha enviado una búsqueda
if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
    $busqueda = trim($_GET['buscar']);
    
    // Consulta con búsqueda por nombre
    $sql = "SELECT nom_ejercicio, descripcion_ejer, ejemplo_ejer FROM ejercicio 
            WHERE nom_ejercicio LIKE ? 
            ORDER BY nom_ejercicio";
    
    $stmt = $conn->prepare($sql);
    $terminoBusqueda = "%" . $busqueda . "%";
    $stmt->bind_param("s", $terminoBusqueda);
    $stmt->execute();
    $result = $stmt->get_result();
    
} else {
    // Consulta normal sin búsqueda
    $sql = "SELECT nom_ejercicio, descripcion_ejer, ejemplo_ejer FROM ejercicio 
            ORDER BY nom_ejercicio";
    $result = $conn->query($sql);
}

$ejercicios = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ejercicios[] = $row;
    }
} else {
    $resultadosEncontrados = false;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ejercicios</title>

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
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }

        /* Estilos para la descripción expandible */
        .description-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .description-content.expanded {
            max-height: 500px;
        }
        
        .toggle-icon {
            transition: transform 0.3s ease;
        }
        
        .toggle-icon.expanded {
            transform: rotate(180deg);
        }
        
        .add-button {
            transition: all 0.3s ease;
        }
        
        .add-button:hover {
            transform: scale(1.05);
            background-color: #4c63d9 !important;
        }
        
        .add-button.added {
            background-color: #10b981 !important;
        }
        
        .remove-button {
            transition: all 0.3s ease;
        }
        
        .remove-button:hover {
            transform: scale(1.05);
            background-color: #dc2626 !important;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .added-state {
            display: none;
            align-items: center;
            gap: 0.5rem;
        }
        
        .exercise-added .default-state {
            display: none;
        }
        
        .exercise-added .added-state {
            display: flex;
        }
        
        .search-form {
            display: flex;
            align-items: center;
        }
        
        .search-input-container {
            position: relative;
            flex-grow: 1;
        }
        
        .search-button {
            margin-left: 0.5rem;
            background-color: #607AFB;
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .search-button:hover {
            background-color: #4c63d9;
        }
        
        .clear-search {
            position: absolute;
            right: 3rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .clear-search:hover {
            color: #374151;
        }
        
        .no-results {
            text-align: center;
            padding: 2rem;
            background-color: #fef2f2;
            border-radius: 0.5rem;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        
        .search-info {
            margin-bottom: 1rem;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .exercise-image {
            width: 80px;
            height: 80px;
            background-color: #f3f4f6;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 0.875rem;
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200">

    <div class="flex">
        <aside class="hidden md:flex flex-col w-64 bg-white dark:bg-black/20 border-r border-primary/20 dark:border-primary/30 min-h-screen fixed top-0 left-0 bottom-0 z-40">
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

                <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm font-medium bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary rounded-lg font-semibold">
                    <span class="material-symbols-outlined">search</span>
                    <span>Ejercicios</span>
                </a>

                <a href="../../Rutinas/Lista_Rutinas.html"
                    class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10 hover:text-primary dark:hover:bg-primary/20 dark:hover:text-primary">
                    <span class="material-symbols-outlined">library_books</span>
                    <span>Mis Rutinas</span>
                </a>
            </nav>

            <div class="p-4 border-t border-primary/20 dark:border-primary/30">
                <a href="/Paginas/Perfil/Mi_Perfil.html"
                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-primary/10 dark:hover:bg-primary/20 cursor-pointer">
                    <span class="material-symbols-outlined">account_circle</span>
                    <span class="text-sm font-medium truncate">Mi Perfil</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-h-screen md:ml-64">
            <header class="md:hidden border-b border-primary/20 dark:border-primary/30">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <div class="flex items-center gap-3">
                            <img alt="Fit Mex logo" class="h-8 w-8" src="../Logo_FitsMex.png" />
                            <span class="text-xl font-bold text-gray-900 dark:text-white">Fit Mex</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <button class="p-2 rounded-full hover:bg-primary/10 dark:hover:bg-primary/20">
                                <span class="material-symbols-outlined">notifications</span>
                            </button>
                            <div class="w-10 h-10 rounded-full bg-cover bg-center"
                                style='background-image: url("../Img_Usuario.png");'></div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-16 md:pb-8">
                <div class="max-w-4xl mx-auto">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Ejercicios</h2>
                    </div>

                    <div class="sticky top-[70px] z-10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm py-4 mb-6">
                        <form method="GET" action="" class="search-form mb-4">
                            <div class="search-input-container">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">search</span>
                                <input 
                                    type="text" 
                                    name="buscar"
                                    value="<?php echo htmlspecialchars($busqueda); ?>"
                                    placeholder="Buscar ejercicios por nombre..."
                                    class="form-input w-full rounded-lg border bg-gray-50 dark:bg-gray-900/50 border-gray-200/50 dark:border-gray-800/50 focus:ring-primary focus:border-primary pl-10 pr-12 py-3 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500" 
                                />
                                <?php if (!empty($busqueda)): ?>
                                <a href="?" class="clear-search">
                                    <span class="material-symbols-outlined text-lg">close</span>
                                </a>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="search-button flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">search</span>
                                <span>Buscar</span>
                            </button>
                        </form>

                        <?php if (!empty($busqueda)): ?>
                            <div class="search-info">
                                <?php if ($resultadosEncontrados): ?>
                                    <p>Mostrando resultados para: <strong>"<?php echo htmlspecialchars($busqueda); ?>"</strong></p>
                                <?php else: ?>
                                    <p>No se encontraron ejercicios para: <strong>"<?php echo htmlspecialchars($busqueda); ?>"</strong></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex flex-wrap items-center gap-2">
                            </div>
                    </div>

                    <div class="space-y-4">
                        <?php if (!empty($ejercicios)): ?>
                            <?php foreach ($ejercicios as $ejercicio): ?>
                                <div class="exercise-card bg-white dark:bg-black/10 p-4 rounded-xl shadow-sm hover:shadow-lg transition-all hover:bg-white/10 dark:hover:bg-black/20">
                                    <div class="flex items-start gap-4">
                                        <?php if (!empty($ejercicio['ejemplo_ejer'])): ?>
                                            <div class="w-20 h-20 bg-center bg-cover rounded-lg flex-shrink-0"
                                                style='background-image: url("../../../ejemplos_ejercicios/<?php echo htmlspecialchars($ejercicio['ejemplo_ejer']); ?>");'></div>
                                        <?php else: ?>
                                            <div class="exercise-image">
                                                <span class="material-symbols-outlined">fitness_center</span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-lg text-gray-900 dark:text-white"><?php echo htmlspecialchars($ejercicio['nom_ejercicio']); ?></h3>
                                            
                                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                <?php 
                                                    $descripcion = htmlspecialchars($ejercicio['descripcion_ejer']);
                                                    $descripcionCorta = strlen($descripcion) > 100 ? substr($descripcion, 0, 100) . '...' : $descripcion;
                                                    echo $descripcionCorta;
                                                ?>
                                            </div>
                                            
                                            <div class="description-content text-sm text-gray-600 dark:text-gray-400 mt-2">
                                                <?php echo $descripcion; ?>
                                            </div>
                                            
                                            <div class="action-buttons mt-3">
                                                <div class="default-state">
                                                    <button class="toggle-description flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-full bg-primary/10 dark:bg-primary/20 text-primary hover:bg-primary/20 dark:hover:bg-primary/30 transition-colors">
                                                        <span class="toggle-icon material-symbols-outlined text-sm">expand_more</span>
                                                        <span class="toggle-text">Ver más</span>
                                                    </button>
                                                    
                                                    <button class="add-button flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-full bg-primary text-white hover:bg-primary/90 transition-all">
                                                        <span class="material-symbols-outlined text-sm">add</span>
                                                        <span>Agregar</span>
                                                    </button>
                                                </div>
                                                
                                                <div class="added-state">
                                                    <span class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                                        <span class="material-symbols-outlined text-sm">check</span>
                                                        <span>En tu rutina</span>
                                                    </span>
                                                    
                                                    <button class="remove-button flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-full bg-red-500 text-white hover:bg-red-600 transition-all">
                                                        <span class="material-symbols-outlined text-sm">close</span>
                                                        <span>Eliminar</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php if (!empty($busqueda)): ?>
                                <div class="no-results dark:bg-red-900/20 dark:border-red-800/50 dark:text-red-400">
                                    <span class="material-symbols-outlined text-4xl mb-2">search_off</span>
                                    <h3 class="text-lg font-semibold mb-2">Ejercicio no encontrado</h3>
                                    <p class="mb-4">No se encontraron ejercicios que coincidan con "<strong><?php echo htmlspecialchars($busqueda); ?></strong>"</p>
                                    <a href="?" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                        <span class="material-symbols-outlined">refresh</span>
                                        <span>Ver todos los ejercicios</span>
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-gray-500 mt-8">No hay ejercicios registrados.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>

            <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-black/20 border-t border-primary/20 dark:border-primary/30 flex justify-around py-2 z-50">
                <a href="../../Paginas_principales/Pag_Principal.html"
                    class="flex flex-col items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-primary">
                    <span class="material-symbols-outlined">home</span>
                    <span class="text-xs font-medium">Inicio</span>
                </a>
                <a href="#" class="flex flex-col items-center gap-1 text-primary dark:text-primary font-semibold">
                    <span class="material-symbols-outlined">search</span>
                    <span class="text-xs font-medium">Ejercicios</span>
                </a>
                <a href="../../Rutinas/Lista_Rutinas.html"
                    class="flex flex-col items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-primary">
                    <span class="material-symbols-outlined">library_books</span>
                    <span class="text-xs font-medium">Mis rutinas</span>
                </a>
                <a href="/Paginas/Perfil/Mi_Perfil.html"
                    class="flex flex-col items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-primary">
                    <span class="material-symbols-outlined">account_circle</span>
                    <span class="text-xs font-medium">Perfil</span>
                </a>
            </nav>
        </div>
    </div>

    <script>
        // Funcionalidad para expandir/contraer descripciones
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.toggle-description');
            
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.exercise-card');
                    const description = card.querySelector('.description-content');
                    const icon = this.querySelector('.toggle-icon');
                    const text = this.querySelector('.toggle-text');
                    
                    description.classList.toggle('expanded');
                    icon.classList.toggle('expanded');
                    
                    if (description.classList.contains('expanded')) {
                        text.textContent = 'Ver menos';
                    } else {
                        text.textContent = 'Ver más';
                    }
                });
            });
            
            // Funcionalidad para agregar ejercicios
            const addButtons = document.querySelectorAll('.add-button');
            
            addButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.exercise-card');
                    const exerciseName = card.querySelector('h3').textContent;
                    
                    // Cambiar estado del ejercicio a "agregado"
                    card.classList.add('exercise-added');
                    
                    // Mostrar notificación
                    showNotification(`"${exerciseName}" agregado a tu rutina`, 'success');
                    
                    // Aquí puedes agregar la lógica para guardar en la base de datos
                    console.log(`Ejercicio agregado: ${exerciseName}`);
                });
            });
            
            // Funcionalidad para eliminar ejercicios
            const removeButtons = document.querySelectorAll('.remove-button');
            
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.exercise-card');
                    const exerciseName = card.querySelector('h3').textContent;
                    
                    // Cambiar estado del ejercicio a "no agregado"
                    card.classList.remove('exercise-added');
                    
                    // Mostrar notificación
                    showNotification(`"${exerciseName}" eliminado de tu rutina`, 'warning');
                    
                    // Aquí puedes agregar la lógica para eliminar de la base de datos
                    console.log(`Ejercicio eliminado: ${exerciseName}`);
                });
            });
            
            function showNotification(message, type = 'success') {
                // Crear elemento de notificación
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 transform transition-transform duration-300 translate-x-full ${
                    type === 'success' ? 'bg-green-500 text-white' : 'bg-amber-500 text-white'
                }`;
                notification.textContent = message;
                
                // Agregar al DOM
                document.body.appendChild(notification);
                
                // Animación de entrada
                setTimeout(() => {
                    notification.classList.remove('translate-x-full');
                }, 10);
                
                // Eliminar después de 3 segundos
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => {
                        if (document.body.contains(notification)) {
                            document.body.removeChild(notification);
                        }
                    }, 300);
                }, 3000);
            }
        });
    </script>
</body>
</html>
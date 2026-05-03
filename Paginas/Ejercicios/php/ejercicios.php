<?php
session_start();
// ============================================================
// CONFIGURACIÓN DE CONEXIÓN A BASE DE DATOS
// ============================================================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fits_mex";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
// Habilitar UTF-8
$conn->set_charset("utf8");

// Validar que exista la sesión del usuario
if (!isset($_SESSION['id_usuario'])) {
    die("Error: No has iniciado sesión.");
}
$id_usuario_actual = $_SESSION['id_usuario'];

$notificacion = ["mostrar" => false, "mensaje" => "", "tipo" => ""];

// ============================================================
// PROCESAMIENTO: ELIMINAR EJERCICIO PROPIO
// ============================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_ejercicio_propio'])) {
    $id_eliminar = (int)$_POST['id_ejercicio'];
    
    // Verificar que el ejercicio pertenezca al usuario actual
    $stmt_verificar = $conn->prepare("SELECT ejemplo_ejer FROM ejercicio WHERE id_ejercicio = ? AND id_usuario = ?");
    $stmt_verificar->bind_param("ii", $id_eliminar, $id_usuario_actual);
    $stmt_verificar->execute();
    $res_verificar = $stmt_verificar->get_result();
    
    if ($res_verificar->num_rows > 0) {
        $row_eliminar = $res_verificar->fetch_assoc();
        
        // 1. Eliminar referencias manuales por si acaso no hay CASCADE en tu SQL original
        $conn->query("DELETE FROM rutina_ejercicio WHERE id_ejercicio = $id_eliminar");
        
        // 2. Eliminar el ejercicio
        $stmt_eliminar = $conn->prepare("DELETE FROM ejercicio WHERE id_ejercicio = ? AND id_usuario = ?");
        $stmt_eliminar->bind_param("ii", $id_eliminar, $id_usuario_actual);
        
        if ($stmt_eliminar->execute()) {
            // 3. Borrar la imagen del servidor si existía
            if (!empty($row_eliminar['ejemplo_ejer'])) {
                $ruta_img = "../../../ejemplos_ejercicios/" . $row_eliminar['ejemplo_ejer'];
                if (file_exists($ruta_img)) {
                    unlink($ruta_img);
                }
            }
            header("Location: " . $_SERVER['PHP_SELF'] . "?eliminado=1");
            exit;
        } else {
            $notificacion = ["mostrar" => true, "mensaje" => "Error al eliminar el ejercicio.", "tipo" => "error"];
        }
    } else {
        $notificacion = ["mostrar" => true, "mensaje" => "No tienes permiso para eliminar este ejercicio.", "tipo" => "warning"];
    }
}

// ============================================================
// PROCESAMIENTO: CREAR NUEVO EJERCICIO
// ============================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_ejercicio'])) {
    $nom_ejercicio = trim($_POST['nom_ejercicio']);
    $descripcion_ejer = trim($_POST['descripcion_ejer']);
    $nivel_dificultad = (int)$_POST['nivel_dificultad'];
    $id_base = (int)$_POST['id_base'];
    
    // Manejo de la imagen de ejemplo
    $ejemplo_ejer = NULL;
    if (isset($_FILES['ejemplo_ejer']) && $_FILES['ejemplo_ejer']['error'] == 0) {
        $nombreArchivo = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['ejemplo_ejer']['name']));
        $rutaDestino = "../../../ejemplos_ejercicios/" . $nombreArchivo;
        
        if (!file_exists("../../../ejemplos_ejercicios/")) {
            mkdir("../../../ejemplos_ejercicios/", 0777, true);
        }

        if (move_uploaded_file($_FILES['ejemplo_ejer']['tmp_name'], $rutaDestino)) {
            $ejemplo_ejer = $nombreArchivo;
        }
    }

    $sql_insert = "INSERT INTO ejercicio (nom_ejercicio, descripcion_ejer, nivel_dificultad, id_base, ejemplo_ejer, id_usuario) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssiisi", $nom_ejercicio, $descripcion_ejer, $nivel_dificultad, $id_base, $ejemplo_ejer, $id_usuario_actual);
    
    if ($stmt_insert->execute()) {
        $nuevo_id = $stmt_insert->insert_id;
        
        if (!empty($_POST['musculos'])) {
            $stmt_musculo = $conn->prepare("INSERT INTO rel_ejercicio_musculo (id_ejercicio, id_musculo) VALUES (?, ?)");
            foreach ($_POST['musculos'] as $id_musculo) {
                $id_musculo = (int)$id_musculo;
                $stmt_musculo->bind_param("ii", $nuevo_id, $id_musculo);
                $stmt_musculo->execute();
            }
        }

        if (!empty($_POST['herramientas_sel'])) {
            $stmt_herr = $conn->prepare("INSERT INTO rel_ejercicio_herramienta (id_ejercicio, id_herramienta) VALUES (?, ?)");
            foreach ($_POST['herramientas_sel'] as $id_herr) {
                $id_herr = (int)$id_herr;
                $stmt_herr->bind_param("ii", $nuevo_id, $id_herr);
                $stmt_herr->execute();
            }
        }
        
        header("Location: " . $_SERVER['PHP_SELF'] . "?exito=1");
        exit;
    } else {
        $notificacion = ["mostrar" => true, "mensaje" => "Error al crear: " . $conn->error, "tipo" => "warning"];
    }
}

// Mensajes de redirección
if (isset($_GET['exito']) && $_GET['exito'] == 1) {
    $notificacion = ["mostrar" => true, "mensaje" => "¡Ejercicio creado exitosamente!", "tipo" => "success"];
} elseif (isset($_GET['eliminado']) && $_GET['eliminado'] == 1) {
    $notificacion = ["mostrar" => true, "mensaje" => "Ejercicio eliminado correctamente.", "tipo" => "success"];
}

// ============================================================
// CONSULTAS AUXILIARES (Para llenar filtros y formulario)
// ============================================================
$sql_herramientas = "SELECT id_herramienta, nom_herramienta FROM herramienta ORDER BY nom_herramienta";
$result_herramientas = $conn->query($sql_herramientas);
$herramientas_opciones = [];
if ($result_herramientas) while ($row = $result_herramientas->fetch_assoc()) $herramientas_opciones[] = $row;

$result_bases = $conn->query("SELECT id_base, nombre FROM ejercicio_base ORDER BY nombre");
$bases_opciones = [];
if ($result_bases) while ($row = $result_bases->fetch_assoc()) $bases_opciones[] = $row;

$result_musculos = $conn->query("SELECT id_musculo, nom_musculo, grupo_muscular FROM musculo ORDER BY grupo_muscular, nom_musculo");
$musculos_opciones = [];
if ($result_musculos) while ($row = $result_musculos->fetch_assoc()) $musculos_opciones[] = $row;

// ============================================================
// CONSULTA DE EJERCICIOS (Con Filtros y Restricción de Usuario)
// ============================================================
$busqueda = "";
$filtroGeneral = "";
$resultadosEncontrados = true;

if ((isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) || 
    (isset($_GET['filtro']) && !empty($_GET['filtro']))) {
    
    $busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : "";
    $filtroGeneral = isset($_GET['filtro']) ? $_GET['filtro'] : "";

    $sql = "SELECT 
        e.id_ejercicio, 
        e.id_usuario,
        e.nom_ejercicio, 
        e.descripcion_ejer, 
        e.nivel_dificultad,
        e.ejemplo_ejer,
        GROUP_CONCAT(DISTINCT m.nom_musculo ORDER BY m.nom_musculo SEPARATOR ', ') AS muscle_names,
        GROUP_CONCAT(DISTINCT CONCAT(COALESCE(ch.icono, 'exercise'), '::', h.nom_herramienta) SEPARATOR '||') AS herramientas
    FROM ejercicio e
    LEFT JOIN rel_ejercicio_musculo rem ON e.id_ejercicio = rem.id_ejercicio
    LEFT JOIN musculo m ON rem.id_musculo = m.id_musculo
    LEFT JOIN rel_ejercicio_herramienta reh ON e.id_ejercicio = reh.id_ejercicio
    LEFT JOIN herramienta h ON reh.id_herramienta = h.id_herramienta
    LEFT JOIN categoria_herramienta ch ON h.id_categoria = ch.id_categoria
    WHERE (e.id_usuario IS NULL OR e.id_usuario = ?)"; 

    $params = [$id_usuario_actual];
    $types = "i";

    if (!empty($busqueda)) {
        $sql .= " AND e.nom_ejercicio LIKE ?";
        $params[] = "%" . $busqueda . "%";
        $types .= "s";
    }

    if (!empty($filtroGeneral)) {
        if (strpos($filtroGeneral, 'dif_') === 0) {
            $dificultad = str_replace('dif_', '', $filtroGeneral);
            if ($dificultad === 'principiante') {
                $sql .= " AND e.nivel_dificultad BETWEEN 1 AND 3";
            } elseif ($dificultad === 'intermedio') {
                $sql .= " AND e.nivel_dificultad BETWEEN 4 AND 6";
            } elseif ($dificultad === 'avanzado') {
                $sql .= " AND e.nivel_dificultad BETWEEN 7 AND 10";
            }
        } elseif (strpos($filtroGeneral, 'herr_') === 0) {
            $herramientaId = (int)str_replace('herr_', '', $filtroGeneral);
            $sql .= " AND EXISTS (
                SELECT 1 FROM rel_ejercicio_herramienta reh_sub 
                WHERE e.id_ejercicio = reh_sub.id_ejercicio 
                AND reh_sub.id_herramienta = ?
            )";
            $params[] = $herramientaId;
            $types .= "i";
        }
    }

    $sql .= " GROUP BY e.id_ejercicio ORDER BY e.nom_ejercicio;";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

} else {
    $sql = "SELECT 
        e.id_ejercicio, 
        e.id_usuario,
        e.nom_ejercicio, 
        e.descripcion_ejer, 
        e.nivel_dificultad,
        e.ejemplo_ejer,
        GROUP_CONCAT(DISTINCT m.nom_musculo ORDER BY m.nom_musculo SEPARATOR ', ') AS muscle_names,
        GROUP_CONCAT(DISTINCT CONCAT(COALESCE(ch.icono, 'exercise'), '::', h.nom_herramienta) SEPARATOR '||') AS herramientas
    FROM ejercicio e
    LEFT JOIN rel_ejercicio_musculo rem ON e.id_ejercicio = rem.id_ejercicio
    LEFT JOIN musculo m ON rem.id_musculo = m.id_musculo
    LEFT JOIN rel_ejercicio_herramienta reh ON e.id_ejercicio = reh.id_ejercicio
    LEFT JOIN herramienta h ON reh.id_herramienta = h.id_herramienta
    LEFT JOIN categoria_herramienta ch ON h.id_categoria = ch.id_categoria
    WHERE e.id_usuario IS NULL OR e.id_usuario = $id_usuario_actual
    GROUP BY e.id_ejercicio
    ORDER BY e.nom_ejercicio;";
    $result = $conn->query($sql);
}

$ejercicios = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) $ejercicios[] = $row;
} else {
    $resultadosEncontrados = false;
}

// ============================================================
// CONSULTA DE RUTINAS DEL USUARIO
// ============================================================
$sql_rutinas = "SELECT id_rutina, nom_rutina, icono 
    FROM rutina 
    WHERE id_usuario = $id_usuario_actual 
    ORDER BY nom_rutina";

$result_rutinas = $conn->query($sql_rutinas);
$rutinas = [];
if ($result_rutinas && $result_rutinas->num_rows > 0) {
    while ($row_rutina = $result_rutinas->fetch_assoc()) $rutinas[] = $row_rutina;
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
                    }
                }
            }
        };
    </script>

    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .description-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .description-content.expanded { max-height: 800px; }
        .toggle-icon { transition: transform 0.3s ease; }
        .toggle-icon.expanded { transform: rotate(180deg); }
        .add-button { transition: all 0.3s ease; }
        .add-button:hover { transform: scale(1.05); background-color: #4c63d9 !important; }
        .remove-button { transition: all 0.3s ease; }
        .remove-button:hover { transform: scale(1.05); background-color: #dc2626 !important; }
        .added-state { display: none; align-items: center; }
        .exercise-added .default-state { display: none; }
        .exercise-added .added-state { display: flex; }
        
        .search-button { background-color: #607AFB; color: white; border: none; transition: background-color 0.3s ease; }
        .search-button:hover { background-color: #4c63d9; }
        .clear-search { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #6b7280; }
        .clear-search:hover { color: #374151; }
        
        .exercise-image { width: 80px; height: 80px; background-color: #f3f4f6; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: #9ca3af; }
        
        .routine-select-button { display: flex; align-items: center; width: 100%; padding: 0.75rem; border-radius: 0.5rem; transition: background-color 0.2s ease; text-align: left; gap: 0.75rem; background-color: #f5f6f8; color: #1f2937; }
        .dark .routine-select-button { background-color: #0f1323; color: #f3f4f6; }
        .routine-select-button:hover { background-color: #e0e7ff; color: #607AFB; }
        .dark .routine-select-button:hover { background-color: rgba(96, 122, 251, 0.2); color: #607AFB; }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        .dark .custom-scrollbar::-webkit-scrollbar-track { background: #1f2937; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; }
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
                <a href="../../Paginas_principales/Pag_Principal.html" class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10 hover:text-primary dark:hover:bg-primary/20 dark:hover:text-primary">
                    <span class="material-symbols-outlined">home</span><span>Inicio</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm font-medium bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary rounded-lg font-semibold">
                    <span class="material-symbols-outlined">search</span><span>Ejercicios</span>
                </a>
                <a href="../../Rutinas/Lista_Rutinas.html" class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10 hover:text-primary dark:hover:bg-primary/20 dark:hover:text-primary">
                    <span class="material-symbols-outlined">library_books</span><span>Mis Rutinas</span>
                </a>
            </nav>
            <div class="p-4 border-t border-primary/20 dark:border-primary/30">
                <a href="../../../Paginas/Mi_perfil/Mi_Perfil.html" class="flex items-center gap-3 p-2 rounded-lg hover:bg-primary/10 dark:hover:bg-primary/20 transition-all cursor-pointer">
                    <span class="material-symbols-outlined">account_circle</span><span class="text-sm font-medium truncate">Mi Perfil</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-h-screen md:ml-64">
            <header class="md:hidden border-b border-primary/20 dark:border-primary/30">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <div class="flex items-center gap-3">
                            <img alt="Fit Mex logo" class="h-8 w-8" src="../../Logo_FitsMex.png" />
                            <span class="text-xl font-bold text-gray-900 dark:text-white">Fit Mex</span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-16 md:pb-8">
                <div class="max-w-4xl mx-auto">
                    
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Ejercicios</h2>
                        <button onclick="document.getElementById('create-modal').classList.remove('hidden')" class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-sm">add_circle</span>
                            <span>Nuevo Ejercicio</span>
                        </button>
                    </div>

                    <div class="sticky top-[70px] z-10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm py-4 mb-6">
                        <form method="GET" action="" class="mb-4 flex flex-col md:flex-row gap-3">
                            <div class="search-input-container flex-grow relative w-full">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">search</span>
                                <input type="text" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar ejercicios..." class="form-input w-full rounded-lg border bg-gray-50 dark:bg-gray-900/50 border-gray-200/50 dark:border-gray-800/50 focus:ring-primary focus:border-primary pl-10 pr-12 py-3 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500" />
                                <?php if (!empty($busqueda) || !empty($filtroGeneral)): ?>
                                    <a href="?" class="clear-search"><span class="material-symbols-outlined text-lg">close</span></a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="w-full md:w-64 flex-shrink-0">
                                <select name="filtro" class="form-select w-full rounded-lg border bg-gray-50 dark:bg-gray-900/50 border-gray-200/50 dark:border-gray-800/50 focus:ring-primary focus:border-primary py-3 px-3 text-gray-900 dark:text-white cursor-pointer h-full">
                                    <option value="">Todos los ejercicios</option>
                                    <optgroup label="Por Dificultad">
                                        <option value="dif_principiante" <?php echo ($filtroGeneral === 'dif_principiante') ? 'selected' : ''; ?>>Principiante (1-3)</option>
                                        <option value="dif_intermedio" <?php echo ($filtroGeneral === 'dif_intermedio') ? 'selected' : ''; ?>>Intermedio (4-6)</option>
                                        <option value="dif_avanzado" <?php echo ($filtroGeneral === 'dif_avanzado') ? 'selected' : ''; ?>>Avanzado (7-10)</option>
                                    </optgroup>
                                    <optgroup label="Por Herramienta">
                                        <?php foreach ($herramientas_opciones as $herr): ?>
                                            <option value="herr_<?php echo $herr['id_herramienta']; ?>" <?php echo ($filtroGeneral == 'herr_' . $herr['id_herramienta']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($herr['nom_herramienta']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                            </div>

                            <button type="submit" class="search-button rounded-lg flex items-center justify-center gap-2 m-0 w-full md:w-auto px-6">
                                <span class="material-symbols-outlined text-lg">filter_alt</span>
                                <span>Filtrar</span>
                            </button>
                        </form>
                    </div>

                    <div class="space-y-4">
                        <?php if (!empty($ejercicios)): ?>
                            <?php foreach ($ejercicios as $ejercicio): ?>
                                <div class="exercise-card bg-white dark:bg-black/10 p-4 rounded-xl shadow-sm hover:shadow-lg transition-all hover:bg-white/10 dark:hover:bg-black/20"
                                    data-exercise-id="<?php echo $ejercicio['id_ejercicio']; ?>"
                                    data-muscle-ids="<?php echo htmlspecialchars($ejercicio['muscle_names']); ?>">

                                    <div class="flex items-start gap-4">
                                        <?php if (!empty($ejercicio['ejemplo_ejer'])): ?>
                                            <div class="w-20 h-20 bg-center bg-cover rounded-lg flex-shrink-0" style='background-image: url("../../../ejemplos_ejercicios/<?php echo htmlspecialchars($ejercicio['ejemplo_ejer']); ?>");'></div>
                                        <?php else: ?>
                                            <div class="exercise-image flex-shrink-0">
                                                <span class="material-symbols-outlined">fitness_center</span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="flex-1 w-full">
                                            <div class="flex justify-between items-start gap-2">
                                                <div>
                                                    <h3 class="font-semibold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                                                        <?php echo htmlspecialchars($ejercicio['nom_ejercicio']); ?>
                                                        
                                                        <?php if ($ejercicio['id_usuario'] == $id_usuario_actual): ?>
                                                            <form method="POST" action="" class="inline-flex m-0" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este ejercicio? Esta acción no se puede deshacer.');">
                                                                <input type="hidden" name="eliminar_ejercicio_propio" value="1">
                                                                <input type="hidden" name="id_ejercicio" value="<?php echo $ejercicio['id_ejercicio']; ?>">
                                                                <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors bg-transparent border-none p-0 cursor-pointer flex items-center justify-center" title="Eliminar este ejercicio que creaste">
                                                                    <span class="material-symbols-outlined text-base">delete</span>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </h3>

                                                    <div class="flex flex-wrap items-center gap-2 mt-1 mb-2">
                                                        <?php
                                                        $nivel = (int)$ejercicio['nivel_dificultad'];
                                                        $dificultadTexto = "Desconocido";
                                                        $dificultadClase = "bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400";

                                                        if ($nivel >= 1 && $nivel <= 3) {
                                                            $dificultadTexto = "Principiante (" . $nivel . "/10)";
                                                            $dificultadClase = "bg-green-100 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800/50";
                                                        } elseif ($nivel >= 4 && $nivel <= 6) {
                                                            $dificultadTexto = "Intermedio (" . $nivel . "/10)";
                                                            $dificultadClase = "bg-yellow-100 text-yellow-700 border border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-800/50";
                                                        } elseif ($nivel >= 7 && $nivel <= 10) {
                                                            $dificultadTexto = "Avanzado (" . $nivel . "/10)";
                                                            $dificultadClase = "bg-red-100 text-red-700 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800/50";
                                                        }
                                                        ?>
                                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full <?php echo $dificultadClase; ?>">
                                                            <?php echo $dificultadTexto; ?>
                                                        </span>

                                                        <span class="text-gray-300 dark:text-gray-600">|</span>

                                                        <?php
                                                        if (!empty($ejercicio['muscle_names'])) {
                                                            $tags = explode(', ', $ejercicio['muscle_names']);
                                                            foreach ($tags as $tag) {
                                                                echo '<span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full bg-primary/10 text-primary dark:bg-primary/20 border border-primary/20">'
                                                                    . htmlspecialchars($tag) . '</span>';
                                                            }
                                                        } else {
                                                            echo '<span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">Sin músculo</span>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>

                                                <div class="flex-shrink-0 mt-1">
                                                    <div class="default-state">
                                                        <button class="add-button flex items-center justify-center gap-1 px-3 py-2 text-sm font-medium rounded-lg bg-primary text-white hover:bg-primary/90 transition-all shadow-sm">
                                                            <span class="material-symbols-outlined text-sm">add</span><span class="hidden sm:inline">Agregar</span>
                                                        </button>
                                                    </div>
                                                    <div class="added-state">
                                                        <button class="remove-button flex items-center justify-center gap-1 px-3 py-2 text-sm font-medium rounded-lg bg-red-500 text-white hover:bg-red-600 transition-all shadow-sm" title="Eliminar de mi rutina">
                                                            <span class="material-symbols-outlined text-sm">close</span><span class="hidden sm:inline">Eliminar</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                <?php 
                                                    $descripcion = htmlspecialchars($ejercicio['descripcion_ejer']);
                                                ?>
                                            </div>

                                            <div class="description-content text-sm text-gray-600 dark:text-gray-400 mt-2">
                                                <p class="leading-relaxed"><?php echo nl2br($descripcion); ?></p>
                                                
                                                <?php if (!empty($ejercicio['herramientas'])): ?>
                                                    <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                                                        <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1 text-xs uppercase tracking-wider">
                                                            <span class="material-symbols-outlined text-sm">fitness_center</span> Herramientas requeridas
                                                        </h4>
                                                        <div class="flex flex-wrap gap-2">
                                                            <?php 
                                                            $herramientas_array = explode('||', $ejercicio['herramientas']);
                                                            foreach($herramientas_array as $herr) {
                                                                $partes = explode('::', $herr);
                                                                $icono = isset($partes[0]) ? $partes[0] : 'exercise';
                                                                $nombre = isset($partes[1]) ? $partes[1] : 'Herramienta';
                                                                ?>
                                                                <div class="flex items-center gap-1.5 bg-gray-100 dark:bg-gray-800/50 px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700/50">
                                                                    <span class="material-symbols-outlined text-primary dark:text-primary text-base"><?php echo htmlspecialchars($icono); ?></span>
                                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($nombre); ?></span>
                                                                </div>
                                                                <?php } ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mt-3">
                                                <button class="toggle-description flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-full bg-primary/10 dark:bg-primary/20 text-primary hover:bg-primary/20 dark:hover:bg-primary/30 transition-colors">
                                                    <span class="toggle-icon material-symbols-outlined text-sm">expand_more</span>
                                                    <span class="toggle-text">Ver más</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-results dark:bg-red-900/20 dark:border-red-800/50 dark:text-red-400 flex flex-col items-center justify-center p-8">
                                <span class="material-symbols-outlined text-5xl mb-3 text-red-500">search_off</span>
                                <h3 class="text-xl font-bold mb-2">Ejercicio no encontrado</h3>
                                <p class="mb-6 text-center text-gray-600 dark:text-gray-400">
                                    No se encontraron ejercicios que coincidan con tu búsqueda o filtros. <br>¿Conoces un ejercicio que no está en la lista?
                                </p>
                                <div class="flex gap-4">
                                    <?php if (!empty($busqueda) || !empty($filtroGeneral)): ?>
                                        <a href="?" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-800 dark:bg-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors">
                                            <span class="material-symbols-outlined text-sm">refresh</span>
                                            <span>Limpiar filtros</span>
                                        </a>
                                    <?php endif; ?>
                                    <button onclick="document.getElementById('create-modal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white font-medium rounded-lg hover:bg-primary/90 transition-colors shadow-md shadow-primary/20">
                                        <span class="material-symbols-outlined text-sm">add</span>
                                        <span>Crear nuevo ejercicio</span>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="routine-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-background-dark rounded-xl shadow-lg w-full max-w-md max-h-[80vh] flex flex-col">
            <div class="flex justify-between items-center border-b border-primary/20 p-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modal-title">...</h3>
                <button id="close-modal-btn" class="text-gray-500 hover:text-red-500">
                    <span class="material-symbols-outlined text-3xl">close</span>
                </button>
            </div>
            <div id="routine-list-container" class="overflow-y-auto p-4 space-y-2"></div>
            <div class="p-4 border-t border-primary/20">
                <a href="../../Rutinas/Creacion_Rutina.html" class="flex items-center justify-center gap-2 w-full text-sm bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary font-medium py-2 px-4 rounded-lg hover:bg-primary/20 transition-colors">
                    <span class="material-symbols-outlined">add</span><span>Crear Nueva Rutina</span>
                </a>
            </div>
        </div>
    </div>

    <div id="create-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[60] p-4">
        <div class="bg-white dark:bg-background-dark rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden border border-gray-200 dark:border-gray-800">
            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 p-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">fitness_center</span>
                    Registrar Nuevo Ejercicio
                </h3>
                <button onclick="document.getElementById('create-modal').classList.add('hidden')" class="text-gray-500 hover:text-red-500 transition-colors">
                    <span class="material-symbols-outlined text-2xl">close</span>
                </button>
            </div>
            
            <div class="overflow-y-auto p-6 custom-scrollbar flex-grow">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
                    <input type="hidden" name="crear_ejercicio" value="1">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre del Ejercicio *</label>
                            <input type="text" name="nom_ejercicio" required placeholder="Ej. Press Militar" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-black/20 text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoría Base *</label>
                            <select name="id_base" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-black/20 text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                <option value="">Seleccione...</option>
                                <?php foreach ($bases_opciones as $base): ?>
                                    <option value="<?php echo $base['id_base']; ?>"><?php echo htmlspecialchars($base['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nivel de Dificultad (1-10) *</label>
                            <input type="number" name="nivel_dificultad" min="1" max="10" required placeholder="Ej. 5" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-black/20 text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                            <p class="text-xs text-gray-500 mt-1">1-3 (Principiante), 4-6 (Intermedio), 7-10 (Avanzado)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Imagen/GIF de Ejemplo (Opcional)</label>
                            <input type="file" name="ejemplo_ejer" accept="image/*" class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all cursor-pointer">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción del Movimiento *</label>
                        <textarea name="descripcion_ejer" required rows="3" placeholder="Describe cómo realizar el ejercicio correctamente..." class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-black/20 text-gray-900 dark:text-white focus:ring-primary focus:border-primary"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Músculos Involucrados (Opcional)</label>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 max-h-40 overflow-y-auto custom-scrollbar bg-gray-50 dark:bg-gray-900/30">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <?php foreach ($musculos_opciones as $musculo): ?>
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 p-1 rounded transition-colors">
                                        <input type="checkbox" name="musculos[]" value="<?php echo $musculo['id_musculo']; ?>" class="rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                        <span class="text-sm text-gray-700 dark:text-gray-300 truncate" title="<?php echo htmlspecialchars($musculo['grupo_muscular']); ?>">
                                            <?php echo htmlspecialchars($musculo['nom_musculo']); ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Herramientas Requeridas (Opcional)</label>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 max-h-40 overflow-y-auto custom-scrollbar bg-gray-50 dark:bg-gray-900/30">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <?php foreach ($herramientas_opciones as $herr): ?>
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 p-1 rounded transition-colors">
                                        <input type="checkbox" name="herramientas_sel[]" value="<?php echo $herr['id_herramienta']; ?>" class="rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            <?php echo htmlspecialchars($herr['nom_herramienta']); ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-800">
                        <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:text-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary/90 rounded-lg transition-colors shadow-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">save</span>
                            Guardar Ejercicio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const availableRoutines = <?php echo json_encode($rutinas); ?>;

        document.addEventListener('DOMContentLoaded', function() {

            <?php if ($notificacion['mostrar']): ?>
                showNotification("<?php echo addslashes($notificacion['mensaje']); ?>", "<?php echo $notificacion['tipo']; ?>");
            <?php endif; ?>

            const toggleButtons = document.querySelectorAll('.toggle-description');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.exercise-card');
                    const description = card.querySelector('.description-content');
                    const icon = this.querySelector('.toggle-icon');
                    const text = this.querySelector('.toggle-text');

                    description.classList.toggle('expanded');
                    icon.classList.toggle('expanded');
                    text.textContent = description.classList.contains('expanded') ? 'Ver menos' : 'Ver más';
                });
            });

            const routineModal = document.getElementById('routine-modal');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const routineListContainer = document.getElementById('routine-list-container');
            const modalTitle = document.getElementById('modal-title');
            let currentExerciseData = {};

            const addButtons = document.querySelectorAll('.add-button');
            addButtons.forEach(button => {
                button.addEventListener('click', function() {
                    currentExerciseData = getExerciseData(this.closest('.exercise-card'));
                    showRoutineModal('add');
                });
            });

            const removeButtons = document.querySelectorAll('.remove-button');
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    currentExerciseData = getExerciseData(this.closest('.exercise-card'));
                    showRoutineModal('remove');
                });
            });

            function getExerciseData(cardElement) {
                return {
                    exerciseId: cardElement.dataset.exerciseId,
                    muscleIds: cardElement.dataset.muscleIds,
                    exerciseName: cardElement.querySelector('h3').textContent.trim(),
                    cardElement: cardElement
                };
            }

            closeModalBtn.addEventListener('click', () => routineModal.classList.add('hidden'));
            routineModal.addEventListener('click', (e) => {
                if (e.target === routineModal) routineModal.classList.add('hidden');
            });

            function showRoutineModal(mode) {
                const { exerciseName } = currentExerciseData;
                const isAdding = (mode === 'add');
                modalTitle.textContent = isAdding ? `Agregar "${exerciseName}" a...` : `Eliminar "${exerciseName}" de...`;
                routineListContainer.innerHTML = ''; 

                if (availableRoutines.length === 0) {
                    routineListContainer.innerHTML = '<p class="text-center text-gray-500 p-4">No tienes rutinas creadas.</p>';
                } else {
                    availableRoutines.forEach(routine => {
                        const button = document.createElement('button');
                        button.className = 'routine-select-button';
                        button.innerHTML = `
                            <span class="material-symbols-outlined">${routine.icono || 'fitness_center'}</span>
                            <span class="font-medium">${routine.nom_rutina}</span>
                        `;
                        button.addEventListener('click', () => {
                            if (isAdding) handleRoutineSelection(routine.id_rutina, routine.nom_rutina);
                            else handleRoutineRemoval(routine.id_rutina, routine.nom_rutina);
                        });
                        routineListContainer.appendChild(button);
                    });
                }
                routineModal.classList.remove('hidden');
            }

            async function handleRoutineSelection(routineId, routineName) {
                const { exerciseId, muscleIds, exerciseName, cardElement } = currentExerciseData;
                if (!muscleIds || muscleIds.trim() === "") {
                    showNotification(`Error: El ejercicio "${exerciseName}" no tiene músculos asignados.`, 'warning');
                    return;
                }
                routineListContainer.innerHTML = '<p class="text-center text-primary p-4 animate-pulse">Agregando...</p>';

                try {
                    const response = await fetch('api_agregar_ejercicio.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_ejercicio: exerciseId, id_rutina: routineId, muscle_ids: muscleIds })
                    });
                    const result = await response.json();
                    if (result.success) {
                        routineModal.classList.add('hidden');
                        cardElement.classList.add('exercise-added');
                        showNotification(`"${exerciseName}" agregado a "${routineName}"`, 'success');
                    } else {
                        routineModal.classList.add('hidden');
                        showNotification(`Error: ${result.message}`, 'warning');
                    }
                } catch (error) {
                    routineModal.classList.add('hidden');
                    showNotification('Error de conexión con el servidor.', 'warning');
                }
            }

            async function handleRoutineRemoval(routineId, routineName) {
                const { exerciseId, muscleIds, exerciseName, cardElement } = currentExerciseData;
                if (!muscleIds || muscleIds.trim() === "") {
                    showNotification(`Error: El ejercicio "${exerciseName}" no tiene músculos asignados.`, 'warning');
                    return;
                }
                routineListContainer.innerHTML = '<p class="text-center text-red-500 p-4 animate-pulse">Eliminando...</p>';

                try {
                    const response = await fetch('api_eliminar_ejercicio.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_ejercicio: exerciseId, id_rutina: routineId, muscle_ids: muscleIds })
                    });
                    const result = await response.json();
                    if (result.success) {
                        routineModal.classList.add('hidden');
                        cardElement.classList.remove('exercise-added');
                        showNotification(`"${exerciseName}" eliminado de "${routineName}"`, 'warning');
                    } else {
                        routineModal.classList.add('hidden');
                        showNotification(`Error: ${result.message}`, 'warning');
                    }
                } catch (error) {
                    routineModal.classList.add('hidden');
                    showNotification('Error de conexión con el servidor.', 'warning');
                }
            }

            const createModal = document.getElementById('create-modal');
            createModal.addEventListener('click', (e) => {
                if (e.target === createModal) createModal.classList.add('hidden');
            });

            function showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                const bgColor = type === 'success' ? 'bg-green-500' : (type === 'error' ? 'bg-red-500' : 'bg-amber-500');
                notification.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-xl z-[100] transform transition-transform duration-300 translate-x-full text-white ${bgColor} flex items-center gap-2`;
                
                const iconName = type === 'success' ? 'check_circle' : (type === 'error' ? 'error' : 'warning');
                notification.innerHTML = `
                    <span class="material-symbols-outlined">${iconName}</span>
                    <span class="font-medium">${message}</span>
                `;

                document.body.appendChild(notification);

                setTimeout(() => notification.classList.remove('translate-x-full'), 10);
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => {
                        if (document.body.contains(notification)) document.body.removeChild(notification);
                    }, 300);
                }, 3500);
            }
        });
    </script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Paginas_Principales/Pag_Principal.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fits-Mex - Inicio</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: { extend: { colors: { primary: "#607AFB", "background-light": "#f5f6f8", "background-dark": "#0f1323" }, fontFamily: { display: "Lexend" } } }
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }

        .fc {
            --fc-border-color: rgba(96, 122, 251, 0.1);
            font-family: 'Lexend', sans-serif;
            height: 350px;
            background: white;
            border-radius: 1rem;
            padding: 10px;
        }

        .dark .fc {
            background: rgba(0, 0, 0, 0.2);
            color: white;
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200">
    <div class="flex">
        <aside
            class="hidden md:flex flex-col w-64 bg-white dark:bg-black/20 border-r border-primary/20 fixed top-0 left-0 bottom-0 z-40">
            <div class="flex items-center justify-center h-20 border-b border-primary/20 px-4">
                <div class="flex items-center justify-center gap-2 bg-primary py-2 px-3 rounded-xl w-full">
                    <img alt="Logo" class="h-8 w-8 object-contain" src="../../Logo_FitsMex.png" />
                    <span class="text-lg font-bold text-white uppercase tracking-tight">Fits - Mex</span>
                </div>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="#"
                    class="flex items-center gap-3 px-4 py-2 text-sm font-medium bg-primary/10 text-primary rounded-lg">
                    <span class="material-symbols-outlined">home</span><span>Inicio</span>
                </a>
                <a href="../../Paginas/Ejercicios/php/ejercicios.php"
                    class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10">
                    <span class="material-symbols-outlined">search</span><span>Explorar</span>
                </a>
                <a href="../Rutinas/Lista_Rutinas.html"
                    class="flex items-center gap-3 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-lg hover:bg-primary/10">
                    <span class="material-symbols-outlined">library_books</span><span>Mis rutinas</span>
                </a>
            </nav>
            <div class="p-4 border-t border-primary/20">
                <a href="/Paginas/Perfil/Mi_Perfil.html"
                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-primary/10">
                    <span class="material-symbols-outlined text-gray-500">account_circle</span>
                    <span class="text-sm font-medium">Mi Perfil</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 md:ml-64">
            <main class="container mx-auto px-6 py-8 pb-24 md:pb-8">
                <div class="max-w-5xl mx-auto">
                    <h2 class="text-3xl font-extrabold mb-8">Panel de Control</h2>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
                        <div
                            class="bg-white dark:bg-black/10 p-5 rounded-2xl border border-primary/20 shadow-sm flex flex-col items-center justify-center">
                            <h3 class="text-xs font-bold mb-4 self-start text-gray-400 uppercase">Cumplimiento Semanal
                            </h3>
                            <div class="relative w-full h-36">
                                <canvas id="complianceChart"></canvas>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span id="perc_text" class="text-2xl font-bold text-primary">0%</span>
                                </div>
                            </div>
                        </div>
                        <div
                            class="lg:col-span-2 bg-white dark:bg-black/10 p-4 rounded-2xl border border-primary/20 shadow-sm">
                            <div id="calendar"></div>
                        </div>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-sm text-gray-500">Racha actual</span>
                        <span id="racha_val" class="text-xl font-bold text-orange-500">0 días</span>
                    </div>
                    <section class="mb-12">
                        <h3 class="text-2xl font-bold mb-6 flex items-center gap-2"><span
                                class="material-symbols-outlined text-primary">event_note</span> Rutina del Día</h3>
                        <div
                            class="rounded-2xl bg-white dark:bg-black/10 border border-primary/20 overflow-hidden flex flex-col md:flex-row shadow-lg">
                            <img src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=800&q=80"
                                class="md:w-1/3 h-48 md:h-auto object-cover" />
                            <div class="p-8">
                                <h4 class="text-2xl font-bold">Full Body Explosivo</h4>
                                <p class="mt-2 text-gray-500">Maximiza tu rendimiento hoy.</p>
                                <a href="finalizar_rutina.php?id=1"
                                    class="mt-6 inline-block bg-primary text-white px-8 py-3 rounded-xl font-bold hover:scale-105 transition-all">Empezar
                                    ahora</a>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="text-2xl font-bold mb-6">Acciones Rápidas</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <a href="../Rutinas/Lista_Rutinas.html"
                                class="h-16 flex items-center justify-center rounded-lg bg-primary text-white font-bold text-lg hover:scale-105 transition-all">Iniciar
                                Entrenamiento</a>
                            <a href="../../Paginas/Ejercicios/php/ejercicios.php"
                                class="h-16 flex items-center justify-center rounded-lg bg-primary/20 text-gray-900 dark:text-white font-bold text-lg hover:scale-105 transition-all">Explorar
                                Ejercicios</a>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log("Cargando estadísticas...");

        fetch('get_stats.php')
            .then(response => {
                if (!response.ok) throw new Error('Error al cargar get_stats.php. Revisa la consola de red.');
                return response.json();
            })
            .then(data => {
                console.log("Datos recibidos:", data);

                // Actualizar Porcentaje
                const perc = document.getElementById('perc_val');
                if (perc) perc.innerText = Math.round(data.compliance) + '%';

                // Actualizar Racha
                const racha = document.getElementById('racha_val');
                if (racha) racha.innerText = data.streak + " días";

                // Gráfica
                const chartCanvas = document.getElementById('complianceChart');
                if (chartCanvas) {
                    new Chart(chartCanvas, {
                        type: 'doughnut',
                        data: {
                            datasets: [{
                                data: [data.compliance, 100 - data.compliance],
                                backgroundColor: ['#607AFB', '#e5e7eb'],
                                borderWidth: 0, cutout: '80%', borderRadius: 10
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                    });
                }

                // Calendario
                const calEl = document.getElementById('calendar');
                if (calEl) {
                    const calendar = new FullCalendar.Calendar(calEl, {
                        initialView: 'dayGridMonth',
                        locale: 'es',
                        headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                        events: data.events
                    });
                    calendar.render();
                }
            })
            .catch(error => {
                console.error("Hubo un error:", error);
            });
    });
</script>

</body>

</html>
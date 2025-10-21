-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-10-2025 a las 23:26:39
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `modular`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicio`
--

CREATE TABLE `ejercicio` (
  `id_ejercicio` int(11) NOT NULL,
  `nom_ejercicio` varchar(30) NOT NULL,
  `descripcion_ejer` varchar(500) NOT NULL,
  `ejemplo_ejer` varchar(100) NOT NULL,
  `puntuacion` decimal(5,0) NOT NULL,
  `n_puntuacion` int(5) NOT NULL,
  `nivel` decimal(5,0) NOT NULL,
  `clasificacion` tinyint(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `ejercicio`
--

INSERT INTO `ejercicio` (`id_ejercicio`, `nom_ejercicio`, `descripcion_ejer`, `ejemplo_ejer`, `puntuacion`, `n_puntuacion`, `nivel`, `clasificacion`) VALUES
(1, 'Balanceo de piernas de adelant', 'Ejercicio / Cadera, Pierna\r\n\r\n\r\n1.Párese erguido junto a una pared o un objeto firme para mantener el equilibrio.\r\n\r\n2.Transfiere tu peso a una pierna y levanta ligeramente la pierna opuesta del suelo.\r\n\r\n3.Balancee la pierna levantada hacia adelante, manteniendo la rodilla recta o ligeramente doblada.\r\n\r\n4.Permita que la pierna se balancee hacia atrás, creando un arco suave de adelante hacia atrás.\r\n\r\n5.Realice de 10 a 15 movimientos por pierna, aumentando el rango de movimiento gradualmente co', 'balanceo_de_piernas_de_adelante_hacia_atras.gif', 0, 0, 1, 1),
(2, 'Navy Seal Burpee', 'Ejercicio / Cardio, Cuerpo completo\r\n\r\nComience desde una posición de pie con los pies separados al ancho de los hombros.\r\n\r\nPonte en cuclillas, coloca las manos en el suelo y patea los pies hacia atrás en una posición de flexión.\r\n\r\nRealice una flexión, luego conduzca la rodilla derecha hacia el codo derecho.\r\n\r\nRealice una segunda flexión, luego conduzca la rodilla izquierda hacia el codo izquierdo.\r\n\r\nRealice una tercera flexión, luego salte los pies hacia adelante para volver a la posición d', 'burpee_de_los_navy _seal.gif', 0, 0, 3, 2),
(3, 'Estocada de péndulo', 'Ejercicio / Cadera, Pierna\r\n\r\n\r\nPosición inicial: Párese derecho con los pies separados al ancho de las caderas, las manos entrelazadas en las caderas o frente al pecho para mantener el equilibrio.\r\n\r\nEstocada hacia adelante: Da un paso adelante con una pierna, bajando las caderas hasta que el muslo delantero esté casi paralelo al piso, manteniendo el torso erguido.\r\n\r\nTransición: Sin detenerse, empuje el pie delantero y balancee la misma pierna hacia atrás en una estocada inversa.\r\n\r\nEstocada i', 'estocada_de_pendulo.gif', 0, 0, 2, 1),
(4, 'Peso muerto con mancuernas', 'Ejercicio / erector de la columna, cuerpo completo, cadera, pierna\r\n\r\nArreglo:\r\nPárate con los pies separados al ancho de las caderas. Coloque una mancuerna a cada lado de sus pies.\r\nLas mancuernas deben colocarse paralelas a sus pies, de modo que las manijas estén perpendiculares a su cuerpo.\r\nPosición:\r\nDobla las caderas y las rodillas para bajar.\r\nMantenga la espalda recta, el pecho hacia arriba y los hombros hacia atrás.\r\nSostenga las pesas con un agarre firme, con las palmas hacia su cuerpo', 'peso_muerto_con_macuernas.gif', 0, 0, 3, 3),
(5, 'Sentadilla Pie con peso corpor', 'Ejercicio / Cadera, Pierna\r\n\r\nPárese con los pies más anchos que el ancho de los hombros y gire los dedos de los pies en un ángulo de aproximadamente 45 grados.\r\n\r\nMantenga el pecho alto y comprometa su núcleo para sostener su columna vertebral.\r\n\r\nEmpuje las rodillas en línea con los dedos de los pies mientras comienza a bajar a la sentadilla.\r\n\r\nDobla las rodillas y baja las caderas hacia abajo hasta que los muslos estén paralelos al suelo o ligeramente más bajos.\r\n\r\nHaga una breve pausa en la', 'sentadilla_a_pie_con_peso_corporal.gif', 0, 0, 1, 1),
(6, 'Salto de longitud Burpee', 'Ejercicio / Cardio, Cuerpo completo\r\n\r\nPosición inicial:\r\n\r\nPárate erguido con los pies separados al ancho de los hombros.\r\n\r\nMantén tu núcleo apoyado y los brazos relajados a los lados.\r\n\r\nFase de burpee:\r\n\r\nPonte en cuclillas y coloca las manos en el suelo frente a ti.\r\n\r\nSalta o retrocede los pies a una posición de tabla alta.\r\n\r\nRealiza una flexión (opcional pero común en esta variación).\r\n\r\nVolver a la sentadilla:\r\n\r\nSalta o da un paso adelante con los pies para que caigan justo fuera de tu', 'salto_de_longitud_con_burpee.gif', 0, 0, 4, 2),
(7, 'Sentadilla con copa elevada co', 'Ejercicio / Cadera, Pierna\r\n\r\nPárese con los talones elevados sobre discos de pesas, cuñas en cuclillas o una tabla inclinada, y los dedos de los pies apoyados en el piso.\r\n\r\nSostenga una mancuerna o pesa rusa verticalmente a la altura del pecho con ambas manos (agarre de copa).\r\n\r\nMantenga el pecho alto y el núcleo contraído mientras comienza a descender.\r\n\r\nPóngase en cuclillas lentamente, permitiendo que sus rodillas se desplacen hacia adelante sobre los dedos de los pies mientras mantiene el', 'sentadilla_con_copa_con_elevaciones_del_talon.gif\r\n', 0, 0, 3, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramienta`
--

CREATE TABLE `herramienta` (
  `id_herramienta` int(11) NOT NULL,
  `img_herramienta` varchar(100) NOT NULL,
  `nom_herramiena` varchar(50) NOT NULL,
  `tipo_herramienta` tinyint(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `musculo`
--

CREATE TABLE `musculo` (
  `id_musculo` int(11) NOT NULL,
  `nom_musculo` varchar(30) NOT NULL,
  `grupo_muscular` varchar(30) NOT NULL,
  `img_musculo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rel_ejer_rutina_musculo`
--

CREATE TABLE `rel_ejer_rutina_musculo` (
  `id_ejercicio` int(11) NOT NULL,
  `id_rutina` int(11) NOT NULL,
  `id_musculo` int(11) NOT NULL,
  `segundos` int(10) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rel_usu_herra_ejer`
--

CREATE TABLE `rel_usu_herra_ejer` (
  `fk_usuario` int(11) NOT NULL,
  `fk_herramienta` int(11) NOT NULL,
  `fk_ejercicio` int(11) NOT NULL,
  `peso` decimal(10,0) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutina`
--

CREATE TABLE `rutina` (
  `id_rutina` int(11) NOT NULL,
  `nom_rutina` varchar(100) NOT NULL,
  `color` varchar(30) NOT NULL,
  `icono` varchar(30) NOT NULL,
  `nivel` decimal(5,0) NOT NULL,
  `puntuacion` decimal(5,0) NOT NULL,
  `n_puntuacion` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `rutina`
--

INSERT INTO `rutina` (`id_rutina`, `nom_rutina`, `color`, `icono`, `nivel`, `puntuacion`, `n_puntuacion`) VALUES
(1, 'nombre', '#DC3545', 'directions_run', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contra` varchar(100) NOT NULL,
  `codigo_activacion` varchar(40) NOT NULL,
  `estado_activacion` tinyint(4) NOT NULL,
  `nivel` decimal(5,0) NOT NULL,
  `puntuacion` decimal(5,0) NOT NULL,
  `n_puntuacion` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ejercicio`
--
ALTER TABLE `ejercicio`
  ADD PRIMARY KEY (`id_ejercicio`);

--
-- Indices de la tabla `herramienta`
--
ALTER TABLE `herramienta`
  ADD PRIMARY KEY (`id_herramienta`);

--
-- Indices de la tabla `musculo`
--
ALTER TABLE `musculo`
  ADD PRIMARY KEY (`id_musculo`);

--
-- Indices de la tabla `rel_ejer_rutina_musculo`
--
ALTER TABLE `rel_ejer_rutina_musculo`
  ADD KEY `id_ejercicio` (`id_ejercicio`,`id_rutina`,`id_musculo`),
  ADD KEY `id_rutina` (`id_rutina`),
  ADD KEY `id_musculo` (`id_musculo`);

--
-- Indices de la tabla `rutina`
--
ALTER TABLE `rutina`
  ADD PRIMARY KEY (`id_rutina`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ejercicio`
--
ALTER TABLE `ejercicio`
  MODIFY `id_ejercicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `herramienta`
--
ALTER TABLE `herramienta`
  MODIFY `id_herramienta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `musculo`
--
ALTER TABLE `musculo`
  MODIFY `id_musculo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rutina`
--
ALTER TABLE `rutina`
  MODIFY `id_rutina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `rel_ejer_rutina_musculo`
--
ALTER TABLE `rel_ejer_rutina_musculo`
  ADD CONSTRAINT `rel_ejer_rutina_musculo_ibfk_1` FOREIGN KEY (`id_rutina`) REFERENCES `rutina` (`id_rutina`),
  ADD CONSTRAINT `rel_ejer_rutina_musculo_ibfk_2` FOREIGN KEY (`id_musculo`) REFERENCES `musculo` (`id_musculo`),
  ADD CONSTRAINT `rel_ejer_rutina_musculo_ibfk_3` FOREIGN KEY (`id_ejercicio`) REFERENCES `ejercicio` (`id_ejercicio`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

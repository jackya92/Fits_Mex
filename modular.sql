-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-10-2025 a las 01:12:46
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
  `nivel` decimal(10,0) NOT NULL DEFAULT 0,
  `puntuacion` decimal(10,0) NOT NULL DEFAULT 0,
  `n_puntuacion` int(5) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

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
  ADD KEY `id_rutina` (`id_rutina`),
  ADD KEY `id_ejercicio` (`id_ejercicio`,`id_musculo`),
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
  MODIFY `id_ejercicio` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id_rutina` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `rel_ejer_rutina_musculo_ibfk_3` FOREIGN KEY (`id_ejercicio`) REFERENCES `ejercicio` (`id_ejercicio`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `rel_ejer_rutina_musculo_ibfk_4` FOREIGN KEY (`id_musculo`) REFERENCES `musculo` (`id_musculo`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `rel_ejer_rutina_musculo_ibfk_5` FOREIGN KEY (`id_rutina`) REFERENCES `rutina` (`id_rutina`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

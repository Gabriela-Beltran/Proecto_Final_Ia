-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 13-12-2024 a las 00:01:17
-- Versión del servidor: 8.0.31
-- Versión de PHP: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyectoia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `health_data`
--

DROP TABLE IF EXISTS `health_data`;
CREATE TABLE IF NOT EXISTS `health_data` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `heart_rate` float NOT NULL,
  `temperature` float NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `spo2` float NOT NULL DEFAULT '0',
  `id_paciente` int NOT NULL,
  `estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `fk_health_data_usuarios` (`id_paciente`)
) ENGINE=MyISAM AUTO_INCREMENT=209 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `health_data`
--

INSERT INTO `health_data` (`ID`, `heart_rate`, `temperature`, `timestamp`, `spo2`, `id_paciente`, `estado`) VALUES
(1, 58.43, 32.5, '2024-11-17 01:39:33', 98, 1, 'dormido'),
(40, 85.83, 35.3125, '2024-12-09 03:37:40', 99.86, 1, 'normal'),
(75, 92, 36.5, '2024-12-11 02:18:59', 92.3, 1, 'normal'),
(74, 82, 36.5, '2024-12-11 02:13:51', 96.3, 1, 'normal'),
(73, 80, 36.5, '2024-12-11 02:13:32', 90.3, 1, 'normal'),
(72, 67.15, 30.9375, '2024-12-11 02:05:51', 95.47, 1, 'normal'),
(71, 77.79, 29.125, '2024-12-11 02:05:41', 90.08, 1, 'normal'),
(168, 63.63, 32.9375, '2024-12-12 06:21:06', 100, 30, 'normal'),
(167, 58.17, 32.1875, '2024-12-12 06:20:56', 99.01, 30, 'normal'),
(166, 49.98, 31.1875, '2024-12-12 06:20:46', 98.89, 30, 'normal'),
(165, 0, 28.875, '2024-12-12 06:20:36', 0, 30, 'normal'),
(164, 0, 28.5, '2024-12-12 06:20:26', 0, 30, 'normal'),
(163, 0, 27.9375, '2024-12-12 06:20:17', 0, 30, 'normal'),
(162, 0, 27.125, '2024-12-12 06:20:07', 0, 30, 'normal'),
(161, 0, 29.875, '2024-12-12 05:57:29', 0, 30, 'normal'),
(160, 0, 29.4375, '2024-12-12 05:57:19', 0, 30, 'normal'),
(159, 0, 29.3125, '2024-12-12 05:57:17', 0, 30, 'normal'),
(158, 80, 36.5, '2024-12-12 05:56:05', 91.3, 30, 'normal'),
(157, 80, 36.5, '2024-12-12 05:51:56', 91.3, 30, 'normal'),
(156, 80, 37, '2024-12-12 12:43:49', 99, 30, 'normal'),
(155, 100, 37, '2024-12-12 12:43:49', 99, 30, 'normal'),
(154, 80, 37, '2024-12-12 11:40:58', 99, 29, 'normal'),
(90, 92, 36.5, '2024-12-11 02:39:45', 91.3, 1, 'normal'),
(91, 80, 36.5, '2024-12-11 03:19:47', 91.3, 23, 'normal'),
(92, 80, 36.5, '2024-12-11 03:20:10', 91.3, 23, 'normal'),
(93, 92, 36.5, '2024-12-11 03:23:02', 91.3, 23, 'normal'),
(94, 80, 36.5, '2024-12-11 03:23:38', 91.3, 23, 'normal'),
(95, 80, 36.5, '2024-12-11 03:24:50', 91.3, 23, 'normal'),
(96, 80, 36.5, '2024-12-11 03:25:20', 91.3, 23, 'normal'),
(97, 80, 36.5, '2024-12-11 03:32:39', 91.3, 23, 'normal'),
(98, 80, 36.5, '2024-12-11 03:34:04', 91.3, 23, 'normal'),
(99, 80, 36.5, '2024-12-11 03:34:41', 91.3, 23, 'normal'),
(100, 80, 36.5, '2024-12-11 03:36:48', 91.3, 23, 'normal'),
(101, 80, 36.5, '2024-12-11 05:11:52', 91.3, 22, 'normal'),
(102, 80, 36.5, '2024-12-11 05:13:32', 91.3, 24, 'normal'),
(103, 80, 36.5, '2024-12-11 05:15:21', 91.3, 25, 'normal'),
(114, 88, 37, '2024-12-12 05:49:51', 99, 1, 'Normal'),
(169, 68.17, 33.0625, '2024-12-12 06:21:16', 99.63, 30, 'normal'),
(112, 88, 37, '2024-12-12 05:48:57', 99, 1, 'Normal'),
(111, 92, 37, '2024-12-12 05:48:57', 99, 1, 'Normal'),
(202, 100, 37, '2024-12-12 14:07:29', 99, 27, 'ejercicio'),
(153, 100, 37, '2024-12-12 11:40:58', 99, 29, 'normal'),
(170, 0, 33.375, '2024-12-12 06:21:27', 0, 30, 'normal'),
(171, 0, 32.4375, '2024-12-12 06:21:36', 0, 30, 'normal'),
(172, 0, 32.3125, '2024-12-12 06:21:46', 0, 30, 'normal'),
(173, 0, 32.0625, '2024-12-12 06:21:56', 0, 30, 'normal'),
(174, 0, 31.875, '2024-12-12 06:22:07', 0, 30, 'normal'),
(175, 0, 31.625, '2024-12-12 06:22:17', 0, 30, 'normal'),
(176, 0, 31.6875, '2024-12-12 06:22:26', 0, 30, 'normal'),
(177, 0, 31.5, '2024-12-12 06:22:36', 0, 30, 'normal'),
(178, 0, 31.375, '2024-12-12 06:22:46', 0, 30, 'normal'),
(179, 0, 31.5, '2024-12-12 06:22:56', 0, 30, 'normal'),
(180, 0, 31.3125, '2024-12-12 06:23:06', 0, 30, 'normal'),
(181, 0, 31.375, '2024-12-12 06:23:16', 0, 30, 'normal'),
(182, 0, 31.375, '2024-12-12 06:23:26', 0, 30, 'normal'),
(183, 0, 31.375, '2024-12-12 06:23:39', 0, 30, 'normal'),
(184, 0, 31.5, '2024-12-12 06:23:47', 0, 30, 'normal'),
(185, 0, 31.4375, '2024-12-12 06:23:57', 0, 30, 'normal'),
(186, 0, 31.375, '2024-12-12 06:24:08', 0, 30, 'normal'),
(187, 0, 31.375, '2024-12-12 06:24:17', 0, 30, 'normal'),
(188, 0, 31.3125, '2024-12-12 06:24:29', 0, 30, 'normal'),
(189, 0, 31.375, '2024-12-12 06:24:37', 0, 30, 'normal'),
(190, 0, 31.4375, '2024-12-12 06:24:48', 0, 30, 'normal'),
(191, 0, 31.4375, '2024-12-12 06:24:57', 0, 30, 'normal'),
(192, 0, 31.375, '2024-12-12 06:25:07', 0, 30, 'normal'),
(193, 0, 31.4375, '2024-12-12 06:25:16', 0, 30, 'normal'),
(194, 80, 36.5, '2024-12-12 06:28:13', 80.3, 30, 'normal'),
(195, 80, 36.5, '2024-12-12 06:39:58', 80.3, 30, 'normal'),
(196, 80, 36.5, '2024-12-12 06:40:08', 80.3, 30, 'normal'),
(197, 80, 36.5, '2024-12-12 06:41:52', 80.3, 30, 'normal'),
(198, 80, 36.5, '2024-12-12 06:43:45', 80.3, 30, 'normal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `normal_ranges`
--

DROP TABLE IF EXISTS `normal_ranges`;
CREATE TABLE IF NOT EXISTS `normal_ranges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_paciente` int NOT NULL,
  `estado` varchar(50) NOT NULL,
  `heart_rate_min` float NOT NULL,
  `heart_rate_max` float NOT NULL,
  `crtd_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_RANGES_USERS` (`id_paciente`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `states`
--

DROP TABLE IF EXISTS `states`;
CREATE TABLE IF NOT EXISTS `states` (
  `id` int NOT NULL AUTO_INCREMENT,
  `state` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `states`
--

INSERT INTO `states` (`id`, `state`) VALUES
(1, 'Normal'),
(2, 'Durmiendo'),
(3, 'Ejercicio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `ID_Usuario` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `Edad` int NOT NULL,
  `Tel` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `Usuario` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `Contrasena` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `Estatus` tinyint(1) NOT NULL,
  PRIMARY KEY (`ID_Usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`ID_Usuario`, `Nombre`, `Edad`, `Tel`, `Usuario`, `Contrasena`, `Estatus`) VALUES
(1, 'Juan', 20, '6681496085', 'admin', 'admin', 1),
(2, 'Gabriela', 20, '6681611449', 'Yotape', '24', 1),
(22, 'Carlos', 20, '6681496085', 'holiwis', '1234', 1),
(25, 'grisel', 22, '6879998946', 'gylo', '1234', 1),
(26, 'Angel', 12, '6441654383', 'angel', '1234', 1),
(27, 'veronica', 20, '6682318853', 'vero', '1234', 1),
(28, 'Juanito', 23, '6687906654', 'juan', '1234', 1),
(29, 'Mariana', 21, '6687906543', 'maria', '1234', 1),
(30, 'gaby', 23, '6681641449', 'gaby', '1234', 1),
(31, 'Rocio', 35, '6687432689', 'rocio', '12´34', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

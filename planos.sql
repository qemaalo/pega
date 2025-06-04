-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 03-06-2025 a las 08:14:42
-- Versión del servidor: 8.0.32
-- Versión de PHP: 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ingomarbd2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planos`
--

DROP TABLE IF EXISTS `planos`;
CREATE TABLE IF NOT EXISTS `planos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `tipo_plano` int DEFAULT NULL,
  `nombre_plano` varchar(200) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `ruta` text COLLATE utf8mb4_spanish_ci,
  `temporal` text COLLATE utf8mb4_spanish_ci,
  `ext` varchar(10) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `nombre_dwg` varchar(300) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `ruta_dwg` text COLLATE utf8mb4_spanish_ci,
  `temporal_dwg` text COLLATE utf8mb4_spanish_ci,
  `ext_dwg` varchar(10) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `nombre_otro` varchar(500) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `temporal_otro` text COLLATE utf8mb4_spanish_ci,
  `ext_otro` varchar(10) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `vercion` varchar(10) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `orden` int DEFAULT NULL,
  `rev` varchar(5) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `comentario` text COLLATE utf8mb4_spanish_ci,
  `ani_form` int DEFAULT NULL,
  `materiales` varchar(200) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `subido_user` int DEFAULT NULL,
  `activo` varchar(2) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `valido` int DEFAULT NULL,
  `ref_np` int DEFAULT NULL,
  `comentario_desabi` varchar(200) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

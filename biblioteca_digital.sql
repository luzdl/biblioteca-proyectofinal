-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-12-2025 a las 04:24:43
-- Versión del servidor: 12.0.2-MariaDB
-- Versión de PHP: 8.0.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `biblioteca_digital`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carreras`
--

CREATE TABLE `carreras` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carreras`
--

INSERT INTO `carreras` (`id`, `nombre`) VALUES
(1, 'Ingeniería en Sistemas Computacionales'),
(2, 'Ingeniería de Software'),
(3, 'Licenciatura en Matemática'),
(4, 'Licenciatura en Estadística'),
(5, 'Licenciatura en Química');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_libros`
--

CREATE TABLE `categorias_libros` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `cip` varchar(50) NOT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) NOT NULL,
  `segundo_apellido` varchar(50) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `carrera_id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `administrador` varchar(20) NOT NULL DEFAULT 'estudiante',
  `rol` varchar(20) NOT NULL DEFAULT 'estudiante'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `cip`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `fecha_nacimiento`, `carrera_id`, `usuario`, `email`, `password_hash`, `created_at`, `administrador`, `rol`) VALUES
(2, '0-000-001', 'Admin', NULL, 'General', NULL, '1985-01-01', 1, 'admin', 'admin@biblioteca.com', '$2y$10$C4bRGjp6Ajt41kVQ/HgQ3u0gM0MoxSLO2YURg3Ri4ARSJtkCQSK3S', '2025-12-12 19:03:05', 'estudiante', 'administrador'),
(3, '0-000-002', 'Maria', NULL, 'Bibliotecaria', NULL, '1992-05-12', 1, 'biblio', 'bibliotecaria@biblioteca.com', '$2y$10$8z5BrVt9fP7E7EdfMnRg.uNJVbzXY0Vq1tViTsz1b2VPRPyRusXbO', '2025-12-12 19:03:06', 'estudiante', 'bibliotecario'),
(4, '1-111-111', 'José', NULL, 'Bustamante', NULL, '2002-03-20', 1, 'joseb', 'jose@biblioteca.com', '$2y$10$7CbG2T1WJ9gIxHS9eP5bQOdH9E5w2kvCL5JvD4zca5JL1Mp/sInS6', '2025-12-12 19:03:06', 'estudiante', 'estudiante'),
(5, '1-222-222', 'Abigail', NULL, 'Koo', NULL, '2001-07-10', 2, 'abikoo', 'abi@biblioteca.com', '$2y$10$G2Xu6XdrtHzV1gwq5HCWOuqBp1tVpiRAIUsLLvdTGMlJPQDRZeZSK', '2025-12-12 19:03:06', 'estudiante', 'estudiante');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carreras`
--
ALTER TABLE `carreras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias_libros`
--
ALTER TABLE `categorias_libros`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_email` (`email`),
  ADD UNIQUE KEY `uniq_cip` (`cip`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carreras`
--
ALTER TABLE `carreras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `categorias_libros`
--
ALTER TABLE `categorias_libros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

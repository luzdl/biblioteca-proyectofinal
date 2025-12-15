-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2025 at 11:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `biblioteca_digital`
--

-- --------------------------------------------------------

--
-- Table structure for table `carreras`
--

CREATE TABLE `carreras` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carreras`
--

INSERT INTO `carreras` (`id`, `nombre`) VALUES
(1, 'Ingeniería en Sistemas Computacionales'),
(2, 'Ingeniería de Software'),
(3, 'Licenciatura en Matemática'),
(4, 'Licenciatura en Estadística'),
(5, 'Licenciatura en Química');

-- --------------------------------------------------------

--
-- Table structure for table `categorias_libros`
--

CREATE TABLE `categorias_libros` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorias_libros`
--

INSERT INTO `categorias_libros` (`id`, `nombre`) VALUES
(1, 'Base de Datos'),
(4, 'Química'),
(5, 'Informática'),
(6, 'Lógica'),
(7, 'Matemática'),
(8, 'Estadística');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'estudiante', 'Rol para estudiantes', '2025-12-14 15:45:59', NULL),
(2, 'bibliotecario', 'Rol para personal/bibliotecario', '2025-12-14 15:45:59', NULL),
(3, 'administrador', 'Rol administrador', '2025-12-14 15:45:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `uploads`
--

CREATE TABLE `uploads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `relative_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size_bytes` bigint(20) UNSIGNED NOT NULL,
  `sha256` char(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uploads`
--

INSERT INTO `uploads` (`id`, `usuario_id`, `original_name`, `stored_name`, `relative_path`, `mime_type`, `size_bytes`, `sha256`, `created_at`) VALUES
(1, 11, 'sepalabola.jpg', 'libro_693fdb273c756.jpg', 'img/portadas/libro_693fdb273c756.jpg', 'image/jpeg', 20220, '318488217da7ae363c15e7512dcae3322413696a374b98a3e1756f71ca292c85', '2025-12-15 04:55:51'),
(2, 11, 'image.png', 'libro_693fdf3c17f60.png', 'img/portadas/libro_693fdf3c17f60.png', 'image/jpeg', 17137, '5a3ca2228322e32f33670a9fb6fce76dee51c20045e998b477a9aa24bd824bd5', '2025-12-15 05:13:16'),
(3, 11, 'CartoonPond_Base_Color.png', 'libro_693feb150dec1.png', 'img/portadas/libro_693feb150dec1.png', 'image/png', 535815, 'e66543ae792b3194bd50d50621a67640990448ec44be68f8cec256c52a3cfc01', '2025-12-15 06:03:49'),
(4, 11, 'Química_ General, orgánica y biológica. Estructuras de la vida.png', 'libro_69400568f227b.png', 'img/portadas/libro_69400568f227b.png', 'image/png', 32097, 'feec3864f94808a806d1c8fb3366fcb1d675c56323b2266b22e568d8e5fe668f', '2025-12-15 07:56:08'),
(5, 11, '2.png', 'libro_6940064e4d70c.png', 'img/portadas/libro_6940064e4d70c.png', 'image/png', 30283, '25374989e4a16cf1739726fa3dc12eb832932f0ede6e6e9e79455586ea31b227', '2025-12-15 07:59:58'),
(6, 11, '3.png', 'libro_69400677870f9.png', 'img/portadas/libro_69400677870f9.png', 'image/png', 33602, 'dccf9df8997cc3953b9980bf61e1cffcf30db1f450fe069423be26a6feb783b2', '2025-12-15 08:00:39'),
(7, 11, '4.png', 'libro_694006b26351c.png', 'img/portadas/libro_694006b26351c.png', 'image/png', 28170, '79577a57559331780ac275d7070e9ff6cc54bf973c0e3420381dc8a4453254dc', '2025-12-15 08:01:38'),
(8, 11, '8.png', 'libro_694007000acda.png', 'img/portadas/libro_694007000acda.png', 'image/png', 31885, '0e308998118f7adeaab362204212f7b36391ea1da49592191dc8c55aab3acdcc', '2025-12-15 08:02:56'),
(9, 11, '14.png', 'libro_6940073664745.png', 'img/portadas/libro_6940073664745.png', 'image/png', 40040, '07ef37b949b20bdb503ca05344ce6dc2bdb53f351f7c100a375ce691ecf33402', '2025-12-15 08:03:50'),
(10, 11, '17.png', 'libro_6940078993c4f.png', 'img/portadas/libro_6940078993c4f.png', 'image/png', 22770, '0dcb1b98d8bd1a97d0ff00b43fea9553c4acc86f0b6230ac3b9fdb90096c0cf0', '2025-12-15 08:05:13'),
(11, 11, '25.png', 'libro_694007f1c0225.png', 'img/portadas/libro_694007f1c0225.png', 'image/png', 30150, 'a5641e8b85e882d43a20835849f0222132655c49b2af595940441567be797c20', '2025-12-15 08:06:57'),
(12, 11, '26.png', 'libro_6940083a00212.png', 'img/portadas/libro_6940083a00212.png', 'image/png', 26793, '92f103b657efda626b0262ecf7457d060b6482e83688aa6e410b63f6626dab6d', '2025-12-15 08:08:10'),
(13, 11, '27.png', 'libro_6940087649591.png', 'img/portadas/libro_6940087649591.png', 'image/png', 30434, '348885fcea3fed78967d5c541122b4b7edc8a296717b964e7314217062f4f91f', '2025-12-15 08:09:10'),
(14, 11, '28.png', 'libro_694009a52b549.png', 'img/portadas/libro_694009a52b549.png', 'image/png', 36767, '31b51654bac005a907198b13154fac87fe074228453d77c9b8d08c284c36579c', '2025-12-15 08:14:13'),
(15, 11, '29.png', 'libro_69402667a7023.png', 'img/portadas/libro_69402667a7023.png', 'image/png', 34273, '6fb7ca81a317c119670b1cca7341113e867f21bb300c14bd7f8c0d5e7ac5d364', '2025-12-15 10:16:55'),
(16, 11, '12.png', 'libro_694026c8b74f0.png', 'img/portadas/libro_694026c8b74f0.png', 'image/png', 43608, '8ac1c0549f65791a5e814024c024a350516294ba2a2aba2e0d7d7fe78835b0ca', '2025-12-15 10:18:32'),
(17, 11, '17.png', 'libro_694028aa62ce3.png', 'img/portadas/libro_694028aa62ce3.png', 'image/png', 22770, '0dcb1b98d8bd1a97d0ff00b43fea9553c4acc86f0b6230ac3b9fdb90096c0cf0', '2025-12-15 10:26:34');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
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
  `rol` varchar(20) NOT NULL DEFAULT 'estudiante',
  `profile_upload_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `cip`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `fecha_nacimiento`, `carrera_id`, `usuario`, `email`, `password_hash`, `created_at`, `administrador`, `rol`, `profile_upload_id`) VALUES
(2, '0-000-001', 'Admin', NULL, 'General', NULL, '1985-01-01', 1, 'admin', 'admin@biblioteca.com', '$2y$10$8KMlQGs3m2d5QgyCDN3v5eAxzKeMVvnGU6pPx4rDffnxoJ59zBbs.', '2025-12-12 19:03:05', 'estudiante', 'administrador', NULL),
(3, '0-000-002', 'Maria', NULL, 'Bibliotecaria', NULL, '1992-05-12', 1, 'biblio', 'bibliotecaria@biblioteca.com', '$2y$10$Y6dNw0H7qYuVlEw0M6TalO2V7O2D6O4EJvvjFmJZ9Y8oDdUK2Rt7u\r\n', '2025-12-12 19:03:06', 'estudiante', 'bibliotecario', NULL),
(4, '1-111-111', 'José', NULL, 'Bustamante', NULL, '2002-03-20', 1, 'joseb', 'jose@biblioteca.com', '$2y$10$7CbG2T1WJ9gIxHS9eP5bQOdH9E5w2kvCL5JvD4zca5JL1Mp/sInS6', '2025-12-12 19:03:06', 'estudiante', 'estudiante', NULL),
(5, '1-222-222', 'Abigail', NULL, 'Koo', NULL, '2001-07-10', 2, 'abikoo', 'abi@biblioteca.com', '$2y$10$G2Xu6XdrtHzV1gwq5HCWOuqBp1tVpiRAIUsLLvdTGMlJPQDRZeZSK', '2025-12-12 19:03:06', 'estudiante', 'estudiante', NULL),
(7, '8-1020-247', 'Luz', 'Luz', 'De León', 'De León', '2025-12-03', 2, 'luzdel', 'luz.deleon2@utp.ac.pa', '$2y$10$WVGF/Zwukae2Y0ZMQRoPZunr9d7iUzd0/70xruIahMbc8jC3FUV4O', '2025-12-14 14:29:01', 'estudiante', 'estudiante', NULL),
(8, '8-1011-9078', 'Ana', NULL, 'García', NULL, '1990-05-15', 0, 'anabiblio', 'ana.garcia@biblioteca.com', '$2y$10$Ur4WbSc4GG4QVxXZheWCZuVlptKnPTL.yW3sWlN1iffJc5FhjNffi', '0000-00-00 00:00:00', 'estudiante', 'bibliotecario', NULL),
(9, '7-27-2345', 'Carlos', NULL, 'Rodríguez', NULL, '1985-08-22', 0, 'carlosbiblio', 'carlos.rodriguez@biblioteca.com', '$2y$10$TCGZW8ehwPYa1zKGQisAuugLMDAx3mvsg/D3EEIERNZ8xOt.Htm6C', '0000-00-00 00:00:00', 'estudiante', 'bibliotecario', NULL),
(10, 'mariafferrer', 'María', 'Alejandra', 'Ferrer', 'Valles', '2003-10-04', 2, 'mariaafferrer', 'maria.ferrer@utp.ac.pa', '$2y$10$u730aLAhvbKmJ851w.t6QenUXiTMT8cV8pQAna9PVPYJXEJns5P0u', '2025-12-14 19:19:03', 'estudiante', 'estudiante', NULL),
(11, '', '', NULL, '', NULL, '0000-00-00', 0, 'thisistheskinofakiller', 'edward@cullen.com', '$2y$10$9tmqbfeGll.EKKpUs2BxHOQBqkjqdqgLf1poNDnAkbwaq0fDN2MKm', '0000-00-00 00:00:00', 'estudiante', 'bibliotecario', NULL),
(12, '8-1011-560', 'JUan', 'esteban', 'botacio', 'rivas', '2004-07-23', 2, 'Juan.botacio', 'botaciojuan3@gmail.com', '$2y$10$EzYRDW8Nwb7N9edS4mGMS.3kTAeOuF8LsEdkUPZoc13hCJTTgHNJ2', '2025-12-15 07:39:07', 'estudiante', 'estudiante', NULL),
(13, '8-1020-244', 'Luz', 'Luz', 'De León', 'De León', '2025-12-16', 1, 'admin', 'lucesitam1771@gmail.com', '$2y$10$kovV/9MhDj4qCp.PxnF6dO.sMyooEv3B8/y6O6/Pas8DemaIkarzm', '2025-12-15 10:03:44', 'estudiante', 'estudiante', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usuario_roles`
--

CREATE TABLE `usuario_roles` (
  `usuario_id` int(11) NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuario_roles`
--

INSERT INTO `usuario_roles` (`usuario_id`, `role_id`, `assigned_at`) VALUES
(2, 3, '2025-12-14 15:45:59'),
(3, 2, '2025-12-14 15:45:59'),
(4, 1, '2025-12-14 15:45:59'),
(5, 1, '2025-12-14 15:45:59'),
(7, 1, '2025-12-14 15:45:59'),
(11, 2, '2025-12-15 08:31:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carreras`
--
ALTER TABLE `carreras`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categorias_libros`
--
ALTER TABLE `categorias_libros`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_roles_name` (`name`);

--
-- Indexes for table `uploads`
--
ALTER TABLE `uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uploads_usuario_id` (`usuario_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_email` (`email`),
  ADD UNIQUE KEY `uniq_cip` (`cip`),
  ADD KEY `idx_usuarios_profile_upload_id` (`profile_upload_id`);

--
-- Indexes for table `usuario_roles`
--
ALTER TABLE `usuario_roles`
  ADD PRIMARY KEY (`usuario_id`,`role_id`),
  ADD KEY `idx_usuario_roles_role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carreras`
--
ALTER TABLE `carreras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categorias_libros`
--
ALTER TABLE `categorias_libros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `uploads`
--
ALTER TABLE `uploads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `uploads`
--
ALTER TABLE `uploads`
  ADD CONSTRAINT `fk_uploads_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_profile_upload` FOREIGN KEY (`profile_upload_id`) REFERENCES `uploads` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `usuario_roles`
--
ALTER TABLE `usuario_roles`
  ADD CONSTRAINT `fk_usuario_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_roles_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

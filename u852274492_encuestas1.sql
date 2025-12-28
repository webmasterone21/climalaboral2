-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 09-11-2025 a las 19:20:05
-- Versión del servidor: 11.8.3-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u852274492_encuestas1`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de logs de actividad del sistema HERCO v2.1';

--
-- Volcado de datos para la tabla `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `metadata`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, 'migration', 'Tabla activity_logs creada via script de migración v2', NULL, 'system', NULL, '143.202.196.82', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-22 20:20:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `companies`
--

INSERT INTO `companies` (`id`, `name`, `description`, `logo`, `created_at`, `updated_at`) VALUES
(1, 'Empresa Principal', 'Empresa creada durante la instalación', NULL, '2025-10-04 06:35:00', '2025-10-17 23:25:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT 1,
  `department_id` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `token` varchar(64) NOT NULL,
  `status` enum('pending','completed','expired') DEFAULT 'pending',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `invitation_sent_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('likert5','likert7','text','multiple') DEFAULT 'likert5',
  `is_required` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `question_categories`
--

CREATE TABLE `question_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `question_categories`
--

INSERT INTO `question_categories` (`id`, `name`, `description`, `sort_order`, `created_at`) VALUES
(1, 'Satisfacción Laboral', NULL, 1, '2025-10-04 06:35:00'),
(2, 'Participación y Autonomía', NULL, 2, '2025-10-04 06:35:00'),
(3, 'Comunicación y Objetivos', NULL, 3, '2025-10-04 06:35:00'),
(4, 'Equilibrio y Evaluación', NULL, 4, '2025-10-04 06:35:00'),
(5, 'Distribución y Carga de Trabajo', NULL, 5, '2025-10-04 06:35:00'),
(6, 'Reconocimiento y Promoción', NULL, 6, '2025-10-04 06:35:00'),
(7, 'Ambiente de Trabajo', NULL, 7, '2025-10-04 06:35:00'),
(8, 'Capacitación', NULL, 8, '2025-10-04 06:35:00'),
(9, 'Tecnología y Recursos', NULL, 9, '2025-10-04 06:35:00'),
(10, 'Colaboración y Compañerismo', NULL, 10, '2025-10-04 06:35:00'),
(11, 'Normativas y Regulaciones', NULL, 11, '2025-10-04 06:35:00'),
(12, 'Compensación y Beneficios', NULL, 12, '2025-10-04 06:35:00'),
(13, 'Bienestar y Salud', NULL, 13, '2025-10-04 06:35:00'),
(14, 'Seguridad en el Trabajo', NULL, 14, '2025-10-04 06:35:00'),
(15, 'Información y Comunicación', NULL, 15, '2025-10-04 06:35:00'),
(16, 'Relaciones con Supervisores', NULL, 16, '2025-10-04 06:35:00'),
(17, 'Feedback y Reconocimiento', NULL, 17, '2025-10-04 06:35:00'),
(18, 'Diversidad e Inclusión', NULL, 18, '2025-10-04 06:35:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `responses`
--

CREATE TABLE `responses` (
  `id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_value` text DEFAULT NULL,
  `answer_numeric` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `surveys`
--

CREATE TABLE `surveys` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('draft','active','closed') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT 1,
  `department_id` int(11) DEFAULT NULL COMMENT 'ID del departamento',
  `department` varchar(100) DEFAULT NULL COMMENT 'Nombre del departamento',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Teléfono',
  `position` varchar(100) DEFAULT NULL COMMENT 'Cargo o puesto',
  `avatar` varchar(255) DEFAULT NULL COMMENT 'URL foto de perfil',
  `email_verified_at` timestamp NULL DEFAULT NULL COMMENT 'Verificación email',
  `last_login` timestamp NULL DEFAULT NULL COMMENT 'Último acceso',
  `failed_login_attempts` int(11) DEFAULT 0 COMMENT 'Intentos fallidos',
  `locked_until` timestamp NULL DEFAULT NULL COMMENT 'Bloqueado hasta',
  `password_changed_at` timestamp NULL DEFAULT NULL COMMENT 'Cambio contraseña',
  `remember_token` varchar(255) DEFAULT NULL COMMENT 'Token sesión',
  `created_by` int(11) DEFAULT NULL COMMENT 'Creado por',
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','user','superadmin') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL COMMENT 'ID del usuario que realizó la última actualización'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `company_id`, `department_id`, `department`, `phone`, `position`, `avatar`, `email_verified_at`, `last_login`, `failed_login_attempts`, `locked_until`, `password_changed_at`, `remember_token`, `created_by`, `email`, `password`, `name`, `full_name`, `role`, `status`, `reset_token`, `reset_token_expiry`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'mario14_sanchez@hotmail.com', '$2y$10$HwMhefcOPHPfKJc83reXPe/b/O65t0PAsUU/DUzF69W4j2495IXyC', 'Administrador', 'Administrador del Sistema', 'admin', 'active', NULL, NULL, '2025-10-04 06:35:00', '2025-11-09 17:31:44', 1),
(11, 1, NULL, '', '', '', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'svmarioad@hotmail.com', '$2y$10$ym/6lNfyzdy/46wJI5fXFOOQTlXlIuCFvKAM.4BuodE9f24PElm8y', 'SANVE TECHNOLOGIES', NULL, '', 'active', NULL, NULL, '2025-10-27 20:50:57', '2025-11-09 13:19:23', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indices de la tabla `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company` (`company_id`);

--
-- Indices de la tabla `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_survey` (`survey_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_company` (`company_id`),
  ADD KEY `idx_email` (`email`);

--
-- Indices de la tabla `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_survey` (`survey_id`),
  ADD KEY `idx_category` (`category_id`);

--
-- Indices de la tabla `question_categories`
--
ALTER TABLE `question_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `responses`
--
ALTER TABLE `responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_participant` (`participant_id`),
  ADD KEY `idx_question` (`question_id`);

--
-- Indices de la tabla `surveys`
--
ALTER TABLE `surveys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_company` (`company_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_reset_token` (`reset_token`),
  ADD KEY `idx_company` (`company_id`),
  ADD KEY `idx_department_id` (`department_id`),
  ADD KEY `idx_last_login` (`last_login`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `question_categories`
--
ALTER TABLE `question_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `responses`
--
ALTER TABLE `responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `surveys`
--
ALTER TABLE `surveys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `fk_participants_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_participants_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participants_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Filtros para la tabla `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `question_categories` (`id`);

--
-- Filtros para la tabla `responses`
--
ALTER TABLE `responses`
  ADD CONSTRAINT `responses_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `responses_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `surveys`
--
ALTER TABLE `surveys`
  ADD CONSTRAINT `surveys_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `surveys_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

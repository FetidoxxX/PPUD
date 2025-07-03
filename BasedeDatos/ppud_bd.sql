-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-07-2025 a las 11:55:25
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ppud_bd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

CREATE TABLE `administrador` (
  `idAdministrador` int(11) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `n_doc` varchar(25) NOT NULL,
  `tipo_documento_id_tipo` int(11) NOT NULL,
  `ciudad_id_ciudad` int(11) DEFAULT NULL,
  `estado_id_estado` int(11) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `codigo_recuperacion` varchar(100) DEFAULT NULL,
  `codigo_expira_en` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`idAdministrador`, `contrasena`, `nombres`, `apellidos`, `correo`, `telefono`, `n_doc`, `tipo_documento_id_tipo`, `ciudad_id_ciudad`, `estado_id_estado`, `fecha_creacion`, `fecha_actualizacion`, `codigo_recuperacion`, `codigo_expira_en`) VALUES
(3001, 'admin1', 'Ana', 'Gómez', 'ana.gomez@udistrital.edu.co', '3101234567', '52123456', 1, 1, 1, '2025-06-13 03:33:49', '2025-06-13 03:33:49', NULL, NULL),
(3002, 'admin2', 'Luis', 'Martínez', 'luis.martinez@udistrital.edu.co', '3109876543', '98765432', 2, 1, 1, '2025-06-13 03:33:49', '2025-06-13 03:33:49', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `area_conocimiento`
--

CREATE TABLE `area_conocimiento` (
  `id_area` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `area_conocimiento`
--

INSERT INTO `area_conocimiento` (`id_area`, `nombre`, `descripcion`) VALUES
(1, 'Desarrollo de Software', 'Programación y desarrollo de aplicaciones'),
(2, 'Redes y Telecomunicaciones', 'Administración y configuración de redes'),
(3, 'Desarrollo Web', 'Desarrollo de aplicaciones web frontend y backend'),
(4, 'Bases de Datos', 'Administración y diseño de bases de datos'),
(5, 'Seguridad Informática', 'Ciberseguridad y protección de sistemas'),
(6, 'Inteligencia Artificial', 'Machine Learning y análisis de datos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrera`
--

CREATE TABLE `carrera` (
  `id_carrera` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `facultad` varchar(100) DEFAULT NULL,
  `duracion_semestres` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `carrera`
--

INSERT INTO `carrera` (`id_carrera`, `nombre`, `codigo`, `facultad`, `duracion_semestres`) VALUES
(1, 'Sistematización de Datos', 'SIST', 'Facultad Tecnologica', 10),
(2, 'Ingeniería Telemática', 'TELE', 'Facultad de Ingeniería', 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudad`
--

CREATE TABLE `ciudad` (
  `id_ciudad` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `pais` varchar(100) DEFAULT 'Colombia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `ciudad`
--

INSERT INTO `ciudad` (`id_ciudad`, `nombre`, `departamento`, `pais`) VALUES
(1, 'Bogotá', 'Cundinamarca', 'Colombia'),
(2, 'Medellín', 'Antioquia', 'Colombia'),
(3, 'Cali', 'Valle del Cauca', 'Colombia'),
(4, 'Barranquilla', 'Atlántico', 'Colombia'),
(5, 'Cartagena', 'Bolívar', 'Colombia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `disponibilidad_horaria`
--

CREATE TABLE `disponibilidad_horaria` (
  `id_disponibilidad` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `disponibilidad_horaria`
--

INSERT INTO `disponibilidad_horaria` (`id_disponibilidad`, `nombre`, `descripcion`) VALUES
(1, 'mañana', 'Disponibilidad en horario de mañana'),
(2, 'tarde', 'Disponibilidad en horario de tarde'),
(3, 'noche', 'Disponibilidad en horario nocturno'),
(4, 'tiempo_completo', 'Disponibilidad de tiempo completo'),
(5, 'flexible', 'Horario flexible según necesidades');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `idEmpresa` int(11) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` text NOT NULL,
  `n_doc` varchar(25) NOT NULL,
  `tipo_documento_id_tipo` int(11) NOT NULL,
  `ciudad_id_ciudad` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `sector_id_sector` int(11) DEFAULT NULL,
  `sitio_web` varchar(200) DEFAULT NULL,
  `numero_empleados` int(11) DEFAULT NULL,
  `ano_fundacion` year(4) DEFAULT NULL,
  `contacto_nombres` varchar(100) DEFAULT NULL,
  `contacto_apellidos` varchar(100) DEFAULT NULL,
  `contacto_cargo` varchar(100) DEFAULT NULL,
  `estado_id_estado` int(11) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `codigo_recuperacion` varchar(100) DEFAULT NULL,
  `codigo_expira_en` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`idEmpresa`, `correo`, `contrasena`, `nombre`, `telefono`, `direccion`, `n_doc`, `tipo_documento_id_tipo`, `ciudad_id_ciudad`, `descripcion`, `sector_id_sector`, `sitio_web`, `numero_empleados`, `ano_fundacion`, `contacto_nombres`, `contacto_apellidos`, `contacto_cargo`, `estado_id_estado`, `fecha_creacion`, `fecha_actualizacion`, `codigo_recuperacion`, `codigo_expira_en`) VALUES
(2001, 'empresa1@correo.com', 'empresa123', 'TechCol SAS', '3000000000', 'Av. Siempre Viva 1234', '900123456', 5, 4, 'Empresa de desarrollo de software y soluciones tecnológicas', 1, 'https://docs.google.com', 1503, '2025', 'María', 'gloriales ', 'Gerente de Recursos Humanos', 1, '2025-06-13 03:33:49', '2025-06-26 14:05:40', NULL, NULL),
(2002, 'empresa2@gmail.com', 'claveemp', 'Innovar Ltda.', '31278345', 'Calle Falsa 456', '', 5, 1, 'Consultora en innovación y transformación digital', 2, 'https://estudiantes.portaloas.udistrital.edu.', 75, '2018', 'Carlos ', 'Rodrígues', 'Director de Talento Humano', 1, '2025-06-13 03:33:49', '2025-07-03 09:25:01', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `id_estado` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`id_estado`, `nombre`, `descripcion`) VALUES
(1, 'activo', 'Registro activo en el sistema'),
(2, 'inactivo', 'Registro inactivo temporalmente'),
(3, 'vencida', 'Oferta vencida por fecha límite'),
(4, 'suspendido', 'Usuario suspendido por incumplimiento');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante`
--

CREATE TABLE `estudiante` (
  `idEstudiante` int(11) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fechaNac` date NOT NULL,
  `direccion` text NOT NULL,
  `n_doc` varchar(25) NOT NULL,
  `tipo_documento_id_tipo` int(11) NOT NULL,
  `ciudad_id_ciudad` int(11) NOT NULL,
  `codigo_estudiante` varchar(20) DEFAULT NULL,
  `carrera_id_carrera` int(11) NOT NULL,
  `semestre` int(11) DEFAULT NULL,
  `promedio_academico` decimal(3,2) DEFAULT NULL,
  `habilidades` text DEFAULT NULL,
  `experiencia_laboral` text DEFAULT NULL,
  `certificaciones` text DEFAULT NULL,
  `idiomas` text DEFAULT NULL,
  `objetivos_profesionales` text DEFAULT NULL,
  `disponibilidad_id_disponibilidad` int(11) DEFAULT 5,
  `estado_id_estado` int(11) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `codigo_recuperacion` varchar(100) DEFAULT NULL,
  `codigo_expira_en` datetime DEFAULT NULL,
  `hoja_vida_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `estudiante`
--

INSERT INTO `estudiante` (`idEstudiante`, `contrasena`, `nombre`, `correo`, `telefono`, `apellidos`, `fechaNac`, `direccion`, `n_doc`, `tipo_documento_id_tipo`, `ciudad_id_ciudad`, `codigo_estudiante`, `carrera_id_carrera`, `semestre`, `promedio_academico`, `habilidades`, `experiencia_laboral`, `certificaciones`, `idiomas`, `objetivos_profesionales`, `disponibilidad_id_disponibilidad`, `estado_id_estado`, `fecha_creacion`, `fecha_actualizacion`, `codigo_recuperacion`, `codigo_expira_en`, `hoja_vida_path`) VALUES
(1001, '123456', 'Laura', 'maic132530@gmail.com', '3111167', 'Pérez', '2000-01-01', 'Calle 1 #1-12', '100000001', 1, 1, '20201578001', 1, 4, 4.20, 'Java, Python, SQL, HTML, CSS, JavaScript', 'Desarrolladora junior en startup por 6 meses', 'Oracle Database Foundations 2', 'Español (nativo), Inglés (intermedio)', 'Especializarme en desarrollo de software empresarial y cine ', 4, 1, '2025-06-13 03:33:49', '2025-07-03 09:33:14', NULL, NULL, '../uploads/cv/cv_68663a87d5507.pdf'),
(1002, 'abcd', 'Carlos ', 'carlos@mail.com', '6576575', 'Ramírez', '1999-02-17', 'Carrera 2 #2-24', '100000002', 1, 1, '20191578002', 1, 9, 3.85, 'Redes, Cisco, Python, Linux, Seguridad Informática', 'Técnico en soporte de redes por 1 año', 'CCNA Routing and Switching', 'Español (nativo), Inglés (avanzado)', 'Trabajar en infraestructura de redes empresariales', 5, 1, '2025-06-13 03:33:49', '2025-07-03 09:55:08', NULL, NULL, '../uploads/cv/cv_6866537cc5c20.pdf');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `interes_estudiante_carrera`
--

CREATE TABLE `interes_estudiante_carrera` (
  `estudiante_idEstudiante` int(11) NOT NULL,
  `carrera_id_carrera` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `interes_estudiante_carrera`
--

INSERT INTO `interes_estudiante_carrera` (`estudiante_idEstudiante`, `carrera_id_carrera`) VALUES
(1001, 1),
(1001, 2),
(1002, 1),
(1002, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `interes_estudiante_oferta`
--

CREATE TABLE `interes_estudiante_oferta` (
  `estudiante_idEstudiante` int(11) NOT NULL,
  `oferta_idOferta` int(11) NOT NULL,
  `fecha_interes` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `interes_estudiante_oferta`
--

INSERT INTO `interes_estudiante_oferta` (`estudiante_idEstudiante`, `oferta_idOferta`, `fecha_interes`) VALUES
(1001, 2, '2025-07-03 09:03:08'),
(1001, 8, '2025-07-03 09:51:14'),
(1002, 2, '2025-07-03 09:54:27'),
(1002, 8, '2025-07-03 09:54:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modalidad`
--

CREATE TABLE `modalidad` (
  `id_modalidad` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `modalidad`
--

INSERT INTO `modalidad` (`id_modalidad`, `nombre`, `descripcion`) VALUES
(1, 'presencial', 'Trabajo presencial en las instalaciones de la empresa'),
(2, 'remoto', 'Trabajo completamente remoto desde casa'),
(3, 'hibrido', 'Combinación de trabajo presencial y remoto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oferta`
--

CREATE TABLE `oferta` (
  `idOferta` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text NOT NULL,
  `requisitos` text NOT NULL,
  `beneficios` text DEFAULT NULL,
  `modalidad_id_modalidad` int(11) NOT NULL,
  `tipo_oferta_id_tipo_oferta` int(11) NOT NULL,
  `duracion_meses` int(11) NOT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `remuneracion` varchar(100) DEFAULT NULL,
  `area_conocimiento_id_area` int(11) NOT NULL,
  `semestre_minimo` int(11) DEFAULT NULL,
  `promedio_minimo` decimal(3,2) DEFAULT NULL,
  `habilidades_requeridas` text DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_vencimiento` date NOT NULL,
  `cupos_disponibles` int(11) DEFAULT 1,
  `empresa_idEmpresa` int(11) NOT NULL,
  `estado_id_estado` int(11) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `oferta`
--

INSERT INTO `oferta` (`idOferta`, `titulo`, `descripcion`, `requisitos`, `beneficios`, `modalidad_id_modalidad`, `tipo_oferta_id_tipo_oferta`, `duracion_meses`, `horario`, `remuneracion`, `area_conocimiento_id_area`, `semestre_minimo`, `promedio_minimo`, `habilidades_requeridas`, `fecha_inicio`, `fecha_fin`, `fecha_vencimiento`, `cupos_disponibles`, `empresa_idEmpresa`, `estado_id_estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(2, 'Analista de Redes Jr', 'Pasantía en administración y monitoreo de infraestructura de redes corporativas', 'Conocimientos en redes, protocolos TCP/IP, experiencia con equipos Cisco', 'Certificación técnica, experiencia práctica con equipos enterprise', 1, 1, 4, '7:00 AM - 3:00 PM', 'Auxilio de alimentación y transporte', 2, 4, 3.80, 'CCNA, Cisco, Routing, Switching', '2025-08-01', '2025-11-30', '2025-07-15', 1, 2002, 1, '2025-06-13 03:33:49', '2025-07-03 09:24:41'),
(8, 'Desarrollador Junior Backend', 'Práctica en desarrollo de aplicaciones web completas usando tecnologías modernas', 'Conocimientos en JavaScript, frameworks frontend y backend', 'Proyecto real en portafolio, mentoría senior developer', 1, 1, 8, '8:00 AM - 5:00 PM', '1000000', 4, 4, 5.00, 'JavaScript, React, Node.js, MongoDB', '2025-06-14', '2025-06-14', '2025-07-14', 30, 2001, 1, '2025-06-13 05:54:27', '2025-07-03 05:18:13'),
(17, 'pruebaaaa ', 'pruebaaaa ', 'pruebaaaa ', 'pruebaaaa ', 3, 2, 5, '8 - 1', '1000000', 1, 4, 4.00, 'pruebaaaa ', '2025-07-10', '2025-07-24', '2025-08-24', 23, 2001, 2, '2025-07-02 22:43:37', '2025-07-02 22:43:53'),
(18, 'pruebaaaa', 'pruebaaaa', 'pruebaaaa', 'pruebaaaa', 1, 2, 3, '7- 8', '100000', 1, 4, 4.00, 'pruebaaaa', '2025-07-17', '2025-07-10', '2025-08-16', 1, 2001, 2, '2025-07-03 00:00:56', '2025-07-03 00:01:19'),
(19, 'prueba  q', 'prueba ', 'prueba ', 'prueba ', 3, 2, 3, '7-8 ', '10000', 4, 3, 3.00, 'prueba ', '2025-07-11', '2025-07-17', '2025-08-17', 15, 2002, 2, '2025-07-03 09:05:48', '2025-07-03 09:05:57'),
(20, 'prueba rg', 'prueba 4', 'prueba 4', 'prueba 4', 3, 2, 3, '3', '3', 1, 3, 3.00, 'prueba 4', '2025-07-10', '2025-07-28', '2025-08-20', 13, 2001, 2, '2025-07-03 09:43:41', '2025-07-03 09:43:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oferta_carrera_dirigida`
--

CREATE TABLE `oferta_carrera_dirigida` (
  `id_oferta_carrera` int(11) NOT NULL,
  `oferta_idOferta` int(11) NOT NULL,
  `carrera_id_carrera` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `oferta_carrera_dirigida`
--

INSERT INTO `oferta_carrera_dirigida` (`id_oferta_carrera`, `oferta_idOferta`, `carrera_id_carrera`) VALUES
(74, 17, 2),
(77, 18, 2),
(78, 8, 1),
(84, 19, 2),
(98, 20, 2),
(99, 2, 2),
(100, 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `referencia`
--

CREATE TABLE `referencia` (
  `idReferencia` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `puntuacion` decimal(2,1) DEFAULT NULL,
  `tipo_referencia_id_tipo_referencia` int(11) NOT NULL,
  `estudiante_idEstudiante` int(11) NOT NULL,
  `empresa_idEmpresa` int(11) NOT NULL,
  `estado_id_estado` int(11) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `referencia`
--

INSERT INTO `referencia` (`idReferencia`, `comentario`, `puntuacion`, `tipo_referencia_id_tipo_referencia`, `estudiante_idEstudiante`, `empresa_idEmpresa`, `estado_id_estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Excelente ambiente laboral, muy buena mentoría técnica. Aprendí mucho sobre desarrollo enterprise y buenas prácticas de programación. hol;a', 4.6, 1, 1001, 2001, 2, '2025-06-13 03:33:49', '2025-06-18 19:39:03'),
(2, 'Laura demostró excelentes habilidades técnicas y gran capacidad de aprendizaje. Se integró muy bien al equipo y completó todos sus objetivos.', 4.8, 2, 1001, 2001, 1, '2025-06-13 03:33:49', '2025-06-13 03:33:49'),
(3, 'La empresa brinda muy buena formación en redes empresariales. El equipo técnico es muy colaborativo y el ambiente de trabajo es profesional.', 4.2, 1, 1002, 2002, 1, '2025-06-13 03:33:49', '2025-06-13 03:33:49'),
(4, 'Carlos mostró dominio técnico sólido en redes y gran iniciativa para resolver problemas. Recomendamos su trabajo sin reservas.', 4.6, 2, 1002, 2002, 1, '2025-06-13 03:33:49', '2025-06-13 03:33:49'),
(21, 'muy buena chacha  la muchacha ', 5.0, 2, 1001, 2001, 1, '2025-07-02 22:45:51', '2025-07-03 09:52:03'),
(25, 'hola  prueba innoiva 2', 5.0, 2, 1001, 2002, 1, '2025-06-30 23:20:55', '2025-07-02 23:45:14'),
(32, 'excelente servicio 5/5', 5.0, 1, 1001, 2002, 1, '2025-07-03 04:56:11', '2025-07-03 04:59:53'),
(33, 'prueba 3', 5.0, 1, 1001, 2001, 2, '2025-07-03 05:16:58', '2025-07-03 05:19:39'),
(34, 'prueba ', 3.0, 2, 1002, 2001, 2, '2025-07-03 05:18:33', '2025-07-03 05:18:56'),
(35, 'hola 1', 5.0, 2, 1002, 2002, 2, '2025-07-03 05:39:38', '2025-07-03 05:39:46'),
(36, 'hola 1 ', 4.0, 2, 1002, 2001, 2, '2025-07-03 07:39:06', '2025-07-03 07:39:15'),
(37, 'dfgdfg', 4.0, 2, 1001, 2001, 2, '2025-07-03 07:46:32', '2025-07-03 07:46:37'),
(38, 'fsdfsdf', 3.0, 2, 1001, 2002, 1, '2025-07-03 09:17:01', '2025-07-03 09:17:01'),
(39, 'sdfsdfasdasd', 5.0, 2, 1002, 2002, 2, '2025-07-03 09:24:10', '2025-07-03 09:24:19'),
(40, 'hola  2', 5.0, 1, 1001, 2002, 1, '2025-07-03 09:32:44', '2025-07-03 09:48:01'),
(41, 'hola  1', 3.0, 2, 1001, 2002, 2, '2025-07-03 09:40:54', '2025-07-03 09:41:02'),
(42, 'sdfsdf3r3r3', 3.0, 2, 1001, 2001, 2, '2025-07-03 09:42:56', '2025-07-03 09:43:03'),
(43, 'sdfsdfsdfsdf', 5.0, 2, 1002, 2001, 2, '2025-07-03 09:44:05', '2025-07-03 09:44:16'),
(44, 'hola  3 ', 4.0, 1, 1002, 2002, 1, '2025-07-03 09:54:41', '2025-07-03 09:54:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sector_empresarial`
--

CREATE TABLE `sector_empresarial` (
  `id_sector` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `sector_empresarial`
--

INSERT INTO `sector_empresarial` (`id_sector`, `nombre`, `descripcion`) VALUES
(1, 'Tecnología', 'Empresas de desarrollo de software y tecnología'),
(2, 'Consultoría', 'Empresas de consultoría y asesoría empresarial'),
(3, 'Telecomunicaciones', 'Empresas de servicios de telecomunicaciones'),
(4, 'Financiero', 'Bancos, cooperativas y entidades financieras'),
(5, 'Educación', 'Instituciones educativas y de formación'),
(6, 'Salud', 'Entidades del sector salud y farmacéutico');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_documento`
--

CREATE TABLE `tipo_documento` (
  `id_tipo` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tipo_documento`
--

INSERT INTO `tipo_documento` (`id_tipo`, `nombre`) VALUES
(1, 'Cédula de Ciudadanía'),
(2, 'Cédula de Extranjería'),
(5, 'NIT'),
(3, 'Pasaporte'),
(4, 'Tarjeta de Identidad');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_oferta`
--

CREATE TABLE `tipo_oferta` (
  `id_tipo_oferta` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tipo_oferta`
--

INSERT INTO `tipo_oferta` (`id_tipo_oferta`, `nombre`, `descripcion`) VALUES
(1, 'practica', 'Práctica profesional o empresarial'),
(2, 'pasantia', 'Pasantía de investigación o desarrollo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_referencia`
--

CREATE TABLE `tipo_referencia` (
  `id_tipo_referencia` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tipo_referencia`
--

INSERT INTO `tipo_referencia` (`id_tipo_referencia`, `nombre`, `descripcion`) VALUES
(1, 'estudiante_a_empresa', 'Referencia del estudiante hacia la empresa'),
(2, 'empresa_a_estudiante', 'Referencia de la empresa hacia el estudiante');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`idAdministrador`),
  ADD UNIQUE KEY `correo_UNIQUE` (`correo`),
  ADD UNIQUE KEY `n_doc_UNIQUE` (`n_doc`),
  ADD KEY `fk_Administrador_tipo_documento_idx` (`tipo_documento_id_tipo`),
  ADD KEY `fk_Administrador_ciudad_idx` (`ciudad_id_ciudad`),
  ADD KEY `fk_Administrador_estado_idx` (`estado_id_estado`);

--
-- Indices de la tabla `area_conocimiento`
--
ALTER TABLE `area_conocimiento`
  ADD PRIMARY KEY (`id_area`),
  ADD UNIQUE KEY `nombre_UNIQUE` (`nombre`);

--
-- Indices de la tabla `carrera`
--
ALTER TABLE `carrera`
  ADD PRIMARY KEY (`id_carrera`),
  ADD UNIQUE KEY `codigo_UNIQUE` (`codigo`),
  ADD UNIQUE KEY `nombre_UNIQUE` (`nombre`);

--
-- Indices de la tabla `ciudad`
--
ALTER TABLE `ciudad`
  ADD PRIMARY KEY (`id_ciudad`),
  ADD KEY `idx_departamento` (`departamento`);

--
-- Indices de la tabla `disponibilidad_horaria`
--
ALTER TABLE `disponibilidad_horaria`
  ADD PRIMARY KEY (`id_disponibilidad`),
  ADD UNIQUE KEY `nombre_UNIQUE` (`nombre`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`idEmpresa`),
  ADD UNIQUE KEY `correo_UNIQUE` (`correo`),
  ADD UNIQUE KEY `n_doc_UNIQUE` (`n_doc`),
  ADD KEY `fk_Empresa_tipo_documento_idx` (`tipo_documento_id_tipo`),
  ADD KEY `fk_Empresa_ciudad_idx` (`ciudad_id_ciudad`),
  ADD KEY `fk_Empresa_sector_idx` (`sector_id_sector`),
  ADD KEY `fk_Empresa_estado_idx` (`estado_id_estado`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id_estado`),
  ADD UNIQUE KEY `nombre_UNIQUE` (`nombre`);

--
-- Indices de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD PRIMARY KEY (`idEstudiante`),
  ADD UNIQUE KEY `n_doc_UNIQUE` (`n_doc`),
  ADD UNIQUE KEY `correo_UNIQUE` (`correo`),
  ADD UNIQUE KEY `codigo_estudiante_UNIQUE` (`codigo_estudiante`),
  ADD KEY `fk_Estudiante_tipo_documento_idx` (`tipo_documento_id_tipo`),
  ADD KEY `fk_Estudiante_ciudad_idx` (`ciudad_id_ciudad`),
  ADD KEY `fk_Estudiante_carrera_idx` (`carrera_id_carrera`),
  ADD KEY `fk_Estudiante_disponibilidad_idx` (`disponibilidad_id_disponibilidad`),
  ADD KEY `fk_Estudiante_estado_idx` (`estado_id_estado`);

--
-- Indices de la tabla `interes_estudiante_carrera`
--
ALTER TABLE `interes_estudiante_carrera`
  ADD PRIMARY KEY (`estudiante_idEstudiante`,`carrera_id_carrera`),
  ADD KEY `fk_interes_estudiante_carrera_carrera1_idx` (`carrera_id_carrera`);

--
-- Indices de la tabla `interes_estudiante_oferta`
--
ALTER TABLE `interes_estudiante_oferta`
  ADD PRIMARY KEY (`estudiante_idEstudiante`,`oferta_idOferta`),
  ADD KEY `fk_interes_estudiante_oferta_oferta` (`oferta_idOferta`);

--
-- Indices de la tabla `modalidad`
--
ALTER TABLE `modalidad`
  ADD PRIMARY KEY (`id_modalidad`),
  ADD UNIQUE KEY `nombre_UNIQUE` (`nombre`);

--
-- Indices de la tabla `oferta`
--
ALTER TABLE `oferta`
  ADD PRIMARY KEY (`idOferta`),
  ADD KEY `fk_Oferta_empresa_idx` (`empresa_idEmpresa`),
  ADD KEY `fk_Oferta_modalidad_idx` (`modalidad_id_modalidad`),
  ADD KEY `fk_Oferta_tipo_oferta_idx` (`tipo_oferta_id_tipo_oferta`),
  ADD KEY `fk_Oferta_area_conocimiento_idx` (`area_conocimiento_id_area`),
  ADD KEY `fk_Oferta_estado_idx` (`estado_id_estado`),
  ADD KEY `idx_fecha_vencimiento` (`fecha_vencimiento`);

--
-- Indices de la tabla `oferta_carrera_dirigida`
--
ALTER TABLE `oferta_carrera_dirigida`
  ADD PRIMARY KEY (`id_oferta_carrera`),
  ADD KEY `fk_OfertaCarrera_oferta_idx` (`oferta_idOferta`),
  ADD KEY `fk_OfertaCarrera_carrera_idx` (`carrera_id_carrera`);

--
-- Indices de la tabla `referencia`
--
ALTER TABLE `referencia`
  ADD PRIMARY KEY (`idReferencia`),
  ADD KEY `fk_Referencia_estudiante_idx` (`estudiante_idEstudiante`),
  ADD KEY `fk_Referencia_empresa_idx` (`empresa_idEmpresa`),
  ADD KEY `fk_Referencia_tipo_referencia_idx` (`tipo_referencia_id_tipo_referencia`),
  ADD KEY `fk_Referencia_estado_idx` (`estado_id_estado`);

--
-- Indices de la tabla `sector_empresarial`
--
ALTER TABLE `sector_empresarial`
  ADD PRIMARY KEY (`id_sector`),
  ADD UNIQUE KEY `nombre_UNIQUE` (`nombre`);

--
-- Indices de la tabla `tipo_documento`
--
ALTER TABLE `tipo_documento`
  ADD PRIMARY KEY (`id_tipo`),
  ADD UNIQUE KEY `nombre_UNIQUE` (`nombre`);

--
-- Indices de la tabla `tipo_oferta`
--
ALTER TABLE `tipo_oferta`
  ADD PRIMARY KEY (`id_tipo_oferta`),
  ADD UNIQUE KEY `nombre_UNIQUE` (`nombre`);

--
-- Indices de la tabla `tipo_referencia`
--
ALTER TABLE `tipo_referencia`
  ADD PRIMARY KEY (`id_tipo_referencia`),
  ADD UNIQUE KEY `nombre_UNIQUE` (`nombre`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `area_conocimiento`
--
ALTER TABLE `area_conocimiento`
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `carrera`
--
ALTER TABLE `carrera`
  MODIFY `id_carrera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ciudad`
--
ALTER TABLE `ciudad`
  MODIFY `id_ciudad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `disponibilidad_horaria`
--
ALTER TABLE `disponibilidad_horaria`
  MODIFY `id_disponibilidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `modalidad`
--
ALTER TABLE `modalidad`
  MODIFY `id_modalidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `oferta`
--
ALTER TABLE `oferta`
  MODIFY `idOferta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `oferta_carrera_dirigida`
--
ALTER TABLE `oferta_carrera_dirigida`
  MODIFY `id_oferta_carrera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de la tabla `referencia`
--
ALTER TABLE `referencia`
  MODIFY `idReferencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `sector_empresarial`
--
ALTER TABLE `sector_empresarial`
  MODIFY `id_sector` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tipo_documento`
--
ALTER TABLE `tipo_documento`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipo_oferta`
--
ALTER TABLE `tipo_oferta`
  MODIFY `id_tipo_oferta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipo_referencia`
--
ALTER TABLE `tipo_referencia`
  MODIFY `id_tipo_referencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD CONSTRAINT `fk_Administrador_ciudad` FOREIGN KEY (`ciudad_id_ciudad`) REFERENCES `ciudad` (`id_ciudad`),
  ADD CONSTRAINT `fk_Administrador_estado` FOREIGN KEY (`estado_id_estado`) REFERENCES `estado` (`id_estado`),
  ADD CONSTRAINT `fk_Administrador_tipo_documento` FOREIGN KEY (`tipo_documento_id_tipo`) REFERENCES `tipo_documento` (`id_tipo`);

--
-- Filtros para la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD CONSTRAINT `fk_Empresa_ciudad` FOREIGN KEY (`ciudad_id_ciudad`) REFERENCES `ciudad` (`id_ciudad`),
  ADD CONSTRAINT `fk_Empresa_estado` FOREIGN KEY (`estado_id_estado`) REFERENCES `estado` (`id_estado`),
  ADD CONSTRAINT `fk_Empresa_sector` FOREIGN KEY (`sector_id_sector`) REFERENCES `sector_empresarial` (`id_sector`),
  ADD CONSTRAINT `fk_Empresa_tipo_documento` FOREIGN KEY (`tipo_documento_id_tipo`) REFERENCES `tipo_documento` (`id_tipo`);

--
-- Filtros para la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD CONSTRAINT `fk_Estudiante_carrera` FOREIGN KEY (`carrera_id_carrera`) REFERENCES `carrera` (`id_carrera`),
  ADD CONSTRAINT `fk_Estudiante_ciudad` FOREIGN KEY (`ciudad_id_ciudad`) REFERENCES `ciudad` (`id_ciudad`),
  ADD CONSTRAINT `fk_Estudiante_disponibilidad` FOREIGN KEY (`disponibilidad_id_disponibilidad`) REFERENCES `disponibilidad_horaria` (`id_disponibilidad`),
  ADD CONSTRAINT `fk_Estudiante_estado` FOREIGN KEY (`estado_id_estado`) REFERENCES `estado` (`id_estado`),
  ADD CONSTRAINT `fk_Estudiante_tipo_documento` FOREIGN KEY (`tipo_documento_id_tipo`) REFERENCES `tipo_documento` (`id_tipo`);

--
-- Filtros para la tabla `interes_estudiante_carrera`
--
ALTER TABLE `interes_estudiante_carrera`
  ADD CONSTRAINT `fk_interes_estudiante_carrera_carrera1` FOREIGN KEY (`carrera_id_carrera`) REFERENCES `carrera` (`id_carrera`),
  ADD CONSTRAINT `fk_interes_estudiante_carrera_estudiante1` FOREIGN KEY (`estudiante_idEstudiante`) REFERENCES `estudiante` (`idEstudiante`) ON DELETE CASCADE;

--
-- Filtros para la tabla `interes_estudiante_oferta`
--
ALTER TABLE `interes_estudiante_oferta`
  ADD CONSTRAINT `fk_interes_estudiante_oferta_estudiante` FOREIGN KEY (`estudiante_idEstudiante`) REFERENCES `estudiante` (`idEstudiante`),
  ADD CONSTRAINT `fk_interes_estudiante_oferta_oferta` FOREIGN KEY (`oferta_idOferta`) REFERENCES `oferta` (`idOferta`);

--
-- Filtros para la tabla `oferta`
--
ALTER TABLE `oferta`
  ADD CONSTRAINT `fk_Oferta_area_conocimiento` FOREIGN KEY (`area_conocimiento_id_area`) REFERENCES `area_conocimiento` (`id_area`),
  ADD CONSTRAINT `fk_Oferta_empresa` FOREIGN KEY (`empresa_idEmpresa`) REFERENCES `empresa` (`idEmpresa`),
  ADD CONSTRAINT `fk_Oferta_estado` FOREIGN KEY (`estado_id_estado`) REFERENCES `estado` (`id_estado`),
  ADD CONSTRAINT `fk_Oferta_modalidad` FOREIGN KEY (`modalidad_id_modalidad`) REFERENCES `modalidad` (`id_modalidad`),
  ADD CONSTRAINT `fk_Oferta_tipo_oferta` FOREIGN KEY (`tipo_oferta_id_tipo_oferta`) REFERENCES `tipo_oferta` (`id_tipo_oferta`);

--
-- Filtros para la tabla `oferta_carrera_dirigida`
--
ALTER TABLE `oferta_carrera_dirigida`
  ADD CONSTRAINT `fk_OfertaCarrera_carrera` FOREIGN KEY (`carrera_id_carrera`) REFERENCES `carrera` (`id_carrera`),
  ADD CONSTRAINT `fk_OfertaCarrera_oferta` FOREIGN KEY (`oferta_idOferta`) REFERENCES `oferta` (`idOferta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `referencia`
--
ALTER TABLE `referencia`
  ADD CONSTRAINT `fk_Referencia_empresa` FOREIGN KEY (`empresa_idEmpresa`) REFERENCES `empresa` (`idEmpresa`),
  ADD CONSTRAINT `fk_Referencia_estado` FOREIGN KEY (`estado_id_estado`) REFERENCES `estado` (`id_estado`),
  ADD CONSTRAINT `fk_Referencia_estudiante` FOREIGN KEY (`estudiante_idEstudiante`) REFERENCES `estudiante` (`idEstudiante`),
  ADD CONSTRAINT `fk_Referencia_tipo_referencia` FOREIGN KEY (`tipo_referencia_id_tipo_referencia`) REFERENCES `tipo_referencia` (`id_tipo_referencia`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

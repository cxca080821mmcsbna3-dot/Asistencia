-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-03-2026 a las 07:11:22
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
-- Base de datos: `asistencia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

CREATE TABLE `administrador` (
  `id_admin` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`id_admin`, `nombre`, `password`, `correo`) VALUES
(3, 'ERICK URIEL', '$2y$10$8ndt3Hin1CeUlm1HPuVjROrXDuYyLu2LDcU3/inTYWkpiDb8F2ZyW', 'admin@gmail.com'),
(6, 'test', '$2y$10$TB41HfcfJ.gmVwuDo4xI6OYGBfy6H.XQ8D1GJdwYPsoFfCIbYjCB2', 'test@test.com');

--
-- Disparadores `administrador`
--
DELIMITER $$
CREATE TRIGGER `trg_administrador_delete` BEFORE DELETE ON `administrador` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, id_admin, admin_nombre)
    VALUES (
        'administrador',
        OLD.id_admin,
        'DELETE',
        CONCAT(
            'nombre=', OLD.nombre,
            ', correo=', OLD.correo
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_administrador_update` BEFORE UPDATE ON `administrador` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, datos_despues, id_admin, admin_nombre)
    VALUES (
        'administrador',
        OLD.id_admin,
        'UPDATE',
        CONCAT(
            'nombre=', OLD.nombre,
            ', correo=', OLD.correo
        ),
        CONCAT(
            'nombre=', NEW.nombre,
            ', correo=', NEW.correo
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno`
--

CREATE TABLE `alumno` (
  `id_alumno` int(11) NOT NULL,
  `numero_lista` int(11) NOT NULL,
  `matricula` bigint(20) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` bigint(11) NOT NULL,
  `id_grupo` int(11) DEFAULT NULL,
  `curp` varchar(50) NOT NULL,
  `foto_ruta` varchar(255) DEFAULT NULL,
  `foto_nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alumno`
--

INSERT INTO `alumno` (`id_alumno`, `numero_lista`, `matricula`, `apellidos`, `nombre`, `telefono`, `id_grupo`, `curp`, `foto_ruta`, `foto_nombre`) VALUES
(18, 1, 23415082610076, 'Aguirre Chavez', 'Jostin Josue', 0, 38546385, '', NULL, NULL),
(19, 2, 23415082610044, 'Casas Cabrera', 'Ana Sofia', 0, 38546385, '', NULL, NULL),
(20, 3, 23415082610098, 'Chavez Sanchez', 'Erick Uriel', 5537969100, 38546385, 'CASE080317HMCHNRA7', NULL, NULL),
(21, 4, 23415082610149, 'Cruz Rodriguez', 'Ian Santiago', 5611056506, 38546385, 'curi080122hdfrdna8', NULL, NULL),
(22, 5, 23415082610100, 'De La Cruz Martinez', 'Isaac Zadquiel', 0, 38546385, '', NULL, NULL),
(23, 6, 23415082610025, 'Hernandez Cortes', 'Angel Gabriel', 0, 38546385, '', NULL, NULL),
(24, 7, 23415082610027, 'Lopez Martinez', 'Avril Aurora', 0, 38546385, '', NULL, NULL),
(25, 8, 23415082610146, 'Lugo Ordoñez', 'Nataly Fernanda', 0, 38546385, '', NULL, NULL),
(26, 9, 23415082610111, 'Luna Moreno', 'Alexis Ernesto', 0, 38546385, '', NULL, NULL),
(27, 10, 23415082610190, 'Melchor Hernandez', 'Ricardo', 0, 38546385, '', NULL, NULL),
(28, 11, 23415082610150, 'Meneses Ramirez', 'Israel', 0, 38546385, '', NULL, NULL),
(29, 12, 23415082610180, 'Orozco Lopez', 'Nadia Irene', 0, 38546385, '', NULL, NULL),
(30, 13, 23415082610172, 'Rosales Villarreal', 'Josue', 0, 38546385, '', NULL, NULL),
(31, 14, 23415082610144, 'Sandoval Arteaga', 'Karla', 0, 38546385, '', NULL, NULL),
(32, 15, 23415082610179, 'Yañez Moreno', 'Xavi Santiago', 0, 38546385, '', NULL, NULL),
(35, 1, 24415082610152, 'Martha Sofia', 'Angeles Vazquez', 0, 23498772, '', NULL, NULL),
(36, 2, 24415082610156, 'Jarod Ali', 'Arellano Zacarias', 0, 23498772, '', NULL, NULL),
(37, 3, 24415082610139, 'Camila', 'Balleza Ramirez', 0, 23498772, '', NULL, NULL),
(38, 4, 24415082610105, 'Fernanda Belem', 'Bernardo Fernandez', 0, 23498772, '', NULL, NULL),
(39, 5, 24415082610095, 'Pamela Jaqueline', 'Campos Piña', 0, 23498772, '', NULL, NULL),
(40, 6, 24415082610108, 'Avril', 'Caudillo Sosa', 0, 23498772, '', NULL, NULL),
(41, 7, 24415082610191, 'Leonardo', 'Cordoba Almaguer', 0, 23498772, '', NULL, NULL),
(42, 8, 24415082610121, 'Alexa Dayanitza', 'Diaz Maldonado', 0, 23498772, '', NULL, NULL),
(43, 9, 24415082610103, 'Angel Kael', 'Espinoza Carballo', 0, 23498772, '', NULL, NULL),
(44, 10, 24415082610122, 'Santiago', 'Estrada Rios', 0, 23498772, '', NULL, NULL),
(45, 11, 24415082610148, 'Samantha', 'Flores Nuñez', 0, 23498772, '', NULL, NULL),
(46, 12, 24415082610023, 'Judith Juanita', 'Garcia Romero', 0, 23498772, '', NULL, NULL),
(47, 13, 24415082610072, 'Alyn Estefani', 'Gonzalez Ibañez', 0, 23498772, '', NULL, NULL),
(48, 14, 24415082610141, 'Joseph Tadeo', 'Gonzalez Orozco', 0, 23498772, '', NULL, NULL),
(49, 15, 24415082610178, 'Samantha Michell', 'Govea Trejo', 0, 23498772, '', NULL, NULL),
(50, 16, 24415082610053, 'Lallha Noemi', 'Hernandez Navarro', 0, 23498772, '', NULL, NULL),
(51, 17, 24415082610123, 'Meztli Valentina', 'Jimenez Reyes', 0, 23498772, '', NULL, NULL),
(52, 18, 24415082610160, 'Hanna', 'Martinez Buendia', 0, 23498772, '', NULL, NULL),
(53, 19, 24415082610140, 'Valentina', 'Mireles Molina', 0, 23498772, '', NULL, NULL),
(54, 20, 24415082610066, 'Juan Pablo', 'Montes Caluhe', 0, 23498772, '', NULL, NULL),
(55, 21, 24415082610027, 'Tonahtiuh', 'Moreno Lugo Dante', 0, 23498772, '', NULL, NULL),
(56, 22, 24415082610133, 'Antonio Emanuel', 'Oaxaca Simon', 0, 23498772, '', NULL, NULL),
(57, 23, 24415082610193, 'Renee Iran', 'Ocampo Vazquez', 0, 23498772, '', NULL, NULL),
(58, 24, 24415082610109, 'Jaime', 'Ortiz Cruz', 0, 23498772, '', NULL, NULL),
(59, 25, 24415082610115, 'Aelyn Josabeth', 'Perez Nochebuena', 0, 23498772, '', NULL, NULL),
(60, 26, 24415082610048, 'Juan Pablo', 'Ponce Maldonado', 0, 23498772, '', NULL, NULL),
(61, 27, 24415082610047, 'Bernardo Constantino', 'Reyes De La Rosa', 0, 23498772, '', NULL, NULL),
(62, 28, 24415082610214, 'Liam Gabriel', 'Rojo Racho', 0, 23498772, '', NULL, NULL),
(63, 29, 24415082610063, 'Diana Yohali', 'Sanchez Alvarez', 0, 23498772, '', NULL, NULL),
(64, 30, 24415082610198, 'Williams Zaid', 'Sanchez Dominguez', 0, 23498772, '', NULL, NULL),
(65, 31, 24415082610078, 'Maria Cecilia', 'Silva Lazcano', 0, 23498772, '', NULL, NULL),
(66, 32, 24415082610205, 'Eunice Daniela', 'Silva Mendoza', 0, 23498772, '', NULL, NULL),
(67, 33, 24415082610033, 'Alejandro', 'Tapia Flores', 0, 23498772, '', NULL, NULL),
(68, 34, 24415082610080, 'Daren Gael', 'Velazquez Diaz', 0, 23498772, '', NULL, NULL),
(69, 35, 24415082610187, 'Angel Giovanni', 'Zarraga Velasco', 0, 23498772, '', NULL, NULL),
(70, 36, 24415082610008, 'Angel Daniel', 'Zenil Madera', 0, 23498772, '', NULL, NULL),
(71, 37, 24415082610087, 'Maria Fernanda', 'Zuñiga Rojo', 0, 23498772, '', NULL, NULL),
(72, 1, 23415082610066, 'Diego Eduardo', 'Alba Hernandez', 0, 71900101, '', NULL, NULL),
(73, 2, 23415082610135, 'Angel David', 'Alvarez Hernandez', 0, 71900101, '', NULL, NULL),
(74, 3, 23415082610029, 'Ian Marat', 'Bravo Reyes', 0, 71900101, '', NULL, NULL),
(75, 4, 23415082610205, 'Regina', 'Castañeda Hernandez', 0, 71900101, '', NULL, NULL),
(76, 5, 23415082610053, 'Giovanni', 'Collazo Gonzalez', 0, 71900101, '', NULL, NULL),
(77, 6, 23415082610064, 'Juan', 'Cortes Martinez', 0, 71900101, '', NULL, NULL),
(78, 7, 23415082610051, 'Abraham Yair', 'Cruz Cortes', 0, 71900101, '', NULL, NULL),
(79, 8, 23415082610087, 'Emily Jaqueline', 'Delgadillo Salazar', 0, 71900101, '', NULL, NULL),
(80, 9, 23415082610031, 'Leon Michel', 'Flores Villarreal', 0, 71900101, '', NULL, NULL),
(81, 10, 23415082610033, 'Betzy Noemi', 'Garcia Lopez', 0, 71900101, '', NULL, NULL),
(82, 11, 23415082610134, 'Guadalupe', 'Gomez Guerrero', 0, 71900101, '', NULL, NULL),
(83, 12, 23415082610137, 'Ariadna Ximena', 'Gonzalez Martinez', 0, 71900101, '', NULL, NULL),
(84, 13, 23415082610203, 'Angel Gabriel', 'Gutierrez Gutierrez', 0, 71900101, '', NULL, NULL),
(85, 14, 23415082610108, 'Ilse Sarahi', 'Hernandez Gutierrez', 0, 71900101, '', NULL, NULL),
(86, 15, 23415082610055, 'Melany Alejandra', 'Hernandez Hernandez', 0, 71900101, '', NULL, NULL),
(87, 16, 23415082610072, 'Owen Nicolas', 'Hernandez Jimenez', 0, 71900101, '', NULL, NULL),
(88, 17, 23415082610059, 'Metzi Delia', 'Hernandez Martinez', 0, 71900101, '', NULL, NULL),
(89, 18, 23415082610206, 'Karen Geraldine', 'Hernandez Martir', 0, 71900101, '', NULL, NULL),
(90, 19, 23415082610151, 'Ian Alexander', 'Jimenez Lopez', 0, 71900101, '', NULL, NULL),
(91, 20, 23415082610188, 'Iyari Maria', 'Lopez Chavez', 0, 71900101, '', NULL, NULL),
(92, 21, 23415082610012, 'Eddy', 'Luis Aguilar', 0, 71900101, '', NULL, NULL),
(93, 22, 23415082610113, 'Miguel Jacob', 'Maldonado Rodriguez', 0, 71900101, '', NULL, NULL),
(94, 23, 23415082610153, 'Cassandra Guadalupe', 'Nogueda Nuñez', 0, 71900101, '', NULL, NULL),
(95, 24, 23415082610084, 'Ethel Giovanna', 'Ortega Gonzalez', 0, 71900101, '', NULL, NULL),
(96, 25, 23415082610164, 'Emily', 'Ortega Vazquez', 0, 71900101, '', NULL, NULL),
(97, 26, 23415082610200, 'Diego Orlando', 'Reyes Arias', 0, 71900101, '', NULL, NULL),
(98, 27, 23415082610170, 'Romina', 'Rios Quijano', 0, 71900101, '', NULL, NULL),
(99, 28, 23415082610124, 'Leonardo', 'Ruiz Santos', 0, 71900101, '', NULL, NULL),
(100, 29, 23415082610158, 'Zuriel Moises', 'Silva Marquez', 0, 71900101, '', NULL, NULL),
(101, 30, 23415082610182, 'Oscar Ivan', 'Solis Chavez', 0, 71900101, '', NULL, NULL),
(102, 31, 23415082610177, 'Ramon De Jesus', 'Talamantes Castañeda', 0, 71900101, '', NULL, NULL),
(103, 32, 23415082610126, 'Santiago', 'Torres Cano', 0, 71900101, '', NULL, NULL),
(104, 33, 23415082610128, 'Luis Urie', 'Yubi Mendoza', 0, 71900101, '', NULL, NULL),
(106, 1, 23415082610005, 'Angeles Sanchez', 'Emiliano', 0, 87876458, '', NULL, NULL),
(107, 2, 23415082610071, 'Betancourt Perez', 'Jessica Daniela', 0, 87876458, '', NULL, NULL),
(108, 3, 23415082610009, 'Carranco Barreto', 'Rodrigo Ratzel', 0, 87876458, '', NULL, NULL),
(109, 4, 23415082610101, 'Decilagua Espinoza', 'Cristian Michel', 0, 87876458, '', NULL, NULL),
(110, 5, 23415082610022, 'Gachuz Serrano', 'Fernanda', 0, 87876458, '', NULL, NULL),
(111, 6, 23415082610069, 'Galvan Rodriguez', 'Renatto', 0, 87876458, '', NULL, NULL),
(112, 7, 23415082610213, 'Garcia Garcia', 'Maria Fernanda', 0, 87876458, '', NULL, NULL),
(113, 8, 23415082610023, 'Gomez Rodriguez', 'Keren Hallel', 0, 87876458, '', NULL, NULL),
(114, 9, 23415082610019, 'Gonzalez Ayala', 'Emmanuel', 0, 87876458, '', NULL, NULL),
(115, 10, 23415082610049, 'Gonzalez Garcia', 'Mauricio', 0, 87876458, '', NULL, NULL),
(116, 11, 23415082610035, 'Hernandez Gutierrez', 'Monica', 0, 87876458, '', NULL, NULL),
(117, 12, 23415082610073, 'Hernandez Rios', 'Diego Armando', 0, 87876458, '', NULL, NULL),
(118, 13, 23415082610142, 'Huerta Alatorre', 'Valeria Naomi', 0, 87876458, '', NULL, NULL),
(119, 14, 23415082610143, 'Jimenez Morales', 'Luis Israel', 0, 87876458, '', NULL, NULL),
(120, 15, 23415082610185, 'Mandujano Ortega', 'America Atziry', 0, 87876458, '', NULL, NULL),
(121, 16, 23415082610034, 'Martinez Ramirez', 'Mareli Lizbeth', 0, 87876458, '', NULL, NULL),
(122, 17, 23415082610147, 'Mendez Rodriguez', 'Walfre Leonardo', 0, 87876458, '', NULL, NULL),
(123, 18, 23415082610148, 'Mendieta Martinez', 'Gabriela Edith', 0, 87876458, '', NULL, NULL),
(124, 19, 23415082610211, 'Mendoza Contreras', 'Fernando', 0, 87876458, '', NULL, NULL),
(125, 20, 23415082610013, 'Mendoza Huerta', 'Leslie Yanel', 0, 87876458, '', NULL, NULL),
(126, 21, 23415082610115, 'Mendoza Reyes', 'Aranza Junuen', 0, 87876458, '', NULL, NULL),
(127, 22, 23415082610047, 'Mote Velazquez', 'Andrea', 0, 87876458, '', NULL, NULL),
(128, 23, 23415082610163, 'Ortega Gonzalez', 'Pavel Andre', 0, 87876458, '', NULL, NULL),
(129, 24, 23415082610201, 'Peña Gonzalez', 'Yenifer Ximena', 0, 87876458, '', NULL, NULL),
(130, 25, 23415082610117, 'Plascencia Sosa', 'Abril Ariadna', 0, 87876458, '', NULL, NULL),
(131, 26, 23415082610121, 'Reyes Ramirez', 'Angel David', 0, 87876458, '', NULL, NULL),
(132, 27, 23415082610122, 'Rincon Vela', 'Jose Gabriel', 0, 87876458, '', NULL, NULL),
(133, 28, 23415082610123, 'Rios Barrera', 'Axel Ariel', 0, 87876458, '', NULL, NULL),
(134, 29, 23415082610202, 'Robles Duran', 'Tania Michelle', 0, 87876458, '', NULL, NULL),
(135, 30, 23415082610054, 'Romero Chepetla', 'Armando Alain', 0, 87876458, '', NULL, NULL),
(136, 31, 23415082610208, 'Ruiz Garcia', 'Erick Patricio', 0, 87876458, '', NULL, NULL),
(137, 32, 23415082610175, 'Sanchez Galvan', 'Kevin Josue', 0, 87876458, '', NULL, NULL),
(138, 33, 23415082610129, 'Zamora Perez', 'Josue Eduardo', 0, 87876458, '', NULL, NULL),
(139, 34, 23415082610008, 'Zarate Falcon', 'Santiago David', 0, 87876458, '', NULL, NULL),
(176, 1, 23415082610197, 'Bernal Cadenas', 'Jessica Michelle', 0, 55235409, '', NULL, NULL),
(177, 2, 23415082610097, 'Cabadas Valora', 'Emilio', 0, 55235409, '', NULL, NULL),
(178, 3, 23415082610196, 'Chable Ramirez', 'Karla Janay', 0, 55235409, '', NULL, NULL),
(179, 4, 23415082610138, 'Chavez Lopez', 'Ximena', 0, 55235409, '', NULL, NULL),
(180, 5, 23415082610099, 'Cristales Resendis', 'Fatima Regina', 0, 55235409, '', NULL, NULL),
(181, 6, 23415082610018, 'Cruz Chavez', 'Alejandro', 0, 55235409, '', NULL, NULL),
(182, 7, 23415082610045, 'Cruzaley Arguello', 'Sara Isabel', 0, 55235409, '', NULL, NULL),
(183, 8, 23415082610140, 'De La Cruz Claudio', 'Carlos', 0, 55235409, '', NULL, NULL),
(184, 9, 23415082610133, 'Estrada Casas', 'Martin Alejandro', 0, 55235409, '', NULL, NULL),
(185, 10, 23415082610103, 'Fernandez Perez', 'Oscar Yael', 0, 55235409, '', NULL, NULL),
(186, 11, 23415082610052, 'Flores Vega', 'Derek Yeraj', 0, 55235409, '', NULL, NULL),
(187, 12, 23415082610070, 'Gutierrez Gomez', 'Alison', 0, 55235409, '', NULL, NULL),
(188, 13, 23415082610141, 'Hernandez Dominguez', 'Erika Saray', 0, 55235409, '', NULL, NULL),
(189, 14, 23415082610021, 'Hernandez Luna', 'Naomi Daniela', 0, 55235409, '', NULL, NULL),
(190, 15, 23415082610040, 'Hidalgo Cortes', 'Marco Antonio', 0, 55235409, '', NULL, NULL),
(191, 16, 23415082610109, 'Hinojos Merchand', 'Luis Antonio', 0, 55235409, '', NULL, NULL),
(192, 17, 23415082610077, 'Jimenez Casasola', 'Jesus Alejandro', 0, 55235409, '', NULL, NULL),
(193, 18, 23415082610186, 'Linares Ramirez', 'Naomi Yamilet', 0, 55235409, '', NULL, NULL),
(194, 19, 23415082610032, 'Lugo Quintanar', 'Adrian', 0, 55235409, '', NULL, NULL),
(195, 20, 23415082610166, 'Marin Dominguez', 'Fernando', 0, 55235409, '', NULL, NULL),
(196, 21, 23415082610145, 'Medina Guzman', 'Orlando Daniel', 0, 55235409, '', NULL, NULL),
(197, 22, 23415082610116, 'Morales Paramo', 'Karol De Jesus', 0, 55235409, '', NULL, NULL),
(198, 23, 23415082610162, 'Munguia Miralrio', 'Karla Angeles', 0, 55235409, '', NULL, NULL),
(199, 24, 23415082610082, 'Navarrete Monroy', 'Diego', 0, 55235409, '', NULL, NULL),
(200, 25, 23415082610168, 'Reyes Amaro', 'Jose Miguel', 0, 55235409, '', NULL, NULL),
(201, 26, 23415082610086, 'Reyes Arevalo', 'Jessica Harumi', 0, 55235409, '', NULL, NULL),
(202, 27, 23415082610156, 'Rodriguez Gonzalez', 'Luis Aaron', 0, 55235409, '', NULL, NULL),
(203, 28, 23415082610184, 'Rodriguez Torres Cano', 'Shaddai Estefania', 0, 55235409, '', NULL, NULL),
(204, 29, 23415082610157, 'Rosas Coria', 'Ivanka Valentina', 0, 55235409, '', NULL, NULL),
(205, 30, 23415082610125, 'Salgado Ramirez', 'Ximena', 0, 55235409, '', NULL, NULL),
(206, 31, 23415082610060, 'Solis Rodriguez', 'Pablo Samuel', 0, 55235409, '', NULL, NULL),
(207, 32, 23415082610127, 'Tovar Lopez', 'Nanaxhy', 0, 55235409, '', NULL, NULL),
(208, 33, 23415082610089, 'Tovar Ramirez', 'Angelique Yenonybetzy', 0, 55235409, '', NULL, NULL),
(209, 34, 23415082610090, 'Urban Rubio', 'Leonardo Noe', 0, 55235409, '', NULL, NULL),
(210, 35, 23415082610091, 'Vega Romero', 'Irvin', 0, 55235409, '', NULL, NULL),
(211, 36, 23415082610130, 'Zarate Batalla', 'Ricardo', 0, 55235409, '', NULL, NULL),
(212, 1, 23415082610015, 'Abuadili Ruelas', 'Gael Alejandro', 0, 16011634, '', NULL, NULL),
(213, 2, 23415082610061, 'Aguilar Hernandez', 'Andrea', 0, 16011634, '', NULL, NULL),
(214, 3, 23415082610094, 'Alcantara Muñiz', 'Hugo Isaac', 0, 16011634, '', NULL, NULL),
(215, 4, 23415082610191, 'Aldana Alegria', 'Erick Leonardo', 0, 16011634, '', NULL, NULL),
(216, 5, 23415082610028, 'Almaraz Sanchez', 'Chenoa Yaretzi', 0, 16011634, '', NULL, NULL),
(217, 6, 23415082610011, 'Alvarez Sierra', 'Leonardo', 0, 16011634, '', NULL, NULL),
(218, 7, 23415082610037, 'Cruz Juarez', 'Jose Jofiel', 0, 16011634, '', NULL, NULL),
(219, 8, 23415082610102, 'Diaz Paredes', 'Ashley Beth', 0, 16011634, '', NULL, NULL),
(220, 9, 23415082610020, 'Fernandez Gonzalez', 'Luis Fernando', 0, 16011634, '', NULL, NULL),
(221, 10, 23415082610067, 'Flores Escobar', 'Catalina', 0, 16011634, '', NULL, NULL),
(222, 11, 23415082610198, 'Garcia Alvarado', 'Saul', 0, 16011634, '', NULL, NULL),
(223, 12, 23415082610039, 'Garcia Gomez', 'Santiago Jesus', 0, 16011634, '', NULL, NULL),
(224, 13, 23415082610107, 'Gonzalez Jimenez', 'Gilberto', 0, 16011634, '', NULL, NULL),
(225, 14, 23415082610189, 'Hernandez Lopez', 'Jared Joshua', 0, 16011634, '', NULL, NULL),
(226, 15, 23415082610036, 'Herrera Pimentel', 'Said Alejandro', 0, 16011634, '', NULL, NULL),
(227, 16, 23415082610026, 'Ibarra Perez', 'Marco Antonio', 0, 16011634, '', NULL, NULL),
(228, 17, 23415082610024, 'Jaimes Valadez', 'Samantha Daniela', 0, 16011634, '', NULL, NULL),
(229, 18, 23415082610007, 'Lara Marquez', 'Karen Mayrin', 0, 16011634, '', NULL, NULL),
(230, 19, 23415082610041, 'Lira Martinez', 'Alexander', 0, 16011634, '', NULL, NULL),
(231, 20, 23415082610192, 'Marquez Hernandez', 'Berenice', 0, 16011634, '', NULL, NULL),
(232, 21, 23415082610114, 'Medina Rios', 'Mitshell Jael', 0, 16011634, '', NULL, NULL),
(233, 22, 23415082610080, 'Morales Marzuca', 'Sasha Ximena', 0, 16011634, '', NULL, NULL),
(234, 23, 23415082610152, 'Moreno Diego', 'Alan Santiago', 0, 16011634, '', NULL, NULL),
(235, 24, 23415082610120, 'Reyes Barragan', 'Dominic Daniel', 0, 16011634, '', NULL, NULL),
(236, 25, 23415082610169, 'Reynoso Gonzalez', 'Miranda Valeria', 0, 16011634, '', NULL, NULL),
(237, 26, 23415082050239, 'Rico Solorzano', 'Camila', 0, 16011634, '', NULL, NULL),
(238, 27, 23415082610155, 'Rivas Galicia', 'Gael', 0, 16011634, '', NULL, NULL),
(239, 28, 23415082610171, 'Rivera Nuñez', 'Fernanda', 0, 16011634, '', NULL, NULL),
(240, 29, 23415082610050, 'Rodriguez Aguilar', 'Valeria', 0, 16011634, '', NULL, NULL),
(241, 30, 23415082610173, 'Ruiz Esquivel', 'Stephany Dayane', 0, 16011634, '', NULL, NULL),
(242, 31, 23415082610174, 'Saavedra Belmares', 'Aaron', 0, 16011634, '', NULL, NULL),
(243, 32, 23415082610195, 'Toriz Colin', 'Abel Eliel', 0, 16011634, '', NULL, NULL),
(244, 33, 23415082610160, 'Vasquez Muñoz', 'Maria Guadalupe', 0, 16011634, '', NULL, NULL),
(245, 1, 25415082610005, 'Bravo Paredes', 'Danna Aquetzalli', 0, 13908098, 'BAPDXXXXXX', NULL, NULL),
(246, 2, 25415082610017, 'Cervantes Contreras', 'Leslye Yareni', 0, 13908098, 'CECLXXXXXX', NULL, NULL),
(247, 3, 25415082610018, 'Claudio Peña', 'Ximena', 0, 13908098, 'CAPXXXXXXX', NULL, NULL),
(248, 4, 25415082610012, 'Cordova Tapia', 'Saul', 0, 13908098, 'COTSXXXXXX', NULL, NULL),
(249, 5, 25415082610009, 'Espinosa Ruiz', 'Oscar Alexander', 0, 13908098, 'EIROXXXXXX', NULL, NULL),
(250, 6, 25415082610019, 'Flores Suarez', 'Valeria', 0, 13908098, 'FOSVXXXXXX', NULL, NULL),
(251, 7, 25415082610020, 'Garcia Martinez', 'Allison Yareth', 0, 13908098, 'GAMAXXXXXX', NULL, NULL),
(252, 8, 25415082610022, 'Garcia Perez', 'Carlos Adair', 0, 13908098, 'GAPCXXXXXX', NULL, NULL),
(253, 9, 25415082610023, 'Garcia Rodriguez', 'Karol', 0, 13908098, 'GARKXXXXXX', NULL, NULL),
(254, 10, 25415082610024, 'Garcia Zuñiga', 'Ana Karen', 0, 13908098, 'GAZAXXXXXX', NULL, NULL),
(255, 11, 25415082610025, 'Gebhardt Rodriguez', 'Genesis Paola', 0, 13908098, 'GERGXXXXXX', NULL, NULL),
(256, 12, 25415082610026, 'Guevara Navarrete', 'Santiago', 0, 13908098, 'GUNSXXXXXX', NULL, NULL),
(257, 13, 25415082610027, 'Hernandez Campos', 'Juan', 0, 13908098, 'HECJXXXXXX', NULL, NULL),
(258, 14, 25415082610028, 'Huerta Luna', 'Luis Santiago', 0, 13908098, 'HULLXXXXXX', NULL, NULL),
(259, 15, 25415082610029, 'Leyva Picaz', 'Valentina', 0, 13908098, 'LEPVXXXXXX', NULL, NULL),
(260, 16, 25415082610031, 'Linares Hernandez', 'Gael Esau', 0, 13908098, 'LIHGXXXXXX', NULL, NULL),
(261, 17, 25415082610032, 'Lopez De Los Santos', 'Romina Montserrat', 0, 13908098, 'LOSRXXXXXX', NULL, NULL),
(262, 18, 25415082610033, 'Macias Gonzalez', 'Frida Sofia', 0, 13908098, 'MAGFXXXXXX', NULL, NULL),
(263, 19, 25415082610034, 'Martinez Angeles', 'Iker Javier', 0, 13908098, 'MAAIXXXXXX', NULL, NULL),
(264, 20, 25415082610035, 'Martinez Muñoz', 'Mariana Elizabeth', 0, 13908098, 'MAMMXYXXXX', NULL, NULL),
(265, 21, 25415082610036, 'Mendivil Castro', 'Mia Renata', 0, 13908098, 'MECMXXXXXX', NULL, NULL),
(266, 22, 25415082610037, 'Montalvo Vega', 'Danika Yosebeth', 0, 13908098, 'MOVDXXXXXX', NULL, NULL),
(267, 23, 25415082610041, 'Olguin Mata', 'Luis Abraham', 0, 13908098, 'OIMALXXXXX', NULL, NULL),
(268, 24, 25415082610043, 'Ortega Garcia', 'Cinthya Melissa', 0, 13908098, 'OEGCXXXXXX', NULL, NULL),
(269, 25, 25415082610044, 'Palacios Resendiz', 'Andrea Euridice', 0, 13908098, 'PARAXXXXXX', NULL, NULL),
(270, 26, 25415082610008, 'Ramirez Gonzalez', 'Valeria', 0, 13908098, 'RAGVXXXXXX', NULL, NULL),
(271, 27, 25415082610046, 'Rodriguez Ponce', 'Vania Denisse', 0, 13908098, 'ROPVXXXXXX', NULL, NULL),
(272, 28, 25415082610047, 'Sanchez Cuevas', 'Amber Abril', 0, 13908098, 'SACAXXXXXX', NULL, NULL),
(273, 29, 25415082610048, 'Suarez Martinez', 'Jose Antonio', 0, 13908098, 'SUMJXXXXXX', NULL, NULL),
(274, 30, 25415082610016, 'Urrieta Gutierrez', 'Odette Guadalupe', 0, 13908098, 'UIGXXXXXXX', NULL, NULL),
(275, 31, 25415082610049, 'Velazquez Jaramillo', 'Israel Alejandro', 0, 13908098, 'VEJIXXXXXX', NULL, NULL),
(276, 32, 25415082610050, 'Yañez Flores', 'Sebastian', 0, 13908098, 'YAFSXXXXXX', NULL, NULL),
(277, 33, 25410000000000, 'Olmedo Pruneda', 'Alexander', 0, 13908098, 'OEPAXXXXXX', NULL, NULL),
(278, 34, 25410000000000, 'Castillo Romero', 'Dayana Amely', 0, 13908098, 'CARDXXXXXX', NULL, NULL),
(279, 35, 25410000000000, 'Jimenez Hernandez', 'Dylan Octavio', 0, 13908098, 'JIHDXXXXXX', NULL, NULL),
(280, 36, 25410000000000, 'Gachuz Serrano', 'Jimena Del Carmen', 0, 13908098, 'GASJXXXXXX', NULL, NULL),
(281, 37, 25410000000000, 'Gaviña Corona', 'Kenya Maribel', 0, 13908098, 'GACKXXXXXX', NULL, NULL),
(282, 38, 25410000000000, 'Toriz Colin', 'Maria Rebeca', 0, 13908098, 'TOCRXXXXXX', NULL, NULL),
(283, 39, 25410000000000, 'Hernandez Tellez', 'Michelle Samantha', 0, 13908098, 'HETMXXXXXX', NULL, NULL),
(284, 40, 25410000000000, 'Lopez Flores', 'Nahomi Jezabel', 0, 13908098, 'LOFNXXXXXX', NULL, NULL),
(285, 41, 25410000000000, 'Fernandez Cardona', 'Sebastian', 0, 13908098, 'FECSXXXXXX', NULL, NULL),
(286, 1, 25415082610086, 'Arroyo Juarez', 'Berenice Abigail', 0, 64840321, 'AUJBXXXXXX', NULL, NULL),
(287, 2, 25415082610088, 'Bueno', 'Santiago', 0, 64840321, 'BUXSXXXXXX', NULL, NULL),
(288, 3, 25415082610002, 'Camacho Garcia', 'Bruno Joseph', 0, 64840321, 'CAGBXXXXXX', NULL, NULL),
(289, 4, 25415082610090, 'De La Cruz Martinez', 'Naomi Belen', 0, 64840321, 'CUMNXXXXXX', NULL, NULL),
(290, 5, 25415082610092, 'Galindo Preciado', 'Nicolas Alberto', 0, 64840321, 'GAPNXXXXXX', NULL, NULL),
(291, 6, 25415082610093, 'Granados Medina', 'Carlos Manuel', 0, 64840321, 'GAMCXXXXXX', NULL, NULL),
(292, 7, 25415082610094, 'Guzman Fuentes', 'Naya Ashanti', 0, 64840321, 'GUFNXXXXXX', NULL, NULL),
(293, 8, 25415082610095, 'Hernandez Guerrero', 'Eduardo Javier', 0, 64840321, 'HEGEXXXXXX', NULL, NULL),
(294, 9, 25415082610096, 'Hernandez Vazquez', 'Maria Luisa', 0, 64840321, 'HEVMXXXXXX', NULL, NULL),
(295, 10, 25415082610097, 'Ibarra Gamez', 'Gael Alfonso', 0, 64840321, 'IAGGXXXXXX', NULL, NULL),
(296, 11, 25415082610099, 'Kraulles Reyes', 'Jaime', 0, 64840321, 'KRXJXXXXXX', NULL, NULL),
(297, 12, 25415082610100, 'Linares Iturbes', 'Angel Yosiel', 0, 64840321, 'LIIAXXXXXX', NULL, NULL),
(298, 13, 25415082610101, 'Lopez Hernandez', 'Arandi', 0, 64840321, 'LOHAXXXXXX', NULL, NULL),
(299, 14, 25415082610102, 'Lopez Vega', 'Luis Eduardo', 0, 64840321, 'LOVLXXXXXX', NULL, NULL),
(300, 15, 25415082610103, 'Madrid Botello', 'Ivanna Valeria', 0, 64840321, 'MABIXXXXXX', NULL, NULL),
(301, 16, 25415082610104, 'Martinez Morales', 'Santiago', 0, 64840321, 'MAMSXXXXXX', NULL, NULL),
(302, 17, 25415082610105, 'Mayorga Jimenez', 'Angel Uriel', 0, 64840321, 'MAJAXXXXXX', NULL, NULL),
(303, 18, 25415082610106, 'Merlos Acevedo', 'Valeria Daly', 0, 64840321, 'MEAVXXXXXX', NULL, NULL),
(304, 19, 25415082610107, 'Morales Martinez', 'Maria Fernanda', 0, 64840321, 'MOMMXXXXXX', NULL, NULL),
(305, 20, 25415082610108, 'Munguia Miralrio', 'Esteban Leonel', 0, 64840321, 'MUMEXXXXXX', NULL, NULL),
(306, 21, 25415082610109, 'Muñoz Garcia', 'Yosgart Neftali', 0, 64840321, 'MUGYXXXXXX', NULL, NULL),
(307, 22, 25415082610110, 'Paredez Olvera', 'Norma Gabriela', 0, 64840321, 'PAONXXXXXX', NULL, NULL),
(308, 23, 25415082610111, 'Pereira Garcia', 'Karen Yuritzi', 0, 64840321, 'PEGKXXXXXX', NULL, NULL),
(309, 24, 25415082610112, 'Perez Chavez', 'Aldo', 0, 64840321, 'PECAXXXXXX', NULL, NULL),
(310, 25, 25415082610113, 'Pulido Mendez', 'Salvador Hiromi', 0, 64840321, 'PUMSXXXXXX', NULL, NULL),
(311, 26, 25415082610114, 'Quiñones Rodriguez', 'Karla', 0, 64840321, 'QURKXXXXXX', NULL, NULL),
(312, 27, 25415082610115, 'Reyes Onofre', 'Julio Cesar', 0, 64840321, 'REOJXXXXXX', NULL, NULL),
(313, 28, 25415082610116, 'Rodriguez Razo', 'Pamela Dannae', 0, 64840321, 'RORPXXXXXX', NULL, NULL),
(314, 29, 25415082610117, 'Romero Gomez', 'Rochely Leilani', 0, 64840321, 'ROGRXXXXXX', NULL, NULL),
(315, 30, 25415082610118, 'Ruiz Rojas', 'Rodrigo', 0, 64840321, 'RURRXXXXXX', NULL, NULL),
(316, 31, 25415082610119, 'Sanchez Diaz', 'Antonio Eduardo', 0, 64840321, 'SADAXXXXXX', NULL, NULL),
(317, 32, 25415082610120, 'Torres Alfaro', 'Esteban', 0, 64840321, 'TOAEXXXXXX', NULL, NULL),
(318, 33, 25415082610121, 'Velasco Martinez', 'Yaretzy', 0, 64840321, 'VEMYXXXXXX', NULL, NULL),
(319, 34, 25415082610122, 'Vieyra Contreras', 'Valeria', 0, 64840321, 'VICVXXXXXX', NULL, NULL),
(320, 35, 25415082610123, 'Xu Hurtado', 'Xiao Lian Camila', 0, 64840321, 'XUHXXXXXXX', NULL, NULL),
(321, 36, 25415082610001, 'Zuñiga Lopez', 'Paola', 0, 64840321, 'ZULPXXXXXX', NULL, NULL),
(322, 37, 25000000000000, 'Curiel Miranda', 'Abigail', 0, 64840321, 'CUMAXXXXXX', NULL, NULL),
(323, 38, 25000000000000, 'Fragoso Santiago', 'Dylan Dael', 0, 64840321, 'FASDXXXXXX', NULL, NULL),
(324, 39, 25000000000000, 'Garcia Ramos', 'Julisa', 0, 64840321, 'GARJXXXXXX', NULL, NULL),
(325, 40, 25000000000000, 'Gonzalez Lope', 'Jorge Antonio', 0, 64840321, 'GOLJXXXXXX', NULL, NULL),
(326, 41, 25000000000000, 'Gonzalez Ordaz', 'Leonardo', 0, 64840321, 'GOOLXXXXXX', NULL, NULL),
(327, 1, 25415082610127, 'Balcazar Jaranillo', 'Esmeralda', 0, 10236290, 'BAJEXXXXXX', NULL, NULL),
(328, 2, 25415082610128, 'Cabrera Garrido', 'Jazmin', 0, 10236290, 'CAGJXXXXXX', NULL, NULL),
(329, 3, 25415082610129, 'Calderon Gamiño', 'Jonathan', 0, 10236290, 'CAGJXXXXXX', NULL, NULL),
(330, 4, 25415082610130, 'Castañeda Bolaños', 'Carolina', 0, 10236290, 'CABCXXXXXX', NULL, NULL),
(331, 5, 25415082610131, 'Chavez Novia', 'Fatima Geraldine', 0, 10236290, 'CHNFXXXXXX', NULL, NULL),
(332, 6, 25415082610132, 'Cruz Dominguez', 'Edgar Alfonso', 0, 10236290, 'CUDEXXXXXX', NULL, NULL),
(333, 7, 25415082610133, 'Escalona Perez', 'Sofia Geraldine', 0, 10236290, 'EAPSXXXXXX', NULL, NULL),
(334, 8, 25415082610014, 'Fernandez Perez', 'Didier Ruben', 0, 10236290, 'FEPDXXXXXX', NULL, NULL),
(335, 9, 25415082610134, 'Flores Hernandez', 'Emilio', 0, 10236290, 'FLHEXXXXXX', NULL, NULL),
(336, 10, 25415082610136, 'Gonzalez Gonzalez', 'Axel Daniel', 0, 10236290, 'GOGAXXXXXX', NULL, NULL),
(337, 11, 25415082610137, 'Gonzalez Islas', 'Josue Emanuel', 0, 10236290, 'GOIJXXXXXX', NULL, NULL),
(338, 12, 25415082610138, 'Guadalupe Garcia', 'Diana Paola', 0, 10236290, 'GAGDXXXXXX', NULL, NULL),
(339, 13, 25415082610139, 'Hernandez Pozos', 'Fernanda', 0, 10236290, 'HEPFXXXXXX', NULL, NULL),
(340, 14, 25415082610124, 'Hernandez Rojas', 'Gabino Uriel', 0, 10236290, 'HERGXXXXXX', NULL, NULL),
(341, 15, 25415082610140, 'Hurtado Vazquez', 'Yael Zaid', 0, 10236290, 'HUYYXXXXXX', NULL, NULL),
(342, 16, 25415082610141, 'Jauregui Medina', 'Aeryn Danae', 0, 10236290, 'JAMAXXXXXX', NULL, NULL),
(343, 17, 25415082610142, 'Larios Lira', 'Emilio Santiago', 0, 10236290, 'LALEXXXXXX', NULL, NULL),
(344, 18, 25415082610013, 'Lopez Hernandez', 'Naomi Zoe', 0, 10236290, 'LOHNXXXXXX', NULL, NULL),
(345, 19, 25415082610143, 'Martinez Caballero', 'Erick Israel', 0, 10236290, 'MACEXXXXXX', NULL, NULL),
(346, 20, 25415082610144, 'Martinez Lozano', 'Daniela', 0, 10236290, 'MALDXXXXXX', NULL, NULL),
(347, 21, 25415082610145, 'Martinez Roldan', 'Jonatan Jiovani', 0, 10236290, 'MARJXXXXXX', NULL, NULL),
(348, 22, 25415082610147, 'Moreno Antonio', 'Diego', 0, 10236290, 'MOADXXXXXX', NULL, NULL),
(349, 23, 25415082610148, 'Navarro Villagomez', 'Nedjma Xanat', 0, 10236290, 'NAVNXXXXXX', NULL, NULL),
(350, 24, 25415082610003, 'Patricio Hernandez', 'Brandon Josue', 0, 10236290, 'PAHBXXXXXX', NULL, NULL),
(351, 25, 25415082610149, 'Perez Vargas', 'Hugo Sebastian', 0, 10236290, 'PEVHXXXXXX', NULL, NULL),
(352, 26, 25415082610152, 'Quiñonez Payan', 'Alexis Johansen', 0, 10236290, 'QUPAXXXXXX', NULL, NULL),
(353, 27, 25415082610151, 'Quintana Roldan', 'Mateo', 0, 10236290, 'QURMXXXXXX', NULL, NULL),
(354, 28, 25415082610153, 'Rodriguez Meza', 'Renata Amellaly', 0, 10236290, 'ROMRXXXXXX', NULL, NULL),
(355, 29, 25415082610154, 'Salazar Osuna', 'Agustin', 0, 10236290, 'SAOAXXXXXX', NULL, NULL),
(356, 30, 25415082610155, 'Sanchez Hernandez', 'Yazmin', 0, 10236290, 'SAHYXXXXXX', NULL, NULL),
(357, 31, 25415082610156, 'Sanchez Trejo', 'David', 0, 10236290, 'SATDXXXXXX', NULL, NULL),
(358, 32, 25415082610004, 'Suarez Ostos', 'Elifelet Cristina', 0, 10236290, 'SUOEXXXXXX', NULL, NULL),
(359, 33, 25415082610158, 'Valderrabano Juarez', 'Samara Guadalupe', 0, 10236290, 'VAJSXXXXXX', NULL, NULL),
(360, 34, 25415082610159, 'Venegas Valladares', 'Juan Santiago', 0, 10236290, 'VEVJXXXXXX', NULL, NULL),
(361, 35, 25000000000000, 'Ibarra Hernandez', 'Miguel Angel', 0, 10236290, 'IAHMXXXXXX', NULL, NULL),
(362, 36, 25000000000000, 'Lopez Rangel', 'Luis Enrique', 0, 10236290, 'LORLXXXXXX', NULL, NULL),
(363, 37, 25000000000000, 'Morales Torres', 'Manuel Enrique', 0, 10236290, 'MOTMXXXXXX', NULL, NULL),
(364, 38, 25000000000000, 'Pedroza Morales', 'Andree Santiago', 0, 10236290, 'PEMAXXXXXX', NULL, NULL),
(365, 39, 25000000000000, 'Pérez Cervantes', 'Regina Dánae', 0, 10236290, 'PECRXXXXXX', NULL, NULL),
(366, 40, 25000000000000, 'Rivero Tovar', 'Yael', 0, 10236290, 'RITYXXXXXX', NULL, NULL),
(367, 41, 25000000000000, 'Silva Franco', 'Ximena Aydee', 0, 10236290, 'SIFXXXXXXX', NULL, NULL),
(368, 1, 25415082610160, 'Alcantara Sanchez', 'Abigail', 0, 21768545, 'AASAXXXXXX', NULL, NULL),
(369, 2, 25415082610161, 'Blasi Franco', 'Jairo', 0, 21768545, 'BLFJXXXXXX', NULL, NULL),
(370, 3, 25415082610162, 'Bucio Gutierrez', 'Priscila Zurith', 0, 21768545, 'BUGPXXXXXX', NULL, NULL),
(371, 4, 25415082610164, 'Carrasco Carmona', 'Jaqueline', 0, 21768545, 'CACJXXXXXX', NULL, NULL),
(372, 5, 25415082610165, 'Castillo Salgado', 'Daniela Griselda', 0, 21768545, 'CASDXXXXXX', NULL, NULL),
(373, 6, 25415082610166, 'Copado Choreño', 'Melanie Keyclin', 0, 21768545, 'COCMXXXXXX', NULL, NULL),
(374, 7, 25415082610167, 'Escutia Gomez', 'Alejandro', 0, 21768545, 'ESGAXXXXXX', NULL, NULL),
(375, 8, 25415082610040, 'Espinoza Valencia', 'Leany', 0, 21768545, 'ESVLXXXXXX', NULL, NULL),
(376, 9, 25415082610168, 'Flores Escobar', 'Carlos Alberto', 0, 21768545, 'FLECXXXXXX', NULL, NULL),
(377, 10, 25415082610169, 'Gomez Hernandez', 'Gloria Lorena', 0, 21768545, 'GOHGXXXXXX', NULL, NULL),
(378, 11, 25415082610170, 'Gomez Jaca', 'Rodrigo', 0, 21768545, 'GOJRXXXXXX', NULL, NULL),
(379, 12, 25415082610171, 'Gonzalez Hernandez', 'Edgar', 0, 21768545, 'GOHEXXXXXX', NULL, NULL),
(380, 13, 25415082610011, 'Hernandez Florentino', 'Kevin Orlando', 0, 21768545, 'HEFKXXXXXX', NULL, NULL),
(381, 14, 25415082610173, 'Hernandez Vazquez', 'Delia', 0, 21768545, 'HEVDXXXXXX', NULL, NULL),
(382, 15, 25415082610175, 'Jimenez Avila', 'Oziel', 0, 21768545, 'JIAOXXXXXX', NULL, NULL),
(383, 16, 25415082610176, 'Lazaro Javier', 'Jose Armando', 0, 21768545, 'LAJJXXXXXX', NULL, NULL),
(384, 17, 25415082610177, 'Lezama Aguilar', 'Isabel', 0, 21768545, 'LEAIXXXXXX', NULL, NULL),
(385, 18, 25415082610178, 'Martinez Alvarez', 'Mia Yaretzi', 0, 21768545, 'MAAMXXXXXX', NULL, NULL),
(386, 19, 25415082610180, 'Martinez Rivera', 'Axel Uriel', 0, 21768545, 'MARAXXXXXX', NULL, NULL),
(387, 20, 25415082610181, 'Mendoza Cassal', 'Jesus Arturo', 0, 21768545, 'MECJXXXXXX', NULL, NULL),
(388, 21, 25415082610182, 'Montaño Contreras', 'Laksmi Mary Jose', 0, 21768545, 'MOCLXXXXXX', NULL, NULL),
(389, 22, 25415082610184, 'Parra Solares', 'Ailin Itzel', 0, 21768545, 'PASAXXXXXX', NULL, NULL),
(390, 23, 25415082610185, 'Perez Tovar', 'Diego Josue', 0, 21768545, 'PETDXXXXXX', NULL, NULL),
(391, 24, 25415082610186, 'Prieto Espinosa', 'Axel Dario', 0, 21768545, 'PREAXXXXXX', NULL, NULL),
(392, 25, 25415082610187, 'Quintero Marmol Gamboa', 'Alfonso', 0, 21768545, 'QUMAXAXXXX', NULL, NULL),
(393, 26, 25415082610188, 'Regalado Silva', 'Alan Enrique', 0, 21768545, 'RESAXXXXXX', NULL, NULL),
(394, 27, 25415082610189, 'Rodriguez Garcia', 'Anahi Samara', 0, 21768545, 'ROGAXXXXXX', NULL, NULL),
(395, 28, 25415082610006, 'Rojas Chamorro', 'Ariadna Montserrat', 0, 21768545, 'ROCAXXXXXX', NULL, NULL),
(396, 29, 25415082610021, 'Saldivar Cortes', 'Ricardo', 0, 21768545, 'SACRXXXXXX', NULL, NULL),
(397, 30, 25415082610190, 'Silva Campos', 'Luis Gabriel', 0, 21768545, 'SICLXXXXXX', NULL, NULL),
(398, 31, 25415082610191, 'Suarez Diaz', 'Natalia', 0, 21768545, 'SUDNXXXXXX', NULL, NULL),
(399, 32, 25415082610192, 'Tinoco Chavez', 'Antonio', 0, 21768545, 'TICAXXXXXX', NULL, NULL),
(400, 33, 25415082610193, 'Ugalde Reyes', 'Yolanda Esmeralda', 0, 21768545, 'UGRYXXXXXX', NULL, NULL),
(401, 34, 25415082610194, 'Vasquez Santiago', 'Maricarmen', 0, 21768545, 'VASMXXXXXX', NULL, NULL),
(402, 35, 25415082610195, 'Vidales Simon', 'Jatziry Montserrat', 0, 21768545, 'VISJXXXXXX', NULL, NULL),
(403, 36, 25000000000000, 'Luna Lopez', 'Dana Victoria', 0, 21768545, 'LULDXXXXXX', NULL, NULL),
(404, 37, 25000000000000, 'Sosa Majarrez', 'Osdana Atzin', 0, 21768545, 'SOMOXXXXXX', NULL, NULL),
(405, 38, 25000000000000, 'Texco Santiago', 'Giovany', 0, 21768545, 'TESGXXXXXX', NULL, NULL),
(406, 39, 25000000000000, 'Torres Leon', 'Erick Adair', 0, 21768545, 'TOLEXXXXXX', NULL, NULL),
(407, 40, 25000000000000, 'Vela Cardona', 'Bryan Alexys', 0, 21768545, 'VECBXXXXXX', NULL, NULL),
(408, 41, 25000000000000, 'Vidales Simon', 'Brian Alejandro', 0, 21768545, 'VISBXXXXXX', NULL, NULL),
(409, 1, 24415082610170, 'Abarca Robles', 'Claudia Michelle', 0, 95102509, 'AARCXXXXXX', NULL, NULL),
(410, 2, 24415082610195, 'Alvarado Morales', 'Jose Ramon', 0, 95102509, 'AMAJXXXXXX', NULL, NULL),
(411, 3, 24415082610183, 'Balderas Navarrete', 'Paola', 0, 95102509, 'BANPXXXXXX', NULL, NULL),
(412, 4, 24415082610144, 'Basurto Bon', 'Alfredo', 0, 95102509, 'BABAXXXXXX', NULL, NULL),
(413, 5, 24415082610018, 'Buitron Pallares', 'Maria Elisa', 0, 95102509, 'BUPMXXXXXX', NULL, NULL),
(414, 6, 24415082610200, 'Chavez Urrutia', 'Angel Surem', 0, 95102509, 'CUAAXXXXXX', NULL, NULL),
(415, 7, 24415082610119, 'Cortes Reyes', 'Victoria Estefania', 0, 95102509, 'CORVXXXXXX', NULL, NULL),
(416, 8, 24415082610185, 'Crisostomo Jimenez', 'Mateo', 0, 95102509, 'CJMXXXXXXX', NULL, NULL),
(417, 9, 24415082610090, 'Cruz Rodriguez', 'Victor Cesar', 0, 95102509, 'CURVXXXXXX', NULL, NULL),
(418, 10, 24415082610011, 'Dolores Penibal', 'Mikel Sebastian', 0, 95102509, 'DOPMXXXXXX', NULL, NULL),
(419, 11, 24415082610125, 'Fernandez Longino', 'Santiago', 0, 95102509, 'FELSXXXXXX', NULL, NULL),
(420, 12, 24415082610184, 'Fortunato Hernandez', 'Ana Paola', 0, 95102509, 'FOHAXXXXXX', NULL, NULL),
(421, 13, 24415082610001, 'Garduño Piña', 'Yamileth', 0, 95102509, 'GAPYXXXXXX', NULL, NULL),
(422, 14, 23415082610136, 'Gomez Santander', 'Lilian', 0, 95102509, 'GASLXXXXXX', NULL, NULL),
(423, 15, 24415082610149, 'Gonzalez Gallegos', 'Joseph Adair', 0, 95102509, 'GAGJXXXXXX', NULL, NULL),
(424, 16, 24415082610021, 'Ham Garcia', 'Ashley', 0, 95102509, 'HAGAXAXXXX', NULL, NULL),
(425, 17, 24415082610022, 'Hernandez Cruz', 'Lot Jafeth', 0, 95102509, 'HECLXXXXXX', NULL, NULL),
(426, 18, 24415082610057, 'Hernandez Perez', 'Evelyn Shaddai', 0, 95102509, 'HEPEXXXXXX', NULL, NULL),
(427, 19, 24415082610110, 'Hernandez Sanchez', 'Zayra Yamilet', 0, 95102509, 'HESZXXXXXX', NULL, NULL),
(428, 20, 24415082610174, 'Lorea Ayala', 'Christian Andre', 0, 95102509, 'LOACXXXXXX', NULL, NULL),
(429, 21, 24415082610137, 'Marquez Mejia', 'Alejandra', 0, 95102509, 'MAMAXXXXXX', NULL, NULL),
(430, 22, 24415082610111, 'Mondragon Obispo', 'Edgar', 0, 95102509, 'MOOEXXXXXX', NULL, NULL),
(431, 23, 24415082610031, 'Moreno Garcia', 'Berenice', 0, 95102509, 'MOGBXXXXXX', NULL, NULL),
(432, 24, 24415082610104, 'Navarrete Perez', 'Roberto Carlos', 0, 95102509, 'NAPRXXXXXX', NULL, NULL),
(433, 25, 24415082610107, 'Noriega Ortega', 'Jonathan Kalel', 0, 95102509, 'NOOJXXXXXX', NULL, NULL),
(434, 26, 24415082610076, 'Nuño Martinez', 'Victor Hugo', 0, 95102509, 'NUMVXXXXXX', NULL, NULL),
(435, 27, 24415082610192, 'Ortiz Gutierrez', 'Jesus Alejandro', 0, 95102509, 'ORGJXXXXXX', NULL, NULL),
(436, 28, 24415082610161, 'Ortiz Rueda', 'Victoria', 0, 95102509, 'ORUVXXXXXX', NULL, NULL),
(437, 29, 24415082050216, 'Ortiz Rueda', 'Victoria', 0, 95102509, 'ORUVXXXXXX', NULL, NULL),
(438, 30, 24415082610044, 'Rangel Gutierrez', 'Abril Rocio', 0, 95102509, 'RAGAXXXXXX', NULL, NULL),
(439, 31, 24415082610007, 'Rodriguez Velazquez', 'Cecia Renata', 0, 95102509, 'ROVCXXXXXX', NULL, NULL),
(440, 32, 24415082610121, 'Romero Ely', 'Annie', 0, 95102509, 'REAXXXXXXX', NULL, NULL),
(441, 33, 24415082610014, 'Sanchez Hernandez', 'Vania Jazmin', 0, 95102509, 'SAHVXXXXXX', NULL, NULL),
(442, 34, 24415082610118, 'Soledad Tovar', 'Eitan Daniel', 0, 95102509, 'SOTEXXXXXX', NULL, NULL),
(443, 35, 24415082610073, 'Solis Briseño', 'Angel Geovanni', 0, 95102509, 'SOBAXXXXXX', NULL, NULL),
(444, 36, 24415082610016, 'Urban Rodriguez', 'Yeoryeth Margarita', 0, 95102509, 'URRYXXXXXX', NULL, NULL),
(445, 37, 24415082610188, 'Valdez Soto', 'Danahe Giselle', 0, 95102509, 'VASDXXXXXX', NULL, NULL),
(446, 38, 24415082610074, 'Zavala Escobar', 'Valentine', 0, 95102509, 'ZAEVXXXXXX', NULL, NULL);

--
-- Disparadores `alumno`
--
DELIMITER $$
CREATE TRIGGER `trg_alumno_delete` BEFORE DELETE ON `alumno` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, id_admin, admin_nombre)
    VALUES (
        'alumno',
        OLD.id_alumno,
        'DELETE',
        CONCAT(
            'nombre=', OLD.nombre,
            ', apellidos=', OLD.apellidos,
            ', matricula=', OLD.matricula
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_alumno_insert` AFTER INSERT ON `alumno` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_despues, id_admin, admin_nombre)
    VALUES (
        'alumno',
        NEW.id_alumno,
        'INSERT',
        CONCAT(
            'nombre=', NEW.nombre,
            ', apellidos=', NEW.apellidos,
            ', matricula=', NEW.matricula,
            ', grupo=', NEW.id_grupo
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_alumno_update` AFTER UPDATE ON `alumno` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, datos_despues, id_admin, admin_nombre)
    VALUES (
        'alumno',
        OLD.id_alumno,
        'UPDATE',
        CONCAT(
            'nombre=', OLD.nombre,
            ', apellidos=', OLD.apellidos,
            ', grupo=', OLD.id_grupo
        ),
        CONCAT(
            'nombre=', NEW.nombre,
            ', apellidos=', NEW.apellidos,
            ', grupo=', NEW.id_grupo
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('Justificante','Ausente','Retardo') NOT NULL,
  `id_alumno` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencia`
--

INSERT INTO `asistencia` (`id_asistencia`, `fecha`, `estado`, `id_alumno`, `id_grupo`, `id_materia`) VALUES
(7, '2026-01-20', 'Ausente', 21, 38546385, 3),
(8, '2026-01-20', 'Ausente', 21, 38546385, 1),
(9, '2026-01-14', 'Ausente', 25, 38546385, 1),
(10, '2026-01-16', 'Ausente', 25, 38546385, 1),
(11, '2026-01-07', 'Justificante', 21, 38546385, 3),
(12, '2026-01-08', 'Ausente', 21, 38546385, 3),
(13, '2026-01-13', 'Ausente', 21, 38546385, 3),
(14, '2026-01-12', 'Ausente', 21, 38546385, 3),
(15, '2026-01-11', 'Retardo', 21, 38546385, 3),
(16, '2026-01-01', 'Ausente', 21, 38546385, 3);

--
-- Disparadores `asistencia`
--
DELIMITER $$
CREATE TRIGGER `trg_asistencia_delete` BEFORE DELETE ON `asistencia` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, id_admin, admin_nombre)
    VALUES (
        'asistencia',
        OLD.id_asistencia,
        'DELETE',
        CONCAT(
            'fecha=', OLD.fecha,
            ', estado=', OLD.estado,
            ', alumno=', OLD.id_alumno
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_asistencia_insert` AFTER INSERT ON `asistencia` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_despues, id_admin, admin_nombre)
    VALUES (
        'asistencia',
        NEW.id_asistencia,
        'INSERT',
        CONCAT(
            'fecha=', NEW.fecha,
            ', estado=', NEW.estado,
            ', alumno=', NEW.id_alumno
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_asistencia_update` AFTER UPDATE ON `asistencia` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, datos_despues, id_admin, admin_nombre)
    VALUES (
        'asistencia',
        OLD.id_asistencia,
        'UPDATE',
        CONCAT('estado=', OLD.estado),
        CONCAT('estado=', NEW.estado),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_diaria`
--

CREATE TABLE `asistencia_diaria` (
  `id_asistencia_diaria` int(11) NOT NULL,
  `id_alumno` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT curtime(),
  `estado` enum('Presente','Tardío') DEFAULT 'Presente',
  `dispositivo` varchar(100) DEFAULT 'QR Scanner',
  `observaciones` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_diaria_logs`
--

CREATE TABLE `asistencia_diaria_logs` (
  `id_log` int(11) NOT NULL,
  `id_alumno` int(11) NOT NULL,
  `fecha_intento` date NOT NULL,
  `hora_intento` time NOT NULL,
  `resultado` enum('Éxito','Duplicado','Error') DEFAULT 'Éxito',
  `mensaje` text DEFAULT NULL,
  `fecha_log` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL,
  `tabla_afectada` varchar(50) NOT NULL,
  `id_registro` int(11) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `datos_antes` text DEFAULT NULL,
  `datos_despues` text DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `admin_nombre` varchar(100) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id_auditoria`, `tabla_afectada`, `id_registro`, `accion`, `datos_antes`, `datos_despues`, `id_admin`, `admin_nombre`, `fecha`) VALUES
(1, 'asistencia', 2, 'INSERT', NULL, 'fecha=2026-01-19, estado=Ausente, alumno=18', NULL, NULL, '2026-01-19 22:54:00'),
(2, 'asistencia', 3, 'INSERT', NULL, 'fecha=2026-01-19, estado=Ausente, alumno=19', NULL, NULL, '2026-01-19 22:54:15'),
(3, 'asistencia', 4, 'INSERT', NULL, 'fecha=2026-01-19, estado=Ausente, alumno=20', NULL, NULL, '2026-01-19 22:54:18'),
(4, 'asistencia', 2, 'DELETE', 'fecha=2026-01-19, estado=Ausente, alumno=18', NULL, NULL, NULL, '2026-01-19 22:54:32'),
(5, 'asistencia', 3, 'DELETE', 'fecha=2026-01-19, estado=Ausente, alumno=19', NULL, NULL, NULL, '2026-01-19 22:54:33'),
(6, 'asistencia', 4, 'DELETE', 'fecha=2026-01-19, estado=Ausente, alumno=20', NULL, NULL, NULL, '2026-01-19 22:54:33'),
(7, 'asistencia', 5, 'INSERT', NULL, 'fecha=2026-01-19, estado=Ausente, alumno=20', NULL, NULL, '2026-01-19 23:11:26'),
(8, 'asistencia', 5, 'DELETE', 'fecha=2026-01-19, estado=Ausente, alumno=20', NULL, NULL, NULL, '2026-01-19 23:12:07'),
(9, 'grupo', 98118006, 'INSERT', NULL, 'nombre=213321, tutor=1221', NULL, NULL, '2026-01-20 00:24:10'),
(10, 'grupo', 98118006, 'UPDATE', 'nombre=213321, tutor=1221', 'nombre=213321, tutor=1221', NULL, NULL, '2026-01-20 00:24:22'),
(11, 'grupo', 13908098, 'UPDATE', 'nombre=101, tutor=nose', 'nombre=101, tutor=nose1', NULL, NULL, '2026-01-20 00:28:00'),
(12, 'grupo', 13908098, 'UPDATE', 'nombre=101, tutor=nose1', 'nombre=101, tutor=nose', NULL, NULL, '2026-01-20 00:29:27'),
(13, 'materias', 26, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 00:42:42'),
(14, 'grupo', 64840321, 'UPDATE', 'nombre=102, tutor=nose', 'nombre=102, tutor=nose', NULL, NULL, '2026-01-20 00:48:43'),
(15, 'grupo', 10236290, 'UPDATE', 'nombre=103, tutor=nose', 'nombre=103, tutor=nose', NULL, NULL, '2026-01-20 00:48:51'),
(16, 'grupo', 21768545, 'UPDATE', 'nombre=104, tutor=nose', 'nombre=104, tutor=nose', NULL, NULL, '2026-01-20 00:48:55'),
(17, 'grupo', 89421652, 'UPDATE', 'nombre=105, tutor=nose', 'nombre=105, tutor=nose', NULL, NULL, '2026-01-20 00:49:04'),
(18, 'grupo', 98118006, 'DELETE', 'nombre=213321, tutor=1221', NULL, NULL, NULL, '2026-01-20 00:49:10'),
(19, 'grupo', 49988421, 'UPDATE', 'nombre=301, tutor=nose', 'nombre=301, tutor=nose', NULL, NULL, '2026-01-20 00:52:06'),
(20, 'grupo', 23498772, 'UPDATE', 'nombre=302, tutor=Quien sabe', 'nombre=302, tutor=Quien sabe', NULL, NULL, '2026-01-20 00:52:16'),
(21, 'grupo', 95102509, 'UPDATE', 'nombre=303, tutor=nose', 'nombre=303, tutor=nose', NULL, NULL, '2026-01-20 00:52:21'),
(22, 'grupo', 79757662, 'UPDATE', 'nombre=304, tutor=nose', 'nombre=304, tutor=nose', NULL, NULL, '2026-01-20 00:52:29'),
(23, 'grupo', 99357180, 'UPDATE', 'nombre=305, tutor=Daniel Garcia Salinas', 'nombre=305, tutor=Daniel Garcia Salinas', NULL, NULL, '2026-01-20 00:52:35'),
(24, 'grupo', 38546385, 'UPDATE', 'nombre=501, tutor=Mauricio Sanchez Reyes', 'nombre=501, tutor=Mauricio Sanchez Reyes', NULL, NULL, '2026-01-20 00:52:42'),
(25, 'grupo', 87876458, 'UPDATE', 'nombre=502, tutor=Tarsisio Nava Arzaluz', 'nombre=502, tutor=Tarsisio Nava Arzaluz', NULL, NULL, '2026-01-20 00:52:49'),
(26, 'grupo', 55235409, 'UPDATE', 'nombre=503, tutor=Yadira Guadalupe Garcia Ramos', 'nombre=503, tutor=Yadira Guadalupe Garcia Ramos', NULL, NULL, '2026-01-20 00:52:55'),
(27, 'grupo', 71900101, 'UPDATE', 'nombre=504, tutor=Rene Lorea Ayala, Marian Elizabeth Pontigo Suarez', 'nombre=504, tutor=Rene Lorea Ayala, Marian Elizabeth Pontigo Suarez', NULL, NULL, '2026-01-20 00:53:02'),
(28, 'grupo', 16011634, 'UPDATE', 'nombre=505, tutor=nose', 'nombre=505, tutor=nose', NULL, NULL, '2026-01-20 00:53:07'),
(29, 'grupo', 59995390, 'INSERT', NULL, 'nombre=201, tutor=nose', NULL, NULL, '2026-01-20 00:53:49'),
(30, 'grupo', 73049269, 'INSERT', NULL, 'nombre=202, tutor=nose', NULL, NULL, '2026-01-20 00:53:59'),
(31, 'grupo', 53059442, 'INSERT', NULL, 'nombre=203, tutor=nose', NULL, NULL, '2026-01-20 00:54:05'),
(32, 'grupo', 10709410, 'INSERT', NULL, 'nombre=204, tutor=nose', NULL, NULL, '2026-01-20 00:54:12'),
(33, 'grupo', 10586358, 'INSERT', NULL, 'nombre=205, tutor=nose', NULL, NULL, '2026-01-20 00:54:20'),
(34, 'grupo', 54972252, 'INSERT', NULL, 'nombre=401, tutor=nose', NULL, NULL, '2026-01-20 00:54:49'),
(35, 'grupo', 96660927, 'INSERT', NULL, 'nombre=402, tutor=nose', NULL, NULL, '2026-01-20 00:54:57'),
(36, 'grupo', 79832944, 'INSERT', NULL, 'nombre=403, tutor=nose', NULL, NULL, '2026-01-20 00:55:04'),
(37, 'grupo', 75279400, 'INSERT', NULL, 'nombre=404, tutor=nose', NULL, NULL, '2026-01-20 00:55:10'),
(38, 'grupo', 81259732, 'INSERT', NULL, 'nombre=405, tutor=nose', NULL, NULL, '2026-01-20 00:55:17'),
(39, 'grupo', 58233444, 'INSERT', NULL, 'nombre=601, tutor=nose', NULL, NULL, '2026-01-20 00:55:24'),
(40, 'grupo', 11842919, 'INSERT', NULL, 'nombre=602, tutor=nose', NULL, NULL, '2026-01-20 00:55:31'),
(41, 'grupo', 27974496, 'INSERT', NULL, 'nombre=603, tutor=nose', NULL, NULL, '2026-01-20 00:55:37'),
(42, 'grupo', 78236176, 'INSERT', NULL, 'nombre=605, tutor=nose', NULL, NULL, '2026-01-20 00:55:47'),
(43, 'grupo', 72896025, 'INSERT', NULL, 'nombre=604, tutor=nose', NULL, NULL, '2026-01-20 00:56:21'),
(44, 'materias', 24, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:04:24'),
(45, 'materias', 25, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:04:41'),
(46, 'materias', 27, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:04:55'),
(47, 'materias', 28, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:05:18'),
(48, 'materias', 3, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:05:34'),
(49, 'materias', 1, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:05:38'),
(50, 'materias', 12, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:05:46'),
(51, 'materias', 9, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:05:51'),
(52, 'materias', 11, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:05:58'),
(53, 'materias', 15, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:06:06'),
(54, 'materias', 14, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:06:12'),
(55, 'materias', 10, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:06:18'),
(56, 'materias', 13, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:06:37'),
(57, 'materias', 21, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:06:59'),
(58, 'materias', 23, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:07:13'),
(59, 'materias', 22, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:07:19'),
(60, 'materias', 20, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:07:26'),
(61, 'materias', 19, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:07:32'),
(62, 'materias', 18, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:07:39'),
(63, 'materias', 17, 'UPDATE', NULL, NULL, NULL, NULL, '2026-01-20 01:07:45'),
(64, 'alumno', 245, 'UPDATE', 'nombre=Danna Aquetzalli, apellidos=Bravo Paredes, grupo=13908098', 'nombre=Danna Aquetzalli, apellidos=Bravo Paredes, grupo=59995390', NULL, NULL, '2026-01-20 02:55:56'),
(65, 'alumno', 246, 'UPDATE', 'nombre=Leslye Yareni, apellidos=Cervantes Contreras, grupo=13908098', 'nombre=Leslye Yareni, apellidos=Cervantes Contreras, grupo=59995390', NULL, NULL, '2026-01-20 02:55:56'),
(66, 'alumno', 263, 'UPDATE', 'nombre=Iker Javier, apellidos=Martinez Angeles, grupo=13908098', 'nombre=Iker Javier, apellidos=Martinez Angeles, grupo=59995390', NULL, NULL, '2026-01-20 02:55:56'),
(67, 'alumno', 271, 'UPDATE', 'nombre=Vania Denisse, apellidos=Rodriguez Ponce, grupo=13908098', 'nombre=Vania Denisse, apellidos=Rodriguez Ponce, grupo=59995390', NULL, NULL, '2026-01-20 02:55:56'),
(68, 'alumno', 278, 'UPDATE', 'nombre=Dayana Amely, apellidos=Castillo Romero, grupo=13908098', 'nombre=Dayana Amely, apellidos=Castillo Romero, grupo=59995390', NULL, NULL, '2026-01-20 02:55:56'),
(69, 'alumno', 279, 'UPDATE', 'nombre=Dylan Octavio, apellidos=Jimenez Hernandez, grupo=13908098', 'nombre=Dylan Octavio, apellidos=Jimenez Hernandez, grupo=59995390', NULL, NULL, '2026-01-20 02:55:56'),
(70, 'alumno', 245, 'UPDATE', 'nombre=Danna Aquetzalli, apellidos=Bravo Paredes, grupo=59995390', 'nombre=Danna Aquetzalli, apellidos=Bravo Paredes, grupo=13908098', NULL, NULL, '2026-01-20 02:56:31'),
(71, 'alumno', 246, 'UPDATE', 'nombre=Leslye Yareni, apellidos=Cervantes Contreras, grupo=59995390', 'nombre=Leslye Yareni, apellidos=Cervantes Contreras, grupo=13908098', NULL, NULL, '2026-01-20 02:56:31'),
(72, 'alumno', 263, 'UPDATE', 'nombre=Iker Javier, apellidos=Martinez Angeles, grupo=59995390', 'nombre=Iker Javier, apellidos=Martinez Angeles, grupo=13908098', NULL, NULL, '2026-01-20 02:56:31'),
(73, 'alumno', 271, 'UPDATE', 'nombre=Vania Denisse, apellidos=Rodriguez Ponce, grupo=59995390', 'nombre=Vania Denisse, apellidos=Rodriguez Ponce, grupo=13908098', NULL, NULL, '2026-01-20 02:56:31'),
(74, 'alumno', 278, 'UPDATE', 'nombre=Dayana Amely, apellidos=Castillo Romero, grupo=59995390', 'nombre=Dayana Amely, apellidos=Castillo Romero, grupo=13908098', NULL, NULL, '2026-01-20 02:56:31'),
(75, 'alumno', 279, 'UPDATE', 'nombre=Dylan Octavio, apellidos=Jimenez Hernandez, grupo=59995390', 'nombre=Dylan Octavio, apellidos=Jimenez Hernandez, grupo=13908098', NULL, NULL, '2026-01-20 02:56:31'),
(76, 'grupo_materia', 92, 'UPDATE', 'grupo=23498772, materia=22, profesor=3', 'grupo=23498772, materia=22, profesor=13', NULL, NULL, '2026-01-20 03:34:54'),
(77, 'grupo_materia', 93, 'UPDATE', 'grupo=23498772, materia=23, profesor=3', 'grupo=23498772, materia=23, profesor=13', NULL, NULL, '2026-01-20 03:35:30'),
(78, 'grupo_materia', 93, 'UPDATE', 'grupo=23498772, materia=23, profesor=13', 'grupo=23498772, materia=23, profesor=3', NULL, NULL, '2026-01-20 03:39:13'),
(79, 'grupo_materia', 92, 'UPDATE', 'grupo=23498772, materia=22, profesor=13', 'grupo=23498772, materia=22, profesor=3', NULL, NULL, '2026-01-20 03:39:23'),
(80, 'asistencia', 6, 'INSERT', NULL, 'fecha=2026-01-20, estado=Ausente, alumno=21', NULL, NULL, '2026-01-21 04:39:20'),
(81, 'asistencia', 6, 'DELETE', 'fecha=2026-01-20, estado=Ausente, alumno=21', NULL, NULL, NULL, '2026-01-21 04:39:22'),
(82, 'asistencia', 7, 'INSERT', NULL, 'fecha=2026-01-20, estado=Ausente, alumno=21', NULL, NULL, '2026-01-21 04:39:22'),
(83, 'asistencia', 8, 'INSERT', NULL, 'fecha=2026-01-20, estado=Ausente, alumno=21', NULL, NULL, '2026-01-21 04:44:01'),
(84, 'asistencia', 9, 'INSERT', NULL, 'fecha=2026-01-14, estado=Retardo, alumno=25', NULL, NULL, '2026-01-21 05:39:00'),
(85, 'asistencia', 10, 'INSERT', NULL, 'fecha=2026-01-16, estado=Retardo, alumno=25', NULL, NULL, '2026-01-21 05:39:00'),
(86, 'asistencia', 10, 'UPDATE', 'estado=Retardo', 'estado=Justificante', NULL, NULL, '2026-01-21 05:39:00'),
(87, 'asistencia', 9, 'UPDATE', 'estado=Retardo', 'estado=Justificante', NULL, NULL, '2026-01-21 05:39:00'),
(88, 'asistencia', 9, 'UPDATE', 'estado=Justificante', 'estado=Ausente', NULL, NULL, '2026-01-21 05:39:00'),
(89, 'asistencia', 10, 'UPDATE', 'estado=Justificante', 'estado=Ausente', NULL, NULL, '2026-01-21 05:39:00'),
(90, 'asistencia', 10, 'UPDATE', 'estado=Ausente', 'estado=Retardo', NULL, NULL, '2026-01-21 05:39:00'),
(91, 'asistencia', 10, 'UPDATE', 'estado=Retardo', 'estado=Justificante', NULL, NULL, '2026-01-21 05:39:00'),
(92, 'asistencia', 10, 'UPDATE', 'estado=Justificante', 'estado=Ausente', NULL, NULL, '2026-01-21 05:39:00'),
(93, 'asistencia', 11, 'INSERT', NULL, 'fecha=2026-01-07, estado=Retardo, alumno=21', NULL, NULL, '2026-01-21 06:16:21'),
(94, 'asistencia', 12, 'INSERT', NULL, 'fecha=2026-01-08, estado=Retardo, alumno=21', NULL, NULL, '2026-01-21 06:16:21'),
(95, 'asistencia', 12, 'UPDATE', 'estado=Retardo', 'estado=Justificante', NULL, NULL, '2026-01-21 06:16:21'),
(96, 'asistencia', 11, 'UPDATE', 'estado=Retardo', 'estado=Justificante', NULL, NULL, '2026-01-21 06:16:21'),
(97, 'asistencia', 11, 'UPDATE', 'estado=Justificante', 'estado=Ausente', NULL, NULL, '2026-01-21 06:16:21'),
(98, 'asistencia', 12, 'UPDATE', 'estado=Justificante', 'estado=Ausente', NULL, NULL, '2026-01-21 06:16:21'),
(99, 'asistencia', 12, 'UPDATE', 'estado=Ausente', 'estado=Retardo', NULL, NULL, '2026-01-21 06:16:21'),
(100, 'asistencia', 12, 'UPDATE', 'estado=Retardo', 'estado=Justificante', NULL, NULL, '2026-01-21 06:16:21'),
(101, 'asistencia', 12, 'UPDATE', 'estado=Justificante', 'estado=Ausente', NULL, NULL, '2026-01-21 06:16:21'),
(102, 'asistencia', 13, 'INSERT', NULL, 'fecha=2026-01-13, estado=Retardo, alumno=21', NULL, NULL, '2026-01-21 06:16:35'),
(103, 'asistencia', 13, 'UPDATE', 'estado=Retardo', 'estado=Justificante', NULL, NULL, '2026-01-21 06:16:35'),
(104, 'asistencia', 13, 'UPDATE', 'estado=Justificante', 'estado=Ausente', NULL, NULL, '2026-01-21 06:16:35'),
(105, 'asistencia', 14, 'INSERT', NULL, 'fecha=2026-01-12, estado=Retardo, alumno=21', NULL, NULL, '2026-01-21 06:16:35'),
(106, 'asistencia', 14, 'UPDATE', 'estado=Retardo', 'estado=Justificante', NULL, NULL, '2026-01-21 06:16:35'),
(107, 'asistencia', 14, 'UPDATE', 'estado=Justificante', 'estado=Ausente', NULL, NULL, '2026-01-21 06:16:35'),
(108, 'asistencia', 15, 'INSERT', NULL, 'fecha=2026-01-11, estado=Retardo, alumno=21', NULL, NULL, '2026-01-21 06:16:35'),
(109, 'asistencia', 15, 'UPDATE', 'estado=Retardo', 'estado=Justificante', NULL, NULL, '2026-01-21 06:16:35'),
(110, 'asistencia', 15, 'UPDATE', 'estado=Justificante', 'estado=Ausente', NULL, NULL, '2026-01-21 06:16:35'),
(111, 'asistencia', 16, 'INSERT', NULL, 'fecha=2026-01-01, estado=Retardo, alumno=21', NULL, NULL, '2026-01-21 06:19:09'),
(112, 'asistencia', 16, 'UPDATE', 'estado=Retardo', 'estado=Justificante', NULL, NULL, '2026-01-21 06:19:09'),
(113, 'asistencia', 16, 'UPDATE', 'estado=Justificante', 'estado=Ausente', NULL, NULL, '2026-01-21 06:19:09'),
(114, 'asistencia', 15, 'UPDATE', 'estado=Ausente', 'estado=Retardo', NULL, NULL, '2026-01-21 06:23:06'),
(115, 'asistencia', 11, 'UPDATE', 'estado=Ausente', 'estado=Retardo', NULL, NULL, '2026-01-21 06:23:06'),
(116, 'asistencia', 11, 'UPDATE', 'estado=Retardo', 'estado=Justificante', NULL, NULL, '2026-01-21 06:23:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo`
--

CREATE TABLE `grupo` (
  `idGrupo` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `semestre` varchar(50) NOT NULL,
  `tutor` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grupo`
--

INSERT INTO `grupo` (`idGrupo`, `nombre`, `semestre`, `tutor`) VALUES
(10236290, '103', 'Primer semestre', 'nose'),
(10586358, '205', 'Segundo semestre', 'nose'),
(10709410, '204', 'Segundo semestre', 'nose'),
(11842919, '602', 'Sexto semestre', 'nose'),
(13908098, '101', 'Primer semestre', 'nose'),
(16011634, '505', 'Quinto semestre', 'nose'),
(21768545, '104', 'Primer semestre', 'nose'),
(23498772, '302', 'Tercer semestre', 'Quien sabe'),
(27974496, '603', 'Sexto semestre', 'nose'),
(38546385, '501', 'Quinto semestre', 'Mauricio Sanchez Reyes'),
(49988421, '301', 'Tercer semestre', 'nose'),
(53059442, '203', 'Segundo semestre', 'nose'),
(54972252, '401', 'Cuarto semestre', 'nose'),
(55235409, '503', 'Quinto semestre', 'Yadira Guadalupe Garcia Ramos'),
(58233444, '601', 'Sexto semestre', 'nose'),
(59995390, '201', 'Segundo semestre', 'nose'),
(64840321, '102', 'Primer semestre', 'nose'),
(71900101, '504', 'Quinto semestre', 'Rene Lorea Ayala, Marian Elizabeth Pontigo Suarez'),
(72896025, '604', 'Sexto semestre', 'nose'),
(73049269, '202', 'Segundo semestre', 'nose'),
(75279400, '404', 'Cuarto semestre', 'nose'),
(78236176, '605', 'Sexto semestre', 'nose'),
(79757662, '304', 'Tercer semestre', 'nose'),
(79832944, '403', 'Cuarto semestre', 'nose'),
(81259732, '405', 'Cuarto semestre', 'nose'),
(87876458, '502', 'Quinto semestre', 'Tarsisio Nava Arzaluz'),
(89421652, '105', 'Primer semestre', 'nose'),
(95102509, '303', 'Tercer semestre', 'nose'),
(96660927, '402', 'Cuarto semestre', 'nose'),
(99357180, '305', 'Tercer semestre', 'Daniel Garcia Salinas');

--
-- Disparadores `grupo`
--
DELIMITER $$
CREATE TRIGGER `trg_grupo_delete` BEFORE DELETE ON `grupo` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, id_admin, admin_nombre)
    VALUES (
        'grupo',
        OLD.idGrupo,
        'DELETE',
        CONCAT(
            'nombre=', OLD.nombre,
            ', tutor=', OLD.tutor
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_grupo_insert` AFTER INSERT ON `grupo` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_despues, id_admin, admin_nombre)
    VALUES (
        'grupo',
        NEW.idGrupo,
        'INSERT',
        CONCAT(
            'nombre=', NEW.nombre,
            ', tutor=', NEW.tutor
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_grupo_update` AFTER UPDATE ON `grupo` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, datos_despues, id_admin, admin_nombre)
    VALUES (
        'grupo',
        OLD.idGrupo,
        'UPDATE',
        CONCAT(
            'nombre=', OLD.nombre,
            ', tutor=', OLD.tutor
        ),
        CONCAT(
            'nombre=', NEW.nombre,
            ', tutor=', NEW.tutor
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_materia`
--

CREATE TABLE `grupo_materia` (
  `id_clase` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `id_profesor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grupo_materia`
--

INSERT INTO `grupo_materia` (`id_clase`, `id_grupo`, `id_materia`, `id_profesor`) VALUES
(1, 38546385, 24, 1),
(2, 38546385, 25, 2),
(3, 38546385, 26, 4),
(4, 38546385, 27, 5),
(5, 38546385, 28, 6),
(6, 38546385, 3, 3),
(7, 38546385, 1, 3),
(8, 71900101, 24, 1),
(9, 71900101, 25, 8),
(10, 71900101, 26, 4),
(11, 71900101, 27, 5),
(12, 71900101, 28, 2),
(13, 71900101, 3, 9),
(14, 71900101, 1, 9),
(15, 87876458, 24, 1),
(17, 87876458, 25, 8),
(18, 87876458, 26, 4),
(19, 87876458, 27, 5),
(20, 87876458, 28, 2),
(21, 87876458, 3, 10),
(22, 87876458, 1, 10),
(23, 16011634, 24, 1),
(24, 16011634, 25, 8),
(25, 16011634, 26, 4),
(26, 16011634, 27, 5),
(27, 16011634, 28, 6),
(28, 16011634, 1, 11),
(29, 16011634, 3, 11),
(30, 55235409, 24, 1),
(31, 55235409, 25, 8),
(32, 55235409, 26, 4),
(33, 55235409, 27, 5),
(34, 55235409, 28, 2),
(35, 55235409, 1, 10),
(36, 55235409, 3, 10),
(37, 13908098, 9, 1),
(38, 13908098, 10, 13),
(39, 13908098, 11, 14),
(40, 13908098, 12, 4),
(41, 13908098, 13, 15),
(42, 13908098, 14, 16),
(43, 13908098, 15, 17),
(44, 64840321, 9, 1),
(45, 64840321, 10, 13),
(46, 64840321, 11, 14),
(47, 64840321, 12, 4),
(48, 64840321, 13, 15),
(49, 64840321, 14, 16),
(50, 64840321, 15, 17),
(51, 10236290, 9, 18),
(52, 10236290, 10, 13),
(53, 10236290, 11, 14),
(54, 10236290, 12, 19),
(55, 10236290, 13, 15),
(56, 10236290, 14, 16),
(58, 10236290, 15, 17),
(59, 21768545, 9, 18),
(60, 21768545, 10, 13),
(61, 21768545, 11, 14),
(62, 21768545, 12, 19),
(63, 21768545, 13, 15),
(64, 21768545, 14, 16),
(65, 21768545, 15, 17),
(66, 89421652, 9, 18),
(67, 89421652, 10, 13),
(68, 89421652, 11, 14),
(69, 89421652, 12, 19),
(70, 89421652, 13, 15),
(71, 89421652, 14, 16),
(72, 89421652, 15, 17),
(73, 99357180, 17, 14),
(74, 99357180, 18, 20),
(75, 99357180, 19, 16),
(76, 99357180, 20, 2),
(77, 99357180, 21, 21),
(78, 99357180, 22, 22),
(79, 99357180, 23, 22),
(80, 79757662, 17, 14),
(81, 79757662, 18, 20),
(82, 79757662, 19, 16),
(83, 79757662, 20, 2),
(84, 79757662, 21, 21),
(85, 79757662, 22, 23),
(86, 79757662, 23, 23),
(87, 23498772, 17, 14),
(88, 23498772, 18, 20),
(89, 23498772, 19, 16),
(90, 23498772, 20, 2),
(91, 23498772, 21, 21),
(92, 23498772, 22, 3),
(93, 23498772, 23, 3),
(94, 49988421, 17, 14),
(95, 49988421, 18, 20),
(96, 49988421, 19, 16),
(97, 49988421, 20, 8),
(98, 49988421, 21, 21),
(99, 49988421, 22, 24),
(100, 49988421, 23, 24);

--
-- Disparadores `grupo_materia`
--
DELIMITER $$
CREATE TRIGGER `trg_grupo_materia_delete` BEFORE DELETE ON `grupo_materia` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, id_admin, admin_nombre)
    VALUES (
        'grupo_materia',
        OLD.id_clase,
        'DELETE',
        CONCAT(
            'grupo=', OLD.id_grupo,
            ', materia=', OLD.id_materia,
            ', profesor=', OLD.id_profesor
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_grupo_materia_insert` AFTER INSERT ON `grupo_materia` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_despues, id_admin, admin_nombre)
    VALUES (
        'grupo_materia',
        NEW.id_clase,
        'INSERT',
        CONCAT(
            'grupo=', NEW.id_grupo,
            ', materia=', NEW.id_materia,
            ', profesor=', NEW.id_profesor
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_grupo_materia_update` AFTER UPDATE ON `grupo_materia` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, datos_despues, id_admin, admin_nombre)
    VALUES (
        'grupo_materia',
        OLD.id_clase,
        'UPDATE',
        CONCAT(
            'grupo=', OLD.id_grupo,
            ', materia=', OLD.id_materia,
            ', profesor=', OLD.id_profesor
        ),
        CONCAT(
            'grupo=', NEW.id_grupo,
            ', materia=', NEW.id_materia,
            ', profesor=', NEW.id_profesor
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id_materia` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `semestre` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id_materia`, `nombre`, `semestre`, `descripcion`) VALUES
(1, 'Desarrolla Aplicaciones WEB con Conexión a Bases de Datos', 'Quinto semestre', NULL),
(3, 'Construye Bases de Datos para Aplicaciones WEB', 'Quinto semestre', NULL),
(9, 'Pensamiento Matemático I', 'Primer semestre', NULL),
(10, 'Ciencias Sociales', 'Primer semestre', NULL),
(11, 'Lengua y Comunicación I', 'Primer semestre', NULL),
(12, 'Ingles I', 'Primer semestre', NULL),
(13, 'La Materia y sus Interacciones ', 'Primer semestre', NULL),
(14, 'Cultura Digital', 'Primer semestre', NULL),
(15, 'Humanidades I', 'Primer semestre', NULL),
(16, 'Orientacion', 'Orientadoras', NULL),
(17, 'Lengua y Comunicación III', 'Tercer semestre', NULL),
(18, 'Ingles III', 'Tercer semestre', NULL),
(19, 'Pensamiento Matemático III', 'Tercer semestre', NULL),
(20, 'Ecosistemas: Interacción Energía y Dinámica', 'Tercer semestre', NULL),
(21, 'Humanidades II', 'Tercer semestre', NULL),
(22, 'Emplea Frameworks  Para El Desarrollo de Software ', 'Tercer semestre', NULL),
(23, 'Aplica Metodologías Agiles Para El Desarrollo de Software', 'Tercer semestre', NULL),
(24, 'Temas Selectos de Matemáticas II', 'Quinto semestre', NULL),
(25, 'La Energía en los Procesos de la Vida Diaria ', 'Quinto semestre', NULL),
(26, 'Ingles V', 'Quinto semestre', NULL),
(27, 'Conciencia Histórica II', 'Quinto semestre', NULL),
(28, 'Movimiento y Estabilidad: Fuerzas e Interacción', 'Quinto semestre', NULL);

--
-- Disparadores `materias`
--
DELIMITER $$
CREATE TRIGGER `trg_materias_delete` BEFORE DELETE ON `materias` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, id_admin, admin_nombre)
    VALUES (
        'materias',
        OLD.id_materia,
        'DELETE',
        CONCAT(
            'nombre=', OLD.nombre,
            ', descripcion=', OLD.descripcion
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_materias_insert` AFTER INSERT ON `materias` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_despues, id_admin, admin_nombre)
    VALUES (
        'materias',
        NEW.id_materia,
        'INSERT',
        CONCAT(
            'nombre=', NEW.nombre,
            ', descripcion=', NEW.descripcion
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_materias_update` AFTER UPDATE ON `materias` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, datos_despues, id_admin, admin_nombre)
    VALUES (
        'materias',
        OLD.id_materia,
        'UPDATE',
        CONCAT(
            'nombre=', OLD.nombre,
            ', descripcion=', OLD.descripcion
        ),
        CONCAT(
            'nombre=', NEW.nombre,
            ', descripcion=', NEW.descripcion
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor`
--

CREATE TABLE `profesor` (
  `id_profesor` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `telefono` bigint(20) NOT NULL,
  `domicilio` varchar(200) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesor`
--

INSERT INTO `profesor` (`id_profesor`, `nombre`, `apellidos`, `telefono`, `domicilio`, `correo`, `password`) VALUES
(1, 'Gilberto Jesus ', 'Chavez Osorio', 5555555555, 'nose', 'gilberto@gmail.com', '$2y$10$oYAWy9K1p44X69oevvYnh.OAGwj8Fv2QYr6u5xWu5hOLEel3GsRzS'),
(2, 'Marian Elizabeth ', 'Pontigo Suarez', 5555555556, 'nose', 'mari@gmail.com', '$2y$10$j0uMsTDc4NAG1RiJnM/jyeSeRoElMAmlXD0aqpCJpBf2891B9ayK2'),
(3, 'Mauricio', 'Sanchez Reyes', 5555555551, 'nose', 'mau@gmail.com', '$2y$10$oSSt2JY0/b1iMxI4jwb31OYrRlH2zM4GqUyqZllfzBbtjjoKBo8zi'),
(4, 'Julio Cesar Alan', 'Santillan Garcia', 5555555552, 'nose', 'Julio@gmail.com', '$2y$10$VKeBN9POX6dElV4Div0sdO8.Oz03vxwRZNxXcIss0ohaW50MZc.jG'),
(5, 'Cesar Adrian ', 'Romero Nieto', 5555555553, 'nose', 'cesar@gmail.com', '$2y$10$2CnyuW.ZMalOuiRI/rM32u/BeXWkJltfaMLok/./YsG2FWThR5.I.'),
(6, 'Juan Manuel ', 'Morales Hernandez', 5555555554, 'nose', 'Juan@gmail.com', '$2y$10$Oc0iNYzmL2mhvVz3fH5d0OXTCbQoK55sekQ1g3jygTSMj1rj9bBHO'),
(8, 'Tarsicio ', 'Nava Arzaluz ', 5555555557, 'nose', 'tarsi@gmail.com', '$2y$10$BFVyy/aEZSiUjMhU7Ov71eyDa/nLH1ANbEMtthv3riHMpQcSzukiC'),
(9, 'Rene', 'Lorea Ayala', 5555555558, 'nose', 'rene@gmail.com', '$2y$10$iswEcehR6Q87WYNH6GLExus4mhXlx9QmVEtub.TV4Rqi2toHqGedu'),
(10, 'Yadira Guadalupe', 'Garcia Ramos', 5555555559, 'nose', 'yadira@gmailcom', '$2y$10$KUZtcRsPHwSKYH4A5cA6hOB.Wm23OlALCzL7nWTRKqdeLNDMuy1ka'),
(11, 'Valentin ', 'Vargas Contreras ', 5555555510, 'nose', 'valentin@gmail.com', '$2y$10$FuL8riJ3oZJspnUxmTGUUuvXuxTjs6U2jCdPnxVDJiR1utqj9yHQ.'),
(13, 'Carmen Mitzy ', 'Moreno Vargas ', 5555555511, 'nose', 'carmen@gmail.com', '$2y$10$Id4SEaEjrPhjWTE2yoFTGOsWjGmUgBIOY8scoO70QtKJusUzICWBa'),
(14, 'Hilda', 'Jimenez Zamora', 5555555512, 'nose', 'hilda@gmail.com', '$2y$10$e4FH5gwIkpCHPX2jQtbAnOuecVImOdVT2fywn3MYKlKhaR8rZ9Isu'),
(15, 'Elizabeth', 'Lopez Cruz ', 5555555513, 'nose', 'elizabeth@gmail.com', '$2y$10$0S01aPuS5udLPmMp22k1A.HCWuylgPduuq49KHE/krKpQJ4yCc4Ve'),
(16, 'Miguel Angel ', 'Fernandez Sanchez ', 5555555514, 'nose', 'miguel@gmail.com', '$2y$10$p6K3IoyxzUVQIjzrKKmKs.hOUEeeFvBVtSUCAU6l2iDcaxw05QulK'),
(17, 'Gustavo ', 'Rebollo Torres', 5555555515, 'nose', 'gustavo@gmail.com', '$2y$10$8Cm/ckcj7TCZ1qGR3k1NPOhCq.D7VbL0.Hx7UKR84nWjSCFpGU0PK'),
(18, 'Fabiola ', 'Salazar Sanchez', 5555555516, 'nose', 'fabiola@gmail.com', '$2y$10$z9Y3oAHcn6apghFH0GKVBOQrw3QaEQsPyRBfg9ica/A/2heJUvBFi'),
(19, 'Mariana ', 'Ramirez Hernandez', 5555555517, 'nose', 'mariana@gmail.com', '$2y$10$WkSBY2nLVRySBF2chTcDg.rHZoXnz0KlH1rp/PT/daPJgp3SV/yXC'),
(20, 'Michelle', 'Violante Paez', 5555555518, 'nose', 'michelle@gmail.com', '$2y$10$4ZhsPlL8A/vaVEqKLCcanuUiLyV/casDfh3UHlqjVZIzer7UG1DBa'),
(21, 'Itzel Mariel', 'Nava Cortes', 5555555519, 'nose', 'itzel@gmail.com', '$2y$10$PKs3cGffPdbNrrDA079vyeIjm4eYPYyvmLC14NnRQUH0wWFfRPGia'),
(22, 'Daniel ', 'Garcia Salinas ', 5555555520, 'nose', 'daniel@gmail.com', '$2y$10$8MjgiHlWnHQp3Z0Ic1Z0EO.eVLgJJfsMur12AqUtgGLAnjiEhtlZq'),
(23, 'Alejandra Victoria ', 'Vega Garcia', 5555555521, 'nose', 'alejandra@gmail.com', '$2y$10$pnTszitAz4FmQ4aGlLSBVO6l.K9x.Vgl9uz8QsZvxBHtQ8Dmw01oC'),
(24, 'Jaime', 'Cruz Lopez', 5555555522, 'nose', 'jaime@gmail.com', '$2y$10$nyvzFx5C4poyXZo.85TMYOu/cFcXEmDvJ68JJRqBr/FfhSWKDly.S');

--
-- Disparadores `profesor`
--
DELIMITER $$
CREATE TRIGGER `trg_profesor_delete` BEFORE DELETE ON `profesor` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, id_admin, admin_nombre)
    VALUES (
        'profesor',
        OLD.id_profesor,
        'DELETE',
        CONCAT(
            'nombre=', OLD.nombre,
            ', apellidos=', OLD.apellidos,
            ', correo=', OLD.correo
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_profesor_insert` AFTER INSERT ON `profesor` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_despues, id_admin, admin_nombre)
    VALUES (
        'profesor',
        NEW.id_profesor,
        'INSERT',
        CONCAT(
            'nombre=', NEW.nombre,
            ', apellidos=', NEW.apellidos,
            ', correo=', NEW.correo
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_profesor_update` AFTER UPDATE ON `profesor` FOR EACH ROW BEGIN
    INSERT INTO auditoria
    (tabla_afectada, id_registro, accion, datos_antes, datos_despues, id_admin, admin_nombre)
    VALUES (
        'profesor',
        OLD.id_profesor,
        'UPDATE',
        CONCAT(
            'nombre=', OLD.nombre,
            ', apellidos=', OLD.apellidos,
            ', correo=', OLD.correo
        ),
        CONCAT(
            'nombre=', NEW.nombre,
            ', apellidos=', NEW.apellidos,
            ', correo=', NEW.correo
        ),
        @admin_id,
        @admin_nombre
    );
END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indices de la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD PRIMARY KEY (`id_alumno`),
  ADD KEY `id_grupo` (`id_grupo`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD KEY `id_alumno` (`id_alumno`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `id_grupo` (`id_grupo`);

--
-- Indices de la tabla `asistencia_diaria`
--
ALTER TABLE `asistencia_diaria`
  ADD PRIMARY KEY (`id_asistencia_diaria`),
  ADD UNIQUE KEY `unique_alumno_fecha` (`id_alumno`,`fecha`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_alumno` (`id_alumno`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `asistencia_diaria_logs`
--
ALTER TABLE `asistencia_diaria_logs`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_fecha_intento` (`fecha_intento`),
  ADD KEY `idx_alumno` (`id_alumno`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `fk_auditoria_admin` (`id_admin`);

--
-- Indices de la tabla `grupo`
--
ALTER TABLE `grupo`
  ADD PRIMARY KEY (`idGrupo`);

--
-- Indices de la tabla `grupo_materia`
--
ALTER TABLE `grupo_materia`
  ADD PRIMARY KEY (`id_clase`),
  ADD KEY `id_grupo` (`id_grupo`),
  ADD KEY `id_profesor` (`id_profesor`),
  ADD KEY `id_materia` (`id_materia`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id_materia`);

--
-- Indices de la tabla `profesor`
--
ALTER TABLE `profesor`
  ADD PRIMARY KEY (`id_profesor`),
  ADD UNIQUE KEY `telefono` (`telefono`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `alumno`
--
ALTER TABLE `alumno`
  MODIFY `id_alumno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=447;

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `asistencia_diaria`
--
ALTER TABLE `asistencia_diaria`
  MODIFY `id_asistencia_diaria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencia_diaria_logs`
--
ALTER TABLE `asistencia_diaria_logs`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT de la tabla `grupo`
--
ALTER TABLE `grupo`
  MODIFY `idGrupo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99357181;

--
-- AUTO_INCREMENT de la tabla `grupo_materia`
--
ALTER TABLE `grupo_materia`
  MODIFY `id_clase` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id_materia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `profesor`
--
ALTER TABLE `profesor`
  MODIFY `id_profesor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD CONSTRAINT `alumno_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `grupo` (`idGrupo`);

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `asistencia_ibfk_3` FOREIGN KEY (`id_grupo`) REFERENCES `grupo` (`idGrupo`),
  ADD CONSTRAINT `asistencia_ibfk_4` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asistencia_ibfk_5` FOREIGN KEY (`id_alumno`) REFERENCES `alumno` (`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `asistencia_diaria`
--
ALTER TABLE `asistencia_diaria`
  ADD CONSTRAINT `asistencia_diaria_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumno` (`id_alumno`) ON DELETE CASCADE;

--
-- Filtros para la tabla `asistencia_diaria_logs`
--
ALTER TABLE `asistencia_diaria_logs`
  ADD CONSTRAINT `asistencia_diaria_logs_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumno` (`id_alumno`) ON DELETE CASCADE;

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `fk_auditoria_admin` FOREIGN KEY (`id_admin`) REFERENCES `administrador` (`id_admin`) ON DELETE SET NULL;

--
-- Filtros para la tabla `grupo_materia`
--
ALTER TABLE `grupo_materia`
  ADD CONSTRAINT `grupo_materia_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `grupo` (`idGrupo`),
  ADD CONSTRAINT `grupo_materia_ibfk_3` FOREIGN KEY (`id_profesor`) REFERENCES `profesor` (`id_profesor`),
  ADD CONSTRAINT `grupo_materia_ibfk_4` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

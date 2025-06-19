DROP TABLE IF EXISTS `cumpleanos`;

CREATE TABLE `cumpleanos` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `rut` VARCHAR(20) NOT NULL UNIQUE,
  `nombre` VARCHAR(255) NOT NULL,
  `apellido` VARCHAR(255) NOT NULL,
  `fecha_cumpleanos` DATE NOT NULL,
  `cargo` VARCHAR(33) NOT NULL UNIQUE,
  `edad` INT UNSIGNED NOT NULL,
  `vinculado_empresa` TINYINT(1) NOT NULL DEFAULT 0,
  `email_enviado` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*CUMPLEAÑOS POBLACION*/

INSERT INTO `cumpleanos` (`rut`, `nombre`, `apellido`,  `fecha_cumpleanos`, `cargo`, `edad`, `vinculado_empresa`, `email_enviado`) VALUES
('12345678-9', 'Juan', 'Pérez', '1990-06-16', 'Supervisor Logístico', 34, 1, 0),
('98765432-1', 'María' , 'López', '1985-12-25', 'Jefa de Finanzas', 39, 1, 1),
('11222333-4', 'Carlos', ' Ramírez', '2000-01-10', 'Asistente Técnico', 24, 0, 0),
('99887766-5', 'Fernanda' , ' Soto', '1992-08-30', 'Analista de Datos', 31, 1, 1),
('55667788-0', 'Ana',  'Torres', '1998-03-14', 'Encargada de Bodega', 26, 0, 0),
('44556677-2', 'Luis ', 'Rojas', '1995-11-05', 'Gerente Comercial', 28, 1, 0),
('77889900-1', 'Sofía' , ' Alarcón', '1999-07-20', 'Diseñadora UX', 24, 1, 0),
('33445566-3', 'Daniel' , ' Vega', '1988-01-03', 'Administrador TI', 36, 1, 1);
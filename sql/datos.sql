-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-09-2026 a las 20:59:11
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
-- Base de datos: `login_db`
--

--
-- Volcado de datos para la tabla `categoria_postre`
--

INSERT INTO `categoria_postre` (`id_categoria_postre`, `categoria_postre`, `estado`) VALUES
(1, 'Torta Clásica', 1),
(2, 'Torta Personalizada', 1),
(3, 'Postre Individual', 1),
(4, 'Decoración', 1);

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `id_tipo_documento`, `numero_documento`, `foto_perfil`, `correo`, `telefono`, `fecha_nacimiento`, `puntos_dulces`, `estado`, `contrasena_hash`, `direccion`, `ciudad`, `barrio`) VALUES
(1, 'Laura Ramírez', 1, '1015662738', NULL, 'laura.ramirez@email.com', '3124567890', NULL, 0, 1, NULL, NULL, NULL, NULL),
(2, 'Eventos y Sabores S.A.S', 2, '901556234-8', NULL, 'compras@eventosysabores.com', '6017654321', NULL, 0, 1, NULL, NULL, NULL, NULL),
(3, 'Camilo Torres', 1, '1102234981', NULL, 'camilo.torres@email.com', '3159876543', NULL, 0, 1, NULL, NULL, NULL, NULL),
(4, 'Panadería Dulce Hogar', 2, '800455211-3', NULL, 'pedidos@dulcehogar.com', '6023456789', NULL, 0, 1, NULL, NULL, NULL, NULL),
(5, 'Natalia Gómez', 1, '1024560789', NULL, 'natalia.gomez@gmail.com', '3125034969', NULL, 0, 1, NULL, NULL, NULL, NULL),
(6, 'Andrés Cárdenas', 1, '80881448', NULL, 'andres.cardenas@gmail.com', '3112830729', NULL, 0, 1, NULL, NULL, NULL, NULL),
(10, 'Elena Maria Cano Gomez', 1, '67016516', 'cliente_10_5565c08e.png', 'maria.bruno.mona@gmail.com', '3107538847', NULL, 0, 1, '$2y$10$RJ08cPEL5rh/GJSZuLRT7uow1uhPCaZbYf3Eyk96wnNQdW6U88ST6', 'Calle 6b #3-47', 'Bogotá D.C.', 'Belen'),
(11, 'Juan Velasco', 1, '1107845023', 'cliente_11_98e8ec81.jpg', 'd.jcc.juan.velasco@gmail.com', '3104746216', NULL, 0, 1, '$2y$10$/84wcWYI5SgBMgbVp6r1r.WoMAsZb1bk1eBxqyZQohkQzrie1yKJO', 'Calle 6b #3-47', 'Bogotá D.C.', 'Belen');

--
-- Volcado de datos para la tabla `contabilidad`
--

INSERT INTO `contabilidad` (`id_contabilidad`, `tipo_movimiento`, `monto_transaccion`, `descripcion_registro`, `fecha_registro`, `estado`) VALUES
(20, 'INGRESO', 100000.00, 'Pago aprobado - Pedido #24', '2026-09-22', 1),
(21, 'INGRESO', 180000.00, 'Pago aprobado - Pedido #25', '2026-09-22', 1),
(22, 'INGRESO', 20000.00, 'Pago aprobado - Pedido #26', '2026-09-23', 1),
(23, 'INGRESO', 360000.00, 'Pago aprobado - Pedido #27', '2026-09-23', 1),
(24, 'INGRESO', 20000.00, 'Pago aprobado - Pedido #28', '2026-09-23', 1),
(25, 'INGRESO', 20000.00, 'Pago aprobado - Pedido #29', '2026-09-23', 1),
(26, 'INGRESO', 20000.00, 'Pago aprobado - Pedido #30', '2026-09-23', 1),
(27, 'INGRESO', 1800000.00, 'Pago aprobado - Pedido #31', '2026-09-24', 1),
(28, 'INGRESO', 40000.00, 'Pago aprobado - Pedido #32', '2026-09-24', 1),
(29, 'INGRESO', 48000.00, 'Pago aprobado - Pedido #33', '2026-09-24', 1),
(30, 'INGRESO', 12000.00, 'Pago aprobado - Pedido #34', '2026-09-24', 1),
(31, 'INGRESO', 12000.00, 'Pago aprobado - Pedido #35', '2026-09-24', 1),
(32, 'INGRESO', 20000.00, 'Pago aprobado - Pedido #36', '2026-09-24', 1);

--
-- Volcado de datos para la tabla `decoracion`
--

INSERT INTO `decoracion` (`id_decoracion`, `precio`, `estado`) VALUES
(1, 5000.00, 1),
(2, 15000.00, 1),
(3, 30000.00, 1);

--
-- Volcado de datos para la tabla `estado_pagos`
--

INSERT INTO `estado_pagos` (`id_estado_pago`, `estado_pago`, `estado`) VALUES
(1, 'Pendiente', 1),
(2, 'En proceso', 1),
(3, 'Completado', 1);

--
-- Volcado de datos para la tabla `estado_pedido`
--

INSERT INTO `estado_pedido` (`id_estado_pedido`, `estado_pedido`, `estado`) VALUES
(1, 'Pendiente', 1),
(2, 'En preparación', 1),
(3, 'Completado', 1),
(4, 'Enviado', 1),
(5, 'Entregado', 1);

--
-- Volcado de datos para la tabla `gasto_decoracion`
--

INSERT INTO `gasto_decoracion` (`id_gasto_decoracion`, `id_decoracion`, `numero_diseno`, `nombre_diseno`, `fondant_inicial`, `gasto_fondant`, `colorante_inicial`, `gasto_colorante`, `estado`) VALUES
(1, 1, 113, 'Flores Sencillas', 500.00, 40.00, 20.00, 3.00, 1),
(2, 1, 94, 'Borde Clásico', 500.00, 30.00, 20.00, 2.00, 1),
(3, 2, 519, 'Figura 3D Mediana', 500.00, 120.00, 20.00, 6.00, 1),
(4, 3, 250, 'Tema Infantil Completo', 500.00, 200.00, 20.00, 10.00, 1),
(5, 3, 375, 'Nombre y Números', 500.00, 60.00, 20.00, 4.00, 1);

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id_inventario`, `id_producto`, `cantidad`, `estado`) VALUES
(64, 1, 0, 1),
(65, 2, 5, 1),
(66, 3, 5, 1),
(67, 4, 5, 1),
(68, 5, 5, 1),
(69, 6, 5, 1),
(70, 7, 5, 1),
(71, 8, 5, 1),
(72, 9, 5, 1),
(73, 10, 5, 1),
(74, 11, 5, 1),
(75, 12, 5, 1),
(76, 13, 5, 1),
(77, 14, 5, 1),
(78, 15, 5, 1),
(79, 16, 5, 1),
(80, 17, 5, 1),
(81, 18, 5, 1),
(82, 19, 5, 1),
(83, 20, 5, 1),
(84, 21, 5, 1),
(85, 22, 5, 1),
(86, 23, 5, 1),
(87, 24, 5, 1),
(88, 25, 5, 1),
(89, 26, 5, 1),
(90, 27, 5, 1),
(91, 28, 5, 1),
(92, 29, 5, 1),
(93, 30, 5, 1),
(94, 31, 5, 1),
(95, 32, 5, 1),
(96, 33, 5, 1),
(97, 34, 5, 1),
(98, 35, 5, 1),
(99, 36, 5, 1),
(127, 67, 5, 1),
(128, 68, 5, 1),
(129, 69, 5, 1),
(130, 70, 5, 1),
(131, 71, 5, 1),
(132, 72, 5, 1),
(133, 73, 5, 1),
(134, 74, 5, 1),
(135, 75, 5, 1),
(136, 76, 5, 1),
(137, 77, 5, 1),
(138, 78, 5, 1),
(139, 79, 5, 1),
(140, 80, 4, 1),
(141, 81, 5, 1),
(142, 82, 5, 1),
(143, 83, 5, 1),
(144, 84, 5, 1),
(145, 1, 0, 0);

--
-- Volcado de datos para la tabla `materia_prima`
--

INSERT INTO `materia_prima` (`id_materia_prima`, `nombre_insumo`, `descripcion`, `stock_disponible`, `id_unidad_medida`, `estado`) VALUES
(1, 'Harina de Trigo', 'Base para bizcochos y masas', 25.00, 4, 1),
(2, 'Azúcar', 'Endulzante base para todas las preparaciones', 30.00, 4, 1),
(3, 'Huevos', 'Insumo base para bizcochos y cremas', 15.00, 3, 1),
(4, 'Mantequilla', 'Grasa base para masas y coberturas', 10.00, 4, 1),
(5, 'Chocolate', 'Cobertura y relleno para tortas y postres', 12.00, 4, 1),
(6, 'Crema de Leche', 'Base para rellenos y coberturas cremosas', 20000.00, 5, 1),
(7, 'Queso Crema', 'Ingrediente principal del cheesecake', 8.00, 4, 1),
(8, 'Galleta Molida', 'Base crocante para cheesecakes y vasos', 6.00, 4, 1),
(9, 'Fondant', 'Cobertura moldeable para decoración de tortas', 5.00, 4, 1),
(10, 'Colorante Comestible', 'Pigmentación para decoración de tortas', 40.00, 3, 1),
(11, 'Leche', 'Base para masas y preparaciones', 0.00, 5, 1),
(12, 'Polvo de hornear', 'Leudante para masas', 0.00, 1, 1),
(13, 'Sal', 'Ingrediente base para masas', 0.00, 1, 1),
(14, 'Esencia de Vainilla', 'Base y aroma para preparaciones', 0.00, 5, 1),
(15, 'Cacao en Polvo', 'Ingrediente para masas de chocolate y Red Velvet', 0.00, 4, 1),
(16, 'Colorante Rojo', 'Colorante para preparaciones Red Velvet', 0.00, 1, 1),
(17, 'Fresa', 'Relleno y cobertura de productos de fresa', 0.00, 4, 1),
(18, 'Mermelada de Fresa', 'Relleno para productos de fresa', 0.00, 4, 1),
(19, 'Maracuyá', 'Relleno y cobertura de productos de maracuyá', 1000.00, 4, 1),
(20, 'Pulpa de Maracuyá', 'Preparación para productos de maracuyá', 1000.00, 4, 1),
(21, 'Arequipe', 'Relleno y cobertura de productos', 0.00, 4, 1),
(22, 'Helado', 'Bola de helado de vainilla para acompañar el brownie', 0.00, 5, 1);

--
-- Volcado de datos para la tabla `metodos_pago`
--

INSERT INTO `metodos_pago` (`id_metodos_pago`, `metodo_pago`, `estado`) VALUES
(1, 'Efectivo', 1),
(2, 'Nequi', 1),
(3, 'Bancolombia', 1),
(4, 'Transferencia', 1),
(5, 'PSE', 1),
(6, 'Bre-B', 1),
(7, 'Tarjeta Credito', 1),
(8, 'Tarjeta Debito', 1);

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pagos`, `id_pedido`, `id_metodo_pago`, `monto`, `id_estado_pago`, `fecha_pago`, `estado`) VALUES
(18, 24, 2, 100000.00, 3, '2026-09-23', 1),
(19, 25, 1, 180000.00, 3, '2026-09-23', 1),
(20, 26, 1, 20000.00, 3, '2026-09-22', 1),
(21, 27, 1, 360000.00, 3, '2026-09-22', 1),
(22, 28, 8, 20000.00, 3, '2026-09-23', 1),
(23, 29, 8, 20000.00, 3, '2026-09-23', 1),
(24, 30, 8, 20000.00, 3, '2026-09-23', 1),
(25, 31, 8, 1800000.00, 3, '2026-09-24', 1),
(26, 32, 8, 40000.00, 3, '2026-09-24', 1),
(27, 33, 1, 48000.00, 3, '2026-09-25', 1),
(28, 34, 8, 12000.00, 3, '2026-09-24', 1),
(29, 35, 8, 12000.00, 3, '2026-09-25', 1),
(30, 36, 6, 20000.00, 3, '2026-09-25', 1);

--
-- Volcado de datos para la tabla `pedido_completo`
--

INSERT INTO `pedido_completo` (`id_pedido`, `codigo_pedido`, `id_producto`, `id_cliente`, `fecha_pedido`, `fecha_estimada_entrega`, `id_categoria_postre`, `cantidad`, `id_tamano`, `id_sabor`, `precio_unitario`, `subtotal`, `id_estado_pedido`, `id_metodo_pago`, `fecha_pago`, `id_repostera_asignada`, `estado`, `id_pedido_web`) VALUES
(24, 'DM-20260923-086B', 83, 1, '2026-09-23', '2026-09-26', 3, 5, 1, 5, 20000.00, 100000.00, 5, 2, '2026-09-23', 2, 1, NULL),
(25, 'DM-20260923-50C7', 2, 4, '2026-09-23', '2026-09-26', 1, 5, 2, 1, 36000.00, 180000.00, 5, 1, '2026-09-23', 2, 1, NULL),
(26, NULL, 75, 11, '2026-09-22', '2026-09-23', 3, 1, 1, 3, 20000.00, 20000.00, 5, 1, '2026-09-23', 1, 1, 7),
(27, NULL, 24, 11, '2026-09-22', '2026-09-23', 1, 1, 6, 4, 360000.00, 360000.00, 5, 1, '2026-09-23', 1, 1, 8),
(28, NULL, 75, 11, '2026-09-23', '2026-09-24', 3, 1, 1, 3, 20000.00, 20000.00, 5, 8, '2026-09-23', 2, 1, 9),
(29, 'DM-20260923-AAA8', 75, 11, '2026-09-23', '2026-09-26', 3, 1, 1, 3, 20000.00, 20000.00, 5, 8, '2026-09-23', NULL, 1, NULL),
(30, NULL, 76, 11, '2026-09-23', '2026-09-24', 3, 1, 1, 4, 20000.00, 20000.00, 1, 8, '2026-09-23', NULL, 1, 10),
(31, 'DM-20260924-A1AF', 6, 10, '2026-09-24', '2026-09-27', 1, 5, 6, 1, 360000.00, 1800000.00, 5, 8, '2026-09-24', NULL, 1, NULL),
(32, NULL, 69, 11, '2026-09-24', '2026-09-27', 3, 2, 1, 3, 20000.00, 40000.00, 1, 8, '2026-09-24', NULL, 1, 11),
(33, 'DM-20260925-7F40', 1, 2, '2026-09-25', '2026-09-28', 3, 4, 1, 1, 12000.00, 48000.00, 5, 1, '2026-09-25', NULL, 1, NULL),
(34, NULL, 1, 11, '2026-09-24', '2026-09-25', 3, 1, 1, 1, 12000.00, 12000.00, 1, 8, '2026-09-24', NULL, 1, 12),
(35, 'DM-20260925-4D84', 1, 6, '2026-09-25', '2026-09-28', 3, 1, 1, 1, 12000.00, 12000.00, 5, 8, '2026-09-25', NULL, 1, NULL),
(36, 'DM-20260925-19DF', 80, 11, '2026-09-25', '2026-09-28', 3, 1, 1, 2, 20000.00, 20000.00, 5, 6, '2026-09-25', NULL, 1, NULL);

--
-- Volcado de datos para la tabla `pedido_web`
--

INSERT INTO `pedido_web` (`id_pedido_web`, `numero_orden`, `id_cliente`, `fecha_entrega`, `franja`, `ciudad`, `direccion`, `barrio`, `indicaciones`, `dedicatoria`, `telefono_contacto`, `subtotal`, `costo_envio`, `total`, `correo_factura`, `factura_enviada`, `fecha_creacion`, `estado`) VALUES
(6, 'DM-20260922-042D', 10, '2026-09-27', 'manana', 'Bogotá D.C.', 'Calle 6b #3-47', 'Belen', 'APT  102', 'FELIZ CUMPLE', '3107538847', 120000.00, 8000.00, 128000.00, 'maria.bruno.mona@gmail.com', 1, '2026-09-22 18:19:38', 1),
(7, 'DM-20260922-C70E', 11, '2026-09-23', 'manana', 'Bogotá D.C.', 'Calle 6b #3-47', 'Belen', 'APT 102', 'NA', '3104746216', 20000.00, 8000.00, 28000.00, 'd.jcc.juan.velasco@gmail.com', 1, '2026-09-22 23:35:27', 1),
(8, 'DM-20260922-CD85', 11, '2026-09-23', 'manana', 'Bogotá D.C.', 'Calle 6b #3-47', 'Belen', '', '', '3104746216', 360000.00, 0.00, 360000.00, 'd.jcc.juan.velasco@gmail.com', 1, '2026-09-22 23:41:44', 1),
(9, 'DM-20260923-01A7', 11, '2026-09-24', 'manana', 'Bogotá D.C.', 'Calle 6b #3-47', 'Belen', '', 'NA', '3104746216', 20000.00, 8000.00, 28000.00, 'd.jcc.juan.velasco@gmail.com', 1, '2026-09-23 12:07:49', 1),
(10, 'DM-20260923-CF39', 11, '2026-09-24', 'manana', 'Bogotá D.C.', 'Calle 6b #3-47', 'Belen', '', '', '3104746216', 20000.00, 8000.00, 28000.00, 'd.jcc.juan.velasco@gmail.com', 1, '2026-09-23 20:01:36', 1),
(11, 'DM-20260924-C168', 11, '2026-09-27', 'tarde', 'Bogotá D.C.', 'Calle 6b #3-47', 'Belen', 'Dejar en APTO 102', 'Feliz Cumpleaños Juan', '3104746216', 40000.00, 8000.00, 48000.00, 'd.jcc.juan.velasco@gmail.com', 1, '2026-09-24 15:28:03', 1),
(12, 'DM-20260924-4416', 11, '2026-09-25', 'manana', 'Bogotá D.C.', 'Calle 6b #3-47', 'Belen', '', '', '3104746216', 12000.00, 8000.00, 20000.00, 'd.jcc.juan.velasco@gmail.com', 1, '2026-09-24 19:47:37', 1);

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre_producto`, `descripcion`, `id_categoria_postre`, `id_tortas`, `id_sabor`, `id_tamano`, `precio_base`, `imagen`, `estado`, `fecha_registro`) VALUES
(1, 'Postre Individual - Chocolate - Individual', 'Chocolate - Individual - 1 porciones - 150 gramos', 3, NULL, 1, 1, 12000.00, NULL, 1, '2026-09-22 22:22:52'),
(2, 'Torta - Chocolate - 1/4 Kilo', 'Chocolate - 1/4 Kilo - 3 porciones - 250 gramos', 1, NULL, 1, 2, 36000.00, NULL, 1, '2026-09-22 22:22:52'),
(3, 'Torta - Chocolate - 1/2 Kilo', 'Chocolate - 1/2 Kilo - 5 porciones - 500 gramos', 1, NULL, 1, 3, 60000.00, NULL, 1, '2026-09-22 22:22:52'),
(4, 'Torta - Chocolate - 1 Kilo', 'Chocolate - 1 Kilo - 10 porciones - 1000 gramos', 1, NULL, 1, 4, 120000.00, NULL, 1, '2026-09-22 22:22:52'),
(5, 'Torta - Chocolate - 2 Kilos', 'Chocolate - 2 Kilos - 20 porciones - 2000 gramos', 1, NULL, 1, 5, 240000.00, NULL, 1, '2026-09-22 22:22:52'),
(6, 'Torta - Chocolate - Familiar', 'Chocolate - Familiar - 30 porciones - 3000 gramos', 1, NULL, 1, 6, 360000.00, NULL, 1, '2026-09-22 22:22:52'),
(7, 'Postre Individual - Vainilla - Individual', 'Vainilla - Individual - 1 porciones - 150 gramos', 3, NULL, 2, 1, 12000.00, NULL, 1, '2026-09-22 22:22:52'),
(8, 'Torta - Vainilla - 1/4 Kilo', 'Vainilla - 1/4 Kilo - 3 porciones - 250 gramos', 1, NULL, 2, 2, 36000.00, NULL, 1, '2026-09-22 22:22:52'),
(9, 'Torta - Vainilla - 1/2 Kilo', 'Vainilla - 1/2 Kilo - 5 porciones - 500 gramos', 1, NULL, 2, 3, 60000.00, NULL, 1, '2026-09-22 22:22:52'),
(10, 'Torta - Vainilla - 1 Kilo', 'Vainilla - 1 Kilo - 10 porciones - 1000 gramos', 1, NULL, 2, 4, 120000.00, NULL, 1, '2026-09-22 22:22:52'),
(11, 'Torta - Vainilla - 2 Kilos', 'Vainilla - 2 Kilos - 20 porciones - 2000 gramos', 1, NULL, 2, 5, 240000.00, NULL, 1, '2026-09-22 22:22:52'),
(12, 'Torta - Vainilla - Familiar', 'Vainilla - Familiar - 30 porciones - 3000 gramos', 1, NULL, 2, 6, 360000.00, NULL, 1, '2026-09-22 22:22:52'),
(13, 'Postre Individual - Red Velvet - Individual', 'Red Velvet - Individual - 1 porciones - 150 gramos', 3, NULL, 3, 1, 12000.00, NULL, 1, '2026-09-22 22:22:52'),
(14, 'Torta - Red Velvet - 1/4 Kilo', 'Red Velvet - 1/4 Kilo - 3 porciones - 250 gramos', 1, NULL, 3, 2, 36000.00, NULL, 1, '2026-09-22 22:22:52'),
(15, 'Torta - Red Velvet - 1/2 Kilo', 'Red Velvet - 1/2 Kilo - 5 porciones - 500 gramos', 1, NULL, 3, 3, 60000.00, NULL, 1, '2026-09-22 22:22:52'),
(16, 'Torta - Red Velvet - 1 Kilo', 'Red Velvet - 1 Kilo - 10 porciones - 1000 gramos', 1, NULL, 3, 4, 120000.00, NULL, 1, '2026-09-22 22:22:52'),
(17, 'Torta - Red Velvet - 2 Kilos', 'Red Velvet - 2 Kilos - 20 porciones - 2000 gramos', 1, NULL, 3, 5, 240000.00, NULL, 1, '2026-09-22 22:22:52'),
(18, 'Torta - Red Velvet - Familiar', 'Red Velvet - Familiar - 30 porciones - 3000 gramos', 1, NULL, 3, 6, 360000.00, NULL, 1, '2026-09-22 22:22:52'),
(19, 'Postre Individual - Fresa - Individual', 'Fresa - Individual - 1 porciones - 150 gramos', 3, NULL, 4, 1, 12000.00, NULL, 1, '2026-09-22 22:22:52'),
(20, 'Torta - Fresa - 1/4 Kilo', 'Fresa - 1/4 Kilo - 3 porciones - 250 gramos', 1, NULL, 4, 2, 36000.00, NULL, 1, '2026-09-22 22:22:52'),
(21, 'Torta - Fresa - 1/2 Kilo', 'Fresa - 1/2 Kilo - 5 porciones - 500 gramos', 1, NULL, 4, 3, 60000.00, NULL, 1, '2026-09-22 22:22:52'),
(22, 'Torta - Fresa - 1 Kilo', 'Fresa - 1 Kilo - 10 porciones - 1000 gramos', 1, NULL, 4, 4, 120000.00, NULL, 1, '2026-09-22 22:22:52'),
(23, 'Torta - Fresa - 2 Kilos', 'Fresa - 2 Kilos - 20 porciones - 2000 gramos', 1, NULL, 4, 5, 240000.00, NULL, 1, '2026-09-22 22:22:52'),
(24, 'Torta - Fresa - Familiar', 'Fresa - Familiar - 30 porciones - 3000 gramos', 1, NULL, 4, 6, 360000.00, NULL, 1, '2026-09-22 22:22:52'),
(25, 'Postre Individual - Maracuyá - Individual', 'Maracuyá - Individual - 1 porciones - 150 gramos', 3, NULL, 5, 1, 12000.00, NULL, 1, '2026-09-22 22:22:52'),
(26, 'Torta - Maracuyá - 1/4 Kilo', 'Maracuyá - 1/4 Kilo - 3 porciones - 250 gramos', 1, NULL, 5, 2, 36000.00, NULL, 1, '2026-09-22 22:22:52'),
(27, 'Torta - Maracuyá - 1/2 Kilo', 'Maracuyá - 1/2 Kilo - 5 porciones - 500 gramos', 1, NULL, 5, 3, 60000.00, NULL, 1, '2026-09-22 22:22:52'),
(28, 'Torta - Maracuyá - 1 Kilo', 'Maracuyá - 1 Kilo - 10 porciones - 1000 gramos', 1, NULL, 5, 4, 120000.00, NULL, 1, '2026-09-22 22:22:52'),
(29, 'Torta - Maracuyá - 2 Kilos', 'Maracuyá - 2 Kilos - 20 porciones - 2000 gramos', 1, NULL, 5, 5, 240000.00, NULL, 1, '2026-09-22 22:22:52'),
(30, 'Torta - Maracuyá - Familiar', 'Maracuyá - Familiar - 30 porciones - 3000 gramos', 1, NULL, 5, 6, 360000.00, NULL, 1, '2026-09-22 22:22:52'),
(31, 'Postre Individual - Arequipe - Individual', 'Arequipe - Individual - 1 porciones - 150 gramos', 3, NULL, 6, 1, 12000.00, NULL, 1, '2026-09-22 22:22:52'),
(32, 'Torta - Arequipe - 1/4 Kilo', 'Arequipe - 1/4 Kilo - 3 porciones - 250 gramos', 1, NULL, 6, 2, 36000.00, NULL, 1, '2026-09-22 22:22:52'),
(33, 'Torta - Arequipe - 1/2 Kilo', 'Arequipe - 1/2 Kilo - 5 porciones - 500 gramos', 1, NULL, 6, 3, 60000.00, NULL, 1, '2026-09-22 22:22:52'),
(34, 'Torta - Arequipe - 1 Kilo', 'Arequipe - 1 Kilo - 10 porciones - 1000 gramos', 1, NULL, 6, 4, 120000.00, NULL, 1, '2026-09-22 22:22:52'),
(35, 'Torta - Arequipe - 2 Kilos', 'Arequipe - 2 Kilos - 20 porciones - 2000 gramos', 1, NULL, 6, 5, 240000.00, NULL, 1, '2026-09-22 22:22:52'),
(36, 'Torta - Arequipe - Familiar', 'Arequipe - Familiar - 30 porciones - 3000 gramos', 1, NULL, 6, 6, 360000.00, NULL, 1, '2026-09-22 22:22:52'),
(67, 'Postre en Vaso - Chocolate - Individual', 'Chocolate - Individual - 1 porciones - 200 gramos', 3, NULL, 1, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(68, 'Postre en Vaso - Vainilla - Individual', 'Vainilla - Individual - 1 porciones - 200 gramos', 3, NULL, 2, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(69, 'Postre en Vaso - Red Velvet - Individual', 'Red Velvet - Individual - 1 porciones - 200 gramos', 3, NULL, 3, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(70, 'Postre en Vaso - Fresa - Individual', 'Fresa - Individual - 1 porciones - 200 gramos', 3, NULL, 4, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(71, 'Postre en Vaso - Maracuyá - Individual', 'Maracuyá - Individual - 1 porciones - 200 gramos', 3, NULL, 5, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(72, 'Postre en Vaso - Arequipe - Individual', 'Arequipe - Individual - 1 porciones - 200 gramos', 3, NULL, 6, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(73, 'Cheesecake Individual - Chocolate', 'Chocolate - Individual - 1 porciones - 200 gramos', 3, NULL, 1, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(74, 'Cheesecake Individual - Vainilla', 'Vainilla - Individual - 1 porciones - 200 gramos', 3, NULL, 2, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(75, 'Cheesecake Individual - Red Velvet', 'Red Velvet - Individual - 1 porciones - 200 gramos', 3, NULL, 3, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(76, 'Cheesecake Individual - Fresa', 'Fresa - Individual - 1 porciones - 200 gramos', 3, NULL, 4, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(77, 'Cheesecake Individual - Maracuyá', 'Maracuyá - Individual - 1 porciones - 200 gramos', 3, NULL, 5, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(78, 'Cheesecake Individual - Arequipe', 'Arequipe - Individual - 1 porciones - 200 gramos', 3, NULL, 6, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(79, 'Brownie con Helado - Chocolate', 'Chocolate - Individual - 1 porciones - 200 gramos', 3, NULL, 1, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(80, 'Brownie con Helado - Vainilla', 'Vainilla - Individual - 1 porciones - 200 gramos', 3, NULL, 2, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(81, 'Brownie con Helado - Red Velvet', 'Red Velvet - Individual - 1 porciones - 200 gramos', 3, NULL, 3, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(82, 'Brownie con Helado - Fresa', 'Fresa - Individual - 1 porciones - 200 gramos', 3, NULL, 4, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(83, 'Brownie con Helado - Maracuyá', 'Maracuyá - Individual - 1 porciones - 200 gramos', 3, NULL, 5, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04'),
(84, 'Brownie con Helado - Arequipe', 'Arequipe - Individual - 1 porciones - 200 gramos', 3, NULL, 6, 1, 20000.00, NULL, 1, '2026-09-23 03:06:04');

--
-- Volcado de datos para la tabla `recetas`
--

INSERT INTO `recetas` (`id_receta`, `id_producto`, `id_materia_prima`, `cantidad`, `estado`) VALUES
(6, 1, 15, 4.50, 1),
(7, 1, 5, 22.50, 1),
(8, 1, 6, 22.50, 1),
(9, 1, 14, 1.50, 1),
(10, 1, 13, 0.45, 1),
(11, 1, 12, 1.50, 1),
(12, 1, 11, 22.50, 1),
(13, 1, 4, 22.50, 1),
(14, 1, 3, 0.60, 1),
(15, 1, 2, 33.00, 1),
(16, 1, 1, 42.00, 1),
(17, 2, 15, 7.50, 1),
(18, 2, 5, 37.50, 1),
(19, 2, 6, 37.50, 1),
(20, 2, 14, 2.50, 1),
(21, 2, 13, 0.75, 1),
(22, 2, 12, 2.50, 1),
(23, 2, 11, 37.50, 1),
(24, 2, 4, 37.50, 1),
(25, 2, 3, 1.00, 1),
(26, 2, 2, 55.00, 1),
(27, 2, 1, 70.00, 1),
(28, 3, 15, 15.00, 1),
(29, 3, 5, 75.00, 1),
(30, 3, 6, 75.00, 1),
(31, 3, 14, 5.00, 1),
(32, 3, 13, 1.50, 1),
(33, 3, 12, 5.00, 1),
(34, 3, 11, 75.00, 1),
(35, 3, 4, 75.00, 1),
(36, 3, 3, 2.00, 1),
(37, 3, 2, 110.00, 1),
(38, 3, 1, 140.00, 1),
(39, 4, 15, 30.00, 1),
(40, 4, 5, 150.00, 1),
(41, 4, 6, 150.00, 1),
(42, 4, 14, 10.00, 1),
(43, 4, 13, 3.00, 1),
(44, 4, 12, 10.00, 1),
(45, 4, 11, 150.00, 1),
(46, 4, 4, 150.00, 1),
(47, 4, 3, 4.00, 1),
(48, 4, 2, 220.00, 1),
(49, 4, 1, 280.00, 1),
(50, 5, 15, 60.00, 1),
(51, 5, 5, 300.00, 1),
(52, 5, 6, 300.00, 1),
(53, 5, 14, 20.00, 1),
(54, 5, 13, 6.00, 1),
(55, 5, 12, 20.00, 1),
(56, 5, 11, 300.00, 1),
(57, 5, 4, 300.00, 1),
(58, 5, 3, 8.00, 1),
(59, 5, 2, 440.00, 1),
(60, 5, 1, 560.00, 1),
(61, 6, 15, 90.00, 1),
(62, 6, 5, 450.00, 1),
(63, 6, 6, 450.00, 1),
(64, 6, 14, 30.00, 1),
(65, 6, 13, 9.00, 1),
(66, 6, 12, 30.00, 1),
(67, 6, 11, 450.00, 1),
(68, 6, 4, 450.00, 1),
(69, 6, 3, 12.00, 1),
(70, 6, 2, 660.00, 1),
(71, 6, 1, 840.00, 1),
(72, 7, 6, 22.50, 1),
(73, 7, 14, 1.50, 1),
(74, 7, 13, 0.45, 1),
(75, 7, 12, 1.50, 1),
(76, 7, 11, 22.50, 1),
(77, 7, 4, 22.50, 1),
(78, 7, 3, 0.60, 1),
(79, 7, 2, 33.00, 1),
(80, 7, 1, 45.00, 1),
(81, 8, 6, 37.50, 1),
(82, 8, 14, 2.50, 1),
(83, 8, 13, 0.75, 1),
(84, 8, 12, 2.50, 1),
(85, 8, 11, 37.50, 1),
(86, 8, 4, 37.50, 1),
(87, 8, 3, 1.00, 1),
(88, 8, 2, 55.00, 1),
(89, 8, 1, 75.00, 1),
(90, 9, 6, 75.00, 1),
(91, 9, 14, 5.00, 1),
(92, 9, 13, 1.50, 1),
(93, 9, 12, 5.00, 1),
(94, 9, 11, 75.00, 1),
(95, 9, 4, 75.00, 1),
(96, 9, 3, 2.00, 1),
(97, 9, 2, 110.00, 1),
(98, 9, 1, 150.00, 1),
(99, 10, 6, 150.00, 1),
(100, 10, 14, 10.00, 1),
(101, 10, 13, 3.00, 1),
(102, 10, 12, 10.00, 1),
(103, 10, 11, 150.00, 1),
(104, 10, 4, 150.00, 1),
(105, 10, 3, 4.00, 1),
(106, 10, 2, 220.00, 1),
(107, 10, 1, 300.00, 1),
(108, 11, 6, 300.00, 1),
(109, 11, 14, 20.00, 1),
(110, 11, 13, 6.00, 1),
(111, 11, 12, 20.00, 1),
(112, 11, 11, 300.00, 1),
(113, 11, 4, 300.00, 1),
(114, 11, 3, 8.00, 1),
(115, 11, 2, 440.00, 1),
(116, 11, 1, 600.00, 1),
(117, 12, 6, 450.00, 1),
(118, 12, 14, 30.00, 1),
(119, 12, 13, 9.00, 1),
(120, 12, 12, 30.00, 1),
(121, 12, 11, 450.00, 1),
(122, 12, 4, 450.00, 1),
(123, 12, 3, 12.00, 1),
(124, 12, 2, 660.00, 1),
(125, 12, 1, 900.00, 1),
(126, 13, 7, 22.50, 1),
(127, 13, 16, 0.75, 1),
(128, 13, 15, 3.75, 1),
(129, 13, 6, 22.50, 1),
(130, 13, 14, 1.50, 1),
(131, 13, 13, 0.45, 1),
(132, 13, 12, 1.50, 1),
(133, 13, 11, 22.50, 1),
(134, 13, 4, 22.50, 1),
(135, 13, 3, 0.60, 1),
(136, 13, 2, 33.00, 1),
(137, 13, 1, 42.00, 1),
(138, 14, 7, 37.50, 1),
(139, 14, 16, 1.25, 1),
(140, 14, 15, 6.25, 1),
(141, 14, 6, 37.50, 1),
(142, 14, 14, 2.50, 1),
(143, 14, 13, 0.75, 1),
(144, 14, 12, 2.50, 1),
(145, 14, 11, 37.50, 1),
(146, 14, 4, 37.50, 1),
(147, 14, 3, 1.00, 1),
(148, 14, 2, 55.00, 1),
(149, 14, 1, 70.00, 1),
(150, 15, 7, 75.00, 1),
(151, 15, 16, 2.50, 1),
(152, 15, 15, 12.50, 1),
(153, 15, 6, 75.00, 1),
(154, 15, 14, 5.00, 1),
(155, 15, 13, 1.50, 1),
(156, 15, 12, 5.00, 1),
(157, 15, 11, 75.00, 1),
(158, 15, 4, 75.00, 1),
(159, 15, 3, 2.00, 1),
(160, 15, 2, 110.00, 1),
(161, 15, 1, 140.00, 1),
(162, 16, 7, 150.00, 1),
(163, 16, 16, 5.00, 1),
(164, 16, 15, 25.00, 1),
(165, 16, 6, 150.00, 1),
(166, 16, 14, 10.00, 1),
(167, 16, 13, 3.00, 1),
(168, 16, 12, 10.00, 1),
(169, 16, 11, 150.00, 1),
(170, 16, 4, 150.00, 1),
(171, 16, 3, 4.00, 1),
(172, 16, 2, 220.00, 1),
(173, 16, 1, 280.00, 1),
(174, 17, 7, 300.00, 1),
(175, 17, 16, 10.00, 1),
(176, 17, 15, 50.00, 1),
(177, 17, 6, 300.00, 1),
(178, 17, 14, 20.00, 1),
(179, 17, 13, 6.00, 1),
(180, 17, 12, 20.00, 1),
(181, 17, 11, 300.00, 1),
(182, 17, 4, 300.00, 1),
(183, 17, 3, 8.00, 1),
(184, 17, 2, 440.00, 1),
(185, 17, 1, 560.00, 1),
(186, 18, 7, 450.00, 1),
(187, 18, 16, 15.00, 1),
(188, 18, 15, 75.00, 1),
(189, 18, 6, 450.00, 1),
(190, 18, 14, 30.00, 1),
(191, 18, 13, 9.00, 1),
(192, 18, 12, 30.00, 1),
(193, 18, 11, 450.00, 1),
(194, 18, 4, 450.00, 1),
(195, 18, 3, 12.00, 1),
(196, 18, 2, 660.00, 1),
(197, 18, 1, 840.00, 1),
(198, 19, 18, 15.00, 1),
(199, 19, 17, 22.50, 1),
(200, 19, 6, 22.50, 1),
(201, 19, 14, 1.50, 1),
(202, 19, 13, 0.45, 1),
(203, 19, 12, 1.50, 1),
(204, 19, 11, 22.50, 1),
(205, 19, 4, 22.50, 1),
(206, 19, 3, 0.60, 1),
(207, 19, 2, 33.00, 1),
(208, 19, 1, 45.00, 1),
(209, 20, 18, 25.00, 1),
(210, 20, 17, 37.50, 1),
(211, 20, 6, 37.50, 1),
(212, 20, 14, 2.50, 1),
(213, 20, 13, 0.75, 1),
(214, 20, 12, 2.50, 1),
(215, 20, 11, 37.50, 1),
(216, 20, 4, 37.50, 1),
(217, 20, 3, 1.00, 1),
(218, 20, 2, 55.00, 1),
(219, 20, 1, 75.00, 1),
(220, 21, 18, 50.00, 1),
(221, 21, 17, 75.00, 1),
(222, 21, 6, 75.00, 1),
(223, 21, 14, 5.00, 1),
(224, 21, 13, 1.50, 1),
(225, 21, 12, 5.00, 1),
(226, 21, 11, 75.00, 1),
(227, 21, 4, 75.00, 1),
(228, 21, 3, 2.00, 1),
(229, 21, 2, 110.00, 1),
(230, 21, 1, 150.00, 1),
(231, 22, 18, 100.00, 1),
(232, 22, 17, 150.00, 1),
(233, 22, 6, 150.00, 1),
(234, 22, 14, 10.00, 1),
(235, 22, 13, 3.00, 1),
(236, 22, 12, 10.00, 1),
(237, 22, 11, 150.00, 1),
(238, 22, 4, 150.00, 1),
(239, 22, 3, 4.00, 1),
(240, 22, 2, 220.00, 1),
(241, 22, 1, 300.00, 1),
(242, 23, 18, 200.00, 1),
(243, 23, 17, 300.00, 1),
(244, 23, 6, 300.00, 1),
(245, 23, 14, 20.00, 1),
(246, 23, 13, 6.00, 1),
(247, 23, 12, 20.00, 1),
(248, 23, 11, 300.00, 1),
(249, 23, 4, 300.00, 1),
(250, 23, 3, 8.00, 1),
(251, 23, 2, 440.00, 1),
(252, 23, 1, 600.00, 1),
(253, 24, 18, 300.00, 1),
(254, 24, 17, 450.00, 1),
(255, 24, 6, 450.00, 1),
(256, 24, 14, 30.00, 1),
(257, 24, 13, 9.00, 1),
(258, 24, 12, 30.00, 1),
(259, 24, 11, 450.00, 1),
(260, 24, 4, 450.00, 1),
(261, 24, 3, 12.00, 1),
(262, 24, 2, 660.00, 1),
(263, 24, 1, 900.00, 1),
(264, 25, 20, 15.00, 1),
(265, 25, 19, 18.00, 1),
(266, 25, 6, 22.50, 1),
(267, 25, 14, 1.50, 1),
(268, 25, 13, 0.45, 1),
(269, 25, 12, 1.50, 1),
(270, 25, 11, 22.50, 1),
(271, 25, 4, 22.50, 1),
(272, 25, 3, 0.60, 1),
(273, 25, 2, 33.00, 1),
(274, 25, 1, 45.00, 1),
(275, 26, 20, 25.00, 1),
(276, 26, 19, 30.00, 1),
(277, 26, 6, 37.50, 1),
(278, 26, 14, 2.50, 1),
(279, 26, 13, 0.75, 1),
(280, 26, 12, 2.50, 1),
(281, 26, 11, 37.50, 1),
(282, 26, 4, 37.50, 1),
(283, 26, 3, 1.00, 1),
(284, 26, 2, 55.00, 1),
(285, 26, 1, 75.00, 1),
(286, 27, 20, 50.00, 1),
(287, 27, 19, 60.00, 1),
(288, 27, 6, 75.00, 1),
(289, 27, 14, 5.00, 1),
(290, 27, 13, 1.50, 1),
(291, 27, 12, 5.00, 1),
(292, 27, 11, 75.00, 1),
(293, 27, 4, 75.00, 1),
(294, 27, 3, 2.00, 1),
(295, 27, 2, 110.00, 1),
(296, 27, 1, 150.00, 1),
(297, 28, 20, 100.00, 1),
(298, 28, 19, 120.00, 1),
(299, 28, 6, 150.00, 1),
(300, 28, 14, 10.00, 1),
(301, 28, 13, 3.00, 1),
(302, 28, 12, 10.00, 1),
(303, 28, 11, 150.00, 1),
(304, 28, 4, 150.00, 1),
(305, 28, 3, 4.00, 1),
(306, 28, 2, 220.00, 1),
(307, 28, 1, 300.00, 1),
(308, 29, 20, 200.00, 1),
(309, 29, 19, 240.00, 1),
(310, 29, 6, 300.00, 1),
(311, 29, 14, 20.00, 1),
(312, 29, 13, 6.00, 1),
(313, 29, 12, 20.00, 1),
(314, 29, 11, 300.00, 1),
(315, 29, 4, 300.00, 1),
(316, 29, 3, 8.00, 1),
(317, 29, 2, 440.00, 1),
(318, 29, 1, 600.00, 1),
(319, 30, 20, 300.00, 1),
(320, 30, 19, 360.00, 1),
(321, 30, 6, 450.00, 1),
(322, 30, 14, 30.00, 1),
(323, 30, 13, 9.00, 1),
(324, 30, 12, 30.00, 1),
(325, 30, 11, 450.00, 1),
(326, 30, 4, 450.00, 1),
(327, 30, 3, 12.00, 1),
(328, 30, 2, 660.00, 1),
(329, 30, 1, 900.00, 1),
(330, 31, 21, 27.00, 1),
(331, 31, 6, 22.50, 1),
(332, 31, 14, 1.50, 1),
(333, 31, 13, 0.45, 1),
(334, 31, 12, 1.50, 1),
(335, 31, 11, 22.50, 1),
(336, 31, 4, 22.50, 1),
(337, 31, 3, 0.60, 1),
(338, 31, 2, 33.00, 1),
(339, 31, 1, 45.00, 1),
(340, 32, 21, 45.00, 1),
(341, 32, 6, 37.50, 1),
(342, 32, 14, 2.50, 1),
(343, 32, 13, 0.75, 1),
(344, 32, 12, 2.50, 1),
(345, 32, 11, 37.50, 1),
(346, 32, 4, 37.50, 1),
(347, 32, 3, 1.00, 1),
(348, 32, 2, 55.00, 1),
(349, 32, 1, 75.00, 1),
(350, 33, 21, 90.00, 1),
(351, 33, 6, 75.00, 1),
(352, 33, 14, 5.00, 1),
(353, 33, 13, 1.50, 1),
(354, 33, 12, 5.00, 1),
(355, 33, 11, 75.00, 1),
(356, 33, 4, 75.00, 1),
(357, 33, 3, 2.00, 1),
(358, 33, 2, 110.00, 1),
(359, 33, 1, 150.00, 1),
(360, 34, 21, 180.00, 1),
(361, 34, 6, 150.00, 1),
(362, 34, 14, 10.00, 1),
(363, 34, 13, 3.00, 1),
(364, 34, 12, 10.00, 1),
(365, 34, 11, 150.00, 1),
(366, 34, 4, 150.00, 1),
(367, 34, 3, 4.00, 1),
(368, 34, 2, 220.00, 1),
(369, 34, 1, 300.00, 1),
(370, 35, 21, 360.00, 1),
(371, 35, 6, 300.00, 1),
(372, 35, 14, 20.00, 1),
(373, 35, 13, 6.00, 1),
(374, 35, 12, 20.00, 1),
(375, 35, 11, 300.00, 1),
(376, 35, 4, 300.00, 1),
(377, 35, 3, 8.00, 1),
(378, 35, 2, 440.00, 1),
(379, 35, 1, 600.00, 1),
(380, 36, 21, 540.00, 1),
(381, 36, 6, 450.00, 1),
(382, 36, 14, 30.00, 1),
(383, 36, 13, 9.00, 1),
(384, 36, 12, 30.00, 1),
(385, 36, 11, 450.00, 1),
(386, 36, 4, 450.00, 1),
(387, 36, 3, 12.00, 1),
(388, 36, 2, 660.00, 1),
(389, 36, 1, 900.00, 1),
(517, 67, 6, 30.00, 1),
(518, 67, 8, 15.00, 1),
(519, 67, 2, 10.00, 1),
(520, 67, 11, 10.00, 1),
(521, 67, 5, 15.00, 1),
(522, 67, 15, 3.00, 1),
(523, 68, 6, 30.00, 1),
(524, 68, 8, 15.00, 1),
(525, 68, 2, 10.00, 1),
(526, 68, 11, 10.00, 1),
(527, 68, 14, 2.00, 1),
(528, 69, 6, 30.00, 1),
(529, 69, 8, 15.00, 1),
(530, 69, 2, 10.00, 1),
(531, 69, 11, 10.00, 1),
(532, 69, 15, 3.00, 1),
(533, 69, 16, 0.50, 1),
(534, 70, 6, 30.00, 1),
(535, 70, 8, 15.00, 1),
(536, 70, 2, 10.00, 1),
(537, 70, 11, 10.00, 1),
(538, 70, 17, 15.00, 1),
(539, 70, 18, 10.00, 1),
(540, 71, 6, 30.00, 1),
(541, 71, 8, 15.00, 1),
(542, 71, 2, 10.00, 1),
(543, 71, 11, 10.00, 1),
(544, 71, 19, 15.00, 1),
(545, 71, 20, 10.00, 1),
(546, 72, 6, 30.00, 1),
(547, 72, 8, 15.00, 1),
(548, 72, 2, 10.00, 1),
(549, 72, 11, 10.00, 1),
(550, 72, 21, 20.00, 1),
(551, 73, 7, 25.00, 1),
(552, 73, 8, 10.00, 1),
(553, 73, 4, 5.00, 1),
(554, 73, 2, 8.00, 1),
(555, 73, 3, 0.30, 1),
(556, 73, 5, 15.00, 1),
(557, 73, 15, 3.00, 1),
(558, 74, 7, 25.00, 1),
(559, 74, 8, 10.00, 1),
(560, 74, 4, 5.00, 1),
(561, 74, 2, 8.00, 1),
(562, 74, 3, 0.30, 1),
(563, 74, 14, 2.00, 1),
(564, 75, 7, 25.00, 1),
(565, 75, 8, 10.00, 1),
(566, 75, 4, 5.00, 1),
(567, 75, 2, 8.00, 1),
(568, 75, 3, 0.30, 1),
(569, 75, 15, 3.00, 1),
(570, 75, 16, 0.50, 1),
(571, 76, 7, 25.00, 1),
(572, 76, 8, 10.00, 1),
(573, 76, 4, 5.00, 1),
(574, 76, 2, 8.00, 1),
(575, 76, 3, 0.30, 1),
(576, 76, 17, 15.00, 1),
(577, 76, 18, 10.00, 1),
(578, 77, 7, 25.00, 1),
(579, 77, 8, 10.00, 1),
(580, 77, 4, 5.00, 1),
(581, 77, 2, 8.00, 1),
(582, 77, 3, 0.30, 1),
(583, 77, 19, 15.00, 1),
(584, 77, 20, 10.00, 1),
(585, 78, 7, 25.00, 1),
(586, 78, 8, 10.00, 1),
(587, 78, 4, 5.00, 1),
(588, 78, 2, 8.00, 1),
(589, 78, 3, 0.30, 1),
(590, 78, 21, 20.00, 1),
(591, 79, 1, 20.00, 1),
(592, 79, 2, 15.00, 1),
(593, 79, 3, 0.30, 1),
(594, 79, 4, 10.00, 1),
(595, 79, 15, 8.00, 1),
(596, 79, 22, 0.05, 1),
(597, 79, 5, 10.00, 1),
(598, 80, 1, 20.00, 1),
(599, 80, 2, 15.00, 1),
(600, 80, 3, 0.30, 1),
(601, 80, 4, 10.00, 1),
(602, 80, 15, 8.00, 1),
(603, 80, 22, 0.05, 1),
(604, 80, 14, 2.00, 1),
(605, 81, 1, 20.00, 1),
(606, 81, 2, 15.00, 1),
(607, 81, 3, 0.30, 1),
(608, 81, 4, 10.00, 1),
(609, 81, 15, 8.00, 1),
(610, 81, 22, 0.05, 1),
(611, 81, 16, 0.50, 1),
(612, 82, 1, 20.00, 1),
(613, 82, 2, 15.00, 1),
(614, 82, 3, 0.30, 1),
(615, 82, 4, 10.00, 1),
(616, 82, 15, 8.00, 1),
(617, 82, 22, 0.05, 1),
(618, 82, 17, 10.00, 1),
(619, 82, 18, 8.00, 1),
(620, 83, 1, 20.00, 1),
(621, 83, 2, 15.00, 1),
(622, 83, 3, 0.30, 1),
(623, 83, 4, 10.00, 1),
(624, 83, 15, 8.00, 1),
(625, 83, 22, 0.05, 1),
(626, 83, 19, 10.00, 1),
(627, 83, 20, 8.00, 1),
(628, 84, 1, 20.00, 1),
(629, 84, 2, 15.00, 1),
(630, 84, 3, 0.30, 1),
(631, 84, 4, 10.00, 1),
(632, 84, 15, 8.00, 1),
(633, 84, 22, 0.05, 1),
(634, 84, 21, 15.00, 1);

--
-- Volcado de datos para la tabla `reposteras`
--

INSERT INTO `reposteras` (`id_repostera_asignada`, `nombre_repostera`, `estado`) VALUES
(1, 'Sandra Pérez', 1),
(2, 'Camila Ruiz', 1);

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `estado`) VALUES
(1, 'Administrador', 1),
(2, 'Gerente General', 1),
(3, 'Vendedor', 1),
(4, 'Repostera', 1),
(5, 'Contador', 1),
(6, 'Diseñador', 1),
(7, 'Domiciliario', 1),
(8, 'Auxiliar de Reposteria', 1),
(9, 'Auxuliar de Atencion', 1),
(10, 'Encargado del Inventario', 1);

--
-- Volcado de datos para la tabla `sabor`
--

INSERT INTO `sabor` (`id_sabor`, `sabor`, `estado`) VALUES
(1, 'Chocolate', 1),
(2, 'Vainilla', 1),
(3, 'Red Velvet', 1),
(4, 'Fresa', 1),
(5, 'Maracuyá', 1),
(6, 'Arequipe', 1);

--
-- Volcado de datos para la tabla `tabla_maestra`
--

INSERT INTO `tabla_maestra` (`id_maestro`, `fecha_transaccion`, `id_cliente`, `id_pedido`, `id_producto`, `id_materia_prima`, `id_tortas`, `id_postre_individual`, `cantidad_pedida`, `id_pago`, `id_contabilidad`, `id_estado_pedido`, `estado`) VALUES
(15, '2026-09-22', 1, 24, 83, NULL, NULL, NULL, 5, 18, 20, 5, 1),
(16, '2026-09-22', 4, 25, 2, NULL, NULL, NULL, 5, 19, 21, 5, 1),
(17, '2026-09-23', 11, 26, 75, NULL, NULL, NULL, 1, 20, 22, 1, 1),
(18, '2026-09-23', 11, 27, 24, NULL, NULL, NULL, 1, 21, 23, 1, 1),
(19, '2026-09-23', 11, 28, 75, NULL, NULL, NULL, 1, 22, 24, 1, 1),
(20, '2026-09-23', 11, 29, 75, NULL, NULL, NULL, 1, 23, 25, 5, 1),
(21, '2026-09-23', 11, 30, 76, NULL, NULL, NULL, 1, 24, 26, 1, 1),
(22, '2026-09-24', 10, 31, 6, NULL, NULL, NULL, 5, 25, 27, 5, 1),
(23, '2026-09-24', 11, 32, 69, NULL, NULL, NULL, 2, 26, 28, 1, 1),
(24, '2026-09-24', 2, 33, 1, NULL, NULL, NULL, 4, 27, 29, 5, 1),
(25, '2026-09-24', 11, 34, 1, NULL, NULL, NULL, 1, 28, 30, 1, 1),
(26, '2026-09-24', 6, 35, 1, NULL, NULL, NULL, 1, 29, 31, 5, 1),
(27, '2026-09-24', 11, 36, 80, NULL, NULL, NULL, 1, 30, 32, 5, 1);

--
-- Volcado de datos para la tabla `tamano`
--

INSERT INTO `tamano` (`id_tamano`, `tamano`, `porciones`, `peso_gramos`, `estado`) VALUES
(1, 'Individual', 1, 150, 1),
(2, '1/4 Kilo', 3, 250, 1),
(3, '1/2 Kilo', 5, 500, 1),
(4, '1 Kilo', 10, 1000, 1),
(5, '2 Kilos', 20, 2000, 1),
(6, 'Familiar', 30, 3000, 1);

--
-- Volcado de datos para la tabla `tipo_documento`
--

INSERT INTO `tipo_documento` (`id_tipo_documento`, `tipo_documento`, `estado`) VALUES
(1, 'CC', 1),
(2, 'NIT', 1),
(3, 'PPT', 1);

--
-- Volcado de datos para la tabla `tortas`
--

INSERT INTO `tortas` (`id_tortas`, `tipo_torta`, `estado`) VALUES
(1, 'Torta Clásica', 1),
(2, 'Torta Personalizada', 1);

--
-- Volcado de datos para la tabla `unidad_medida`
--

INSERT INTO `unidad_medida` (`id_unidad_medida`, `unidad_medida`, `estado`) VALUES
(1, 'Gramos', 1),
(2, 'Litros', 1),
(3, 'Unidades', 1),
(4, 'Kilogramos', 1),
(5, 'Mililitros', 1);

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_completo`, `correo_electronico`, `id_roles`, `contrasena_hash`, `fecha_registro`, `estado`, `avatar`) VALUES
(101, 'Juan Velasco', 'd.jcc.juan.velasco@gmail.com', 1, '$2y$10$2SACoMBn/thN26XyOgxSC.dq7XMG/jGtdLqQwWQ4V/zvbBf/mWG76', '2024-10-23', 'ACTIVO', 'undraw_profile_man1.svg'),
(102, 'Valentina Salas', 'valentinasalas@gmail.com', 1, '$2y$10$80aKDNH/x3LX6oPnuhqNwuPmsaILLLoZxBW45iK/rdAP0w43HrQKO', '2026-09-14', 'ACTIVO', 'undraw_profile_woman2.svg'),
(301, 'LAURA SOFIA VELASCO', 'laura.ve@gmail.com', 3, '$2b$10$RYiqXEuzJ/UXIc/yQNc08.4mxO6aFe8.sK9TC5x6C2HzTUYCCjcxW', '2026-09-08', 'ACTIVO', 'undraw_profile_woman1.svg'),
(302, 'MARIA CANO', 'maria.bruno.mona@gmail.com', 3, '$2y$10$JwWgSqzVRJBik04DGdJK2eR5YLoSIlVi.zjI.Es3J5z1J8ofum46S', '2026-09-09', 'ACTIVO', 'undraw_profile_man1.svg');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

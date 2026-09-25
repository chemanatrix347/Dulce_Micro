-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-09-2026 a las 20:58:08
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_postre`
--

CREATE TABLE `categoria_postre` (
  `id_categoria_postre` int(11) NOT NULL,
  `categoria_postre` varchar(100) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `id_tipo_documento` int(11) NOT NULL,
  `numero_documento` varchar(30) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `puntos_dulces` int(11) NOT NULL DEFAULT 0,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `contrasena_hash` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `barrio` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra_materia_prima`
--

CREATE TABLE `compra_materia_prima` (
  `id_compra` int(11) NOT NULL,
  `id_materia_prima` int(11) NOT NULL,
  `cantidad_comprada` decimal(10,2) NOT NULL,
  `costo_total` decimal(10,2) NOT NULL,
  `fecha_compra` date NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `compra_materia_prima`
--
DELIMITER $$
CREATE TRIGGER `trg_compra_materia_prima_egreso` AFTER INSERT ON `compra_materia_prima` FOR EACH ROW BEGIN
  DECLARE v_nombre_insumo VARCHAR(100);

  -- 6.1 Sumar el stock comprado
  UPDATE materia_prima
     SET stock_disponible = stock_disponible + NEW.cantidad_comprada
   WHERE id_materia_prima = NEW.id_materia_prima;

  -- 6.2 Registrar el egreso en contabilidad
  SELECT nombre_insumo INTO v_nombre_insumo
    FROM materia_prima
   WHERE id_materia_prima = NEW.id_materia_prima;

  INSERT INTO contabilidad (tipo_movimiento, monto_transaccion, descripcion_registro, fecha_registro, estado)
  VALUES ('EGRESO', NEW.costo_total, CONCAT('Compra de materia prima - ', v_nombre_insumo), NEW.fecha_compra, 1);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contabilidad`
--

CREATE TABLE `contabilidad` (
  `id_contabilidad` int(11) NOT NULL,
  `tipo_movimiento` varchar(20) NOT NULL,
  `monto_transaccion` decimal(10,2) NOT NULL,
  `descripcion_registro` varchar(255) DEFAULT NULL,
  `fecha_registro` date NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cupones`
--

CREATE TABLE `cupones` (
  `id_cupon` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `tipo_descuento` enum('porcentaje','fijo','envio_gratis') NOT NULL DEFAULT 'porcentaje',
  `valor` decimal(10,2) NOT NULL DEFAULT 20.00,
  `motivo` varchar(100) NOT NULL DEFAULT 'Cumpleaños',
  `fecha_generacion` date NOT NULL,
  `fecha_expiracion` date NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_uso` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `decoracion`
--

CREATE TABLE `decoracion` (
  `id_decoracion` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_pagos`
--

CREATE TABLE `estado_pagos` (
  `id_estado_pago` int(11) NOT NULL,
  `estado_pago` varchar(50) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_pedido`
--

CREATE TABLE `estado_pedido` (
  `id_estado_pedido` int(11) NOT NULL,
  `estado_pedido` varchar(50) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gasto_decoracion`
--

CREATE TABLE `gasto_decoracion` (
  `id_gasto_decoracion` int(11) NOT NULL,
  `id_decoracion` int(11) NOT NULL,
  `numero_diseno` int(11) NOT NULL,
  `nombre_diseno` varchar(150) NOT NULL,
  `fondant_inicial` decimal(10,2) NOT NULL,
  `gasto_fondant` decimal(10,2) NOT NULL,
  `colorante_inicial` decimal(10,2) NOT NULL,
  `gasto_colorante` decimal(10,2) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id_inventario` int(11) NOT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia_prima`
--

CREATE TABLE `materia_prima` (
  `id_materia_prima` int(11) NOT NULL,
  `nombre_insumo` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `stock_disponible` decimal(10,2) NOT NULL,
  `id_unidad_medida` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

CREATE TABLE `metodos_pago` (
  `id_metodos_pago` int(11) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_puntos_dulces`
--

CREATE TABLE `movimientos_puntos_dulces` (
  `id_movimiento` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `puntos` int(11) NOT NULL,
  `tipo` enum('ganados','canjeados') NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pagos` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_metodo_pago` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `id_estado_pago` int(11) NOT NULL,
  `fecha_pago` date NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `pagos`
--
DELIMITER $$
CREATE TRIGGER `trg_pagos_insert_ingreso` AFTER INSERT ON `pagos` FOR EACH ROW BEGIN
  IF NEW.id_estado_pago = 3 THEN
    CALL sp_registrar_ingreso_pago(NEW.id_pagos, NEW.id_pedido, NEW.monto);
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_pagos_update_ingreso` AFTER UPDATE ON `pagos` FOR EACH ROW BEGIN
  IF NEW.id_estado_pago = 3 AND OLD.id_estado_pago <> 3 THEN
    CALL sp_registrar_ingreso_pago(NEW.id_pagos, NEW.id_pedido, NEW.monto);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_completo`
--

CREATE TABLE `pedido_completo` (
  `id_pedido` int(11) NOT NULL,
  `codigo_pedido` varchar(20) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha_pedido` date NOT NULL,
  `fecha_estimada_entrega` date DEFAULT NULL,
  `id_categoria_postre` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `id_tamano` int(11) NOT NULL,
  `id_sabor` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `id_estado_pedido` int(11) NOT NULL,
  `id_metodo_pago` int(11) NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `id_repostera_asignada` int(11) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `id_pedido_web` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_web`
--

CREATE TABLE `pedido_web` (
  `id_pedido_web` int(11) NOT NULL,
  `numero_orden` varchar(20) DEFAULT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha_entrega` date NOT NULL,
  `franja` varchar(10) NOT NULL,
  `ciudad` varchar(80) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `barrio` varchar(100) DEFAULT NULL,
  `indicaciones` varchar(300) DEFAULT NULL,
  `dedicatoria` varchar(300) DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `costo_envio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `correo_factura` varchar(150) NOT NULL,
  `factura_enviada` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postres_individuales`
--

CREATE TABLE `postres_individuales` (
  `id_postre_individual` int(11) NOT NULL,
  `nombre_producto` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `id_categoria_postre` int(11) NOT NULL,
  `id_tortas` int(11) DEFAULT NULL,
  `id_sabor` int(11) NOT NULL,
  `id_tamano` int(11) NOT NULL,
  `precio_base` decimal(10,2) NOT NULL DEFAULT 0.00,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recetas`
--

CREATE TABLE `recetas` (
  `id_receta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_materia_prima` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reposteras`
--

CREATE TABLE `reposteras` (
  `id_repostera_asignada` int(11) NOT NULL,
  `nombre_repostera` varchar(150) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sabor`
--

CREATE TABLE `sabor` (
  `id_sabor` int(11) NOT NULL,
  `sabor` varchar(50) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tabla_maestra`
--

CREATE TABLE `tabla_maestra` (
  `id_maestro` int(11) NOT NULL,
  `fecha_transaccion` date NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_pedido` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `id_materia_prima` int(11) DEFAULT NULL,
  `id_tortas` int(11) DEFAULT NULL,
  `id_postre_individual` int(11) DEFAULT NULL,
  `cantidad_pedida` int(11) NOT NULL,
  `id_pago` int(11) NOT NULL,
  `id_contabilidad` int(11) NOT NULL,
  `id_estado_pedido` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tamano`
--

CREATE TABLE `tamano` (
  `id_tamano` int(11) NOT NULL,
  `tamano` varchar(50) NOT NULL,
  `porciones` int(11) NOT NULL DEFAULT 1,
  `peso_gramos` int(11) NOT NULL DEFAULT 0,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_documento`
--

CREATE TABLE `tipo_documento` (
  `id_tipo_documento` int(11) NOT NULL,
  `tipo_documento` varchar(20) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tortas`
--

CREATE TABLE `tortas` (
  `id_tortas` int(11) NOT NULL,
  `tipo_torta` varchar(100) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidad_medida`
--

CREATE TABLE `unidad_medida` (
  `id_unidad_medida` int(11) NOT NULL,
  `unidad_medida` varchar(50) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `correo_electronico` varchar(150) NOT NULL,
  `id_roles` int(11) NOT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `fecha_registro` date NOT NULL,
  `estado` varchar(20) NOT NULL,
  `avatar` varchar(50) NOT NULL DEFAULT 'undraw_profile_man1.svg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria_postre`
--
ALTER TABLE `categoria_postre`
  ADD PRIMARY KEY (`id_categoria_postre`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `id_tipo_documento` (`id_tipo_documento`);

--
-- Indices de la tabla `compra_materia_prima`
--
ALTER TABLE `compra_materia_prima`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `fk_compra_materia_prima` (`id_materia_prima`);

--
-- Indices de la tabla `contabilidad`
--
ALTER TABLE `contabilidad`
  ADD PRIMARY KEY (`id_contabilidad`);

--
-- Indices de la tabla `cupones`
--
ALTER TABLE `cupones`
  ADD PRIMARY KEY (`id_cupon`),
  ADD KEY `fk_cupones_cliente` (`id_cliente`);

--
-- Indices de la tabla `decoracion`
--
ALTER TABLE `decoracion`
  ADD PRIMARY KEY (`id_decoracion`);

--
-- Indices de la tabla `estado_pagos`
--
ALTER TABLE `estado_pagos`
  ADD PRIMARY KEY (`id_estado_pago`);

--
-- Indices de la tabla `estado_pedido`
--
ALTER TABLE `estado_pedido`
  ADD PRIMARY KEY (`id_estado_pedido`);

--
-- Indices de la tabla `gasto_decoracion`
--
ALTER TABLE `gasto_decoracion`
  ADD PRIMARY KEY (`id_gasto_decoracion`),
  ADD KEY `id_decoracion` (`id_decoracion`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id_inventario`),
  ADD KEY `fk_inventario_producto` (`id_producto`);

--
-- Indices de la tabla `materia_prima`
--
ALTER TABLE `materia_prima`
  ADD PRIMARY KEY (`id_materia_prima`),
  ADD KEY `id_unidad_medida` (`id_unidad_medida`);

--
-- Indices de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD PRIMARY KEY (`id_metodos_pago`);

--
-- Indices de la tabla `movimientos_puntos_dulces`
--
ALTER TABLE `movimientos_puntos_dulces`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `fk_movpuntos_cliente` (`id_cliente`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pagos`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_metodo_pago` (`id_metodo_pago`),
  ADD KEY `id_estado_pago` (`id_estado_pago`);

--
-- Indices de la tabla `pedido_completo`
--
ALTER TABLE `pedido_completo`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_categoria_postre` (`id_categoria_postre`),
  ADD KEY `id_tamano` (`id_tamano`),
  ADD KEY `id_sabor` (`id_sabor`),
  ADD KEY `id_estado_pedido` (`id_estado_pedido`),
  ADD KEY `id_metodo_pago` (`id_metodo_pago`),
  ADD KEY `id_repostera_asignada` (`id_repostera_asignada`),
  ADD KEY `fk_pedido_producto` (`id_producto`),
  ADD KEY `id_pedido_web` (`id_pedido_web`);

--
-- Indices de la tabla `pedido_web`
--
ALTER TABLE `pedido_web`
  ADD PRIMARY KEY (`id_pedido_web`),
  ADD UNIQUE KEY `numero_orden` (`numero_orden`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `postres_individuales`
--
ALTER TABLE `postres_individuales`
  ADD PRIMARY KEY (`id_postre_individual`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `idx_productos_categoria` (`id_categoria_postre`),
  ADD KEY `idx_productos_sabor` (`id_sabor`),
  ADD KEY `idx_productos_tamano` (`id_tamano`),
  ADD KEY `idx_productos_tortas` (`id_tortas`);

--
-- Indices de la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD PRIMARY KEY (`id_receta`),
  ADD KEY `idx_recetas_producto` (`id_producto`),
  ADD KEY `idx_recetas_materia_prima` (`id_materia_prima`);

--
-- Indices de la tabla `reposteras`
--
ALTER TABLE `reposteras`
  ADD PRIMARY KEY (`id_repostera_asignada`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `sabor`
--
ALTER TABLE `sabor`
  ADD PRIMARY KEY (`id_sabor`);

--
-- Indices de la tabla `tabla_maestra`
--
ALTER TABLE `tabla_maestra`
  ADD PRIMARY KEY (`id_maestro`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_materia_prima` (`id_materia_prima`),
  ADD KEY `id_tortas` (`id_tortas`),
  ADD KEY `id_postre_individual` (`id_postre_individual`),
  ADD KEY `id_pago` (`id_pago`),
  ADD KEY `id_contabilidad` (`id_contabilidad`),
  ADD KEY `id_estado_pedido` (`id_estado_pedido`),
  ADD KEY `fk_maestro_pedido` (`id_pedido`),
  ADD KEY `fk_tabla_maestra_producto` (`id_producto`);

--
-- Indices de la tabla `tamano`
--
ALTER TABLE `tamano`
  ADD PRIMARY KEY (`id_tamano`);

--
-- Indices de la tabla `tipo_documento`
--
ALTER TABLE `tipo_documento`
  ADD PRIMARY KEY (`id_tipo_documento`);

--
-- Indices de la tabla `tortas`
--
ALTER TABLE `tortas`
  ADD PRIMARY KEY (`id_tortas`);

--
-- Indices de la tabla `unidad_medida`
--
ALTER TABLE `unidad_medida`
  ADD PRIMARY KEY (`id_unidad_medida`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `id_roles` (`id_roles`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria_postre`
--
ALTER TABLE `categoria_postre`
  MODIFY `id_categoria_postre` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compra_materia_prima`
--
ALTER TABLE `compra_materia_prima`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contabilidad`
--
ALTER TABLE `contabilidad`
  MODIFY `id_contabilidad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cupones`
--
ALTER TABLE `cupones`
  MODIFY `id_cupon` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `decoracion`
--
ALTER TABLE `decoracion`
  MODIFY `id_decoracion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado_pagos`
--
ALTER TABLE `estado_pagos`
  MODIFY `id_estado_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado_pedido`
--
ALTER TABLE `estado_pedido`
  MODIFY `id_estado_pedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gasto_decoracion`
--
ALTER TABLE `gasto_decoracion`
  MODIFY `id_gasto_decoracion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id_inventario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `materia_prima`
--
ALTER TABLE `materia_prima`
  MODIFY `id_materia_prima` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  MODIFY `id_metodos_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_puntos_dulces`
--
ALTER TABLE `movimientos_puntos_dulces`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pagos` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedido_completo`
--
ALTER TABLE `pedido_completo`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedido_web`
--
ALTER TABLE `pedido_web`
  MODIFY `id_pedido_web` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `postres_individuales`
--
ALTER TABLE `postres_individuales`
  MODIFY `id_postre_individual` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recetas`
--
ALTER TABLE `recetas`
  MODIFY `id_receta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reposteras`
--
ALTER TABLE `reposteras`
  MODIFY `id_repostera_asignada` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sabor`
--
ALTER TABLE `sabor`
  MODIFY `id_sabor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tabla_maestra`
--
ALTER TABLE `tabla_maestra`
  MODIFY `id_maestro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tamano`
--
ALTER TABLE `tamano`
  MODIFY `id_tamano` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_documento`
--
ALTER TABLE `tipo_documento`
  MODIFY `id_tipo_documento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tortas`
--
ALTER TABLE `tortas`
  MODIFY `id_tortas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `unidad_medida`
--
ALTER TABLE `unidad_medida`
  MODIFY `id_unidad_medida` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`id_tipo_documento`) REFERENCES `tipo_documento` (`id_tipo_documento`);

--
-- Filtros para la tabla `compra_materia_prima`
--
ALTER TABLE `compra_materia_prima`
  ADD CONSTRAINT `fk_compra_materia_prima` FOREIGN KEY (`id_materia_prima`) REFERENCES `materia_prima` (`id_materia_prima`);

--
-- Filtros para la tabla `cupones`
--
ALTER TABLE `cupones`
  ADD CONSTRAINT `fk_cupones_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `gasto_decoracion`
--
ALTER TABLE `gasto_decoracion`
  ADD CONSTRAINT `gasto_decoracion_ibfk_1` FOREIGN KEY (`id_decoracion`) REFERENCES `decoracion` (`id_decoracion`);

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `fk_inventario_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `materia_prima`
--
ALTER TABLE `materia_prima`
  ADD CONSTRAINT `materia_prima_ibfk_1` FOREIGN KEY (`id_unidad_medida`) REFERENCES `unidad_medida` (`id_unidad_medida`);

--
-- Filtros para la tabla `movimientos_puntos_dulces`
--
ALTER TABLE `movimientos_puntos_dulces`
  ADD CONSTRAINT `fk_movpuntos_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido_completo` (`id_pedido`),
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodos_pago` (`id_metodos_pago`),
  ADD CONSTRAINT `pagos_ibfk_3` FOREIGN KEY (`id_estado_pago`) REFERENCES `estado_pagos` (`id_estado_pago`);

--
-- Filtros para la tabla `pedido_completo`
--
ALTER TABLE `pedido_completo`
  ADD CONSTRAINT `fk_pedido_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pedido_completo_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `pedido_completo_ibfk_2` FOREIGN KEY (`id_categoria_postre`) REFERENCES `categoria_postre` (`id_categoria_postre`),
  ADD CONSTRAINT `pedido_completo_ibfk_3` FOREIGN KEY (`id_tamano`) REFERENCES `tamano` (`id_tamano`),
  ADD CONSTRAINT `pedido_completo_ibfk_4` FOREIGN KEY (`id_sabor`) REFERENCES `sabor` (`id_sabor`),
  ADD CONSTRAINT `pedido_completo_ibfk_5` FOREIGN KEY (`id_estado_pedido`) REFERENCES `estado_pedido` (`id_estado_pedido`),
  ADD CONSTRAINT `pedido_completo_ibfk_6` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodos_pago` (`id_metodos_pago`),
  ADD CONSTRAINT `pedido_completo_ibfk_7` FOREIGN KEY (`id_repostera_asignada`) REFERENCES `reposteras` (`id_repostera_asignada`),
  ADD CONSTRAINT `pedido_completo_ibfk_8` FOREIGN KEY (`id_pedido_web`) REFERENCES `pedido_web` (`id_pedido_web`);

--
-- Filtros para la tabla `pedido_web`
--
ALTER TABLE `pedido_web`
  ADD CONSTRAINT `pedido_web_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`id_categoria_postre`) REFERENCES `categoria_postre` (`id_categoria_postre`),
  ADD CONSTRAINT `fk_productos_sabor` FOREIGN KEY (`id_sabor`) REFERENCES `sabor` (`id_sabor`),
  ADD CONSTRAINT `fk_productos_tamano` FOREIGN KEY (`id_tamano`) REFERENCES `tamano` (`id_tamano`),
  ADD CONSTRAINT `fk_productos_tortas` FOREIGN KEY (`id_tortas`) REFERENCES `tortas` (`id_tortas`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD CONSTRAINT `fk_recetas_materia_prima` FOREIGN KEY (`id_materia_prima`) REFERENCES `materia_prima` (`id_materia_prima`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recetas_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tabla_maestra`
--
ALTER TABLE `tabla_maestra`
  ADD CONSTRAINT `fk_maestro_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido_completo` (`id_pedido`),
  ADD CONSTRAINT `fk_tabla_maestra_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tabla_maestra_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `tabla_maestra_ibfk_2` FOREIGN KEY (`id_materia_prima`) REFERENCES `materia_prima` (`id_materia_prima`),
  ADD CONSTRAINT `tabla_maestra_ibfk_3` FOREIGN KEY (`id_tortas`) REFERENCES `tortas` (`id_tortas`),
  ADD CONSTRAINT `tabla_maestra_ibfk_4` FOREIGN KEY (`id_postre_individual`) REFERENCES `postres_individuales` (`id_postre_individual`),
  ADD CONSTRAINT `tabla_maestra_ibfk_5` FOREIGN KEY (`id_pago`) REFERENCES `pagos` (`id_pagos`),
  ADD CONSTRAINT `tabla_maestra_ibfk_6` FOREIGN KEY (`id_contabilidad`) REFERENCES `contabilidad` (`id_contabilidad`),
  ADD CONSTRAINT `tabla_maestra_ibfk_7` FOREIGN KEY (`id_estado_pedido`) REFERENCES `estado_pedido` (`id_estado_pedido`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_roles`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-06-2024 a las 23:09:05
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
-- Base de datos: `trabajotienda`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id`, `nombre`) VALUES
(1, 'Productos del Terreno'),
(2, 'Panaderia'),
(4, 'Refrescos'),
(5, 'Bollería'),
(6, 'Droguería');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foto`
--

CREATE TABLE `foto` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `foto`
--

INSERT INTO `foto` (`id`, `usuario_id`, `producto_id`, `nombre`) VALUES
(1, 3, NULL, '6643a67e087ae.jpg'),
(2, 4, NULL, '6643a6a4d8b49.jpg'),
(3, NULL, 1, '6643a783c7099.jpg'),
(4, NULL, 2, '6643acdd986be.jpg'),
(5, NULL, 2, '6643aceb28f64.jpg'),
(86, NULL, 6, '665df12b8bf0b.jpg'),
(88, NULL, 7, '665dfc3478c71.jpg'),
(89, NULL, 8, '665e10c96093e.jpg'),
(90, NULL, 9, '665e11caa794c.jpg'),
(91, NULL, 10, '665e129994b23.jpg'),
(94, NULL, 11, '6664509452841.jpg'),
(95, NULL, 12, '66647fae25e27.jpg'),
(98, NULL, 13, '6664a0b8c3970.png'),
(105, 5, NULL, '666e8fa7ef67f.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineas_venta`
--

CREATE TABLE `lineas_venta` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `cantidad` double NOT NULL,
  `descuento` double DEFAULT NULL,
  `total` double NOT NULL,
  `producto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lineas_venta`
--

INSERT INTO `lineas_venta` (`id`, `pedido_id`, `cantidad`, `descuento`, `total`, `producto_id`) VALUES
(10, 19, 2.5, 5, 4.28, 1),
(11, 19, 1.5, NULL, 2.4, 2),
(12, 19, 2, NULL, 2.4, 11),
(13, 20, 3.5, NULL, 7, 7),
(14, 20, 1.5, NULL, 2.7, 1),
(15, 20, 9.5, NULL, 7.6, 8),
(16, 20, 0.5, NULL, 0.8, 2),
(17, 20, 3, NULL, 3.6, 13),
(21, 22, 6, NULL, 3.6, 6),
(22, 22, 4.5, NULL, 3.6, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensaje`
--

CREATE TABLE `mensaje` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `contenido` varchar(255) NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mensaje`
--

INSERT INTO `mensaje` (`id`, `usuario_id`, `producto_id`, `contenido`, `fecha`) VALUES
(1, 3, 1, 'Muy Buenos tomates.', '2024-06-10 12:59:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `total` double NOT NULL,
  `estado` varchar(255) NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id`, `usuario_id`, `total`, `estado`, `fecha`) VALUES
(19, 3, 9.08, 'Pendiente', '2024-06-14 20:30:10'),
(20, 3, 21.7, 'Listo', '2024-06-15 12:36:36'),
(22, 5, 7.2, 'Pendiente', '2024-06-16 09:10:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `precio` double NOT NULL,
  `descuento` double DEFAULT NULL,
  `unidad_venta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `descuento`, `unidad_venta`) VALUES
(1, 1, 'Tomates', 'Una seleccion de nuestros mejores tomates, cultivados cuidadosamente con nuestra dedicacion y pasion por la agricultura.', 1.8, 5, 'Cantidad'),
(2, 1, 'Pepinos', 'Descubre la frescura inigualable de nuestros pepinos, cuidadosamente cultivados con dedicación y pasión por la agricultura sostenible. Una experiencia única en cada bocado.', 1.6, NULL, 'Cantidad'),
(6, 1, 'Sandias', '¡Descubre la frescura y el sabor de la naturaleza en cada bocado con nuestras deliciosas sandías! Cultivadas con cuidado y amor en nuestros campos, nuestras sandías son un festín para los sentidos. Jugosas, dulces y llenas de vitalidad, cada una de nuestras sandías es un testimonio del compromiso con la calidad y la autenticidad que nos distingue.', 0.6, NULL, 'Cantidad'),
(7, 1, 'Pimientos', 'Agrega un toque de color y sabor a tus comidas con nuestros exquisitos pimientos frescos. Cultivados de manera sostenible en nuestros campos, nuestros pimientos destacan por su textura crujiente y su sabor vibrante. Disponibles en una variedad de colores y tamaños, son ideales para cualquier receta, desde ensaladas y parrilladas hasta guisos y salsas.', 2, NULL, 'Cantidad'),
(8, 1, 'Melones', 'Experimenta la dulzura jugosa y la fragancia embriagadora de nuestros melones frescos. Cosechados en su punto óptimo de madurez y cuidadosamente seleccionados de nuestros campos, cada melón es una obra maestra de sabor y frescura. Ya sea disfrutado solo, en ensaladas refrescantes o como parte de un postre exquisito, nuestros melones son una invitación a un festín sensorial en cada bocado.', 0.8, NULL, 'Cantidad'),
(9, 1, 'Berenjenas', 'Descubre la versatilidad y el sabor único de nuestras berenjenas frescas. Provenientes de nuestros campos cultivados con cariño, estas joyas púrpuras son una delicia para los amantes de la cocina saludable. Ya sea asadas a la parrilla, salteadas con hierbas aromáticas o como ingrediente estrella en tus platos favoritos, nuestras berenjenas son sinónimo de calidad y frescura.', 1.2, NULL, 'Cantidad'),
(10, 1, 'Patatas', 'Sumérgete en la satisfacción reconfortante de nuestras patatas frescas, cultivadas con esmero en nuestros campos. Desde las clásicas papas doradas hasta las cremosas variedades rojas y moradas, cada patata es un tesoro de sabor y versatilidad culinaria. Ya sea asadas al horno, convertidas en puré cremoso o fritas hasta obtener una crujiente perfección, nuestras patatas son el acompañamiento perfecto para cualquier comida.', 1.2, NULL, 'Cantidad'),
(11, 4, 'Coca-Cola (330ml)', 'Disfruta del refrescante y clásico sabor de Coca-Cola en su presentación de 330 ml, perfecta para cualquier momento del día. Con su distintiva efervescencia y su inconfundible sabor, esta lata es ideal para acompañar tus comidas, refrescarte durante una pausa o compartir con amigos. Elaborada con ingredientes de la más alta calidad, Coca-Cola es la bebida que nunca pasa de moda y siempre satisface. ¡Añade esta icónica lata de 330 ml a tu carrito y disfruta del placer burbujeante que solo Coca-Cola puede ofrecer!', 1.2, NULL, 'Unidad'),
(12, 4, 'Fanta Limón (330ml)', 'Disfruta del refrescante sabor cítrico de Fanta Limón en su práctica lata de 330 ml. Perfecta para cualquier ocasión, esta bebida burbujeante combina la dulzura y el toque ácido del limón para ofrecerte una experiencia revitalizante. Ideal para compartir con amigos, acompañar tus comidas favoritas o simplemente disfrutar en un momento de relax. Con Fanta Limón, cada sorbo es una explosión de frescura y sabor que te encantará.', 1.2, NULL, 'Unidad'),
(13, 4, 'Fanta Naranja (330ml)', '¡Disfruta de la chispeante y refrescante Fanta Naranja en su lata de 330 ml! Con su vibrante sabor a naranjas jugosas y su efervescencia característica, esta bebida es perfecta para cualquier ocasión. Ideal para acompañar tus comidas o simplemente para disfrutarla sola, Fanta Naranja te ofrece un toque de frescura y alegría en cada sorbo. Siente la explosión de sabor cítrico y revitaliza tu día con esta clásica y deliciosa bebida.\"', 1.2, NULL, 'Unidad');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `username`, `email`, `password`, `rol`) VALUES
(3, 'marcos', 'marcos@gmail.com', '$2y$13$Mxx7Jw6p0P8n5DOU2lscfOje9chib9NaZPWKu4dMmGdF5DbIKPRou', 'Cliente'),
(4, 'administrador', 'admin@gmail.con', '$2y$13$6n3KQPpCXgULePXoi6eK..oQLlzw/ycWUyNmrJtyOjlj7PkqvC/ry', 'Administrador'),
(5, 'prueba', 'prueba@gmail.com', '$2y$13$y5N.O.pT1U.twcpQ4q1JUuS0m9v2sUTkznhjDebGgC2XmCgypyI/2', 'Cliente');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `foto`
--
ALTER TABLE `foto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_EADC3BE5DB38439E` (`usuario_id`),
  ADD KEY `IDX_EADC3BE57645698E` (`producto_id`);

--
-- Indices de la tabla `lineas_venta`
--
ALTER TABLE `lineas_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_D112768A4854653A` (`pedido_id`),
  ADD KEY `IDX_D112768A7645698E` (`producto_id`);

--
-- Indices de la tabla `mensaje`
--
ALTER TABLE `mensaje`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_9B631D01DB38439E` (`usuario_id`),
  ADD KEY `IDX_9B631D017645698E` (`producto_id`);

--
-- Indices de la tabla `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_C4EC16CEDB38439E` (`usuario_id`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A7BB06153397707A` (`categoria_id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `foto`
--
ALTER TABLE `foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT de la tabla `lineas_venta`
--
ALTER TABLE `lineas_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `mensaje`
--
ALTER TABLE `mensaje`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `foto`
--
ALTER TABLE `foto`
  ADD CONSTRAINT `FK_EADC3BE57645698E` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`),
  ADD CONSTRAINT `FK_EADC3BE5DB38439E` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `lineas_venta`
--
ALTER TABLE `lineas_venta`
  ADD CONSTRAINT `FK_D112768A4854653A` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`),
  ADD CONSTRAINT `FK_D112768A7645698E` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`);

--
-- Filtros para la tabla `mensaje`
--
ALTER TABLE `mensaje`
  ADD CONSTRAINT `FK_9B631D017645698E` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`),
  ADD CONSTRAINT `FK_9B631D01DB38439E` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `FK_C4EC16CEDB38439E` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `FK_A7BB06153397707A` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

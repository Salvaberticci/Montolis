-- Complete Database Setup for Montoli's Inventory System
-- This file creates all necessary tables and inserts sample data

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Create database if it doesn't exist
-- CREATE DATABASE IF NOT EXISTS `montolis_inventory` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
-- USE `montolis_inventory`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'admin',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`username`, `password_hash`, `email`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@montolis.com', 'admin');

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `product_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `wholesale_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `third_party_sale_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `third_party_seller_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `category` varchar(100) NOT NULL DEFAULT 'General',
  `image` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert sample products
INSERT INTO `products` (`id`, `name`, `description`, `quantity`, `product_cost`, `sale_price`, `wholesale_price`, `third_party_sale_price`, `third_party_seller_percentage`, `image`) VALUES
(61, 'TERMO STANLEY', 'Termo de acero inoxidable para mantener bebidas calientes o frías durante horas.', 0, 0.00, 35.00, 30.00, 0.00, 11.43, '68c18b317d636-imagen_2025-09-10_102904962.png'),
(62, 'BOLSO PARA STANLEY', 'Bolso diseñado para transportar termos Stanley de forma segura y cómoda.', 0, 0.00, 10.00, 0.00, 20.00, '68c18b4ac2465-imagen_2025-09-10_102930094.png'),
(63, 'POWER BANK 3 SALIDAS', 'Batería externa portátil con tres puertos de salida para cargar múltiples dispositivos a la vez.', 0, 0.00, 15.00, 0.00, 20.00, '68c18b72b8b92-imagen_2025-09-10_103008756.png'),
(64, 'MOUSE PAD MAPA RGB', 'Alfombrilla de ratón con iluminación LED RGB y un diseño de mapa.', 0, 0.00, 15.00, 0.00, 13.33, '68c18b8ee743c-imagen_2025-09-10_103038122.png'),
(65, 'CARGADOR MI 33W CARGA RAPIDA', 'Cargador de pared de 33W para carga rápida compatible con dispositivos Xiaomi.', 0, 0.00, 8.00, 0.00, 0.00, '68c18bb49e930-imagen_2025-09-10_103115750.png'),
(66, 'CAMARA PANORAMICA', 'Cámara de seguridad con lente gran angular para una visión de 360 grados.', 0, 0.00, 26.00, 0.00, 15.38, '68c191891779b-imagen_2025-09-10_105608564.png'),
(67, 'CAMARA A9', 'Cámara de seguridad compacta y portátil, ideal para vigilancia en interiores.', 0, 0.00, 12.00, 0.00, 25.00, '68c1916c3fb66-imagen_2025-09-10_105539617.png'),
(68, 'RELOJ ULTRA 7 EN 1', 'Reloj inteligente con múltiples funciones, incluyendo monitoreo de salud y notificaciones.', 0, 0.00, 18.00, 0.00, 16.67, '68c18cf2d4962-imagen_2025-09-10_103634341.png'),
(69, 'CABLE ADAPTADOR HDMI', 'Adaptador para conectar dispositivos con puerto USB-C o Micro USB a pantallas con entrada HDMI.', 0, 0.00, 6.00, 0.00, 33.33, '68c18be0c93f1-imagen_2025-09-10_103200242.png'),
(70, 'MOUSE INALAMBRICO', 'Mouse ergonómico que se conecta de forma inalámbrica a la computadora, sin necesidad de cables.', 0, 0.00, 12.00, 0.00, 16.67, '68c19120a37df-imagen_2025-09-10_105423923.png'),
(71, 'LLAVERO CARGADOR IPHONE', 'Llavero que funciona como un cable de carga para dispositivos iPhone.', 0, 0.00, 6.00, 0.00, 16.67, '68c190ec23bcc-imagen_2025-09-10_105331651.png'),
(72, 'AUDIFONOS M41', 'Auriculares inalámbricos con sonido estéreo y estuche de carga, ideales para música y llamadas.', 0, 0.00, 13.00, 0.00, 23.08, '68c18cd519c83-imagen_2025-09-10_103604503.png'),
(73, 'AUDIFONOS AIR 31', 'Auriculares con diseño ergonómico y cancelación de ruido, perfectos para el uso diario.', 0, 0.00, 12.00, 0.00, 16.67, '68c18cb8f0eaa-imagen_2025-09-10_103536292.png'),
(74, 'AUDIFONOS AIR 39', 'Auriculares Bluetooth con sonido de alta fidelidad y batería de larga duración.', 0, 0.00, 15.00, 0.00, 13.33, '68c18ca12af54-imagen_2025-09-10_103512730.png'),
(75, 'CABLE EXTENSIBLE 3 EN 1', 'Cable de carga retráctil con tres conectores diferentes: USB-C, Micro USB y Lightning.', 0, 0.00, 7.00, 0.00, 28.57, '68c18c8347790-imagen_2025-09-10_103442668.png'),
(76, 'ENCENDEDOR LINTERNA', 'Encendedor recargable con una linterna LED integrada, ideal para uso en exteriores.', 0, 0.00, 8.00, 0.00, 25.00, '68c191571157e-imagen_2025-09-10_105518439.png'),
(77, 'REPRODUCTOR CIGARRERA', 'Reproductor de MP3 y radio FM que se conecta al encendedor del coche.', 0, 0.00, 9.00, 0.00, 22.22, '68c1913923be2-imagen_2025-09-10_105448597.png'),
(78, 'LUCES LED H4 EL PAR', 'Focos LED de reemplazo para vehículos, que ofrecen una iluminación más brillante y eficiente.', 0, 0.00, 12.00, 0.00, 16.67, '68c190d2211ff-imagen_2025-09-10_105305592.png'),
(79, 'JUEGO DE DADOS 46PCS.', 'Juego de herramientas con 46 piezas, incluyendo dados, llaves y adaptadores para reparaciones.', 0, 0.00, 12.00, 0.00, 16.67, '68c190b9028b1-imagen_2025-09-10_105240396.png'),
(80, 'CUBIERTAS PARA MOTO', 'Cubiertas de protección impermeables para motocicletas, resistentes a la intemperie.', 0, 0.00, 8.00, 0.00, 25.00, '68c1909f368c2-imagen_2025-09-10_105214491.png'),
(81, 'CINTA DE BUTILO', 'Cinta selladora de alta resistencia, ideal para reparaciones de techos y tuberías.', 0, 0.00, 12.00, 0.00, 16.67, '68c19077f3489-imagen_2025-09-10_105135325.png'),
(82, 'PISTOLA DE REMACHES', 'Herramienta manual para unir materiales mediante remaches, ideal para proyectos de bricolaje.', 0, 0.00, 25.00, 0.00, 12.00, '68c1905e23631-imagen_2025-09-10_105109537.png'),
(83, 'ASPIRADORA MINI', 'Aspiradora portátil y compacta, perfecta para limpiar el interior del coche o espacios pequeños.', 0, 0.00, 10.00, 0.00, 20.00, '68c190467a174-imagen_2025-09-10_105045707.png'),
(84, 'VENTILADOR PARA CARRO', 'Ventilador de auto con clip, para refrescar el ambiente dentro del vehículo.', 0, 0.00, 18.00, 0.00, 22.22, '68c19027df909-imagen_2025-09-10_105015194.png'),
(85, 'BALANZA DIGITAL DE EQUIPAJE', 'Báscula portátil para pesar maletas y evitar el exceso de peso en los aeropuertos.', 0, 0.00, 7.00, 0.00, 28.57, '68c18fa975bbc-imagen_2025-09-10_104808942.png'),
(86, 'LINTERNA LED RECARGABLE', 'Linterna con luz LED brillante, que se recarga mediante USB.', 0, 0.00, 5.00, 0.00, 40.00, '68c18f8e1302d-imagen_2025-09-10_104741547.png'),
(87, 'TENSIOMETRO', 'Monitor de presión arterial para uso doméstico, con pantalla digital fácil de leer.', 0, 0.00, 16.00, 0.00, 18.75, '68c18f73171bb-imagen_2025-09-10_104714527.png'),
(88, 'NEBULIZADOR PORTATIL', 'Dispositivo para terapia respiratoria que convierte el medicamento líquido en un vapor fino.', 0, 0.00, 12.00, 0.00, 25.00, '68c18c45a03c3-imagen_2025-09-10_103340769.png'),
(89, 'MASAJEADOR PARA PIE', 'Dispositivo eléctrico que utiliza rodillos o vibración para masajear los pies y aliviar la tensión.', 0, 0.00, 10.00, 0.00, 20.00, '68c18f578b0ff-imagen_2025-09-10_104647123.png'),
(90, 'BALANZA PERSONAL', 'Báscula digital para el hogar que mide el peso corporal con precisión.', 0, 0.00, 18.00, 0.00, 16.67, '68c18f3e50303-imagen_2025-09-10_104617846.png'),
(91, 'FAJA MOLDEADORA', 'Prenda elástica que comprime el área abdominal para un efecto de modelado de figura.', 0, 0.00, 12.00, 0.00, 25.00, '68c18f205ae8b-imagen_2025-09-10_104551837.png'),
(92, 'TOBILLERAS DE COMPRESION', 'Calcetines o mangas de compresión diseñados para mejorar la circulación y reducir el dolor en los tobillos.', 0, 0.00, 8.00, 0.00, 25.00, '68c18f0873e47-imagen_2025-09-10_104527950.png'),
(93, 'MANCUERNAS ANATOMICAS', 'Pesas de mano con un diseño ergonómico para un agarre más cómodo durante el ejercicio.', 0, 0.00, 8.00, 0.00, 25.00, '68c18eeeabb24-imagen_2025-09-10_104501951.png'),
(94, 'KOALA DEPORTIVO', 'Riñonera o bolso de cintura para llevar objetos personales mientras se hace ejercicio.', 0, 0.00, 7.00, 0.00, 28.57, '68c18ecfb15cb-imagen_2025-09-10_104431020.png'),
(95, 'MINI MASAJEADOR MUSCULAR', 'Dispositivo de mano para masajes de percusión que ayuda a aliviar la tensión muscular.', 0, 0.00, 10.00, 0.00, 20.00, '68c18ebab739e-imagen_2025-09-10_104410008.png'),
(96, 'MASAJEADOR DE FRECUENCIA MINI', 'Dispositivo vibratorio para masajes, ideal para relajar músculos pequeños.', 0, 0.00, 7.00, 0.00, 28.57, '68c18e8acdf65-imagen_2025-09-10_104322391.png'),
(97, 'VENTILADOR DE CUELLO', 'Ventilador portátil y recargable que se cuelga del cuello para una brisa personal.', 0, 0.00, 10.00, 0.00, 30.00, '68c18e7377e5e-imagen_2025-09-10_104258727.png'),
(98, 'CUERDA DE SALTAR', 'Cuerda de ejercicio para saltar, ideal para entrenamiento cardiovascular.', 0, 0.00, 8.00, 0.00, 25.00, '68c18c1e3d20a-imagen_2025-09-10_103301539.png'),
(99, 'VENDA KINESTESIKA', 'Cinta elástica terapéutica utilizada para dar soporte a músculos y articulaciones.', 0, 0.00, 8.00, 0.00, 25.00, '68c18e5d81ee5-imagen_2025-09-10_104236938.png'),
(100, 'RODILLERA', 'Soporte de compresión para la rodilla que alivia el dolor y la estabilidad.', 0, 0.00, 9.00, 0.00, 33.33, '68c18c064887c-imagen_2025-09-10_103237699.png'),
(101, 'DEPILADOR FACIAL', 'Dispositivo eléctrico para la eliminación del vello facial de forma rápida y sin dolor.', 0, 0.00, 10.00, 0.00, 20.00, '68c18e3c43c67-imagen_2025-09-10_104203779.png'),
(102, 'CEPILLO REMOVEDOR DE PELO DE PERRO', 'Cepillo diseñado para quitar el pelo suelto de los perros y reducir la muda.', 0, 0.00, 7.00, 0.00, 28.57, '68c18e0c4e550-imagen_2025-09-10_104115762.png'),
(103, 'PECHERA PARA MASCOTA', 'Arnés o pechera para perros que distribuye la presión de la correa en el pecho.', 0, 0.00, 8.00, 0.00, 25.00, '68c18df59f474-imagen_2025-09-10_104052911.png'),
(104, 'COLLAR ANTI PULGAS PARA PERRO', 'Collar medicado que libera sustancias para repeler y matar pulgas en perros.', 0, 0.00, 4.00, 0.00, 25.00, '68c18dde50dd0-imagen_2025-09-10_104029775.png'),
(105, 'COLLAR PARA GATO', 'Collar de seguridad para gatos, a menudo con hebilla de liberación rápida para evitar que se atore.', 0, 0.00, 5.00, 0.00, 20.00, '68c18da9a5142-imagen_2025-09-10_103936563.png'),
(106, 'LAMPARA CON SENSOR', 'Lámpara LED con sensor de movimiento, ideal para pasillos, armarios y escaleras.', 0, 0.00, 8.00, 0.00, 25.00, '68c18d9350ca8-imagen_2025-09-10_103914165.png'),
(107, 'MINI PLANCHA PORTATIL', 'Plancha de ropa compacta, ideal para viajes y retoques rápidos.', 0, 0.00, 16.00, 0.00, 18.75, '68c18d67887c0-imagen_2025-09-10_103827467.png'),
(108, 'ZAPATERA MEDIANA', 'Organizador de zapatos para el hogar con capacidad para varios pares.', 0, 0.00, 23.00, 0.00, 13.04, '68c18d490edf6-imagen_2025-09-10_103800524.png'),
(109, 'MOPA MAGICA', 'Mopa con sistema de escurrido automático y cabezal de microfibra, para una limpieza más eficiente.', 0, 0.00, 13.00, 0.00, 30.77, '68c18d35bc875-imagen_2025-09-10_103741243.png'),
(110, 'MANGUERA CON PISTOLA 22 MTS', 'Manguera de jardín de 22 metros con boquilla de chorro ajustable.', 0, 0.00, 18.00, 0.00, 16.67, '68c18d1f3e48a-imagen_2025-09-10_103718679.png'),
(111, 'PALA CON ESCOBA', 'Juego de pala y escoba para la limpieza de interiores o exteriores.', 0, 0.00, 12.00, 0.00, 16.67, '68c18d0e03d48-imagen_2025-09-10_103701346.png'),
(112, 'DISPENSADOR DE AGUA', 'Dispensador manual de agua para botellones, ideal para el hogar o la oficina.', 0, 0.00, 10.00, 0.00, 20.00, '68c18b13de533-imagen_2025-09-10_102834951.png'),
(113, 'FILTRO DE AGUA GRANDE', 'Filtro purificador de agua para el grifo, diseñado para grandes volúmenes de agua.', 0, 0.00, 14.00, 0.00, 21.43, '68c18af657f0f-imagen_2025-09-10_102805681.png'),
(114, 'FILTRO DE AGUA PEQUEÑO', 'Filtro purificador de agua compacto, para uso personal o para un solo grifo.', 0, 0.00, 3.00, 0.00, 33.33, '68c18ad44e7fe-imagen_2025-09-10_102731687.png'),
(115, 'TENDEDERO DE CUERDA', 'Tendedero retráctil de pared con cuerda extensible para secar ropa.', 0, 0.00, 20.00, 0.00, 15.00, '68c189bccd23a-imagen_2025-09-10_102251568.png'),
(116, 'ZAPATERA GRADE AEREA', 'Zapatera vertical que se cuelga en la pared o detrás de una puerta para ahorrar espacio.', 0, 0.00, 25.00, 0.00, 12.00, '68c1899188e12-imagen_2025-09-10_102208711.png'),
(117, 'SET MARCADORES 60 PCS', 'Set de 60 marcadores de colores variados para dibujo, diseño y manualidades.', 0, 0.00, 15.00, 0.00, 20.00, '68c18978211fd-imagen_2025-09-10_102143426.png'),
(118, 'PIZARRA MAGICA', 'Pizarra de dibujo reutilizable que se borra con un solo botón, ideal para niños.', 0, 0.00, 6.00, 0.00, 33.33, '68c1895ed558e-imagen_2025-09-10_102118170.png'),
(119, 'NINTENDO SUP PLAYERS', 'Consola portátil de videojuegos con juegos clásicos preinstalados.', 0, 0.00, 15.00, 0.00, 20.00, '68c1892f61490-D_NQ_NP_2X_742864-MLV78601983895_082024-T.webp');

-- --------------------------------------------------------
-- Table structure for table `sales`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `sale_type` enum('direct','third_party') NOT NULL,
  `sale_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `payment_type` enum('cash','credit') DEFAULT 'cash',
  `payment_status` enum('paid','pending','partial') DEFAULT 'paid',
  `remaining_balance` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `color` varchar(7) DEFAULT '#3B82F6',
  `icon` varchar(50) DEFAULT 'fas fa-tag',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert default categories
INSERT INTO `categories` (`name`, `description`, `color`, `icon`, `sort_order`) VALUES
('General', 'Categoría general para productos sin clasificación específica', '#6B7280', 'fas fa-tag', 1),
('Electrónicos', 'Productos electrónicos y gadgets', '#3B82F6', 'fas fa-mobile-alt', 2),
('Hogar', 'Artículos para el hogar y decoración', '#10B981', 'fas fa-home', 3),
('Deportes', 'Equipamiento deportivo y recreativo', '#F59E0B', 'fas fa-futbol', 4),
('Belleza', 'Productos de belleza y cuidado personal', '#EC4899', 'fas fa-spa', 5),
('Ropa', 'Ropa y accesorios', '#8B5CF6', 'fas fa-tshirt', 6),
('Juguetes', 'Juguetes y entretenimiento infantil', '#F97316', 'fas fa-gamepad', 7),
('Libros', 'Libros y material educativo', '#6366F1', 'fas fa-book', 8),
('Automotriz', 'Accesorios y productos para vehículos', '#374151', 'fas fa-car', 9),
('Salud', 'Productos de salud y bienestar', '#EF4444', 'fas fa-heartbeat', 10);

-- --------------------------------------------------------
-- Table structure for table `catalog_settings`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `catalog_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_description` text,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert default catalog settings
INSERT INTO `catalog_settings` (`setting_key`, `setting_value`, `setting_description`) VALUES
('show_out_of_stock', '1', 'Mostrar productos sin stock en el catálogo público'),
('wholesale_minimum', '4', 'Cantidad mínima para aplicar precio al mayor'),
('catalog_title', 'Catálogo de Productos - Montoli\'s', 'Título del catálogo público'),
('catalog_description', 'Descubre nuestra amplia gama de productos de calidad', 'Descripción del catálogo'),
('show_third_party_prices', '1', 'Mostrar precios para terceros en el catálogo público'),
('products_per_page', '12', 'Número de productos por página en el catálogo'),
('enable_product_search', '1', 'Habilitar búsqueda de productos'),
('enable_category_filter', '1', 'Habilitar filtro por categorías');

-- --------------------------------------------------------
-- Table structure for table `inventory_movements`
-- --------------------------------------------------------

-- Set AUTO_INCREMENT values
ALTER TABLE `products` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;
ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `sales` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `inventory_movements` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - Montoli's</title>
    <link rel="icon" href="images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .info-icon {
            cursor: help;
            transition: color 0.2s ease;
        }

        .info-icon:hover {
            color: #3B82F6 !important;
        }

        /* Custom tooltip styling */
        .custom-tooltip {
            position: relative;
            display: inline-block;
        }

        .custom-tooltip .tooltip-text {
            visibility: hidden;
            width: 250px;
            background-color: rgba(0, 0, 0, 0.9);
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 8px 12px;
            position: absolute;
            z-index: 1000;
            bottom: 125%;
            left: 50%;
            margin-left: -125px;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
            line-height: 1.4;
            white-space: normal;
            word-wrap: break-word;
        }

        .custom-tooltip .tooltip-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: rgba(0, 0, 0, 0.9) transparent transparent transparent;
        }

        .custom-tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div id="particles-js"></div>
    <?php
    session_start();

    // Check if user is logged in
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    include_once 'config/database.php';
    include_once 'objects/product.php';
    include_once 'objects/sale.php';

    $database = new Database();
    $db = $database->getConnection();

    $product = new Product($db);
    $sale = new Sale($db);

    // Get movement statistics (entries and exits)
    $query_movement_stats = "SELECT
                                SUM(CASE WHEN type = 'entry' THEN quantity ELSE 0 END) as total_entries,
                                SUM(CASE WHEN type = 'exit' THEN quantity ELSE 0 END) as total_exits,
                                COUNT(CASE WHEN type = 'entry' THEN 1 END) as entry_count,
                                COUNT(CASE WHEN type = 'exit' THEN 1 END) as exit_count
                             FROM inventory_movements";
    $stmt_movement_stats = $db->prepare($query_movement_stats);
    $stmt_movement_stats->execute();
    $movement_stats = $stmt_movement_stats->fetch(PDO::FETCH_ASSOC) ?: ['total_entries' => 0, 'total_exits' => 0, 'entry_count' => 0, 'exit_count' => 0];

    // Get movements by month (last 12 months)
    $query_movements_by_month = "SELECT DATE_FORMAT(date, '%Y-%m') as month,
                                        SUM(CASE WHEN type = 'entry' THEN quantity ELSE 0 END) as entries,
                                        SUM(CASE WHEN type = 'exit' THEN quantity ELSE 0 END) as exits
                                 FROM inventory_movements
                                 WHERE date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                                 GROUP BY DATE_FORMAT(date, '%Y-%m')
                                 ORDER BY month";
    $stmt_movements_by_month = $db->prepare($query_movements_by_month);
    $stmt_movements_by_month->execute();
    $movements_by_month = $stmt_movements_by_month->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Get most moved products
    $query_most_moved = "SELECT p.name,
                                SUM(CASE WHEN m.type = 'entry' THEN m.quantity ELSE 0 END) as total_entries,
                                SUM(CASE WHEN m.type = 'exit' THEN m.quantity ELSE 0 END) as total_exits,
                                (SUM(CASE WHEN m.type = 'entry' THEN m.quantity ELSE 0 END) - SUM(CASE WHEN m.type = 'exit' THEN m.quantity ELSE 0 END)) as net_movement
                         FROM inventory_movements m
                         LEFT JOIN products p ON m.product_id = p.id
                         GROUP BY m.product_id
                         ORDER BY (SUM(CASE WHEN m.type = 'entry' THEN m.quantity ELSE 0 END) + SUM(CASE WHEN m.type = 'exit' THEN m.quantity ELSE 0 END)) DESC
                         LIMIT 10";
    $stmt_most_moved = $db->prepare($query_most_moved);
    $stmt_most_moved->execute();
    $most_moved_products = $stmt_most_moved->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Get total stats (use movement data)
    $total_stats = [
        'total_sales' => $movement_stats['exit_count'] ?? 0,
        'total_quantity_sold' => $movement_stats['total_exits'] ?? 0,
        'total_revenue' => 0 // No revenue data in movements
    ];

    $query_total_products = "SELECT COUNT(*) as total_products, SUM(quantity) as total_stock FROM products";
    $stmt_total_products = $db->prepare($query_total_products);
    $stmt_total_products->execute();
    $total_products = $stmt_total_products->fetch(PDO::FETCH_ASSOC) ?: ['total_products' => 0, 'total_stock' => 0];

    // Calculate total investment (cost of all products in inventory)
    $query_total_investment = "SELECT SUM(product_cost * quantity) as total_investment FROM products";
    $stmt_total_investment = $db->prepare($query_total_investment);
    $stmt_total_investment->execute();
    $investment_data = $stmt_total_investment->fetch(PDO::FETCH_ASSOC) ?: ['total_investment' => 0];

    // Calculate profits from exits (sales) - using product sale_price vs product_cost
    $query_profits = "SELECT
                        SUM((p.sale_price - p.product_cost) * m.quantity) as total_profits,
                        SUM(p.product_cost * m.quantity) as total_cost_sold,
                        SUM(p.sale_price * m.quantity) as total_sales_value
                      FROM inventory_movements m
                      LEFT JOIN products p ON m.product_id = p.id
                      WHERE m.type = 'exit'";
    $stmt_profits = $db->prepare($query_profits);
    $stmt_profits->execute();
    $profit_data = $stmt_profits->fetch(PDO::FETCH_ASSOC) ?: ['total_profits' => 0, 'total_cost_sold' => 0, 'total_sales_value' => 0];

    // Calculate total stock value (sum of sale_price * quantity for all products in inventory)
    $query_stock_value = "SELECT SUM(sale_price * quantity) as total_stock_value FROM products";
    $stmt_stock_value = $db->prepare($query_stock_value);
    $stmt_stock_value->execute();
    $stock_data = $stmt_stock_value->fetch(PDO::FETCH_ASSOC) ?: ['total_stock_value' => 0];

    // Calculate total IVA spent (IVA from all inventory exits/sales)
    $query_total_iva = "SELECT SUM((p.sale_price * m.quantity) * 0.16) as total_iva_spent
                       FROM inventory_movements m
                       LEFT JOIN products p ON m.product_id = p.id
                       WHERE m.type = 'exit'";
    $stmt_total_iva = $db->prepare($query_total_iva);
    $stmt_total_iva->execute();
    $iva_data = $stmt_total_iva->fetch(PDO::FETCH_ASSOC) ?: ['total_iva_spent' => 0];

    // Get movement type breakdown (since no sales data exists)
    $query_movement_types = "SELECT type, COUNT(*) as count, SUM(quantity) as total_quantity
                            FROM inventory_movements
                            GROUP BY type";
    $stmt_movement_types = $db->prepare($query_movement_types);
    $stmt_movement_types->execute();
    $movement_types_data = $stmt_movement_types->fetchAll(PDO::FETCH_ASSOC) ?: [];
    ?>
    <div class="flex">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-800 text-white w-64 min-h-screen fixed top-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-30">
            <div class="p-6 text-2xl font-bold flex items-center">
                <img src="images/logo.png" alt="Montoli's Logo" class="h-10 mr-3"> Montoli's
            </div>
            <nav class="mt-10">
                <a href="dashboard.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
                <a href="add_product.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-plus mr-3"></i> Añadir Producto
                </a>
                <a href="categories.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-tags mr-3"></i> Categorías
                </a>
                <a href="catalog_settings.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-cog mr-3"></i> Configuración Catálogo
                </a>
                <a href="catalog.php" target="_blank" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-book-open mr-3"></i> Ver Catálogo
                </a>
                <a href="inventory_movements.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-exchange-alt mr-3"></i> Movimientos
                </a>
                <a href="statistics.php" class="flex items-center py-3 px-6 text-gray-300 bg-gray-700">
                    <i class="fas fa-chart-bar mr-3"></i> Estadísticas
                </a>
                <div class="border-t border-gray-600 mt-6 pt-6">
                    <div class="px-6 py-2 text-gray-400 text-sm">
                        <i class="fas fa-user mr-2"></i><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Usuario'; ?>
                    </div>
                    <a href="logout.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-red-600 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-3"></i> Cerrar Sesión
                    </a>
                </div>
            </nav>
        </div>
        <!-- /#sidebar -->

        <!-- Page Content -->
        <div id="content" class="flex-1 md:ml-64 transition-all duration-300 ease-in-out">
            <header class="bg-white shadow-md p-4 flex justify-between items-center">
                <button id="menu-toggle" class="md:hidden text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors" style="min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <h2 class="text-3xl font-bold text-gray-800">Estadísticas y Análisis</h2>
                <a href="generate_statistics_report.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-file-pdf mr-2"></i>Generar Reporte PDF
                </a>
            </header>

            <main class="p-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 sm:gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 flex items-center justify-between transform hover:scale-105 transition-transform duration-300 relative">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-600">Dinero Invertido</h3>
                                <div class="custom-tooltip ml-2">
                                    <i class="fas fa-info-circle info-icon text-gray-400 text-sm"></i>
                                    <span class="tooltip-text">Total del costo de adquisición de todos los productos actualmente en inventario</span>
                                </div>
                            </div>
                            <p class="text-2xl sm:text-3xl font-bold text-red-600">$<?php echo number_format($investment_data['total_investment'] ?? 0, 2); ?></p>
                        </div>
                        <div class="bg-red-500 rounded-full p-3 sm:p-4 flex-shrink-0">
                            <i class="fas fa-dollar-sign text-white text-xl sm:text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 flex items-center justify-between transform hover:scale-105 transition-transform duration-300 relative">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-600">Ganancias Totales</h3>
                                <div class="custom-tooltip ml-2">
                                    <i class="fas fa-info-circle info-icon text-gray-400 text-sm"></i>
                                    <span class="tooltip-text">Beneficio neto obtenido de las ventas (precio de venta menos costo del producto)</span>
                                </div>
                            </div>
                            <p class="text-2xl sm:text-3xl font-bold text-green-600">$<?php echo number_format($profit_data['total_profits'] ?? 0, 2); ?></p>
                        </div>
                        <div class="bg-green-500 rounded-full p-3 sm:p-4 flex-shrink-0">
                            <i class="fas fa-chart-line text-white text-xl sm:text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 flex items-center justify-between transform hover:scale-105 transition-transform duration-300 relative">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-600">Valor de Ventas</h3>
                                <div class="custom-tooltip ml-2">
                                    <i class="fas fa-info-circle info-icon text-gray-400 text-sm"></i>
                                    <span class="tooltip-text">Ingresos totales generados por las ventas realizadas</span>
                                </div>
                            </div>
                            <p class="text-2xl sm:text-3xl font-bold text-blue-600">$<?php echo number_format($profit_data['total_sales_value'] ?? 0, 2); ?></p>
                        </div>
                        <div class="bg-blue-500 rounded-full p-3 sm:p-4 flex-shrink-0">
                            <i class="fas fa-shopping-cart text-white text-xl sm:text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 flex items-center justify-between transform hover:scale-105 transition-transform duration-300 relative">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-600">Valor del Stock</h3>
                                <div class="custom-tooltip ml-2">
                                    <i class="fas fa-info-circle info-icon text-gray-400 text-sm"></i>
                                    <span class="tooltip-text">Valor potencial de venta de todo el inventario actual (precio de venta × cantidad)</span>
                                </div>
                            </div>
                            <p class="text-2xl sm:text-3xl font-bold text-indigo-600">$<?php echo number_format($stock_data['total_stock_value'] ?? 0, 2); ?></p>
                        </div>
                        <div class="bg-indigo-500 rounded-full p-3 sm:p-4 flex-shrink-0">
                            <i class="fas fa-warehouse text-white text-xl sm:text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 flex items-center justify-between transform hover:scale-105 transition-transform duration-300 relative">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-600">IVA Pagado</h3>
                                <div class="custom-tooltip ml-2">
                                    <i class="fas fa-info-circle info-icon text-gray-400 text-sm"></i>
                                    <span class="tooltip-text">Total del IVA (16%) pagado en todas las ventas realizadas</span>
                                </div>
                            </div>
                            <p class="text-2xl sm:text-3xl font-bold text-orange-600">$<?php echo number_format($iva_data['total_iva_spent'] ?? 0, 2); ?></p>
                        </div>
                        <div class="bg-orange-500 rounded-full p-3 sm:p-4 flex-shrink-0">
                            <i class="fas fa-calculator text-white text-xl sm:text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 flex items-center justify-between transform hover:scale-105 transition-transform duration-300 relative sm:col-span-2 lg:col-span-1">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-600">Total Movimientos</h3>
                                <div class="custom-tooltip ml-2">
                                    <i class="fas fa-info-circle info-icon text-gray-400 text-sm"></i>
                                    <span class="tooltip-text">Número total de registros de entrada y salida en el sistema de inventario</span>
                                </div>
                            </div>
                            <p class="text-2xl sm:text-3xl font-bold text-gray-800"><?php echo number_format(($movement_stats['entry_count'] ?? 0) + ($movement_stats['exit_count'] ?? 0)); ?></p>
                        </div>
                        <div class="bg-purple-500 rounded-full p-3 sm:p-4 flex-shrink-0">
                            <i class="fas fa-exchange-alt text-white text-xl sm:text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Additional Financial Summary -->
                <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 mb-8">
                    <h3 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-700">Resumen Financiero Detallado</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-base sm:text-lg font-semibold text-gray-600 mb-2">Costo de Productos Vendidos</h4>
                            <p class="text-2xl sm:text-3xl font-bold text-orange-600">$<?php echo number_format($profit_data['total_cost_sold'] ?? 0, 2); ?></p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-base sm:text-lg font-semibold text-gray-600 mb-2">Ingresos por Ventas</h4>
                            <p class="text-2xl sm:text-3xl font-bold text-blue-600">$<?php echo number_format($profit_data['total_sales_value'] ?? 0, 2); ?></p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg sm:col-span-2 lg:col-span-1">
                            <h4 class="text-base sm:text-lg font-semibold text-gray-600 mb-2">Margen de Ganancia</h4>
                            <p class="text-2xl sm:text-3xl font-bold <?php echo (($profit_data['total_sales_value'] ?? 0) > 0) ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php
                                $margin = ($profit_data['total_sales_value'] ?? 0) > 0 ?
                                    (($profit_data['total_profits'] ?? 0) / ($profit_data['total_sales_value'] ?? 1)) * 100 : 0;
                                echo number_format($margin, 1) . '%';
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-8 mb-8">
                    <!-- Most Moved Products -->
                    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6">
                        <h3 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-700">Productos Más Movidos</h3>
                        <div class="relative">
                            <canvas id="mostMovedChart" width="400" height="300" class="w-full h-auto"></canvas>
                        </div>
                    </div>

                    <!-- Movements Over Time -->
                    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6">
                        <h3 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-700">Movimientos por Mes (Últimos 12 meses)</h3>
                        <div class="relative">
                            <canvas id="movementsOverTimeChart" width="400" height="300" class="w-full h-auto"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-8 mb-8">
                    <!-- Movement Types -->
                    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6">
                        <h3 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-700">Tipos de Movimiento</h3>
                        <div class="relative">
                            <canvas id="movementTypesChart" width="400" height="300" class="w-full h-auto"></canvas>
                        </div>
                    </div>

                    <!-- Movement Summary -->
                    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6">
                        <h3 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-700">Resumen de Movimientos</h3>
                        <div class="space-y-3 sm:space-y-4">
                            <?php
                            $total_entries = $movement_stats['total_entries'] ?? 0;
                            $total_exits = $movement_stats['total_exits'] ?? 0;
                            $entry_count = $movement_stats['entry_count'] ?? 0;
                            $exit_count = $movement_stats['exit_count'] ?? 0;
                            ?>
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                <span class="text-gray-600 font-medium">Total Entradas:</span>
                                <span class="text-xl sm:text-2xl font-bold text-green-600"><?php echo number_format($total_entries); ?> unidades</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                <span class="text-gray-600 font-medium">Total Salidas:</span>
                                <span class="text-xl sm:text-2xl font-bold text-red-600"><?php echo number_format($total_exits); ?> unidades</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center border-t pt-4 gap-2">
                                <span class="text-gray-600 font-semibold">Movimientos de Inventario:</span>
                                <span class="text-2xl sm:text-3xl font-bold text-blue-700"><?php echo number_format($entry_count + $exit_count); ?> registros</span>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                content.classList.toggle('md:ml-64');
            });
        }

        // Swipe gesture for mobile sidebar
        let startX = 0;
        let currentX = 0;
        let isDragging = false;

        document.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
        });

        document.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            currentX = e.touches[0].clientX;
            const diff = currentX - startX;

            // Only handle swipe from left edge
            if (startX < 20 && diff > 50) {
                sidebar.classList.remove('-translate-x-full');
                content.classList.add('md:ml-64');
            }
        });

        document.addEventListener('touchend', () => {
            isDragging = false;
        });

        // Close sidebar when clicking outside on mobile
        content.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                sidebar.classList.add('-translate-x-full');
                content.classList.remove('md:ml-64');
            }
        });

        // Most Moved Products Chart
        const mostMovedCtx = document.getElementById('mostMovedChart').getContext('2d');
        const mostMovedData = <?php echo json_encode($most_moved_products); ?>;
        new Chart(mostMovedCtx, {
            type: 'bar',
            data: {
                labels: mostMovedData.map(item => item.name.substring(0, 20) + (item.name.length > 20 ? '...' : '')),
                datasets: [{
                    label: 'Entradas',
                    data: mostMovedData.map(item => item.total_entries),
                    backgroundColor: 'rgba(34, 197, 94, 0.6)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                }, {
                    label: 'Salidas',
                    data: mostMovedData.map(item => item.total_exits),
                    backgroundColor: 'rgba(239, 68, 68, 0.6)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Movements Over Time Chart
        const movementsOverTimeCtx = document.getElementById('movementsOverTimeChart').getContext('2d');
        const movementsByMonthData = <?php echo json_encode($movements_by_month); ?>;
        new Chart(movementsOverTimeCtx, {
            type: 'line',
            data: {
                labels: movementsByMonthData.map(item => item.month),
                datasets: [{
                    label: 'Entradas',
                    data: movementsByMonthData.map(item => item.entries),
                    backgroundColor: 'rgba(34, 197, 94, 0.2)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 2,
                    fill: true
                }, {
                    label: 'Salidas',
                    data: movementsByMonthData.map(item => item.exits),
                    backgroundColor: 'rgba(239, 68, 68, 0.2)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Movement Types Chart
        const movementTypesCtx = document.getElementById('movementTypesChart').getContext('2d');
        const movementTypesData = <?php echo json_encode($movement_types_data); ?>;
        const typeLabels = movementTypesData.map(item => {
            switch(item.type) {
                case 'entry': return 'Entradas';
                case 'exit': return 'Salidas';
                default: return item.type;
            }
        });
        const typeColors = movementTypesData.map(item => {
            switch(item.type) {
                case 'entry': return 'rgba(34, 197, 94, 0.6)';
                case 'exit': return 'rgba(239, 68, 68, 0.6)';
                default: return 'rgba(156, 163, 175, 0.6)';
            }
        });

        new Chart(movementTypesCtx, {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    label: 'Tipos de Movimiento',
                    data: movementTypesData.map(item => item.count),
                    backgroundColor: typeColors,
                    borderColor: typeColors.map(color => color.replace('0.6', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });


        // Animations with Anime.js
        anime({
            targets: '.grid > div',
            translateY: [50, 0],
            opacity: [0, 1],
            delay: anime.stagger(100),
            easing: 'easeOutExpo'
        });
    </script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        particlesJS("particles-js", {
            "particles": {
                "number": {
                    "value": 80,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },
                "color": {
                    "value": "#10B981"
                },
                "shape": {
                    "type": "circle",
                },
                "opacity": {
                    "value": 0.5,
                    "random": false,
                },
                "size": {
                    "value": 3,
                    "random": true,
                },
                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#10B981",
                    "opacity": 0.4,
                    "width": 1
                },
                "move": {
                    "enable": true,
                    "speed": 2,
                    "direction": "none",
                    "random": false,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false,
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "grab"
                    },
                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },
                    "resize": true
                },
                "modes": {
                    "grab": {
                        "distance": 140,
                        "line_linked": {
                            "opacity": 1
                        }
                    },
                    "push": {
                        "particles_nb": 4
                    }
                }
            },
            "retina_detect": true
        });
    </script>
</body>
</html>
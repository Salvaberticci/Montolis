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
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div id="particles-js"></div>
    <?php
    include_once 'config/database.php';
    include_once 'objects/product.php';
    include_once 'objects/sale.php';

    $database = new Database();
    $db = $database->getConnection();

    $product = new Product($db);
    $sale = new Sale($db);

    // Get best-selling products
    $query_best_sellers = "SELECT p.name, SUM(s.quantity_sold) as total_sold, SUM(s.sale_price * s.quantity_sold) as total_revenue
                          FROM sales s
                          LEFT JOIN products p ON s.product_id = p.id
                          GROUP BY s.product_id
                          ORDER BY total_sold DESC
                          LIMIT 10";
    $stmt_best_sellers = $db->prepare($query_best_sellers);
    $stmt_best_sellers->execute();
    $best_sellers = $stmt_best_sellers->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Get sales by month (last 12 months)
    $query_sales_by_month = "SELECT DATE_FORMAT(sale_date, '%Y-%m') as month, SUM(quantity_sold) as total_quantity, SUM(sale_price * quantity_sold) as total_revenue
                            FROM sales
                            WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                            GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
                            ORDER BY month";
    $stmt_sales_by_month = $db->prepare($query_sales_by_month);
    $stmt_sales_by_month->execute();
    $sales_by_month = $stmt_sales_by_month->fetchAll(PDO::FETCH_ASSOC) ?: [];


    // Get total stats
    $query_total_sales = "SELECT COUNT(*) as total_sales, SUM(quantity_sold) as total_quantity_sold, SUM(sale_price * quantity_sold) as total_revenue FROM sales";
    $stmt_total_sales = $db->prepare($query_total_sales);
    $stmt_total_sales->execute();
    $total_stats = $stmt_total_sales->fetch(PDO::FETCH_ASSOC) ?: ['total_sales' => 0, 'total_quantity_sold' => 0, 'total_revenue' => 0];

    $query_total_products = "SELECT COUNT(*) as total_products, SUM(quantity) as total_stock FROM products";
    $stmt_total_products = $db->prepare($query_total_products);
    $stmt_total_products->execute();
    $total_products = $stmt_total_products->fetch(PDO::FETCH_ASSOC) ?: ['total_products' => 0, 'total_stock' => 0];
    ?>
    <div class="flex">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-800 text-white w-64 min-h-screen fixed top-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-30">
            <div class="p-6 text-2xl font-bold flex items-center">
                <img src="images/logo.png" alt="Montoli's Logo" class="h-10 mr-3"> Montoli's
            </div>
            <nav class="mt-10">
                <a href="index.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
                <a href="add_product.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-plus mr-3"></i> Añadir Producto
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
            </nav>
        </div>
        <!-- /#sidebar -->

        <!-- Page Content -->
        <div id="content" class="flex-1 md:ml-64 transition-all duration-300 ease-in-out">
            <header class="bg-white shadow-md p-4 flex justify-between items-center">
                <button id="menu-toggle" class="md:hidden text-gray-600">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <h2 class="text-3xl font-bold text-gray-800">Estadísticas y Análisis</h2>
            </header>

            <main class="p-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-xl p-6 flex items-center justify-between transform hover:scale-105 transition-transform duration-300">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-600">Total Ventas</h3>
                            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_stats['total_sales'] ?? 0); ?></p>
                        </div>
                        <div class="bg-blue-500 rounded-full p-4">
                            <i class="fas fa-shopping-cart text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-xl p-6 flex items-center justify-between transform hover:scale-105 transition-transform duration-300">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-600">Productos Vendidos</h3>
                            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_stats['total_quantity_sold'] ?? 0); ?></p>
                        </div>
                        <div class="bg-green-500 rounded-full p-4">
                            <i class="fas fa-boxes text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-xl p-6 flex items-center justify-between transform hover:scale-105 transition-transform duration-300">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-600">Ingresos Totales</h3>
                            <p class="text-3xl font-bold text-gray-800">$<?php echo number_format($total_stats['total_revenue'] ?? 0, 2); ?></p>
                        </div>
                        <div class="bg-yellow-500 rounded-full p-4">
                            <i class="fas fa-dollar-sign text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-xl p-6 flex items-center justify-between transform hover:scale-105 transition-transform duration-300">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-600">Productos en Inventario</h3>
                            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_products['total_products'] ?? 0); ?></p>
                        </div>
                        <div class="bg-purple-500 rounded-full p-4">
                            <i class="fas fa-warehouse text-white text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Best Selling Products -->
                    <div class="bg-white rounded-lg shadow-xl p-6">
                        <h3 class="text-2xl font-bold mb-6 text-gray-700">Productos Más Vendidos</h3>
                        <canvas id="bestSellersChart" width="400" height="300"></canvas>
                    </div>

                    <!-- Sales Over Time -->
                    <div class="bg-white rounded-lg shadow-xl p-6">
                        <h3 class="text-2xl font-bold mb-6 text-gray-700">Ventas por Mes (Últimos 12 meses)</h3>
                        <canvas id="salesOverTimeChart" width="400" height="300"></canvas>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            content.classList.toggle('md:ml-64');
        });

        // Best Sellers Chart
        const bestSellersCtx = document.getElementById('bestSellersChart').getContext('2d');
        const bestSellersData = <?php echo json_encode($best_sellers); ?>;
        new Chart(bestSellersCtx, {
            type: 'bar',
            data: {
                labels: bestSellersData.map(item => item.name.substring(0, 20) + (item.name.length > 20 ? '...' : '')),
                datasets: [{
                    label: 'Unidades Vendidas',
                    data: bestSellersData.map(item => item.total_sold),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
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

        // Sales Over Time Chart
        const salesOverTimeCtx = document.getElementById('salesOverTimeChart').getContext('2d');
        const salesByMonthData = <?php echo json_encode($sales_by_month); ?>;
        new Chart(salesOverTimeCtx, {
            type: 'line',
            data: {
                labels: salesByMonthData.map(item => item.month),
                datasets: [{
                    label: 'Ingresos ($)',
                    data: salesByMonthData.map(item => item.total_revenue),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
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
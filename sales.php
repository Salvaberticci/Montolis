<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Ventas - Inventario Montoli's</title>
    <link rel="icon" href="images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
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
    session_start();

    // Check if user is logged in
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    include_once 'config/database.php';
    include_once 'objects/sale.php';

    $database = new Database();
    $db = $database->getConnection();

    $sale = new Sale($db);
    $stmt = $sale->read();
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
                <a href="add_sale.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-cart-plus mr-3"></i> Registrar Venta
                </a>
                <a href="sales.php" class="flex items-center py-3 px-6 text-gray-300 bg-gray-700">
                    <i class="fas fa-file-invoice-dollar mr-3"></i> Ver Ventas
                </a>
                <a href="inventory_movements.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-exchange-alt mr-3"></i> Movimientos
                </a>
                <a href="statistics.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
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
                <h2 class="text-3xl font-bold text-gray-800">Listado de Ventas</h2>
            </header>

            <main class="p-6">
                <!-- Mobile Card View -->
                <div class="block md:hidden space-y-4">
                    <?php
                    $stmt_mobile = $sale->read();
                    while ($row = $stmt_mobile->fetch(PDO::FETCH_ASSOC)){
                        extract($row);

                        // Format payment type
                        $payment_type_label = ($payment_type == 'cash') ? 'Efectivo' : 'Crédito';

                        // Format payment status with colors
                        $status_color = '';
                        $status_label = '';
                        $bg_color = '';
                        switch($payment_status) {
                            case 'paid':
                                $status_color = 'text-green-600';
                                $status_label = 'Pagado';
                                $bg_color = 'bg-green-50 border-green-200';
                                break;
                            case 'pending':
                                $status_color = 'text-red-600';
                                $status_label = 'Pendiente';
                                $bg_color = 'bg-red-50 border-red-200';
                                break;
                            case 'partial':
                                $status_color = 'text-yellow-600';
                                $status_label = 'Parcial';
                                $bg_color = 'bg-yellow-50 border-yellow-200';
                                break;
                            default:
                                $status_color = 'text-gray-600';
                                $status_label = $payment_status;
                                $bg_color = 'bg-gray-50 border-gray-200';
                        }

                        echo "<div class='{$bg_color} border rounded-lg p-4'>";
                        echo "<div class='flex justify-between items-start mb-3'>";
                        echo "<div class='flex-1'>";
                        echo "<h4 class='font-bold text-lg text-gray-900'>{$product_name}</h4>";
                        echo "<p class='{$status_color} font-semibold'>{$status_label}</p>";
                        echo "</div>";
                        echo "</div>";
                        echo "<div class='grid grid-cols-2 gap-4 mb-3'>";
                        echo "<div><span class='text-gray-500 text-sm'>Cantidad:</span><br><span class='font-semibold'>{$quantity_sold}</span></div>";
                        echo "<div><span class='text-gray-500 text-sm'>Precio:</span><br><span class='font-semibold text-green-600'>&#36;{$sale_price}</span></div>";
                        echo "<div><span class='text-gray-500 text-sm'>Pago:</span><br><span class='font-semibold'>{$payment_type_label}</span></div>";
                        echo "<div><span class='text-gray-500 text-sm'>Fecha:</span><br><span class='font-semibold'>{$sale_date}</span></div>";
                        echo "</div>";
                        if($remaining_balance > 0) {
                            echo "<div class='pt-3 border-t border-gray-300'>";
                            echo "<span class='text-gray-500 text-sm'>Saldo Pendiente:</span><br>";
                            echo "<span class='font-semibold text-red-600'>&#36;" . number_format($remaining_balance, 2) . "</span>";
                            echo "</div>";
                        }
                        echo "</div>";
                    }
                    ?>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto bg-white rounded-lg shadow-xl">
                    <table class="min-w-full">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio de Venta</th>
                                <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Pago</th>
                                <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Pendiente</th>
                                <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                                extract($row);

                                // Format payment type
                                $payment_type_label = ($payment_type == 'cash') ? 'Efectivo' : 'Crédito';

                                // Format payment status with colors
                                $status_color = '';
                                $status_label = '';
                                switch($payment_status) {
                                    case 'paid':
                                        $status_color = 'text-green-600';
                                        $status_label = 'Pagado';
                                        break;
                                    case 'pending':
                                        $status_color = 'text-red-600';
                                        $status_label = 'Pendiente';
                                        break;
                                    case 'partial':
                                        $status_color = 'text-yellow-600';
                                        $status_label = 'Parcial';
                                        break;
                                    default:
                                        $status_color = 'text-gray-600';
                                        $status_label = $payment_status;
                                }

                                echo "<tr class='table-row'>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap'>{$id}</td>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap font-medium text-gray-900'>{$product_name}</td>";
                                    echo "<td class='py-4 px-6 text-gray-500'>{$quantity_sold}</td>";
                                    echo "<td class='py-4 px-6 text-green-600 font-semibold'>&#36;{$sale_price}</td>";
                                    echo "<td class='py-4 px-6 text-gray-500'>{$payment_type_label}</td>";
                                    echo "<td class='py-4 px-6 {$status_color} font-semibold'>{$status_label}</td>";
                                    echo "<td class='py-4 px-6 text-red-600 font-semibold'>&#36;" . number_format($remaining_balance ?? 0, 2) . "</td>";
                                    echo "<td class='py-4 px-6 text-gray-500'>{$sale_date}</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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

        // Animations with Anime.js
        anime({
            targets: '.table-row',
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

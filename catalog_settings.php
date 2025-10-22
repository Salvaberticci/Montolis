<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Catálogo - Inventario Montoli's</title>
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
    include_once 'objects/settings.php';

    $database = new Database();
    $db = $database->getConnection();

    $settings = new Settings($db);
    $message = '';

    // Handle form submission
    if($_POST){
        $updates = [
            'show_out_of_stock' => isset($_POST['show_out_of_stock']) ? '1' : '0',
            'wholesale_minimum' => $_POST['wholesale_minimum'],
            'catalog_title' => $_POST['catalog_title'],
            'catalog_description' => $_POST['catalog_description'],
            'products_per_page' => $_POST['products_per_page'],
            'enable_product_search' => isset($_POST['enable_product_search']) ? '1' : '0',
            'enable_category_filter' => isset($_POST['enable_category_filter']) ? '1' : '0'
        ];

        $success = true;
        foreach($updates as $key => $value) {
            if(!$settings->set($key, $value)) {
                $success = false;
                break;
            }
        }

        if($success) {
            $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                            <strong class='font-bold'>Éxito!</strong>
                            <span class='block sm:inline'>Configuración guardada exitosamente.</span>
                        </div>";
        } else {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                            <strong class='font-bold'>Error!</strong>
                            <span class='block sm:inline'>No se pudo guardar la configuración.</span>
                        </div>";
        }
    }
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
                <a href="catalog_settings.php" class="flex items-center py-3 px-6 text-gray-300 bg-gray-700">
                    <i class="fas fa-cog mr-3"></i> Configuración Catálogo
                </a>
                <a href="catalog.php" target="_blank" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-book-open mr-3"></i> Ver Catálogo
                </a>
                <a href="add_sale.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-cart-plus mr-3"></i> Registrar Venta
                </a>
                <a href="sales.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
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
                <h2 class="text-3xl font-bold text-gray-800">Configuración del Catálogo</h2>
            </header>

            <main class="p-6">
                <div class="max-w-4xl mx-auto">
                    <?php echo $message; ?>

                    <div class="bg-white rounded-lg shadow-xl p-8 form-container">
                        <form action="catalog_settings.php" method="post">
                            <h3 class="text-2xl font-bold mb-6 text-gray-800">Configuración General</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div>
                                    <label for="catalog_title" class="block text-gray-700 text-sm font-bold mb-2">Título del Catálogo</label>
                                    <input type="text" class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200" id="catalog_title" name="catalog_title" value="<?php echo htmlspecialchars($settings->getCatalogTitle()); ?>" required>
                                </div>

                                <div>
                                    <label for="wholesale_minimum" class="block text-gray-700 text-sm font-bold mb-2">Mínimo para Precio Mayor</label>
                                    <input type="number" class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200" id="wholesale_minimum" name="wholesale_minimum" value="<?php echo $settings->getWholesaleMinimum(); ?>" min="1" required>
                                    <p class="text-sm text-gray-500 mt-1">Cantidad mínima de unidades para aplicar precio al mayor</p>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label for="catalog_description" class="block text-gray-700 text-sm font-bold mb-2">Descripción del Catálogo</label>
                                <textarea class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200" id="catalog_description" name="catalog_description" rows="3" required><?php echo htmlspecialchars($settings->getCatalogDescription()); ?></textarea>
                            </div>

                            <div class="mb-6">
                                <label for="products_per_page" class="block text-gray-700 text-sm font-bold mb-2">Productos por Página</label>
                                <input type="number" class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200" id="products_per_page" name="products_per_page" value="<?php echo $settings->getProductsPerPage(); ?>" min="6" max="50" required>
                            </div>

                            <h3 class="text-2xl font-bold mb-6 text-gray-800">Opciones de Visualización</h3>

                            <div class="space-y-4 mb-8">
                                <div class="flex items-center">
                                    <input type="checkbox" class="form-checkbox h-5 w-5 text-green-600" id="show_out_of_stock" name="show_out_of_stock" <?php echo $settings->getShowOutOfStock() ? 'checked' : ''; ?>>
                                    <label for="show_out_of_stock" class="ml-3 text-gray-700">
                                        <span class="font-medium">Mostrar productos sin stock</span>
                                        <p class="text-sm text-gray-500">Los productos agotados serán visibles en el catálogo público</p>
                                    </label>
                                </div>


                                <div class="flex items-center">
                                    <input type="checkbox" class="form-checkbox h-5 w-5 text-green-600" id="enable_product_search" name="enable_product_search" <?php echo $settings->getEnableProductSearch() ? 'checked' : ''; ?>>
                                    <label for="enable_product_search" class="ml-3 text-gray-700">
                                        <span class="font-medium">Habilitar búsqueda de productos</span>
                                        <p class="text-sm text-gray-500">Los usuarios podrán buscar productos por nombre y descripción</p>
                                    </label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" class="form-checkbox h-5 w-5 text-green-600" id="enable_category_filter" name="enable_category_filter" <?php echo $settings->getEnableCategoryFilter() ? 'checked' : ''; ?>>
                                    <label for="enable_category_filter" class="ml-3 text-gray-700">
                                        <span class="font-medium">Habilitar filtro por categorías</span>
                                        <p class="text-sm text-gray-500">Los usuarios podrán filtrar productos por categoría</p>
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 px-8 rounded-lg shadow-lg transform hover:scale-105 transition-all duration-200">
                                    <i class="fas fa-save mr-2"></i>Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Current Settings Preview -->
                    <div class="bg-white rounded-lg shadow-xl p-6 mt-8">
                        <h3 class="text-xl font-bold mb-4 text-gray-800">Vista Previa de Configuración</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div><strong>Título del Catálogo:</strong> <?php echo htmlspecialchars($settings->getCatalogTitle()); ?></div>
                            <div><strong>Mínimo Mayorista:</strong> <?php echo $settings->getWholesaleMinimum(); ?> unidades</div>
                            <div><strong>Productos por Página:</strong> <?php echo $settings->getProductsPerPage(); ?></div>
                            <div><strong>Mostrar Sin Stock:</strong> <?php echo $settings->getShowOutOfStock() ? 'Sí' : 'No'; ?></div>
                            <div><strong>Búsqueda Habilitada:</strong> <?php echo $settings->getEnableProductSearch() ? 'Sí' : 'No'; ?></div>
                            <div><strong>Filtro Categorías:</strong> <?php echo $settings->getEnableCategoryFilter() ? 'Sí' : 'No'; ?></div>
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

        // Animation with Anime.js
        anime({
            targets: '.form-container',
            translateY: [50, 0],
            opacity: [0, 1],
            duration: 800,
            easing: 'easeOutExpo'
        });
    </script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        particlesJS("particles-js", {
            "particles": {
                "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
                "color": { "value": "#10B981" },
                "shape": { "type": "circle" },
                "opacity": { "value": 0.5, "random": false },
                "size": { "value": 3, "random": true },
                "line_linked": { "enable": true, "distance": 150, "color": "#10B981", "opacity": 0.4, "width": 1 },
                "move": { "enable": true, "speed": 2, "direction": "none", "random": false, "straight": false, "out_mode": "out", "bounce": false }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": { "enable": true, "mode": "grab" },
                    "onclick": { "enable": true, "mode": "push" },
                    "resize": true
                },
                "modes": {
                    "grab": { "distance": 140, "line_linked": { "opacity": 1 } },
                    "push": { "particles_nb": 4 }
                }
            },
            "retina_detect": true
        });
    </script>
</body>
</html>
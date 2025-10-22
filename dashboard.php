<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include_once 'config/database.php';
include_once 'objects/product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$stmt = $product->read();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario Montoli's</title>
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
        @media (min-width: 768px) {
            #menu-toggle {
                display: none !important;
            }
            #sidebar {
                transform: translateX(0) !important; /* Siempre visible en escritorio */
            }
        }
        @media (max-width: 767px) {
            #sidebar {
                transform: translateX(-100%); /* Ocultar sidebar en móvil por defecto */
            }
            #sidebar.sidebar-open-mobile {
                transform: translateX(0); /* Mostrar sidebar en móvil */
            }
        }

        /* Fix dropdown option text color to black */
        #category-filter option {
            color: black !important;
            background-color: white !important;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div id="particles-js"></div>
    <div class="flex">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-800 text-white w-64 min-h-screen fixed top-0 left-0 transform transition-transform duration-300 ease-in-out z-30">
            <div class="p-6 text-2xl font-bold flex items-center">
                <img src="images/logo.png" alt="Montoli's Logo" class="h-10 mr-3"> Montoli's
            </div>
            <nav class="mt-10">
                <a href="dashboard.php" class="flex items-center py-3 px-6 text-gray-300 bg-gray-700">
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
        <div id="content" class="flex-1 transition-all duration-300 ease-in-out md:ml-64">
            <header class="bg-white shadow-md p-4 flex justify-between items-center">
                <button id="menu-toggle" class="md:hidden text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors" style="min-width: 44px; min-height: 44px; align-items: center; justify-content: center;">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <h2 class="text-3xl font-bold text-gray-800">Dashboard</h2>
                <a href="catalog_selection.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-file-pdf mr-2"></i>Generar Catálogo
                </a>
            </header>

            <?php
            // Database connection and query to get stats
            $query_products = "SELECT COUNT(*) as total_products FROM products";
            $stmt_products = $db->prepare($query_products);
            $stmt_products->execute();
            $total_products = $stmt_products->fetch(PDO::FETCH_ASSOC)['total_products'];

            ?>

            <main class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Stat Card 1: Total Products -->
                    <div class="bg-white rounded-lg shadow-xl p-6 flex items-center justify-between transform hover:scale-105 transition-transform duration-300">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-600">Total de Productos</h3>
                            <p class="text-3xl font-bold text-gray-800"><?php echo $total_products; ?></p>
                        </div>
                        <div class="bg-blue-500 rounded-full p-4">
                            <i class="fas fa-box-open text-white text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-700">Listado de Productos</h3>
                        <div class="mt-4 sm:mt-0">
                            <label for="category-filter" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Categoría:</label>
                            <select id="category-filter" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" style="color: black;">
                                <option value="">Todas las Categorías</option>
                                <?php
                                $categories_stmt = $product->getCategories();
                                while ($cat_row = $categories_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . htmlspecialchars($cat_row['name']) . "' style='color: black; background-color: white;'>" . htmlspecialchars($cat_row['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="block md:hidden space-y-4" id="mobile-products">
                        <?php
                        $stmt_mobile = $product->read();
                        while ($row = $stmt_mobile->fetch(PDO::FETCH_ASSOC)){
                            extract($row);
                            echo "<div class='bg-white rounded-lg shadow-xl p-4 product-card' data-category='" . htmlspecialchars($category) . "'>";
                            echo "<div class='flex items-center mb-3'>";
                            echo $image ? "<img src='uploads/{$image}' class='h-16 w-16 rounded-full object-cover mr-4 shadow-md' />" : "<img src='images/placeholder.png' class='h-16 w-16 rounded-full object-cover mr-4 shadow-md' />";
                            echo "<div class='flex-1'>";
                            echo "<h4 class='font-bold text-lg text-gray-900'>{$name}</h4>";
                            echo "<p class='text-gray-600 text-sm'>{$description}</p>";
                            echo "<p class='text-xs text-gray-500 mt-1'>Categoría: {$category}</p>";
                            echo "</div>";
                            echo "</div>";
                            echo "<div class='grid grid-cols-2 gap-4 mb-3'>";
                            echo "<div><span class='text-gray-500 text-sm'>Cantidad:</span><br><span class='font-semibold'>{$quantity}</span></div>";
                            echo "<div><span class='text-gray-500 text-sm'>Costo:</span><br><span class='font-semibold text-green-600'>&#36;{$product_cost}</span></div>";
                            echo "<div><span class='text-gray-500 text-sm'>Venta:</span><br><span class='font-semibold text-blue-600'>&#36;{$sale_price}</span></div>";
                            echo "<div><span class='text-gray-500 text-sm'>Mayor:</span><br><span class='font-semibold text-purple-600'>&#36;{$wholesale_price}</span></div>";
                            echo "</div>";
                            echo "<div class='flex justify-between items-center pt-3 border-t border-gray-200'>";
                            echo "<div class='text-sm text-gray-600'>";
                            echo "<span>3ros: <span class='font-semibold text-indigo-600'>&#36;{$third_party_sale_price}</span></span>";
                            echo "<span class='ml-4'>% Vendedor: <span class='font-semibold text-pink-600'>{$third_party_seller_percentage}%</span></span>";
                            echo "</div>";
                            echo "<div class='flex space-x-3'>";
                            echo "<a href='edit_product.php?id={$id}' class='bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-lg transition-colors duration-200' style='min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;'><i class='fas fa-pencil-alt'></i></a>";
                            echo "<button type='button' class='bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition-colors duration-200 delete-btn' data-id='{$id}' style='min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;'><i class='fas fa-trash-alt'></i></button>";
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto bg-white rounded-lg shadow-xl" id="desktop-products">
                        <div class="min-w-full overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-200">
                                    <tr>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo (USD)</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venta (USD)</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mayor (USD)</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venta 3ros (USD)</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% Vendedor 3ros</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                $stmt->execute(); // Re-execute to reset the cursor
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                                    extract($row);
                                    echo "<tr class='table-row' data-category='" . htmlspecialchars($category) . "'>";
                                        echo "<td class='py-4 px-6 whitespace-nowrap flex items-center'>";
                                        echo $image ? "<img src='uploads/{$image}' class='h-12 w-12 rounded-full object-cover mr-4 shadow-md' />" : "<img src='images/placeholder.png' class='h-12 w-12 rounded-full object-cover mr-4 shadow-md' />";
                                        echo "<span class='font-medium text-gray-900'>{$name}</span></td>";
                                        echo "<td class='py-4 px-6 text-gray-500'>{$description}</td>";
                                        echo "<td class='py-4 px-6 text-gray-500'>{$category}</td>";
                                        echo "<td class='py-4 px-6 text-gray-500'>{$quantity}</td>";
                                        echo "<td class='py-4 px-6 text-green-600 font-semibold'>&#36;{$product_cost}</td>";
                                        echo "<td class='py-4 px-6 text-blue-600 font-semibold'>&#36;{$sale_price}</td>";
                                        echo "<td class='py-4 px-6 text-purple-600 font-semibold'>&#36;{$wholesale_price}</td>";
                                        echo "<td class='py-4 px-6 text-indigo-600 font-semibold'>&#36;{$third_party_sale_price}</td>";
                                        echo "<td class='py-4 px-6 text-pink-600 font-semibold'>{$third_party_seller_percentage}%</td>";
                                        echo "<td class='py-4 px-6 whitespace-nowrap'>";
                                            echo "<a href='edit_product.php?id={$id}' class='text-yellow-500 hover:text-yellow-700 transition-colors duration-200 mr-4'><i class='fas fa-pencil-alt text-lg'></i></a>";
                                            echo "<button type='button' class='text-red-500 hover:text-red-700 transition-colors duration-200 delete-btn' data-id='{$id}'><i class='fas fa-trash-alt text-lg'></i></button>";
                                        echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-40">
        <div class="bg-white rounded-lg shadow-2xl p-8 max-w-sm mx-auto">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Confirmar Eliminación</h2>
            <p class="text-gray-600 mb-6">¿Estás seguro de que quieres eliminar este producto?</p>
            <div class="flex justify-end">
                <button id="cancel-delete" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-4 transition-colors duration-200">Cancelar</button>
                <a href="#" id="confirm-delete" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">Eliminar</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');

            if (menuToggle) {
                menuToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    sidebar.classList.toggle('sidebar-open-mobile');
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
                    sidebar.classList.add('sidebar-open-mobile');
                }
            });

            document.addEventListener('touchend', () => {
                isDragging = false;
            });

            // Close sidebar when clicking outside on mobile
            content.addEventListener('click', () => {
                if (window.innerWidth < 768 && sidebar.classList.contains('sidebar-open-mobile')) {
                    sidebar.classList.remove('sidebar-open-mobile');
                }
            });

            // Category filtering
            const categoryFilter = document.getElementById('category-filter');
            const mobileProducts = document.querySelectorAll('#mobile-products .product-card');
            const desktopRows = document.querySelectorAll('#desktop-products .table-row');

            categoryFilter.addEventListener('change', function() {
                const selectedCategory = this.value;

                mobileProducts.forEach(card => {
                    if (selectedCategory === '' || card.dataset.category === selectedCategory) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });

                desktopRows.forEach(row => {
                    if (selectedCategory === '' || row.dataset.category === selectedCategory) {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Animations with Anime.js
            anime({
                targets: '.table-row',
                translateY: [50, 0],
                opacity: [0, 1],
                delay: anime.stagger(100),
                easing: 'easeOutExpo'
            });

            // Delete Modal Logic
            const deleteButtons = document.querySelectorAll('.delete-btn');
            const deleteModal = document.getElementById('delete-modal');
            const cancelDelete = document.getElementById('cancel-delete');
            const confirmDelete = document.getElementById('confirm-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.dataset.id;
                    confirmDelete.href = `delete_product.php?id=${id}`;
                    deleteModal.classList.remove('hidden');
                    deleteModal.classList.add('flex');
                    anime({
                        targets: '#delete-modal .bg-white',
                        scale: [0.7, 1],
                        opacity: [0, 1],
                        duration: 300,
                        easing: 'easeOutExpo'
                    });
                });
            });

            cancelDelete.addEventListener('click', () => {
                anime({
                    targets: '#delete-modal .bg-white',
                    scale: [1, 0.7],
                    opacity: [1, 0],
                    duration: 300,
                    easing: 'easeInExpo',
                    complete: () => {
                        deleteModal.classList.add('hidden');
                        deleteModal.classList.remove('flex');
                    }
                });
            });
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Movimiento - Montoli's</title>
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
    include_once 'objects/product.php';
    include_once 'objects/movement.php';

    $database = new Database();
    $db = $database->getConnection();

    $product = new Product($db);
    $movement = new Movement($db);

    $notification = '';
    $notification_type = '';

    // Get movement ID from URL
    if(isset($_GET['id'])) {
        $movement->id = $_GET['id'];
        $movement->readOne();
    }

    if($_POST) {
        // Obtener la cantidad anterior del movimiento
        $old_movement = new Movement($db);
        $old_movement->id = $_POST['id'];
        $old_movement->readOne();
        $old_quantity = $old_movement->quantity;
        $old_type = $old_movement->type;

        $movement->id = $_POST['id'];
        $movement->product_id = $_POST['product_id'];
        $movement->type = $_POST['type'];
        $movement->quantity = $_POST['quantity'];
        $movement->reason = $_POST['reason'];
        $movement->client_name = $_POST['client_name'] ?? '';
        $movement->client_contact = $_POST['client_contact'] ?? '';

        // Actualizar el movimiento en la base de datos
        if($movement->update()) {
            // Actualizar el stock del producto
            $product_id_to_update = $movement->product_id;
            $product_to_update = new Product($db);
            $product_to_update->id = $product_id_to_update;
            $product_to_update->readOne();
            $current_stock = $product_to_update->quantity;

            $new_stock = $current_stock;

            // Revertir el impacto del movimiento antiguo
            if ($old_type == 'entry') {
                $new_stock -= $old_quantity;
            } else { // 'exit'
                $new_stock += $old_quantity;
            }

            // Aplicar el impacto del nuevo movimiento
            if ($movement->type == 'entry') {
                $new_stock += $movement->quantity;
            } else { // 'exit'
                $new_stock -= $movement->quantity;
            }

            // Validar que el stock no sea negativo
            if ($new_stock < 0) {
                // Revertir el movimiento si el stock sería negativo
                // Esto es una simplificación, en un sistema real se necesitaría una transacción
                $movement->quantity = $old_quantity; // Restaurar cantidad anterior
                $movement->type = $old_type; // Restaurar tipo anterior
                $movement->update(); // Revertir la actualización del movimiento

                $notification = 'Error: La cantidad de salida excede el stock disponible. El movimiento no fue actualizado.';
                $notification_type = 'error';
            } else {
                $product_to_update->quantity = $new_stock;
                if($product_to_update->update()) {
                    $notification = 'Movimiento actualizado exitosamente y stock del producto actualizado.';
                    $notification_type = 'success';
                    // Refresh data
                    $movement->readOne();
                } else {
                    $notification = 'Movimiento actualizado, pero hubo un error al actualizar el stock del producto.';
                    $notification_type = 'error';
                }
            }
        } else {
            $notification = 'Error al actualizar el movimiento.';
            $notification_type = 'error';
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
                <a href="catalog_settings.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-cog mr-3"></i> Configuración Catálogo
                </a>
                <a href="catalog.php" target="_blank" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-book-open mr-3"></i> Ver Catálogo
                </a>
                <a href="inventory_movements.php" class="flex items-center py-3 px-6 text-gray-300 bg-gray-700">
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
                <h2 class="text-3xl font-bold text-gray-800">Editar Movimiento</h2>
                <a href="inventory_movements.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            </header>

            <main class="p-6">
                <div class="max-w-3xl mx-auto">
                    <?php if($notification): ?>
                        <div class="bg-<?php echo $notification_type === 'success' ? 'green' : 'red'; ?>-100 border border-<?php echo $notification_type === 'success' ? 'green' : 'red'; ?>-400 text-<?php echo $notification_type === 'success' ? 'green' : 'red'; ?>-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold"><?php echo $notification_type === 'success' ? 'Éxito!' : 'Error!'; ?></strong>
                            <span class="block sm:inline"><?php echo $notification; ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="bg-white rounded-lg shadow-xl p-8 form-container">
                        <form action="edit_movement.php" method="post">
                            <input type="hidden" name="id" value="<?php echo $movement->id; ?>">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                <div>
                                    <label for="product_id" class="block text-sm font-medium text-gray-700">Producto</label>
                                    <select name="product_id" id="product_id" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" required style="min-height: 44px;">
                                        <option value="">Seleccionar producto</option>
                                        <?php
                                        $stmt = $product->read();
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = ($row['id'] == $movement->product_id) ? 'selected' : '';
                                            echo "<option value='{$row['id']}' {$selected}>{$row['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">Tipo</label>
                                    <select name="type" id="type" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" required style="min-height: 44px;">
                                        <option value="entry" <?php echo ($movement->type == 'entry') ? 'selected' : ''; ?>>Entrada</option>
                                        <option value="exit" <?php echo ($movement->type == 'exit') ? 'selected' : ''; ?>>Salida</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700">Cantidad</label>
                                    <input type="number" name="quantity" id="quantity" value="<?php echo $movement->quantity; ?>" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" required min="1" style="min-height: 44px;">
                                </div>
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700">Razón</label>
                                    <input type="text" name="reason" id="reason" value="<?php echo htmlspecialchars($movement->reason); ?>" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" required style="min-height: 44px;">
                                </div>
                                <div>
                                    <label for="client_name" class="block text-sm font-medium text-gray-700">Nombre del Cliente</label>
                                    <input type="text" name="client_name" id="client_name" value="<?php echo htmlspecialchars($movement->client_name); ?>" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" style="min-height: 44px;">
                                </div>
                                <div>
                                    <label for="client_contact" class="block text-sm font-medium text-gray-700">Contacto del Cliente</label>
                                    <input type="text" name="client_contact" id="client_contact" value="<?php echo htmlspecialchars($movement->client_contact); ?>" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" placeholder="Teléfono o email" style="min-height: 44px;">
                                </div>
                            </div>
                            <div class="mt-6 flex gap-4">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                                    <i class="fas fa-save mr-2"></i>Actualizar Movimiento
                                </button>
                                <a href="inventory_movements.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-200">
                                    <i class="fas fa-times mr-2"></i>Cancelar
                                </a>
                            </div>
                        </form>
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos de Inventario - Montoli's</title>
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

    $filters = [];
    if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
    if (isset($_GET['type']) && $_GET['type'] != '') $filters['type'] = $_GET['type'];
    if (isset($_GET['product_id']) && $_GET['product_id'] != '') $filters['product_id'] = $_GET['product_id'];
    if (isset($_GET['date_from']) && $_GET['date_from'] != '') $filters['date_from'] = $_GET['date_from'];
    if (isset($_GET['date_to']) && $_GET['date_to'] != '') $filters['date_to'] = $_GET['date_to'];

    $notification = '';
    $notification_type = '';

    // Handle delete action
    if(isset($_GET['delete']) && isset($_GET['id'])) {
        $movement->id = $_GET['id'];
        if($movement->delete()) {
            $notification = 'Movimiento eliminado exitosamente.';
            $notification_type = 'success';
        } else {
            $notification = 'Error al eliminar el movimiento.';
            $notification_type = 'error';
        }
        // Redirect to remove delete parameters from URL
        header("Location: inventory_movements.php");
        exit();
    }

    if($_POST) {
        $movement->type = $_POST['type'];
        $movement->reason = $_POST['reason'];
        $movement->client_name = $_POST['client_name'] ?? '';
        $movement->client_contact = $_POST['client_contact'] ?? '';

        if($movement->type == 'entry') {
            // Single product entry
            $movement->product_id = $_POST['product_id'];
            $movement->quantity = $_POST['quantity'];

            if($movement->create()) {
                $notification = 'Movimiento registrado exitosamente.';
                $notification_type = 'success';
            } else {
                $notification = 'Error al registrar el movimiento.';
                $notification_type = 'error';
            }
        } else {
            // Multiple products exit
            $products = $_POST['products'] ?? [];
            $success_count = 0;
            $error_count = 0;

            foreach($products as $product_data) {
                if(!empty($product_data['product_id']) && !empty($product_data['quantity'])) {
                    $movement->product_id = $product_data['product_id'];
                    $movement->quantity = $product_data['quantity'];

                    if($movement->create()) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
            }

            if($success_count > 0) {
                $notification = "Se registraron {$success_count} movimientos exitosamente.";
                if($error_count > 0) {
                    $notification .= " {$error_count} movimientos fallaron.";
                }
                $notification_type = $error_count > 0 ? 'warning' : 'success';
            } else {
                $notification = 'Error al registrar los movimientos.';
                $notification_type = 'error';
            }
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
                <button id="menu-toggle" class="md:hidden text-gray-600">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <h2 class="text-3xl font-bold text-gray-800">Movimientos de Inventario</h2>
            </header>

            <main class="p-6">
                <div class="bg-white rounded-lg shadow-xl p-6 mb-8">
                    <h3 class="text-2xl font-bold mb-6 text-gray-700">Registrar Movimiento</h3>
                    <form action="inventory_movements.php" method="post" id="movement-form">
                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700">Tipo</label>
                            <select name="type" id="type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required onchange="toggleProductSection()">
                                <option value="entry">Entrada</option>
                                <option value="exit">Salida</option>
                            </select>
                        </div>

                        <!-- Single product section for entries -->
                        <div id="single-product-section" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="product_id" class="block text-sm font-medium text-gray-700">Producto</label>
                                <select name="product_id" id="product_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <option value="">Seleccionar producto</option>
                                    <?php
                                    $stmt = $product->read();
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Cantidad</label>
                                <input type="number" name="quantity" id="quantity" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required min="1">
                            </div>
                        </div>

                        <!-- Multiple products section for exits -->
                        <div id="multiple-products-section" class="hidden">
                            <div class="mb-4">
                                <div class="flex justify-between items-center">
                                    <label class="block text-sm font-medium text-gray-700">Productos</label>
                                    <button type="button" onclick="addProductRow()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm">
                                        <i class="fas fa-plus mr-1"></i>Agregar Producto
                                    </button>
                                </div>
                            </div>
                            <div id="products-container">
                                <div class="product-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border border-gray-200 rounded-md">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Producto</label>
                                        <select name="products[0][product_id]" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                                            <option value="">Seleccionar producto</option>
                                            <?php
                                            $stmt = $product->read();
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Cantidad</label>
                                        <input type="number" name="products[0][quantity]" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required min="1">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" onclick="removeProductRow(this)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm">
                                            <i class="fas fa-trash mr-1"></i>Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label for="reason" class="block text-sm font-medium text-gray-700">Razón</label>
                                <input type="text" name="reason" id="reason" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>
                            <div>
                                <label for="client_name" class="block text-sm font-medium text-gray-700">Nombre del Cliente</label>
                                <input type="text" name="client_name" id="client_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="md:col-span-2">
                                <label for="client_contact" class="block text-sm font-medium text-gray-700">Contacto del Cliente</label>
                                <input type="text" name="client_contact" id="client_contact" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Teléfono o email">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-save mr-2"></i>Registrar Movimiento
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow-xl">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-2xl font-bold text-gray-700">Historial de Movimientos</h3>
                        <form method="GET" class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <input type="text" name="search" placeholder="Buscar..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Tipo</option>
                                    <option value="entry" <?php echo (isset($_GET['type']) && $_GET['type'] == 'entry') ? 'selected' : ''; ?>>Entrada</option>
                                    <option value="exit" <?php echo (isset($_GET['type']) && $_GET['type'] == 'exit') ? 'selected' : ''; ?>>Salida</option>
                                </select>
                            </div>
                            <div>
                                <select name="product_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Producto</option>
                                    <?php
                                    $stmt_products = $product->read();
                                    while ($row_product = $stmt_products->fetch(PDO::FETCH_ASSOC)) {
                                        $selected = (isset($_GET['product_id']) && $_GET['product_id'] == $row_product['id']) ? 'selected' : '';
                                        echo "<option value='{$row_product['id']}' {$selected}>{$row_product['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <input type="date" name="date_from" value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <input type="date" name="date_to" value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="md:col-span-5 flex gap-2">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-200">
                                    <i class="fas fa-search mr-2"></i>Filtrar
                                </button>
                                <a href="inventory_movements.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-200">
                                    <i class="fas fa-times mr-2"></i>Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razón</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                $stmt = $movement->read($filters);
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $type_label = $row['type'] == 'entry' ? 'Entrada' : 'Salida';
                                    $type_color = $row['type'] == 'entry' ? 'text-green-600' : 'text-red-600';
                                    echo "<tr class='table-row'>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap font-medium text-gray-900'>{$row['product_name']}</td>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap {$type_color} font-semibold'>{$type_label}</td>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap text-gray-500'>{$row['quantity']}</td>";
                                    echo "<td class='py-4 px-6 text-gray-500'>{$row['reason']}</td>";
                                    echo "<td class='py-4 px-6 text-gray-500'>{$row['client_name']}</td>";
                                    echo "<td class='py-4 px-6 text-gray-500'>{$row['client_contact']}</td>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap text-gray-500'>{$row['date']}</td>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap text-sm font-medium'>";
                                    echo "<div class='flex space-x-2'>";
                                    echo "<a href='edit_movement.php?id={$row['id']}' class='bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-xs transition-colors duration-200'>";
                                    echo "<i class='fas fa-edit mr-1'></i>Editar";
                                    echo "</a>";
                                    echo "<button onclick='confirmDelete({$row['id']})' class='bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-xs transition-colors duration-200'>";
                                    echo "<i class='fas fa-trash mr-1'></i>Eliminar";
                                    echo "</button>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Notification Modal -->
    <div id="notification-modal" class="fixed top-4 right-4 z-50 hidden">
        <div class="bg-white rounded-lg shadow-2xl p-4 max-w-sm border-l-4" id="notification-border">
            <div class="flex items-center">
                <i id="notification-icon" class="text-2xl mr-3"></i>
                <div class="flex-1">
                    <p id="notification-message" class="text-gray-800"></p>
                </div>
                <button onclick="closeNotification()" class="text-gray-500 hover:text-gray-700 ml-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-40">
        <div class="bg-white rounded-lg shadow-2xl p-8 max-w-sm mx-auto">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Confirmar Eliminación</h2>
            <p class="text-gray-600 mb-6">¿Estás seguro de que quieres eliminar este movimiento? Esta acción no se puede deshacer.</p>
            <div class="flex justify-end">
                <button id="cancel-delete" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-4 transition-colors duration-200">Cancelar</button>
                <a href="#" id="confirm-delete" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">Eliminar</a>
            </div>
        </div>
    </div>

    <script>
        function showNotification(message, type = 'success') {
            const modal = document.getElementById('notification-modal');
            const border = document.getElementById('notification-border');
            const icon = document.getElementById('notification-icon');
            const msg = document.getElementById('notification-message');

            msg.textContent = message;

            if (type === 'success') {
                border.className = 'bg-white rounded-lg shadow-2xl p-4 max-w-sm border-l-4 border-green-500';
                icon.className = 'fas fa-check-circle text-green-500 text-2xl mr-3';
            } else if (type === 'error') {
                border.className = 'bg-white rounded-lg shadow-2xl p-4 max-w-sm border-l-4 border-red-500';
                icon.className = 'fas fa-exclamation-triangle text-red-500 text-2xl mr-3';
            }

            modal.classList.remove('hidden');
            anime({
                targets: '#notification-modal',
                translateX: [300, 0],
                opacity: [0, 1],
                duration: 300,
                easing: 'easeOutExpo'
            });

            // Auto close after 3 seconds
            setTimeout(() => {
                closeNotification();
            }, 3000);
        }

        function closeNotification() {
            const modal = document.getElementById('notification-modal');
            anime({
                targets: '#notification-modal',
                translateX: [0, 300],
                opacity: [1, 0],
                duration: 300,
                easing: 'easeInExpo',
                complete: () => {
                    modal.classList.add('hidden');
                }
            });
        }

        function confirmDelete(id) {
            const deleteModal = document.getElementById('delete-modal');
            const confirmDelete = document.getElementById('confirm-delete');

            confirmDelete.href = `inventory_movements.php?delete=1&id=${id}`;
            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');

            anime({
                targets: '#delete-modal .bg-white',
                scale: [0.7, 1],
                opacity: [0, 1],
                duration: 300,
                easing: 'easeOutExpo'
            });
        }

        function closeDeleteModal() {
            const deleteModal = document.getElementById('delete-modal');
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
        }

        // Add event listener for cancel button
        document.getElementById('cancel-delete').addEventListener('click', closeDeleteModal);

        // Close modal when clicking outside
        document.getElementById('delete-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        <?php if($notification): ?>
        showNotification('<?php echo $notification; ?>', '<?php echo $notification_type; ?>');
        <?php endif; ?>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            content.classList.toggle('md:ml-64');
        });

        // Animations with Anime.js
        anime({
            targets: '.table-row',
            translateY: [50, 0],
            opacity: [0, 1],
            delay: anime.stagger(100),
            easing: 'easeOutExpo'
        });

        function toggleProductSection() {
            const type = document.getElementById('type').value;
            const singleSection = document.getElementById('single-product-section');
            const multipleSection = document.getElementById('multiple-products-section');

            if(type === 'exit') {
                singleSection.classList.add('hidden');
                multipleSection.classList.remove('hidden');
            } else {
                singleSection.classList.remove('hidden');
                multipleSection.classList.add('hidden');
            }
        }

        function addProductRow() {
            const container = document.getElementById('products-container');
            const rowCount = container.children.length;
            const rowHtml = `
                <div class="product-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border border-gray-200 rounded-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Producto</label>
                        <select name="products[${rowCount}][product_id]" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Seleccionar producto</option>
                            <?php
                            $stmt = $product->read();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cantidad</label>
                        <input type="number" name="products[${rowCount}][quantity]" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required min="1">
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="removeProductRow(this)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm">
                            <i class="fas fa-trash mr-1"></i>Eliminar
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', rowHtml);
        }

        function removeProductRow(button) {
            const row = button.closest('.product-row');
            row.remove();
        }
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
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

    // Enable error reporting for debugging
    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);

    // Check if user is logged in
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    include_once 'config/database.php';
    include_once 'objects/product.php';
    include_once 'objects/movement.php';
    include_once 'objects/partial_payment.php';

    $database = new Database();
    $db = $database->getConnection();

    $product = new Product($db);
    $movement = new Movement($db);
    $partial_payment = new PartialPayment($db);

    $filters = [];
    if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
    if (isset($_GET['type']) && $_GET['type'] != '') $filters['type'] = $_GET['type'];
    if (isset($_GET['product_id']) && $_GET['product_id'] != '') $filters['product_id'] = $_GET['product_id'];
    if (isset($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
    if (isset($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];

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
        // Debug: Log POST data
        error_log("POST data: " . print_r($_POST, true));

        if (isset($_POST['action']) && $_POST['action'] == 'add_partial_payment_amount') {
            $partial_payment->id = $_POST['pp_id'];
            $amount = $_POST['amount'];

            if ($partial_payment->readOne()) {
                if ($partial_payment->addPayment($amount)) {
                    $notification = 'Pago parcial registrado exitosamente.';
                    $notification_type = 'success';
                } else {
                    $notification = 'Error al registrar el pago parcial.';
                    $notification_type = 'error';
                }
            } else {
                $notification = 'Pago por partes no encontrado.';
                $notification_type = 'error';
            }
        } else {
            $movement_type = $_POST['type'];
            $reason = $_POST['reason'] ?? '';
            $client_name = $_POST['client_name'] ?? '';
            $client_contact = $_POST['client_contact'] ?? '';

            if ($movement_type != 'partial_payment') {
                $movement->type = $movement_type;
                $movement->reason = $reason;
                $movement->client_name = $client_name;
                $movement->client_contact = $client_contact;

                if($movement->type == 'entry') {
                    // Multiple products entry
                    $products = $_POST['entry_products'] ?? [];
                    error_log("Entry products: " . print_r($products, true));
                    if(empty($products)) {
                        $notification = 'Por favor agregue al menos un producto.';
                        $notification_type = 'error';
                    } else {
                        $success_count = 0;
                        $error_count = 0;

                        foreach($products as $product_data) {
                            if(!empty($product_data['product_id']) && !empty($product_data['quantity']) && $product_data['quantity'] > 0) {
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
                            $notification = "Se registraron {$success_count} entradas exitosamente.";
                            if($error_count > 0) {
                                $notification .= " {$error_count} entradas fallaron.";
                            }
                            $notification_type = $error_count > 0 ? 'warning' : 'success';
                        } else {
                            $notification = 'Error al registrar las entradas.';
                            $notification_type = 'error';
                        }
                    }
                } else {
                    // Multiple products exit
                    $products = $_POST['products'] ?? [];
                    error_log("Exit products: " . print_r($products, true));
                    if(empty($products)) {
                        $notification = 'Por favor agregue al menos un producto.';
                        $notification_type = 'error';
                    } else {
                        $success_count = 0;
                        $error_count = 0;

                        foreach($products as $product_data) {
                            if(!empty($product_data['product_id']) && !empty($product_data['quantity']) && $product_data['quantity'] > 0) {
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
                            $notification = "Se registraron {$success_count} salidas exitosamente.";
                            if($error_count > 0) {
                                $notification .= " {$error_count} salidas fallaron.";
                            }
                            $notification_type = $error_count > 0 ? 'warning' : 'success';
                        } else {
                            $notification = 'Error al registrar las salidas.';
                            $notification_type = 'error';
                        }
                    }
                }
            }
        }
    }

    // Handle delete partial payment action
    if(isset($_GET['delete_pp']) && isset($_GET['id'])) {
        $partial_payment->id = $_GET['id'];
        if($partial_payment->delete()) {
            $notification = 'Pago por partes eliminado exitosamente.';
            $notification_type = 'success';
        } else {
            $notification = 'Error al eliminar el pago por partes.';
            $notification_type = 'error';
        }
        header("Location: inventory_movements.php");
        exit();
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
                <h2 class="text-3xl font-bold text-gray-800">Movimientos de Inventario</h2>
            </header>

            <main class="p-6">
                <div class="bg-white rounded-lg shadow-xl p-6 mb-8">
                    <h3 class="text-2xl font-bold mb-6 text-gray-700">Registrar Movimiento</h3>
                    <form action="process_partial_payments.php" method="post" id="movement-form">
                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700">Tipo</label>
                            <select name="type" id="type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required onchange="toggleProductSection()">
                                <option value="entry">Entrada</option>
                                <option value="exit">Salida</option>
                                <option value="partial_payment">Pago por Partes</option>
                            </select>
                        </div>

                        <!-- Partial Payment Section -->
                        <div id="partial-payment-section" class="hidden">
                             <div class="mb-4">
                                 <div class="flex justify-between items-center">
                                     <label class="block text-sm font-medium text-gray-700">Productos</label>
                                     <button type="button" onclick="addPartialPaymentProductRow()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-md text-sm font-medium" style="min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
                                         <i class="fas fa-plus mr-1"></i>Agregar Producto
                                     </button>
                                 </div>
                             </div>
                             <div id="partial-payment-products-container">
                                 <div class="partial-payment-product-row grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 p-4 border border-gray-200 rounded-md">
                                     <div>
                                         <label class="block text-sm font-medium text-gray-700">Producto</label>
                                         <select name="pp_products[0][product_id]" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" required style="display: block; min-height: 44px;">
                                             <option value="">Seleccionar producto</option>
                                             <?php
                                             $stmt = $product->read();
                                             while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                 echo "<option value='{$row['id']}' data-price='{$row['sale_price']}'>{$row['name']} (Precio: {$row['sale_price']})</option>";
                                             }
                                             ?>
                                         </select>
                                     </div>
                                     <div class="flex items-end">
                                         <button type="button" onclick="removePartialPaymentProductRow(this)" class="bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-md text-sm font-medium" style="min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
                                             <i class="fas fa-trash mr-1"></i>Eliminar
                                         </button>
                                     </div>
                                 </div>
                             </div>
                             <div class="mb-4">
                                 <label for="pp_client_name" class="block text-sm font-medium text-gray-700">Nombre del Cliente</label>
                                 <input type="text" name="pp_client_name" id="pp_client_name" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" required style="min-height: 44px;">
                             </div>
                             <div class="mb-4">
                                 <label for="pp_client_contact" class="block text-sm font-medium text-gray-700">Contacto del Cliente</label>
                                 <input type="text" name="pp_client_contact" id="pp_client_contact" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" placeholder="Teléfono o email" required style="min-height: 44px;">
                             </div>
                         </div>

                        <!-- Products section for entries -->
                        <div id="entry-products-section">
                            <div class="mb-4">
                                <div class="flex justify-between items-center">
                                    <label class="block text-sm font-medium text-gray-700">Productos</label>
                                    <button type="button" onclick="addEntryProductRow()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-md text-sm font-medium" style="min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-plus mr-1"></i>Agregar Producto
                                    </button>
                                </div>
                            </div>
                            <div id="entry-products-container">
                                <div class="product-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border border-gray-200 rounded-md">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Producto</label>
                                        <select name="entry_products[0][product_id]" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" required style="display: block; min-height: 44px;">
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
                                        <input type="number" name="entry_products[0][quantity]" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" required min="1" style="min-height: 44px;">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" onclick="removeEntryProductRow(this)" class="bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-md text-sm font-medium" style="min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-trash mr-1"></i>Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Multiple products section for bulk entries -->
                        <div id="bulk-entry-section" class="hidden">
                            <div class="mb-4">
                                <div class="flex justify-between items-center">
                                    <label class="block text-sm font-medium text-gray-700">Productos a Entrar</label>
                                    <button type="button" onclick="addBulkEntryRow()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm">
                                        <i class="fas fa-plus mr-1"></i>Agregar Producto
                                    </button>
                                </div>
                            </div>
                            <div id="bulk-entry-container">
                                <div class="bulk-entry-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border border-gray-200 rounded-md">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Producto</label>
                                        <select name="bulk_products[0][product_id]" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
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
                                        <input type="number" name="bulk_products[0][quantity]" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" min="1">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" onclick="removeBulkEntryRow(this)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm">
                                            <i class="fas fa-trash mr-1"></i>Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Multiple products section for exits -->
                        <div id="multiple-products-section" class="hidden">
                            <div class="mb-4">
                                <div class="flex justify-between items-center">
                                    <label class="block text-sm font-medium text-gray-700">Productos</label>
                                    <button type="button" onclick="addProductRow()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-md text-sm font-medium" style="min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-plus mr-1"></i>Agregar Producto
                                    </button>
                                </div>
                            </div>
                            <div id="products-container">
                                <div class="product-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border border-gray-200 rounded-md">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Producto</label>
                                        <select name="products[0][product_id]" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" required style="display: block; min-height: 44px;">
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
                                        <input type="number" name="products[0][quantity]" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" required min="1" style="min-height: 44px;">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" onclick="removeProductRow(this)" class="bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-md text-sm font-medium" style="min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-trash mr-1"></i>Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-6">
                            <div>
                                <label for="reason" class="block text-sm font-medium text-gray-700">Razón</label>
                                <input type="text" name="reason" id="reason" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" required style="min-height: 44px;">
                            </div>
                            <div>
                                <label for="client_name" class="block text-sm font-medium text-gray-700">Nombre del Cliente</label>
                                <input type="text" name="client_name" id="client_name" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" style="min-height: 44px;">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="client_contact" class="block text-sm font-medium text-gray-700">Contacto del Cliente</label>
                                <input type="text" name="client_contact" id="client_contact" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" placeholder="Teléfono o email" style="min-height: 44px;">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-lg shadow-md transition-all duration-200 transform hover:scale-105 w-full sm:w-auto text-lg" style="min-height: 48px;">
                                <i class="fas fa-save mr-2"></i>Registrar Movimiento
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow-xl mb-8">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-700">Pagos por Partes</h3>
                    </div>
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo Total</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pagado</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restante</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                $stmt_pp = $partial_payment->read();
                                while ($row_pp = $stmt_pp->fetch(PDO::FETCH_ASSOC)) {
                                    $status_label = $row_pp["is_completed"] ? "Completado" : "Pendiente";
                                    $status_color = $row_pp["is_completed"] ? "text-green-600" : "text-yellow-600";
                                    echo "<tr class='table-row'>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap font-medium text-gray-900'>{$row_pp['product_name']}</td>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap text-gray-500'>{$row_pp['total_amount']}</td>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap text-gray-500'>{$row_pp['paid_amount']}</td>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap text-gray-500'>{$row_pp['remaining_amount']}</td>";
                                    echo "<td class='py-4 px-6 text-gray-500'>{$row_pp['client_name']}</td>";
                                    echo "<td class='py-4 px-6 text-gray-500'>{$row_pp['client_contact']}</td>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap {$status_color} font-semibold'>{$status_label}</td>";
                                    echo "<td class='py-4 px-6 whitespace-nowrap text-sm font-medium'>";
                                    if (!$row_pp["is_completed"]) {
                                        echo "<button onclick='openAddPaymentModal({$row_pp['id']}, {$row_pp['remaining_amount']})' class='bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-xs transition-colors duration-200 mr-2'>";
                                        echo "<i class='fas fa-money-bill-wave mr-1'></i>Añadir Pago";
                                        echo "</button>";
                                    }
                                    echo "<button onclick='confirmDeletePartialPayment({$row_pp['id']})' class='bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-xs transition-colors duration-200'>";
                                    echo "<i class='fas fa-trash mr-1'></i>Eliminar";
                                    echo "</button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-xl">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-700">Historial de Movimientos</h3>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="block md:hidden">
                        <?php
                        $stmt_mobile = $movement->read($filters);
                        while ($row = $stmt_mobile->fetch(PDO::FETCH_ASSOC)) {
                            $type_label = $row['type'] == 'entry' ? 'Entrada' : 'Salida';
                            $type_color = $row['type'] == 'entry' ? 'text-green-600' : 'text-red-600';
                            $bg_color = $row['type'] == 'entry' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
                            echo "<div class='{$bg_color} border rounded-lg p-4 m-4'>";
                            echo "<div class='flex justify-between items-start mb-3'>";
                            echo "<div class='flex-1'>";
                            echo "<h4 class='font-bold text-lg text-gray-900'>{$row['product_name']}</h4>";
                            echo "<p class='{$type_color} font-semibold'>{$type_label}</p>";
                            echo "</div>";
                            echo "<div class='text-right'>";
                            echo "<div class='flex space-x-2'>";
                            echo "<a href='edit_movement.php?id={$row['id']}' class='bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg transition-colors duration-200' style='min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;'><i class='fas fa-edit'></i></a>";
                            echo "<button onclick='confirmDelete({$row['id']})' class='bg-red-600 hover:bg-red-700 text-white p-2 rounded-lg transition-colors duration-200' style='min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;'><i class='fas fa-trash'></i></button>";
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                            echo "<div class='grid grid-cols-2 gap-4 mb-3'>";
                            echo "<div><span class='text-gray-500 text-sm'>Cantidad:</span><br><span class='font-semibold'>{$row['quantity']}</span></div>";
                            echo "<div><span class='text-gray-500 text-sm'>Fecha:</span><br><span class='font-semibold'>{$row['date']}</span></div>";
                            echo "</div>";
                            if($row['reason']) echo "<div class='mb-2'><span class='text-gray-500 text-sm'>Razón:</span><br><span class='font-medium'>{$row['reason']}</span></div>";
                            if($row['client_name']) echo "<div class='mb-2'><span class='text-gray-500 text-sm'>Cliente:</span><br><span class='font-medium'>{$row['client_name']}</span></div>";
                            if($row['client_contact']) echo "<div><span class='text-gray-500 text-sm'>Contacto:</span><br><span class='font-medium'>{$row['client_contact']}</span></div>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
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

    <!-- Add Payment Modal -->
    <div id="add-payment-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-40">
        <div class="bg-white rounded-lg shadow-2xl p-8 max-w-md mx-auto">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Registrar Pago Parcial</h2>
            <form action="inventory_movements.php" method="post">
                <input type="hidden" name="action" value="add_partial_payment_amount">
                <input type="hidden" name="pp_id" id="add-payment-pp-id">
                <div class="mb-4">
                    <p class="text-gray-700">Cantidad restante: <span id="add-payment-remaining-amount" class="font-semibold"></span></p>
                </div>
                <div class="mb-4">
                    <label for="add-payment-amount" class="block text-sm font-medium text-gray-700">Monto del Pago</label>
                    <input type="number" name="amount" id="add-payment-amount" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" step="0.01" min="0.01" required>
                </div>
                <div class="flex justify-end">
                    <button type="button" id="cancel-add-payment" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-4 transition-colors duration-200">Cancelar</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal for Partial Payments -->
    <div id="delete-partial-payment-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-40">
        <div class="bg-white rounded-lg shadow-2xl p-8 max-w-sm mx-auto">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Confirmar Eliminación de Pago por Partes</h2>
            <p class="text-gray-600 mb-6">¿Estás seguro de que quieres eliminar este pago por partes? Esta acción no se puede deshacer.</p>
            <div class="flex justify-end">
                <button id="cancel-delete-partial-payment" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-4 transition-colors duration-200">Cancelar</button>
                <a href="#" id="confirm-delete-partial-payment" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">Eliminar</a>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal for Movements -->
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

        // Initialize the form on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleProductSection();
        });

        <?php
        if(isset($_SESSION['notification'])) {
            $notification = $_SESSION['notification'];
            $notification_type = $_SESSION['notification_type'];
            unset($_SESSION['notification']);
            unset($_SESSION['notification_type']);
        }
        if($notification): ?>
            showNotification('<?php echo $notification; ?>', '<?php echo $notification_type; ?>');
            <?php endif; ?>
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

        function toggleProductSection() {
            const type = document.getElementById('type').value;
            const entrySection = document.getElementById('entry-products-section');
            const multipleSection = document.getElementById('multiple-products-section');
            const partialPaymentSection = document.getElementById('partial-payment-section');

            // Hide all sections initially
            entrySection.classList.add('hidden');
            multipleSection.classList.add('hidden');
            partialPaymentSection.classList.add('hidden');

            // Remove required attributes from all product sections
            document.querySelectorAll('#entry-products-container select[required]').forEach(select => select.removeAttribute('required'));
            document.querySelectorAll('#entry-products-container input[required]').forEach(input => input.removeAttribute('required'));
            document.querySelectorAll('#products-container select[required]').forEach(select => select.removeAttribute('required'));
            document.querySelectorAll('#products-container input[required]').forEach(input => input.removeAttribute('required'));
            document.querySelectorAll('#partial-payment-section select[required]').forEach(select => select.removeAttribute('required'));
            document.querySelectorAll('#partial-payment-section input[required]').forEach(input => input.removeAttribute('required'));

            if (type === 'exit') {
                multipleSection.classList.remove('hidden');
                document.querySelectorAll('#products-container select').forEach(select => select.setAttribute('required', 'required'));
                document.querySelectorAll('#products-container input[type="number"]').forEach(input => input.setAttribute('required', 'required'));
            } else if (type === 'entry') {
                entrySection.classList.remove('hidden');
                document.querySelectorAll('#entry-products-container select').forEach(select => select.setAttribute('required', 'required'));
                document.querySelectorAll('#entry-products-container input[type="number"]').forEach(input => input.setAttribute('required', 'required'));
            } else if (type === 'partial_payment') {
                partialPaymentSection.classList.remove('hidden');
                document.querySelectorAll('#partial-payment-products-container select').forEach(select => select.setAttribute('required', 'required'));
                document.getElementById('pp_client_name').setAttribute('required', 'required');
                document.getElementById('pp_client_contact').setAttribute('required', 'required');
            }
        }

        // Update total amount when product is selected for partial payment
        document.getElementById('pp_product_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.dataset.price;
            document.getElementById('pp_total_amount').value = price;
        });

        function addPartialPaymentProductRow() {
            const container = document.getElementById('partial-payment-products-container');
            const rowCount = container.children.length;
            const rowHtml = `
                <div class="partial-payment-product-row grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 p-4 border border-gray-200 rounded-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Producto</label>
                        <select name="pp_products[${rowCount}][product_id]" class="mt-1 block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base" required style="display: block; min-height: 44px;">
                            <option value="">Seleccionar producto</option>
                            <?php
                            $stmt = $product->read();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$row['id']}' data-price='{$row['sale_price']}'>{$row['name']} (Precio: {$row['sale_price']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="removePartialPaymentProductRow(this)" class="bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-md text-sm font-medium" style="min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-trash mr-1"></i>Eliminar
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', rowHtml);
        }

        function removePartialPaymentProductRow(button) {
            const row = button.closest('.partial-payment-product-row');
            row.remove();
        }

        function addEntryProductRow() {
            const container = document.getElementById('entry-products-container');
            const rowCount = container.children.length;
            const rowHtml = `
                <div class="product-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border border-gray-200 rounded-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Producto</label>
                        <select name="entry_products[${rowCount}][product_id]" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
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
                        <input type="number" name="entry_products[${rowCount}][quantity]" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required min="1">
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="removeEntryProductRow(this)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm">
                            <i class="fas fa-trash mr-1"></i>Eliminar
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', rowHtml);
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

        function addBulkEntryRow() {
            const container = document.getElementById('bulk-entry-container');
            const rowCount = container.children.length;
            const rowHtml = `
                <div class="bulk-entry-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border border-gray-200 rounded-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Producto</label>
                        <select name="bulk_products[${rowCount}][product_id]" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
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
                        <input type="number" name="bulk_products[${rowCount}][quantity]" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" min="1">
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="removeBulkEntryRow(this)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm">
                            <i class="fas fa-trash mr-1"></i>Eliminar
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', rowHtml);
        }

        function removeBulkEntryRow(button) {
            const row = button.closest('.bulk-entry-row');
            row.remove();
        }

        function removeEntryProductRow(button) {
            const row = button.closest('.product-row');
            row.remove();
        }

        // Add Payment Modal functions
        function openAddPaymentModal(partialPaymentId, remainingAmount) {
            document.getElementById('add-payment-pp-id').value = partialPaymentId;
            document.getElementById('add-payment-remaining-amount').textContent = parseFloat(remainingAmount).toFixed(2);
            document.getElementById('add-payment-amount').max = remainingAmount;
            document.getElementById('add-payment-modal').classList.remove('hidden');
            document.getElementById('add-payment-modal').classList.add('flex');
            anime({
                targets: '#add-payment-modal .bg-white',
                scale: [0.7, 1],
                opacity: [0, 1],
                duration: 300,
                easing: 'easeOutExpo'
            });
        }

        function closeAddPaymentModal() {
            const modal = document.getElementById('add-payment-modal');
            anime({
                targets: '#add-payment-modal .bg-white',
                scale: [1, 0.7],
                opacity: [1, 0],
                duration: 300,
                easing: 'easeInExpo',
                complete: () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        }

        // Delete Partial Payment Confirmation Modal functions
        function confirmDeletePartialPayment(id) {
            const deleteModal = document.getElementById('delete-partial-payment-modal');
            const confirmDeleteBtn = document.getElementById('confirm-delete-partial-payment');

            confirmDeleteBtn.href = `inventory_movements.php?delete_pp=1&id=${id}`;
            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');

            anime({
                targets: '#delete-partial-payment-modal .bg-white',
                scale: [0.7, 1],
                opacity: [0, 1],
                duration: 300,
                easing: 'easeOutExpo'
            });
        }

        function closeDeletePartialPaymentModal() {
            const deleteModal = document.getElementById('delete-partial-payment-modal');
            anime({
                targets: '#delete-partial-payment-modal .bg-white',
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

        document.getElementById('cancel-add-payment').addEventListener('click', closeAddPaymentModal);
        document.getElementById('cancel-delete-partial-payment').addEventListener('click', closeDeletePartialPaymentModal);

        document.getElementById('add-payment-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddPaymentModal();
            }
        });

        document.getElementById('delete-partial-payment-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeletePartialPaymentModal();
            }
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

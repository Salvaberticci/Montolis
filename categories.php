<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías - Inventario Montoli's</title>
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
    include_once 'objects/category.php';

    $database = new Database();
    $db = $database->getConnection();

    $category = new Category($db);
    $message = '';

    // Handle form submissions
    if($_POST){
        if(isset($_POST['action'])) {
            switch($_POST['action']) {
                case 'create':
                    $category->name = $_POST['name'];
                    $category->description = $_POST['description'];
                    $category->color = $_POST['color'];
                    $category->icon = $_POST['icon'];
                    $category->is_active = isset($_POST['is_active']) ? 1 : 0;
                    $category->sort_order = $_POST['sort_order'];

                    if($category->create()){
                        $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                                        <strong class='font-bold'>Éxito!</strong>
                                        <span class='block sm:inline'>Categoría creada exitosamente.</span>
                                    </div>";
                    } else {
                        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                                        <strong class='font-bold'>Error!</strong>
                                        <span class='block sm:inline'>No se pudo crear la categoría.</span>
                                    </div>";
                    }
                    break;

                case 'update':
                    $category->id = $_POST['id'];
                    $category->name = $_POST['name'];
                    $category->description = $_POST['description'];
                    $category->color = $_POST['color'];
                    $category->icon = $_POST['icon'];
                    $category->is_active = isset($_POST['is_active']) ? 1 : 0;
                    $category->sort_order = $_POST['sort_order'];

                    if($category->update()){
                        $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                                        <strong class='font-bold'>Éxito!</strong>
                                        <span class='block sm:inline'>Categoría actualizada exitosamente.</span>
                                    </div>";
                    } else {
                        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                                        <strong class='font-bold'>Error!</strong>
                                        <span class='block sm:inline'>No se pudo actualizar la categoría.</span>
                                    </div>";
                    }
                    break;

                case 'delete':
                    $category->id = $_POST['id'];
                    if($category->delete()){
                        $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                                        <strong class='font-bold'>Éxito!</strong>
                                        <span class='block sm:inline'>Categoría eliminada exitosamente.</span>
                                    </div>";
                    } else {
                        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                                        <strong class='font-bold'>Error!</strong>
                                        <span class='block sm:inline'>No se pudo eliminar la categoría. Puede estar siendo usada por productos.</span>
                                    </div>";
                    }
                    break;
            }
        }
    }

    // Handle edit request
    $edit_category = null;
    if(isset($_GET['edit'])) {
        $category->id = $_GET['edit'];
        if($category->readOne()) {
            $edit_category = $category;
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
                <a href="categories.php" class="flex items-center py-3 px-6 text-gray-300 bg-gray-700">
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
        <div id="content" class="flex-1 md:ml-64 transition-all duration-300 ease-in-out">
            <header class="bg-white shadow-md p-4 flex justify-between items-center">
                <button id="menu-toggle" class="md:hidden text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors" style="min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <h2 class="text-3xl font-bold text-gray-800">Gestión de Categorías</h2>
                <button onclick="openCreateModal()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>Nueva Categoría
                </button>
            </header>

            <main class="p-6">
                <div class="max-w-6xl mx-auto">
                    <?php echo $message; ?>

                    <!-- Categories Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <?php
                        $stmt = $category->read();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                            extract($row);
                            $product_count = $category->getProductCount($name);
                            $status_class = $is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                            $status_text = $is_active ? 'Activa' : 'Inactiva';
                            echo "<div class='bg-white rounded-lg shadow-xl p-6 border-l-4' style='border-left-color: {$color};'>
                                    <div class='flex items-center justify-between mb-4'>
                                        <div class='flex items-center'>
                                            <div class='p-3 rounded-full mr-4' style='background-color: {$color};'>
                                                <i class='{$icon} text-white text-xl'></i>
                                            </div>
                                            <div>
                                                <h3 class='text-xl font-bold text-gray-800'>{$name}</h3>
                                                <span class='inline-block px-2 py-1 text-xs font-semibold rounded-full {$status_class}'>{$status_text}</span>
                                            </div>
                                        </div>
                                        <div class='flex space-x-2'>
                                            <button onclick='editCategory({$id})' class='bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-lg transition-colors duration-200' title='Editar'>
                                                <i class='fas fa-pencil-alt'></i>
                                            </button>
                                            <button onclick='deleteCategory({$id}, \"{$name}\")' class='bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition-colors duration-200' title='Eliminar'>
                                                <i class='fas fa-trash-alt'></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p class='text-gray-600 text-sm mb-4'>{$description}</p>
                                    <div class='flex justify-between items-center text-sm text-gray-500'>
                                        <span><i class='fas fa-box mr-1'></i>{$product_count} productos</span>
                                        <span><i class='fas fa-sort mr-1'></i>Orden: {$sort_order}</span>
                                    </div>
                                </div>";
                        }
                        ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Create/Edit Category Modal -->
    <div id="category-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-2xl max-w-lg mx-auto overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="modal-title" class="text-2xl font-bold text-gray-800">Nueva Categoría</h2>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="category-form" action="categories.php" method="post">
                    <input type="hidden" name="action" id="form-action" value="create">
                    <input type="hidden" name="id" id="category-id">

                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nombre</label>
                        <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" required>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Descripción</label>
                        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="color" class="block text-gray-700 text-sm font-bold mb-2">Color</label>
                            <input type="color" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline h-10" id="color" name="color" value="#3B82F6">
                        </div>
                        <div>
                            <label for="sort_order" class="block text-gray-700 text-sm font-bold mb-2">Orden</label>
                            <input type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="sort_order" name="sort_order" value="0" min="0">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="icon" class="block text-gray-700 text-sm font-bold mb-2">Ícono (FontAwesome)</label>
                        <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="icon" name="icon">
                            <option value="fas fa-tag">Tag</option>
                            <option value="fas fa-home">Home</option>
                            <option value="fas fa-mobile-alt">Mobile</option>
                            <option value="fas fa-futbol">Sports</option>
                            <option value="fas fa-spa">Beauty</option>
                            <option value="fas fa-tshirt">Clothing</option>
                            <option value="fas fa-gamepad">Games</option>
                            <option value="fas fa-book">Books</option>
                            <option value="fas fa-car">Car</option>
                            <option value="fas fa-heartbeat">Health</option>
                            <option value="fas fa-tools">Tools</option>
                            <option value="fas fa-utensils">Kitchen</option>
                            <option value="fas fa-music">Music</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-green-600" id="is_active" name="is_active" checked>
                            <span class="ml-2 text-gray-700">Categoría activa</span>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-4 transition-colors duration-200">Cancelar</button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-2xl p-6 max-w-sm mx-auto">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Confirmar Eliminación</h2>
            <p id="delete-message" class="text-gray-600 mb-6">¿Estás seguro de que quieres eliminar esta categoría?</p>
            <div class="flex justify-end">
                <button id="cancel-delete" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-4 transition-colors duration-200">Cancelar</button>
                <form id="delete-form" action="categories.php" method="post" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete-category-id">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">Eliminar</button>
                </form>
            </div>
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

        function openCreateModal() {
            document.getElementById('modal-title').textContent = 'Nueva Categoría';
            document.getElementById('form-action').value = 'create';
            document.getElementById('category-form').reset();
            document.getElementById('category-id').value = '';
            document.getElementById('category-modal').classList.remove('hidden');
            document.getElementById('category-modal').classList.add('flex');
            anime({
                targets: '#category-modal .bg-white',
                scale: [0.7, 1],
                opacity: [0, 1],
                duration: 300,
                easing: 'easeOutExpo'
            });
        }

        function editCategory(id) {
            // This would need AJAX to fetch category data, but for now we'll use GET parameter
            window.location.href = `categories.php?edit=${id}`;
        }

        function deleteCategory(id, name) {
            document.getElementById('delete-category-id').value = id;
            document.getElementById('delete-message').textContent = `¿Estás seguro de que quieres eliminar la categoría "${name}"?`;
            document.getElementById('delete-modal').classList.remove('hidden');
            document.getElementById('delete-modal').classList.add('flex');
            anime({
                targets: '#delete-modal',
                scale: [0.7, 1],
                opacity: [0, 1],
                duration: 300,
                easing: 'easeOutExpo'
            });
        }

        function closeModal() {
            anime({
                targets: '#category-modal .bg-white',
                scale: [1, 0.7],
                opacity: [1, 0],
                duration: 300,
                easing: 'easeInExpo',
                complete: () => {
                    document.getElementById('category-modal').classList.add('hidden');
                    document.getElementById('category-modal').classList.remove('flex');
                }
            });
        }

        // Pre-fill form if editing
        <?php if($edit_category): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modal-title').textContent = 'Editar Categoría';
            document.getElementById('form-action').value = 'update';
            document.getElementById('category-id').value = '<?php echo $edit_category->id; ?>';
            document.getElementById('name').value = '<?php echo addslashes($edit_category->name); ?>';
            document.getElementById('description').value = '<?php echo addslashes($edit_category->description); ?>';
            document.getElementById('color').value = '<?php echo $edit_category->color; ?>';
            document.getElementById('icon').value = '<?php echo $edit_category->icon; ?>';
            document.getElementById('sort_order').value = '<?php echo $edit_category->sort_order; ?>';
            document.getElementById('is_active').checked = <?php echo $edit_category->is_active ? 'true' : 'false'; ?>;
            document.getElementById('category-modal').classList.remove('hidden');
            document.getElementById('category-modal').classList.add('flex');
        });
        <?php endif; ?>

        // Close delete modal
        document.getElementById('cancel-delete').addEventListener('click', () => {
            anime({
                targets: '#delete-modal',
                scale: [1, 0.7],
                opacity: [1, 0],
                duration: 300,
                easing: 'easeInExpo',
                complete: () => {
                    document.getElementById('delete-modal').classList.add('hidden');
                    document.getElementById('delete-modal').classList.remove('flex');
                }
            });
        });

        // Animation with Anime.js
        anime({
            targets: '.bg-white',
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
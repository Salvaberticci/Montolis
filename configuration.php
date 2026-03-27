<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include_once 'config/database.php';
include_once 'objects/user.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$message = '';

// Handle Password Change
if(isset($_POST['change_password'])) {
    $user->id = $_SESSION['user_id'];
    if($user->changePassword($_POST['new_password'])) {
        $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                        <strong class='font-bold'>Éxito!</strong>
                        <span class='block sm:inline'>Contraseña actualizada correctamente.</span>
                    </div>";
    } else {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                        <strong class='font-bold'>Error!</strong>
                        <span class='block sm:inline'>No se pudo actualizar la contraseña.</span>
                    </div>";
    }
}

// Handle Add User
if(isset($_POST['add_user'])) {
    $user->username = $_POST['username'];
    $user->email = $_POST['email'];
    $user->password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user->role = $_POST['role'];
    
    if($user->create()) {
        $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                        <strong class='font-bold'>Éxito!</strong>
                        <span class='block sm:inline'>Usuario creado correctamente.</span>
                    </div>";
    } else {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                        <strong class='font-bold'>Error!</strong>
                        <span class='block sm:inline'>No se pudo crear el usuario.</span>
                    </div>";
    }
}

// Handle Delete User
if(isset($_GET['delete_user_id'])) {
    $user->id = $_GET['delete_user_id'];
    // Prevent deleting self
    if($user->id == $_SESSION['user_id']) {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                        <strong class='font-bold'>Error!</strong>
                        <span class='block sm:inline'>No puedes eliminar tu propio usuario.</span>
                    </div>";
    } else {
        if($user->delete()) {
            $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                            <strong class='font-bold'>Éxito!</strong>
                            <span class='block sm:inline'>Usuario eliminado correctamente.</span>
                        </div>";
        } else {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                            <strong class='font-bold'>Error!</strong>
                            <span class='block sm:inline'>No se pudo eliminar el usuario.</span>
                        </div>";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Inventario Montoli's</title>
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
    <div class="flex">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-800 text-white w-64 min-h-screen fixed top-0 left-0 transform translate-x-0 transition-transform duration-300 ease-in-out z-30 md:translate-x-0 -translate-x-full">
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
                <a href="statistics.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-chart-bar mr-3"></i> Estadísticas
                </a>
                <a href="configuration.php" class="flex items-center py-3 px-6 text-gray-300 bg-gray-700">
                    <i class="fas fa-tools mr-3"></i> Configuración
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

        <!-- Page Content -->
        <div id="content" class="flex-1 transition-all duration-300 ease-in-out md:ml-64">
            <header class="bg-white shadow-md p-4 flex justify-between items-center">
                <button id="menu-toggle" class="md:hidden text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors" style="min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <h2 class="text-3xl font-bold text-gray-800">Configuración</h2>
            </header>

            <main class="p-6">
                <div class="max-w-6xl mx-auto">
                    <?php echo $message; ?>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- System Information -->
                        <div class="bg-white rounded-lg shadow-xl p-6 transform transition-transform hover:scale-[1.02]">
                            <h3 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
                                <i class="fas fa-server mr-2 text-blue-500"></i> Información del Sistema
                            </h3>
                            <div class="space-y-4">
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600">Versión PHP:</span>
                                    <span class="font-semibold"><?php echo phpversion(); ?></span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600">S.O. Servidor:</span>
                                    <span class="font-semibold"><?php echo PHP_OS; ?></span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600">Base de Datos:</span>
                                    <span class="text-green-600 font-semibold">Conectado</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Hora del Sistema:</span>
                                    <span class="font-semibold"><?php echo date('Y-m-d H:i:s'); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Change Password -->
                        <div class="bg-white rounded-lg shadow-xl p-6 transform transition-transform hover:scale-[1.02]">
                            <h3 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
                                <i class="fas fa-key mr-2 text-yellow-500"></i> Cambiar Contraseña
                            </h3>
                            <form action="configuration.php" method="post">
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nueva Contraseña</label>
                                    <input type="password" name="new_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                </div>
                                <button type="submit" name="change_password" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded w-full transition-colors">
                                    Actualizar Contraseña
                                </button>
                            </form>
                        </div>

                        <!-- Add User -->
                        <div class="bg-white rounded-lg shadow-xl p-6 transform transition-transform hover:scale-[1.02]">
                            <h3 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
                                <i class="fas fa-user-plus mr-2 text-green-500"></i> Agregar Usuario
                            </h3>
                            <form action="configuration.php" method="post">
                                <div class="mb-3">
                                    <label class="block text-gray-700 text-sm font-bold mb-1">Usuario</label>
                                    <input type="text" name="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                </div>
                                <div class="mb-3">
                                    <label class="block text-gray-700 text-sm font-bold mb-1">Email</label>
                                    <input type="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                </div>
                                <div class="mb-3">
                                    <label class="block text-gray-700 text-sm font-bold mb-1">Contraseña</label>
                                    <input type="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-1">Rol</label>
                                    <select name="role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        <option value="admin">Administrador</option>
                                        <option value="user">Usuario (Staff)</option>
                                    </select>
                                </div>
                                <button type="submit" name="add_user" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded w-full transition-colors">
                                    Crear Usuario
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- User List -->
                    <div class="mt-8 bg-white rounded-lg shadow-xl overflow-hidden">
                        <div class="p-6 bg-gray-50 border-b">
                            <h3 class="text-xl font-bold text-gray-800">Usuarios del Sistema</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-100 uppercase text-xs font-semibold text-gray-600">
                                    <tr>
                                        <th class="px-6 py-3 text-left">Usuario</th>
                                        <th class="px-6 py-3 text-left">Email</th>
                                        <th class="px-6 py-3 text-left">Rol</th>
                                        <th class="px-6 py-3 text-left">Creado</th>
                                        <th class="px-6 py-3 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php
                                    $stmt = $user->readAll();
                                    if($stmt) {
                                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<tr>";
                                            echo "<td class='px-6 py-4 font-medium text-gray-900'>{$row['username']}</td>";
                                            echo "<td class='px-6 py-4 text-gray-600'>{$row['email']}</td>";
                                            echo "<td class='px-6 py-4'><span class='px-2 py-1 rounded-full text-xs font-bold " . ($row['role'] == 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700') . "'>{$row['role']}</span></td>";
                                            echo "<td class='px-6 py-4 text-gray-500'>{$row['created_at']}</td>";
                                            echo "<td class='px-6 py-4 text-center'>";
                                            if($row['id'] != $_SESSION['user_id']) {
                                                echo "<a href='configuration.php?delete_user_id={$row['id']}' class='text-red-500 hover:text-red-700 transition-colors' onclick='return confirm(\"¿Estás seguro de eliminar este usuario?\")'><i class='fas fa-trash-alt'></i></a>";
                                            } else {
                                                echo "<span class='text-gray-400' title='No puedes eliminarte a ti mismo'><i class='fas fa-trash-alt'></i></span>";
                                            }
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='px-6 py-4 text-center text-red-500'>Error al cargar usuarios. Por favor, verifique la base de datos.</td></tr>";
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

    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }

        // Close sidebar when clicking outside on mobile
        content.addEventListener('click', () => {
            if (window.innerWidth < 768 && !sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
            }
        });

        // Animation
        anime({
            targets: '.bg-white',
            translateY: [20, 0],
            opacity: [0, 1],
            delay: anime.stagger(100),
            easing: 'easeOutExpo'
        });
    </script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        particlesJS("particles-js", {
            "particles": {
                "number": { "value": 60, "density": { "enable": true, "value_area": 800 } },
                "color": { "value": "#10B981" },
                "shape": { "type": "circle" },
                "opacity": { "value": 0.3, "random": false },
                "size": { "value": 3, "random": true },
                "line_linked": { "enable": true, "distance": 150, "color": "#10B981", "opacity": 0.2, "width": 1 },
                "move": { "enable": true, "speed": 1, "direction": "none", "random": false, "straight": false, "out_mode": "out", "bounce": false }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": { "onhover": { "enable": true, "mode": "grab" }, "onclick": { "enable": true, "mode": "push" }, "resize": true }
            },
            "retina_detect": true
        });
    </script>
</body>
</html>

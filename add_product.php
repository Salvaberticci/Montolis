<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Producto - Inventario Montoli's</title>
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

    $database = new Database();
    $db = $database->getConnection();

    $product = new Product($db);
    $message = '';
    if($_POST){
        $product->name = $_POST['nombre'];
        $product->description = $_POST['descripcion'];
        $product->quantity = $_POST['cantidad'];
        $product->product_cost = $_POST['product_cost'];
        $product->sale_price = $_POST['sale_price'];
        $product->wholesale_price = $_POST['wholesale_price'];
        $product->third_party_sale_price = $_POST['third_party_sale_price'];
        $product->third_party_seller_percentage = $_POST['third_party_seller_percentage'];
        $product->category = $_POST['categoria'];

        $image=!empty($_FILES["imagen"]["name"])
            ? sha1_file($_FILES['imagen']['tmp_name']) . "-" . basename($_FILES["imagen"]["name"]) : "";
        $product->image = $image;

        if($product->create()){
            $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                            <strong class='font-bold'>Éxito!</strong>
                            <span class='block sm:inline'>Producto creado exitosamente.</span>
                        </div>";
            if($image){
                $target_directory = "uploads/";
                $target_file = $target_directory . $image;
                if(!move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)){
                    $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                                    <strong class='font-bold'>Error!</strong>
                                    <span class='block sm:inline'>No se pudo subir la imagen.</span>
                                </div>";
                }
            }
        } else {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                            <strong class='font-bold'>Error!</strong>
                            <span class='block sm:inline'>No se pudo crear el producto.</span>
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
                <a href="add_product.php" class="flex items-center py-3 px-6 text-gray-300 bg-gray-700">
                    <i class="fas fa-plus mr-3"></i> Añadir Producto
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
                <h2 class="text-3xl font-bold text-gray-800">Añadir Producto</h2>
            </header>

            <main class="p-6">
                <div class="max-w-3xl mx-auto">
                    <?php echo $message; ?>
                    <div class="bg-white rounded-lg shadow-xl p-8 form-container">
                        <form action="add_product.php" method="post" enctype="multipart/form-data">
                            <div class="mb-6">
                                <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre del Producto</label>
                                <input type="text" class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200" id="nombre" name="nombre" required>
                            </div>
                            <div class="mb-6">
                                <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción</label>
                                <textarea class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200" id="descripcion" name="descripcion" rows="4" required></textarea>
                            </div>
                            <div class="mb-6">
                                <label for="categoria" class="block text-gray-700 text-sm font-bold mb-2">Categoría</label>
                                <select class="shadow appearance-none border rounded w-full py-4 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200 text-base" id="categoria" name="categoria" required style="min-height: 44px;">
                                    <option value="General">General</option>
                                    <option value="Electrónicos">Electrónicos</option>
                                    <option value="Hogar">Hogar</option>
                                    <option value="Deportes">Deportes</option>
                                    <option value="Belleza">Belleza</option>
                                    <option value="Ropa">Ropa</option>
                                    <option value="Juguetes">Juguetes</option>
                                    <option value="Libros">Libros</option>
                                    <option value="Automotriz">Automotriz</option>
                                    <option value="Salud">Salud</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-6">
                                <div>
                                    <label for="cantidad" class="block text-gray-700 text-sm font-bold mb-2">Cantidad</label>
                                    <input type="number" class="shadow appearance-none border rounded w-full py-4 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200 text-base" id="cantidad" name="cantidad" required style="min-height: 44px;">
                                </div>
                                <div>
                                    <label for="product_cost" class="block text-gray-700 text-sm font-bold mb-2">Costo del Producto (USD)</label>
                                    <input type="text" class="shadow appearance-none border rounded w-full py-4 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200 text-base" id="product_cost" name="product_cost" required style="min-height: 44px;">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
                                <div>
                                    <label for="sale_price" class="block text-gray-700 text-sm font-bold mb-2">Precio de Venta (USD)</label>
                                    <input type="text" class="shadow appearance-none border rounded w-full py-4 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200 text-base" id="sale_price" name="sale_price" required style="min-height: 44px;">
                                </div>
                                <div>
                                    <label for="wholesale_price" class="block text-gray-700 text-sm font-bold mb-2">Precio al Mayor (USD)</label>
                                    <input type="text" class="shadow appearance-none border rounded w-full py-4 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200 text-base" id="wholesale_price" name="wholesale_price" required style="min-height: 44px;">
                                </div>
                                <div class="sm:col-span-2 lg:col-span-1">
                                    <label for="third_party_sale_price" class="block text-gray-700 text-sm font-bold mb-2">Precio de Venta para Terceros (USD)</label>
                                    <input type="text" class="shadow appearance-none border rounded w-full py-4 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200 text-base" id="third_party_sale_price" name="third_party_sale_price" required style="min-height: 44px;">
                                </div>
                            </div>
                             <div class="mb-6">
                                 <label for="third_party_seller_percentage" class="block text-gray-700 text-sm font-bold mb-2">Porcentaje de Vendedor para Terceros (%)</label>
                                 <input type="text" class="shadow appearance-none border rounded w-full py-4 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition-all duration-200 text-base" id="third_party_seller_percentage" name="third_party_seller_percentage" required style="min-height: 44px;">
                             </div>
                             <div class="mb-6">
                                 <label for="imagen" class="block text-gray-700 text-sm font-bold mb-2">Imagen del Producto</label>
                                 <input class="block w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer focus:outline-none p-4" type="file" id="imagen" name="imagen" style="min-height: 44px;">
                             </div>
                             <div class="flex items-center justify-center">
                                 <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 px-8 rounded-lg shadow-lg transform hover:scale-105 transition-all duration-200 w-full sm:w-auto text-lg" style="min-height: 48px;">
                                     Añadir Producto
                                 </button>
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

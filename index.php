<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario Montoli's</title>
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
    include_once 'config/database.php';
    include_once 'objects/product.php';

    $database = new Database();
    $db = $database->getConnection();

    $product = new Product($db);
    $stmt = $product->read();
    ?>
    <div class="flex">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-800 text-white w-64 min-h-screen fixed top-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-30">
            <div class="p-6 text-2xl font-bold flex items-center">
                <img src="images/logo.png" alt="Montoli's Logo" class="h-10 mr-3"> Montoli's
            </div>
            <nav class="mt-10">
                <a href="index.php" class="flex items-center py-3 px-6 text-gray-300 bg-gray-700">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
                <a href="add_product.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-plus mr-3"></i> Añadir Producto
                </a>
                <a href="catalog.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-book-open mr-3"></i> Ver Catálogo
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
                <h2 class="text-3xl font-bold text-gray-800">Dashboard</h2>
                <a href="catalog_selection.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-file-pdf mr-2"></i>Generar Catálogo
                </a>
            </header>

            <?php include 'dashboard.php'; ?>
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

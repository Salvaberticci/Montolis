<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos - Montoli's</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <style>
        #particles-js {
            position: fixed;
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

    $search_term = isset($_GET['s']) ? $_GET['s'] : '';

    if($search_term){
        $stmt = $product->search($search_term);
    } else {
        $stmt = $product->read();
    }
    ?>
    <header class="bg-green-800 shadow-md p-4 flex justify-between items-center sticky top-0 z-20">
        <div class="flex items-center">
            <img src="images/logo.png" alt="Montoli's Logo" class="h-12 mr-3">
            <h1 class="text-3xl font-bold text-white">Catálogo de Productos</h1>
        </div>
        <div class="flex items-center">
            <a href="index.php" class="text-white hover:text-gray-200 mr-4">
                <i class="fas fa-home mr-1"></i>
                Inicio
            </a>
        </div>
    </header>

    <main class="p-6">
        <div class="mb-6">
            <form action="catalog.php" method="get" class="flex items-center max-w-lg mx-auto bg-white rounded-full shadow-md">
                <input type="text" name="s" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($search_term); ?>" class="w-full px-6 py-3 rounded-full focus:outline-none" id="search-input">
                <button type="submit" class="bg-green-800 text-white rounded-full p-3 hover:bg-green-700 focus:outline-none mx-1">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <div id="product-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                echo "<div class='product-card bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300 ease-in-out'>";
                $image_path = "uploads/{$image}";
                if ($image && file_exists($image_path)) {
                    echo "<img src='{$image_path}' alt='{$name}' class='w-full h-48 object-cover'>";
                } else {
                    echo "<img src='images/placeholder.png' alt='Imagen no disponible' class='w-full h-48 object-cover'>";
                }
                echo "<div class='p-4'>";
                echo "<h3 class='text-xl font-bold text-gray-800 mb-2'>{$name}</h3>";
                echo "<p class='text-gray-600 text-sm mb-4'>{$description}</p>";
                echo "<div class='flex justify-between items-center'>";
                echo "<span class='text-2xl font-bold text-green-800'>&#36;{$sale_price}</span>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
    </main>

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

        anime({
            targets: '.product-card',
            translateY: [50, 0],
            opacity: [0, 1],
            delay: anime.stagger(100, {start: 300}),
            easing: 'easeOutExpo'
        });
    </script>
</body>
</html>

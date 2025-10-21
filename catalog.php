<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos - Montoli's</title>
    <link rel="icon" href="images/logo.png" type="image/png">
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
    include_once 'objects/settings.php';

    $database = new Database();
    $db = $database->getConnection();

    $product = new Product($db);
    $settings = new Settings($db);

    $search_term = isset($_GET['s']) ? $_GET['s'] : '';

    // Apply settings-based filtering
    if($search_term){
        $stmt = $product->search($search_term);
    } else {
        if($settings->getShowOutOfStock()) {
            $stmt = $product->read(); // Show all products including out of stock
        } else {
            $stmt = $product->readInStock(); // Only show products in stock
        }
    }
    ?>
    <header class="bg-green-800 shadow-md p-4 flex justify-between items-center sticky top-0 z-20">
        <div class="flex items-center">
            <img src="images/logo.png" alt="Montoli's Logo" class="h-12 mr-3">
            <h1 class="text-3xl font-bold text-white"><?php echo htmlspecialchars($settings->getCatalogTitle()); ?></h1>
        </div>
        <div class="flex items-center">
            <button onclick="openCart()" class="text-white hover:text-gray-200 relative">
                <i class="fas fa-shopping-cart text-2xl"></i>
                <span id="cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
            </button>
        </div>
    </header>

    <main class="p-6">
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4 max-w-4xl mx-auto">
                <?php if($settings->getEnableProductSearch()): ?>
                <form action="catalog.php" method="get" class="flex items-center flex-1 bg-white rounded-full shadow-md">
                    <input type="text" name="s" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($search_term); ?>" class="w-full px-6 py-3 rounded-full focus:outline-none" id="search-input">
                    <button type="submit" class="bg-green-800 text-white rounded-full p-3 hover:bg-green-700 focus:outline-none mx-1">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <?php endif; ?>
                <?php if($settings->getEnableCategoryFilter()): ?>
                <div class="flex items-center bg-white rounded-full shadow-md px-4 py-3">
                    <label for="category-filter" class="text-gray-700 mr-2">Categoría:</label>
                    <select id="category-filter" class="bg-transparent focus:outline-none text-gray-700">
                        <option value="">Todas las Categorías</option>
                        <?php
                        $categories_stmt = $product->getCategories();
                        while ($cat_row = $categories_stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . htmlspecialchars($cat_row['name']) . "'>" . htmlspecialchars($cat_row['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div id="product-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                $image_path = "uploads/{$image}";
                $image_src = ($image && file_exists($image_path)) ? $image_path : "images/placeholder.png";
                $iva_rate = 0.16;
                $price_with_iva = $sale_price * (1 + $iva_rate);
                $wholesale_with_iva = $wholesale_price * (1 + $iva_rate);
                $whatsapp_message = urlencode("Hola, me interesa este producto:\n\n{$name}\n{$description}\nPrecio: \${$price_with_iva} (incluye IVA 16%)\nPrecio al mayor: \${$wholesale_with_iva} (incluye IVA 16%)");
                $whatsapp_url = "https://wa.me/584163723527?text={$whatsapp_message}";
                echo "<div class='product-card bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300 ease-in-out' data-category='" . htmlspecialchars($category) . "'>";
                echo "<img src='{$image_src}' alt='{$name}' class='w-full h-48 object-cover cursor-pointer' onclick='openModal(\"{$name}\", \"{$description}\", \"{$image_src}\", \"{$sale_price}\", \"{$wholesale_price}\", \"{$whatsapp_url}\")'>";
                echo "<div class='p-4'>";
                echo "<h3 class='text-xl font-bold text-gray-800 mb-2 cursor-pointer' onclick='openModal(\"{$name}\", \"{$description}\", \"{$image_src}\", \"{$sale_price}\", \"{$wholesale_price}\", \"{$whatsapp_url}\")'>{$name}</h3>";
                echo "<p class='text-gray-600 text-sm mb-2 cursor-pointer' onclick='openModal(\"{$name}\", \"{$description}\", \"{$image_src}\", \"{$sale_price}\", \"{$wholesale_price}\", \"{$whatsapp_url}\")'>{$description}</p>";
                echo "<p class='text-xs text-blue-600 mb-4'>Categoría: {$category}</p>";
                echo "<div class='flex justify-between items-center mb-4'>";
                echo "<div class='flex flex-col'>";
                echo "<span class='text-lg font-bold text-green-800'>&#36;{$sale_price}</span>";
                echo "<span class='text-sm text-purple-600'>Mayor (4+): &#36;{$wholesale_price}</span>";
                echo "</div>";
                echo "</div>";
                echo "<div class='flex gap-2 mb-2'>";
                echo "<button onclick='quickAddToCart(\"{$name}\", \"{$description}\", \"{$image_src}\", \"{$sale_price}\", \"{$wholesale_price}\", \"{$whatsapp_url}\", 1)' class='flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center text-sm'>";
                echo "<i class='fas fa-cart-plus mr-1'></i> Agregar 1";
                echo "</button>";
                echo "<button onclick='openWholesaleModal(\"{$name}\", \"{$description}\", \"{$image_src}\", \"{$sale_price}\", \"{$wholesale_price}\", \"{$whatsapp_url}\")' class='flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-3 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center text-sm'>";
                echo "<i class='fas fa-shopping-bag mr-1'></i> Mayor (4+)";
                echo "</button>";
                echo "</div>";
                echo "<a href='{$whatsapp_url}' target='_blank' class='w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center text-sm'>";
                echo "<i class='fab fa-whatsapp mr-1'></i> WhatsApp";
                echo "</a>";
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

        // Category filtering
        const categoryFilter = document.getElementById('category-filter');
        const productCards = document.querySelectorAll('.product-card');

        categoryFilter.addEventListener('change', function() {
            const selectedCategory = this.value;

            productCards.forEach(card => {
                if (selectedCategory === '' || card.dataset.category === selectedCategory) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        anime({
            targets: '.product-card',
            translateY: [50, 0],
            opacity: [0, 1],
            delay: anime.stagger(100, {start: 300}),
            easing: 'easeOutExpo'
        });
    </script>

    <!-- Product Modal -->
    <div id="product-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-2xl max-w-md mx-auto overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="modal-title" class="text-2xl font-bold text-gray-800"></h2>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <img id="modal-image" src="" alt="" class="w-full h-48 object-cover rounded-lg mb-4">
                <p id="modal-description" class="text-gray-600 mb-4"></p>
                <div class="flex justify-between items-center mb-6">
                    <span id="modal-price" class="text-3xl font-bold text-green-800"></span>
                </div>
                <div class="flex gap-2 mb-3">
                    <button onclick="addToCart(1)" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition-all duration-200 flex items-center justify-center">
                        <i class="fas fa-cart-plus mr-2"></i> Agregar 1
                    </button>
                    <button onclick="openWholesaleModalFromModal()" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition-all duration-200 flex items-center justify-center">
                        <i class="fas fa-shopping-bag mr-2"></i> Mayor (4+)
                    </button>
                </div>
                <a id="whatsapp-btn" href="#" target="_blank" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg transition-all duration-200 flex items-center justify-center">
                    <i class="fab fa-whatsapp mr-2"></i> Contactar por WhatsApp
                </a>
            </div>
        </div>
    </div>

    <!-- Cart Modal -->
    <div id="cart-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-2xl max-w-lg mx-auto overflow-hidden max-h-screen">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Carrito de Compras</h2>
                    <button onclick="closeCart()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="cart-items" class="max-h-96 overflow-y-auto mb-4">
                    <!-- Cart items will be added here -->
                </div>
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-lg font-bold">Total:</span>
                        <span id="cart-total" class="text-2xl font-bold text-green-800">$0.00</span>
                    </div>
                    <button onclick="sendCartToWhatsApp()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg transition-all duration-200 flex items-center justify-center">
                        <i class="fab fa-whatsapp mr-2"></i> Enviar Pedido por WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Wholesale Quantity Modal -->
    <div id="wholesale-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-2xl max-w-sm mx-auto overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Cantidad al Mayor</h2>
                    <button onclick="closeWholesaleModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <label for="wholesale-quantity" class="block text-sm font-medium text-gray-700 mb-2">Cantidad (mínimo 4):</label>
                    <input type="number" id="wholesale-quantity" min="4" value="4" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>
                <button onclick="addWholesaleToCart()" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition-all duration-200 flex items-center justify-center">
                    <i class="fas fa-shopping-bag mr-2"></i> Agregar al Carrito
                </button>
            </div>
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

    <script>
        let currentProduct = {};
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        function updateCartCount() {
            document.getElementById('cart-count').textContent = cart.length;
        }

        function openModal(name, description, image, price, wholesalePrice, whatsappUrl) {
            currentProduct = { name, description, image, price, wholesalePrice, whatsappUrl };
            document.getElementById('modal-title').textContent = name;
            document.getElementById('modal-description').textContent = description;
            document.getElementById('modal-image').src = image;
            const ivaRate = 0.16;
            const priceWithIva = (parseFloat(price) * (1 + ivaRate)).toFixed(2);
            const wholesaleWithIva = (parseFloat(wholesalePrice) * (1 + ivaRate)).toFixed(2);
            const thirdPartyWithIva = (parseFloat(third_party_sale_price) * (1 + ivaRate)).toFixed(2);
            document.getElementById('modal-price').innerHTML = '$' + priceWithIva + ' (IVA incluido)<br><small class="text-purple-600">Mayor: $' + wholesaleWithIva + ' (IVA incluido)</small><br><small class="text-blue-600">Terceros: $' + thirdPartyWithIva + ' (IVA incluido)</small>';
            document.getElementById('whatsapp-btn').href = whatsappUrl;
            document.getElementById('product-modal').classList.remove('hidden');
            document.getElementById('product-modal').classList.add('flex');
            anime({
                targets: '#product-modal .bg-white',
                scale: [0.7, 1],
                opacity: [0, 1],
                duration: 300,
                easing: 'easeOutExpo'
            });
        }

        function closeModal() {
            anime({
                targets: '#product-modal .bg-white',
                scale: [1, 0.7],
                opacity: [1, 0],
                duration: 300,
                easing: 'easeInExpo',
                complete: () => {
                    document.getElementById('product-modal').classList.add('hidden');
                    document.getElementById('product-modal').classList.remove('flex');
                }
            });
        }

        function addToCart(quantity = 1) {
            const product = {
                ...currentProduct,
                price: quantity >= 4 ? currentProduct.wholesalePrice : currentProduct.price,
                quantity,
                isWholesale: quantity >= 4
            };
            cart.push(product);
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            closeModal();
            const message = quantity >= 4 ? `Agregado ${quantity} unidades al mayor al carrito` : `Producto agregado al carrito`;
            showNotification(message, 'success');
        }

        function openWholesaleModal(name, description, image, price, wholesalePrice, whatsappUrl) {
            currentProduct = { name, description, image, price, wholesalePrice, whatsappUrl };
            document.getElementById('wholesale-modal').classList.remove('hidden');
            document.getElementById('wholesale-modal').classList.add('flex');
            anime({
                targets: '#wholesale-modal .bg-white',
                scale: [0.7, 1],
                opacity: [0, 1],
                duration: 300,
                easing: 'easeOutExpo'
            });
        }

        function openWholesaleModalFromModal() {
            closeModal();
            openWholesaleModal(currentProduct.name, currentProduct.description, currentProduct.image, currentProduct.price, currentProduct.wholesalePrice, currentProduct.whatsappUrl);
        }

        function closeWholesaleModal() {
            anime({
                targets: '#wholesale-modal .bg-white',
                scale: [1, 0.7],
                opacity: [1, 0],
                duration: 300,
                easing: 'easeInExpo',
                complete: () => {
                    document.getElementById('wholesale-modal').classList.add('hidden');
                    document.getElementById('wholesale-modal').classList.remove('flex');
                }
            });
        }

        function addWholesaleToCart() {
            const quantity = parseInt(document.getElementById('wholesale-quantity').value);
            if (quantity < 4) {
                showNotification('La cantidad mínima para compra al mayor es 4 unidades', 'error');
                return;
            }
            const product = {
                ...currentProduct,
                price: currentProduct.wholesalePrice,
                quantity,
                isWholesale: true
            };
            cart.push(product);
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            closeWholesaleModal();
            showNotification(`Agregado ${quantity} unidades al mayor al carrito`, 'success');
        }

        function quickAddToCart(name, description, image, price, wholesalePrice, whatsappUrl, quantity = 1) {
            const product = {
                name,
                description,
                image,
                price: quantity >= 4 ? wholesalePrice : price,
                wholesalePrice,
                whatsappUrl,
                quantity,
                isWholesale: quantity >= 4
            };
            cart.push(product);
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            const message = quantity >= 4 ? `Agregado ${quantity} unidades al mayor al carrito` : `Producto agregado al carrito`;
            showNotification(message, 'success');
        }

        function openCart() {
            renderCart();
            document.getElementById('cart-modal').classList.remove('hidden');
            document.getElementById('cart-modal').classList.add('flex');
            anime({
                targets: '#cart-modal .bg-white',
                scale: [0.7, 1],
                opacity: [0, 1],
                duration: 300,
                easing: 'easeOutExpo'
            });
        }

        function closeCart() {
            anime({
                targets: '#cart-modal .bg-white',
                scale: [1, 0.7],
                opacity: [1, 0],
                duration: 300,
                easing: 'easeInExpo',
                complete: () => {
                    document.getElementById('cart-modal').classList.add('hidden');
                    document.getElementById('cart-modal').classList.remove('flex');
                }
            });
        }

        function renderCart() {
            const cartItems = document.getElementById('cart-items');
            cartItems.innerHTML = '';
            let total = 0;

            if (cart.length === 0) {
                cartItems.innerHTML = '<p class="text-gray-500 text-center py-8">El carrito está vacío</p>';
            } else {
                cart.forEach((item, index) => {
                    const itemTotal = parseFloat(item.price) * (item.quantity || 1);
                    total += itemTotal;
                    const priceType = item.isWholesale ? ' (Mayor)' : '';
                    cartItems.innerHTML += `
                        <div class="flex items-center justify-between py-2 border-b">
                            <div class="flex items-center">
                                <img src="${item.image}" alt="${item.name}" class="w-12 h-12 object-cover rounded mr-3">
                                <div>
                                    <h4 class="font-semibold">${item.name}</h4>
                                    <p class="text-sm text-gray-600">$${item.price}${priceType} x ${item.quantity || 1} = $${itemTotal.toFixed(2)}</p>
                                </div>
                            </div>
                            <button onclick="removeFromCart(${index})" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                });
            }

            document.getElementById('cart-total').textContent = '$' + total.toFixed(2);
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            renderCart();
        }

        function sendCartToWhatsApp() {
            if (cart.length === 0) {
                showNotification('El carrito está vacío', 'error');
                return;
            }

            let message = 'Hola, me interesan los siguientes productos:\n\n';
            let subtotal = 0;
            const ivaRate = 0.16;

            cart.forEach((item, index) => {
                const quantity = item.quantity || 1;
                const priceType = item.isWholesale ? ' (precio al mayor)' : '';
                const itemPrice = parseFloat(item.price);
                const itemPriceWithIva = itemPrice * (1 + ivaRate);
                const itemTotal = itemPriceWithIva * quantity;
                message += `${index + 1}. ${item.name} - $${itemPriceWithIva.toFixed(2)}${priceType} x ${quantity} = $${itemTotal.toFixed(2)} (IVA incluido)\n`;
                subtotal += itemPrice * quantity;
            });

            const ivaAmount = subtotal * ivaRate;
            const total = subtotal + ivaAmount;

            message += `\nSubtotal: $${subtotal.toFixed(2)}\nIVA (16%): $${ivaAmount.toFixed(2)}\nTotal: $${total.toFixed(2)}`;

            const whatsappUrl = `https://wa.me/584163723527?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }

        // Initialize cart count on page load
        updateCartCount();

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

        // Close modals when clicking outside
        document.getElementById('product-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.getElementById('cart-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCart();
            }
        });

        document.getElementById('wholesale-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeWholesaleModal();
            }
        });
    </script>
</body>
</html>

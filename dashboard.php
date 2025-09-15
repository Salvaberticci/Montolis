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
        <h3 class="text-2xl font-bold mb-6 text-gray-700">Listado de Productos</h3>
        <div class="overflow-x-auto bg-white rounded-lg shadow-xl">
            <div class="min-w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-200">
                        <tr>
                        <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                        <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                        <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                        <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo (USD)</th>
                        <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venta (USD)</th>
                        <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venta 3ros (USD)</th>
                        <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% Vendedor 3ros</th>
                        <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php
                    $product = new Product($db);
                    $stmt = $product->read();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        extract($row);
                        echo "<tr class='table-row'>";
                            echo "<td class='py-4 px-6 whitespace-nowrap flex items-center'>";
                            echo $image ? "<img src='uploads/{$image}' class='h-12 w-12 rounded-full object-cover mr-4 shadow-md' />" : "<img src='images/placeholder.png' class='h-12 w-12 rounded-full object-cover mr-4 shadow-md' />";
                            echo "<span class='font-medium text-gray-900'>{$name}</span></td>";
                            echo "<td class='py-4 px-6 text-gray-500'>{$description}</td>";
                            echo "<td class='py-4 px-6 text-gray-500'>{$quantity}</td>";
                            echo "<td class='py-4 px-6 text-green-600 font-semibold'>&#36;{$product_cost}</td>";
                            echo "<td class='py-4 px-6 text-blue-600 font-semibold'>&#36;{$sale_price}</td>";
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

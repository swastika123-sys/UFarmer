<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'database.php';

echo "<h2>Creating Demo Data</h2>";

try {
    // First, clear existing demo data to avoid conflicts
    echo "Clearing existing demo data...<br>";
    $pdo->exec("DELETE FROM wallet_transactions WHERE reference_type = 'initial'");
    $pdo->exec("DELETE FROM order_items");
    $pdo->exec("DELETE FROM orders");
    $pdo->exec("DELETE FROM reviews");
    $pdo->exec("DELETE FROM messages");
    $pdo->exec("DELETE FROM products");
    $pdo->exec("DELETE FROM farmers");
    $pdo->exec("DELETE FROM users WHERE email LIKE '%@greenvalley.com' OR email LIKE '%@sunshineacres.com' OR email LIKE '%@harvestmoon.com' OR email LIKE '%@meadowbrook.com' OR email LIKE '%@goldenharvest.com' OR email = 'demo@example.com'");
    echo "✅ Cleared existing demo data<br>";

    // Demo farmers data
    $demoFarmers = [
        [
            'name' => 'John Smith',
            'email' => 'john@greenvalley.com',
            'farm_name' => 'Green Valley Organic Farm',
            'description' => 'Family-owned organic farm specializing in seasonal vegetables and herbs.',
            'location' => 'Sonoma County, California',
            'phone' => '(555) 123-4567',
            'rating' => 4.8,
            'total_reviews' => 47
        ],
        [
            'name' => 'Maria Rodriguez',
            'email' => 'maria@sunshineacres.com',
            'farm_name' => 'Sunshine Acres',
            'description' => 'Certified organic farm growing a wide variety of fruits and vegetables.',
            'location' => 'Napa Valley, California',
            'phone' => '(555) 234-5678',
            'rating' => 4.9,
            'total_reviews' => 63
        ],
        [
            'name' => 'David Chen',
            'email' => 'david@harvestmoon.com',
            'farm_name' => 'Harvest Moon Farm',
            'description' => 'Small-scale diversified farm focusing on Asian vegetables and herbs.',
            'location' => 'Central Valley, California',
            'phone' => '(555) 345-6789',
            'rating' => 4.7,
            'total_reviews' => 29
        ],
        [
            'name' => 'Sarah Johnson',
            'email' => 'sarah@meadowbrook.com',
            'farm_name' => 'Meadowbrook Dairy',
            'description' => 'Grass-fed dairy farm producing fresh milk, cheese, and yogurt.',
            'location' => 'Petaluma, California',
            'phone' => '(555) 456-7890',
            'rating' => 4.6,
            'total_reviews' => 34
        ],
        [
            'name' => 'Robert Green',
            'email' => 'robert@goldenharvest.com',
            'farm_name' => 'Golden Harvest Orchards',
            'description' => 'Boutique orchard specializing in heritage apples and stone fruits.',
            'location' => 'Santa Rosa, California',
            'phone' => '(555) 567-8901',
            'rating' => 4.5,
            'total_reviews' => 18
        ]
    ];

    // Create farmer users and profiles
    echo "Creating farmer accounts...<br>";
    $farmerIds = [];
    foreach ($demoFarmers as $farmer) {
        // Create user account
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type, wallet_balance) VALUES (?, ?, ?, 'farmer', 50.00)");
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
        $stmt->execute([$farmer['name'], $farmer['email'], $hashedPassword]);
        $userId = $pdo->lastInsertId();
        
        // Create farmer profile
        $stmt = $pdo->prepare("INSERT INTO farmers (user_id, farm_name, description, location, phone, is_verified, rating, total_reviews) VALUES (?, ?, ?, ?, ?, 1, ?, ?)");
        $stmt->execute([$userId, $farmer['farm_name'], $farmer['description'], $farmer['location'], $farmer['phone'], $farmer['rating'], $farmer['total_reviews']]);
        $farmerId = $pdo->lastInsertId();
        
        $farmerIds[] = ['user_id' => $userId, 'farmer_id' => $farmerId, 'farm_name' => $farmer['farm_name']];
        echo "✅ Created farmer: {$farmer['farm_name']}<br>";
    }

    // Create demo customer
    echo "Creating demo customer...<br>";
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type, wallet_balance) VALUES (?, ?, ?, 'customer', 500.00)");
    $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
    $stmt->execute(['Demo Customer', 'demo@example.com', $hashedPassword]);
    $customerId = $pdo->lastInsertId();
    echo "✅ Created demo customer<br>";

    // Demo products for each farmer
    $productCategories = [
        'Green Valley Organic Farm' => [
            ['name' => 'Organic Tomatoes', 'price' => 4.99, 'unit' => 'lb', 'category' => 'vegetables', 'stock' => 50],
            ['name' => 'Fresh Basil', 'price' => 2.49, 'unit' => 'bunch', 'category' => 'herbs', 'stock' => 30],
            ['name' => 'Baby Spinach', 'price' => 3.99, 'unit' => 'bag', 'category' => 'vegetables', 'stock' => 25],
            ['name' => 'Organic Carrots', 'price' => 2.99, 'unit' => 'lb', 'category' => 'vegetables', 'stock' => 40]
        ],
        'Sunshine Acres' => [
            ['name' => 'Fresh Strawberries', 'price' => 5.99, 'unit' => 'pint', 'category' => 'fruits', 'stock' => 20],
            ['name' => 'Mixed Greens', 'price' => 4.49, 'unit' => 'bag', 'category' => 'vegetables', 'stock' => 35],
            ['name' => 'Cherry Tomatoes', 'price' => 3.99, 'unit' => 'pint', 'category' => 'vegetables', 'stock' => 45],
            ['name' => 'Bell Peppers', 'price' => 1.99, 'unit' => 'each', 'category' => 'vegetables', 'stock' => 60]
        ],
        'Harvest Moon Farm' => [
            ['name' => 'Bok Choy', 'price' => 2.99, 'unit' => 'bunch', 'category' => 'vegetables', 'stock' => 25],
            ['name' => 'Daikon Radish', 'price' => 1.99, 'unit' => 'each', 'category' => 'vegetables', 'stock' => 30],
            ['name' => 'Napa Cabbage', 'price' => 3.49, 'unit' => 'head', 'category' => 'vegetables', 'stock' => 20],
            ['name' => 'Shiitake Mushrooms', 'price' => 6.99, 'unit' => 'lb', 'category' => 'vegetables', 'stock' => 15]
        ],
        'Meadowbrook Dairy' => [
            ['name' => 'Fresh Whole Milk', 'price' => 4.99, 'unit' => 'gallon', 'category' => 'dairy', 'stock' => 30],
            ['name' => 'Artisan Cheese', 'price' => 8.99, 'unit' => 'wheel', 'category' => 'dairy', 'stock' => 12],
            ['name' => 'Greek Yogurt', 'price' => 3.99, 'unit' => 'container', 'category' => 'dairy', 'stock' => 40],
            ['name' => 'Farm Butter', 'price' => 5.49, 'unit' => 'lb', 'category' => 'dairy', 'stock' => 25]
        ],
        'Golden Harvest Orchards' => [
            ['name' => 'Honeycrisp Apples', 'price' => 3.99, 'unit' => 'lb', 'category' => 'fruits', 'stock' => 100],
            ['name' => 'Fresh Peaches', 'price' => 4.49, 'unit' => 'lb', 'category' => 'fruits', 'stock' => 50],
            ['name' => 'Pears', 'price' => 3.49, 'unit' => 'lb', 'category' => 'fruits', 'stock' => 75],
            ['name' => 'Plums', 'price' => 4.99, 'unit' => 'lb', 'category' => 'fruits', 'stock' => 30]
        ]
    ];

    // Create products
    echo "Creating products...<br>";
    $productCount = 0;
    foreach ($farmerIds as $farmer) {
        if (isset($productCategories[$farmer['farm_name']])) {
            foreach ($productCategories[$farmer['farm_name']] as $product) {
                $stmt = $pdo->prepare("INSERT INTO products (farmer_id, name, description, price, unit, category, stock_quantity, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
                $description = "Fresh " . strtolower($product['name']) . " from " . $farmer['farm_name'];
                $stmt->execute([
                    $farmer['farmer_id'],
                    $product['name'],
                    $description,
                    $product['price'],
                    $product['unit'],
                    $product['category'],
                    $product['stock']
                ]);
                $productCount++;
            }
        }
    }
    echo "✅ Created $productCount products<br>";

    echo "<h3>Demo Data Created Successfully!</h3>";
    echo "📊 Created " . count($demoFarmers) . " farmers<br>";
    echo "📦 Created $productCount products<br>";
    echo "👤 Created 1 demo customer<br>";
    echo "<br>";
    echo "<strong>Login credentials:</strong><br>";
    echo "Customer: demo@example.com / password (Balance: $500)<br>";
    echo "Farmers: Use any farmer email with 'password'<br>";
    echo "<br>";
    echo "✅ <strong>All done! You can now test the platform.</strong><br>";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    echo "Error code: " . $e->getCode() . "<br>";
} catch (Exception $e) {
    echo "❌ General error: " . $e->getMessage() . "<br>";
}
?>

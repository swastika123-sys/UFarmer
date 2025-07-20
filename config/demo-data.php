<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'database.php';

echo "Creating demo data...\n";

try {
    // Demo farmers
    $demoFarmers = [
        [
            'name' => 'John Smith',
            'email' => 'john@greenvalley.com',
            'farm_name' => 'Green Valley Organic Farm',
            'description' => 'Family-owned organic farm specializing in seasonal vegetables and herbs. We have been practicing sustainable farming for over 20 years, focusing on soil health and biodiversity. Our produce is grown without synthetic pesticides or fertilizers.',
            'location' => 'Sonoma County, California',
            'phone' => '(555) 123-4567',
            'rating' => 4.8,
            'total_reviews' => 47
        ],
        [
            'name' => 'Maria Rodriguez',
            'email' => 'maria@sunshineacres.com',
            'farm_name' => 'Sunshine Acres',
            'description' => 'Certified organic farm growing a wide variety of fruits and vegetables. We specialize in heirloom tomatoes, fresh berries, and leafy greens. Our farm-to-table approach ensures the freshest produce reaches your table.',
            'location' => 'Napa Valley, California',
            'phone' => '(555) 234-5678',
            'rating' => 4.9,
            'total_reviews' => 63
        ],
        [
            'name' => 'David Chen',
            'email' => 'david@harvestmoon.com',
            'farm_name' => 'Harvest Moon Farm',
            'description' => 'Small-scale diversified farm focusing on Asian vegetables and herbs. We grow unique varieties that are hard to find elsewhere, including bok choy, daikon radish, and various Asian greens.',
            'location' => 'Central Valley, California',
            'phone' => '(555) 345-6789',
            'rating' => 4.7,
            'total_reviews' => 29
        ],
        [
            'name' => 'Sarah Johnson',
            'email' => 'sarah@meadowbrook.com',
            'farm_name' => 'Meadowbrook Dairy',
            'description' => 'Grass-fed dairy farm producing fresh milk, cheese, and yogurt. Our cows graze on natural pastures year-round, resulting in rich, creamy dairy products with exceptional flavor.',
            'location' => 'Petaluma, California',
            'phone' => '(555) 456-7890',
            'rating' => 4.6,
            'total_reviews' => 34
        ],
        [
            'name' => 'Robert Wilson',
            'email' => 'rob@willowcreek.com',
            'farm_name' => 'Willow Creek Orchards',
            'description' => 'Third-generation family orchard specializing in stone fruits and citrus. We grow peaches, plums, apricots, oranges, and lemons using integrated pest management and sustainable practices.',
            'location' => 'Fresno County, California',
            'phone' => '(555) 567-8901',
            'rating' => 4.5,
            'total_reviews' => 51
        ]
    ];

    // Create demo farmer accounts
    foreach ($demoFarmers as $farmer) {
        // Create user account
        $hashedPassword = password_hash('demo123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, 'farmer')");
        $stmt->execute([$farmer['name'], $farmer['email'], $hashedPassword]);
        $userId = $pdo->lastInsertId();

        // Create farmer profile
        $stmt = $pdo->prepare("INSERT INTO farmers (user_id, farm_name, description, location, phone, is_verified, rating, total_reviews, offers_delivery, same_day_delivery, delivery_radius_km, delivery_fee) VALUES (?, ?, ?, ?, ?, 1, ?, ?, 1, 1, 15, 2.50)");
        $stmt->execute([$userId, $farmer['farm_name'], $farmer['description'], $farmer['location'], $farmer['phone'], $farmer['rating'], $farmer['total_reviews']]);
        $farmerId = $pdo->lastInsertId();

        echo "Created farmer: {$farmer['farm_name']}\n";

        // Add some products for each farmer
        $products = [];
        
        switch ($farmerId) {
            case 1: // Green Valley Organic Farm
                $products = [
                    ['Organic Tomatoes', 'Fresh, vine-ripened organic tomatoes perfect for salads and cooking', 4.50, 'lb', 'vegetables', 'June-October', 25],
                    ['Mixed Salad Greens', 'Fresh mix of lettuce, spinach, and arugula', 6.00, 'bag', 'vegetables', 'Year-round', 30],
                    ['Fresh Basil', 'Aromatic sweet basil perfect for cooking', 3.00, 'bunch', 'herbs', 'April-October', 15],
                    ['Organic Carrots', 'Sweet, crunchy carrots grown in rich soil', 3.50, 'lb', 'vegetables', 'Year-round', 40]
                ];
                break;
            case 2: // Sunshine Acres
                $products = [
                    ['Heirloom Tomatoes', 'Colorful variety of heirloom tomatoes with unique flavors', 7.00, 'lb', 'vegetables', 'July-September', 20],
                    ['Fresh Strawberries', 'Sweet, juicy strawberries picked daily', 8.00, 'basket', 'fruits', 'March-June', 18],
                    ['Organic Spinach', 'Tender baby spinach leaves', 4.00, 'bag', 'vegetables', 'Year-round', 25],
                    ['Mixed Berries', 'Seasonal mix of berries including blackberries and raspberries', 10.00, 'basket', 'fruits', 'June-August', 12]
                ];
                break;
            case 3: // Harvest Moon Farm
                $products = [
                    ['Baby Bok Choy', 'Tender Asian greens perfect for stir-fry', 5.00, 'bunch', 'vegetables', 'Year-round', 22],
                    ['Daikon Radish', 'Large white radish with mild flavor', 3.00, 'each', 'vegetables', 'Fall-Spring', 15],
                    ['Chinese Broccoli', 'Gai lan with tender stems and leaves', 4.50, 'bunch', 'vegetables', 'Year-round', 18],
                    ['Napa Cabbage', 'Crisp cabbage perfect for kimchi and salads', 4.00, 'head', 'vegetables', 'Fall-Spring', 20]
                ];
                break;
            case 4: // Meadowbrook Dairy
                $products = [
                    ['Fresh Whole Milk', 'Creamy whole milk from grass-fed cows', 6.00, 'gallon', 'dairy', 'Year-round', 10],
                    ['Artisan Cheese', 'Handcrafted cheese made from our fresh milk', 12.00, 'lb', 'dairy', 'Year-round', 8],
                    ['Greek Yogurt', 'Thick, creamy yogurt with probiotics', 5.50, 'container', 'dairy', 'Year-round', 15],
                    ['Farm Butter', 'Rich, creamy butter churned fresh daily', 8.00, 'lb', 'dairy', 'Year-round', 12]
                ];
                break;
            case 5: // Willow Creek Orchards
                $products = [
                    ['Fresh Peaches', 'Juicy, sweet peaches picked at peak ripeness', 6.00, 'lb', 'fruits', 'June-August', 30],
                    ['Santa Rosa Plums', 'Sweet and tart plums with red skin', 5.50, 'lb', 'fruits', 'July-September', 25],
                    ['Valencia Oranges', 'Sweet oranges perfect for juice', 4.00, 'lb', 'fruits', 'March-July', 35],
                    ['Meyer Lemons', 'Sweet, fragrant lemons with thin skin', 7.00, 'lb', 'fruits', 'November-March', 20]
                ];
                break;
        }

        foreach ($products as $product) {
            $stmt = $pdo->prepare("INSERT INTO products (farmer_id, name, description, price, unit, category, seasonal_availability, stock_quantity, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$farmerId, $product[0], $product[1], $product[2], $product[3], $product[4], $product[5], $product[6]]);
        }
        
        echo "Added " . count($products) . " products for {$farmer['farm_name']}\n";
    }

    // Create a demo customer
    $customerPassword = password_hash('demo123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type) VALUES ('Demo Customer', 'customer@demo.com', ?, 'customer')");
    $stmt->execute([$customerPassword]);
    $customerId = $pdo->lastInsertId();
    echo "Created demo customer account\n";

    // Add some demo reviews
    $reviews = [
        [1, 5, "Excellent tomatoes! Fresh and flavorful, exactly what I was looking for."],
        [1, 4, "Great quality produce. Will definitely order again."],
        [2, 5, "The heirloom tomatoes are amazing! So much flavor compared to store-bought."],
        [2, 5, "Best strawberries I've ever had. Sweet and perfectly ripe."],
        [3, 4, "Love the variety of Asian vegetables. Hard to find elsewhere."],
        [4, 5, "Fresh milk delivered right to my door. Tastes so much better than store milk."],
        [5, 4, "Peaches were delicious and perfectly ripe. Will order more next season."]
    ];

    foreach ($reviews as $review) {
        $stmt = $pdo->prepare("INSERT INTO reviews (customer_id, farmer_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$customerId, $review[0], $review[1], $review[2]]);
    }
    echo "Added demo reviews\n";

    echo "\nDemo data created successfully!\n";
    echo "✅ 5 farmers created\n";
    echo "✅ 20+ products added\n";
    echo "✅ Customer account created (demo@example.com / password)\n";
    echo "📱 Login credentials:\n";
    echo "   Customer: demo@example.com / password\n";
    echo "   Farmers: Use farmer email addresses with 'password'\n";

    // Add initial wallet transactions for realism
    try {
        // Add some transaction history for the demo customer
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'demo@example.com'");
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($customer) {
            // Add initial wallet credit transaction
            $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description, reference_type) VALUES (?, 'credit', 500.00, 'Initial wallet balance', 'initial')");
            $stmt->execute([$customer['id']]);
            
            // Add some sample purchase transactions
            $purchases = [
                ['amount' => 25.50, 'description' => 'Previous order - Green Valley Organic Farm'],
                ['amount' => 18.75, 'description' => 'Previous order - Sunshine Acres'],
                ['amount' => 32.25, 'description' => 'Previous order - Harvest Moon Farm']
            ];
            
            foreach ($purchases as $purchase) {
                $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description, reference_type, created_at) VALUES (?, 'debit', ?, ?, 'purchase', DATE_SUB(NOW(), INTERVAL ? DAY))");
                $stmt->execute([$customer['id'], $purchase['amount'], $purchase['description'], rand(1, 30)]);
            }
            
            // Update customer wallet balance to reflect transactions
            $totalSpent = array_sum(array_column($purchases, 'amount'));
            $newBalance = 500.00 - $totalSpent;
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
            $stmt->execute([$newBalance, $customer['id']]);
        }
        
        // Add some earnings transactions for farmers
        $stmt = $pdo->prepare("SELECT u.id, f.farm_name FROM users u JOIN farmers f ON u.id = f.user_id LIMIT 3");
        $stmt->execute();
        $farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($farmers as $index => $farmer) {
            // Initial farmer balance
            $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description, reference_type) VALUES (?, 'credit', 50.00, 'Welcome bonus for joining UFarmer', 'initial')");
            $stmt->execute([$farmer['id']]);
            
            // Add some sales
            $sales = [
                ['amount' => 15.50, 'description' => 'Sale to customer - Organic vegetables'],
                ['amount' => 22.75, 'description' => 'Sale to customer - Fresh produce bundle']
            ];
            
            foreach ($sales as $sale) {
                $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description, reference_type, created_at) VALUES (?, 'credit', ?, ?, 'sale', DATE_SUB(NOW(), INTERVAL ? DAY))");
                $stmt->execute([$farmer['id'], $sale['amount'], $sale['description'], rand(1, 20)]);
            }
            
            // Update farmer wallet balance
            $totalEarnings = array_sum(array_column($sales, 'amount'));
            $newBalance = 50.00 + $totalEarnings;
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
            $stmt->execute([$newBalance, $farmer['id']]);
        }
        
        echo "💰 Wallet transaction history added\n";
        echo "💳 Customer wallet balance: $" . number_format($newBalance ?? 423.50, 2) . "\n";
        
    } catch (Exception $e) {
        echo "⚠️  Error adding wallet transactions: " . $e->getMessage() . "\n";
    }

} catch(PDOException $e) {
    die("Error creating demo data: " . $e->getMessage());
}
?>

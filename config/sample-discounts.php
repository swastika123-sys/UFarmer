<?php
require_once 'database.php';

try {
    // Add some sample discounts to existing products
    $discounts = [
        ['name' => 'Organic Tomatoes', 'discount' => 15.00],
        ['name' => 'Fresh Strawberries', 'discount' => 20.00],
        ['name' => 'Baby Spinach', 'discount' => 10.00],
        ['name' => 'Russet Potatoes', 'discount' => 25.00],
        ['name' => 'Sweet Corn', 'discount' => 12.00]
    ];
    
    foreach ($discounts as $discount) {
        $stmt = $pdo->prepare("SELECT id, price FROM products WHERE name = ?");
        $stmt->execute([$discount['name']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $discountedPrice = $product['price'] - ($product['price'] * $discount['discount'] / 100);
            $stmt = $pdo->prepare("UPDATE products SET discount_percentage = ?, discounted_price = ? WHERE id = ?");
            $stmt->execute([$discount['discount'], $discountedPrice, $product['id']]);
            echo "✅ Applied {$discount['discount']}% discount to {$discount['name']}\n";
        }
    }
    
    echo "\n🏷️ Sample discounts applied successfully!\n";
    echo "Visit the shop to see products with promotional pricing.\n";
    
} catch (PDOException $e) {
    echo "❌ Error applying discounts: " . $e->getMessage() . "\n";
}
?>

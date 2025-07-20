<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>UFarmer Database Debug</h2>";

try {
    require_once 'database.php';
    echo "✅ Database connection successful<br>";
    
    // Check if tables exist
    $tables = ['users', 'farmers', 'products', 'orders', 'order_items', 'reviews', 'messages', 'wallet_transactions'];
    
    echo "<h3>Table Structure Check:</h3>";
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<strong>✅ Table '$table' exists with columns:</strong><br>";
            foreach ($columns as $column) {
                echo "&nbsp;&nbsp;- {$column['Field']} ({$column['Type']})<br>";
            }
            echo "<br>";
        } catch (PDOException $e) {
            echo "❌ Table '$table' does not exist or error: " . $e->getMessage() . "<br><br>";
        }
    }
    
    // Check users table for wallet_balance column specifically
    echo "<h3>Users Table Wallet Column Check:</h3>";
    try {
        $stmt = $pdo->query("SELECT wallet_balance FROM users LIMIT 1");
        echo "✅ wallet_balance column exists in users table<br>";
    } catch (PDOException $e) {
        echo "❌ wallet_balance column missing: " . $e->getMessage() . "<br>";
        
        // Try to add it
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN wallet_balance DECIMAL(10,2) DEFAULT 0.00");
            echo "✅ Added wallet_balance column<br>";
        } catch (PDOException $e2) {
            echo "❌ Failed to add wallet_balance column: " . $e2->getMessage() . "<br>";
        }
    }
    
    // Check products table for discount columns
    echo "<h3>Products Table Discount Columns Check:</h3>";
    try {
        $stmt = $pdo->query("SELECT discount_percentage, discounted_price FROM products LIMIT 1");
        echo "✅ Discount columns exist in products table<br>";
    } catch (PDOException $e) {
        echo "❌ Discount columns missing: " . $e->getMessage() . "<br>";
        
        // Try to add them
        try {
            $pdo->exec("ALTER TABLE products ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0.00");
            $pdo->exec("ALTER TABLE products ADD COLUMN discounted_price DECIMAL(10,2) NULL");
            echo "✅ Added discount columns<br>";
        } catch (PDOException $e2) {
            echo "❌ Failed to add discount columns: " . $e2->getMessage() . "<br>";
        }
    }
    
    // Test wallet functions
    echo "<h3>Functions Test:</h3>";
    require_once '../includes/functions.php';
    
    // Check if functions exist
    if (function_exists('getUserWalletBalance')) {
        echo "✅ getUserWalletBalance function exists<br>";
    } else {
        echo "❌ getUserWalletBalance function missing<br>";
    }
    
    if (function_exists('addWalletTransaction')) {
        echo "✅ addWalletTransaction function exists<br>";
    } else {
        echo "❌ addWalletTransaction function missing<br>";
    }
    
    // Count existing data
    echo "<h3>Current Data Count:</h3>";
    foreach (['users', 'farmers', 'products'] as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "📊 $table: {$result['count']} records<br>";
        } catch (PDOException $e) {
            echo "❌ Error counting $table: " . $e->getMessage() . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}
?>

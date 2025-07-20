<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Function Test</h2>";

try {
    require_once 'database.php';
    echo "✅ Database connected<br>";
    
    require_once '../includes/functions.php';
    echo "✅ Functions loaded<br>";
    
    // Test basic functions
    if (function_exists('getUserWalletBalance')) {
        echo "✅ getUserWalletBalance function exists<br>";
        
        // Test with a user ID
        $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $balance = getUserWalletBalance($user['id']);
            echo "✅ Wallet balance for user {$user['id']}: $" . number_format($balance, 2) . "<br>";
        }
    } else {
        echo "❌ getUserWalletBalance function missing<br>";
    }
    
    if (function_exists('generateCSRFToken')) {
        echo "✅ generateCSRFToken function exists<br>";
        $token = generateCSRFToken();
        echo "✅ CSRF token generated: " . substr($token, 0, 10) . "...<br>";
    } else {
        echo "❌ generateCSRFToken function missing<br>";
    }
    
    if (function_exists('isLoggedIn')) {
        echo "✅ isLoggedIn function exists<br>";
        $loggedIn = isLoggedIn();
        echo "✅ Currently logged in: " . ($loggedIn ? 'Yes' : 'No') . "<br>";
    } else {
        echo "❌ isLoggedIn function missing<br>";
    }
    
    // Test database queries
    echo "<h3>Database Queries:</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Users count: {$result['count']}<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM farmers");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Farmers count: {$result['count']}<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Products count: {$result['count']}<br>";
    
    // Check if wallet_transactions table exists
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM wallet_transactions");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Wallet transactions count: {$result['count']}<br>";
    } catch (PDOException $e) {
        echo "❌ Wallet transactions table issue: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?>

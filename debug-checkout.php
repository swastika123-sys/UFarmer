<?php
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<h2>Checkout Debug Information</h2>";

echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>POST Data:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

if (isset($_POST['cart_items'])) {
    echo "<h3>Cart Items:</h3>";
    $cartItems = json_decode($_POST['cart_items'], true);
    echo "<pre>";
    print_r($cartItems);
    echo "</pre>";
}

echo "<h3>Database Connection Test:</h3>";
try {
    $stmt = $pdo->query("SELECT 1");
    echo "✅ Database connection OK<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<h3>User Wallet Balance:</h3>";
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END) as balance 
        FROM wallet_transactions 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance = $result ? (float)$result['balance'] : 0.00;
    echo "User ID: $userId, Balance: $" . number_format($balance, 2) . "<br>";
} else {
    echo "No user logged in<br>";
}

echo "<h3>Tables Check:</h3>";
$tables = ['orders', 'order_items', 'wallet_transactions', 'farmers', 'products'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "✅ Table '$table': $count records<br>";
    } catch (Exception $e) {
        echo "❌ Table '$table': " . $e->getMessage() . "<br>";
    }
}
?>

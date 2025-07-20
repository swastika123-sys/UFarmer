<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'database.php';

echo "<h2>Setting up Wallet System</h2>";

try {
    // Check if wallet_balance column exists, add if it doesn't
    echo "Checking wallet_balance column...<br>";
    try {
        $stmt = $pdo->query("SELECT wallet_balance FROM users LIMIT 1");
        echo "✅ wallet_balance column already exists<br>";
    } catch (PDOException $e) {
        echo "Adding wallet_balance column...<br>";
        $pdo->exec("ALTER TABLE users ADD COLUMN wallet_balance DECIMAL(10,2) DEFAULT 0.00");
        echo "✅ wallet_balance column added<br>";
    }
    
    // Check if discount columns exist, add if they don't
    echo "Checking discount columns...<br>";
    try {
        $stmt = $pdo->query("SELECT discount_percentage, discounted_price FROM products LIMIT 1");
        echo "✅ Discount columns already exist<br>";
    } catch (PDOException $e) {
        echo "Adding discount columns...<br>";
        $pdo->exec("ALTER TABLE products ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0.00");
        $pdo->exec("ALTER TABLE products ADD COLUMN discounted_price DECIMAL(10,2) NULL");
        echo "✅ Discount columns added<br>";
    }
    
    // Create wallet transactions table if it doesn't exist
    echo "Creating wallet_transactions table...<br>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS wallet_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('credit', 'debit') NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT,
        reference_type ENUM('initial', 'purchase', 'sale', 'refund', 'recharge') DEFAULT 'purchase',
        reference_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✅ wallet_transactions table created<br>";
    
    // Initialize wallet balances for existing users
    echo "Initializing wallet balances...<br>";
    $pdo->exec("UPDATE users SET wallet_balance = CASE 
        WHEN user_type = 'customer' THEN 500.00 
        WHEN user_type = 'farmer' THEN 50.00 
        ELSE 0.00 
    END WHERE wallet_balance = 0.00");
    
    echo "✅ Wallet system setup completed successfully!<br>";
    echo "📊 All customers initialized with $500 wallet balance<br>";
    echo "🚜 All farmers initialized with $50 wallet balance<br>";
    echo "💰 Wallet transactions table created<br>";
    echo "🏷️ Discount system added to products<br>";
    
} catch (PDOException $e) {
    echo "❌ Error setting up wallet system: " . $e->getMessage() . "<br>";
    echo "SQL State: " . $e->getCode() . "<br>";
}
?>

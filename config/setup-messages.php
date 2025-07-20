<?php
// Database setup for messages table
require_once '../../config/database.php';

try {
    // Check if messages table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'messages'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "Creating messages table...\n";
        
        $createTableSQL = "
        CREATE TABLE messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            farmer_id INT NOT NULL,
            customer_id INT NOT NULL,
            sender_type ENUM('farmer', 'customer') NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE,
            FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_farmer_customer (farmer_id, customer_id),
            INDEX idx_is_read (is_read),
            INDEX idx_created_at (created_at)
        )";
        
        $pdo->exec($createTableSQL);
        echo "Messages table created successfully!\n";
    } else {
        echo "Messages table already exists.\n";
    }
    
    // Add some sample messages for testing
    $stmt = $pdo->query("SELECT COUNT(*) FROM messages");
    $messageCount = $stmt->fetchColumn();
    
    if ($messageCount == 0) {
        echo "Adding sample messages...\n";
        
        // Get first farmer and customer
        $farmerStmt = $pdo->query("SELECT id FROM farmers LIMIT 1");
        $farmer = $farmerStmt->fetch();
        
        $customerStmt = $pdo->query("SELECT id FROM users WHERE user_type = 'customer' LIMIT 1");
        $customer = $customerStmt->fetch();
        
        if ($farmer && $customer) {
            $sampleMessages = [
                [
                    'farmer_id' => $farmer['id'],
                    'customer_id' => $customer['id'],
                    'sender_type' => 'customer',
                    'message' => 'Hi! I\'m interested in your organic tomatoes. Are they still available?',
                    'created_at' => '2024-12-15 10:30:00'
                ],
                [
                    'farmer_id' => $farmer['id'],
                    'customer_id' => $customer['id'],
                    'sender_type' => 'farmer',
                    'message' => 'Hello! Yes, we have fresh organic tomatoes available. They were harvested this morning. Would you like to place an order?',
                    'created_at' => '2024-12-15 11:15:00'
                ],
                [
                    'farmer_id' => $farmer['id'],
                    'customer_id' => $customer['id'],
                    'sender_type' => 'customer',
                    'message' => 'That sounds perfect! I\'d like to order 2kg. What\'s the best way to arrange delivery?',
                    'created_at' => '2024-12-15 11:45:00'
                ],
                [
                    'farmer_id' => $farmer['id'],
                    'customer_id' => $customer['id'],
                    'sender_type' => 'farmer',
                    'message' => 'Great! You can place the order through our website. We deliver within 24 hours in your area. The tomatoes are ₹120 per kg.',
                    'created_at' => '2024-12-15 12:10:00'
                ]
            ];
            
            $insertStmt = $pdo->prepare("INSERT INTO messages (farmer_id, customer_id, sender_type, message, created_at) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($sampleMessages as $msg) {
                $insertStmt->execute([
                    $msg['farmer_id'],
                    $msg['customer_id'],
                    $msg['sender_type'],
                    $msg['message'],
                    $msg['created_at']
                ]);
            }
            
            echo "Sample messages added successfully!\n";
        }
    } else {
        echo "Messages table already has data.\n";
    }
    
    echo "✅ Messages system setup complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Error setting up messages table: " . $e->getMessage() . "\n";
}
?>

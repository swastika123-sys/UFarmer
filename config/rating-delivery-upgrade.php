<?php
require_once 'database.php';

// Enhanced database schema for rating system and same-day delivery
try {
    // Add delivery time slots table
    $pdo->exec("CREATE TABLE IF NOT EXISTS delivery_slots (
        id INT AUTO_INCREMENT PRIMARY KEY,
        farmer_id INT NOT NULL,
        date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        max_orders INT DEFAULT 5,
        current_orders INT DEFAULT 0,
        is_available BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE,
        UNIQUE KEY unique_farmer_date_time (farmer_id, date, start_time, end_time)
    )");

    // Add delivery preferences to farmers table
    $pdo->exec("ALTER TABLE farmers 
        ADD COLUMN IF NOT EXISTS offers_delivery BOOLEAN DEFAULT FALSE,
        ADD COLUMN IF NOT EXISTS delivery_radius_km INT DEFAULT 10,
        ADD COLUMN IF NOT EXISTS same_day_delivery BOOLEAN DEFAULT FALSE,
        ADD COLUMN IF NOT EXISTS delivery_fee DECIMAL(5,2) DEFAULT 0.00,
        ADD COLUMN IF NOT EXISTS min_order_delivery DECIMAL(8,2) DEFAULT 0.00"
    );

    // Add delivery tracking to orders table
    $pdo->exec("ALTER TABLE orders 
        ADD COLUMN IF NOT EXISTS delivery_slot_id INT,
        ADD COLUMN IF NOT EXISTS delivery_time_start TIME,
        ADD COLUMN IF NOT EXISTS delivery_time_end TIME,
        ADD COLUMN IF NOT EXISTS customer_latitude DECIMAL(10, 8),
        ADD COLUMN IF NOT EXISTS customer_longitude DECIMAL(11, 8),
        ADD COLUMN IF NOT EXISTS delivery_fee DECIMAL(8,2) DEFAULT 0.00,
        ADD COLUMN IF NOT EXISTS estimated_delivery_time DATETIME,
        ADD COLUMN IF NOT EXISTS actual_delivery_time DATETIME,
        ADD COLUMN IF NOT EXISTS delivery_instructions TEXT,
        ADD FOREIGN KEY IF NOT EXISTS (delivery_slot_id) REFERENCES delivery_slots(id)"
    );

    // Enhance reviews table for better rating system
    $pdo->exec("ALTER TABLE reviews 
        ADD COLUMN IF NOT EXISTS product_quality_rating INT CHECK (product_quality_rating >= 1 AND product_quality_rating <= 5),
        ADD COLUMN IF NOT EXISTS delivery_rating INT CHECK (delivery_rating >= 1 AND delivery_rating <= 5),
        ADD COLUMN IF NOT EXISTS service_rating INT CHECK (service_rating >= 1 AND service_rating <= 5),
        ADD COLUMN IF NOT EXISTS would_recommend BOOLEAN DEFAULT TRUE,
        ADD COLUMN IF NOT EXISTS review_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
        ADD COLUMN IF NOT EXISTS helpful_votes INT DEFAULT 0,
        ADD COLUMN IF NOT EXISTS verified_purchase BOOLEAN DEFAULT FALSE"
    );

    // Add constraint to prevent multiple reviews per order
    $pdo->exec("ALTER TABLE reviews 
        ADD CONSTRAINT IF NOT EXISTS unique_customer_order_review 
        UNIQUE (customer_id, order_id)"
    );

    // Create rating summary table for faster queries
    $pdo->exec("CREATE TABLE IF NOT EXISTS farmer_rating_summary (
        farmer_id INT PRIMARY KEY,
        total_reviews INT DEFAULT 0,
        average_rating DECIMAL(3,2) DEFAULT 0.00,
        average_product_quality DECIMAL(3,2) DEFAULT 0.00,
        average_delivery DECIMAL(3,2) DEFAULT 0.00,
        average_service DECIMAL(3,2) DEFAULT 0.00,
        five_star_count INT DEFAULT 0,
        four_star_count INT DEFAULT 0,
        three_star_count INT DEFAULT 0,
        two_star_count INT DEFAULT 0,
        one_star_count INT DEFAULT 0,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE
    )");

    // Create notification system for delivery updates
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('order_update', 'delivery_reminder', 'rating_request', 'general') DEFAULT 'general',
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        related_order_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (related_order_id) REFERENCES orders(id) ON DELETE SET NULL
    )");

    // Initialize default delivery slots for farmers (next 7 days, 2-hour slots from 8 AM to 6 PM)
    $stmt = $pdo->prepare("SELECT id FROM farmers WHERE offers_delivery = TRUE OR same_day_delivery = TRUE");
    $stmt->execute();
    $farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($farmers as $farmer) {
        for ($day = 0; $day < 7; $day++) {
            $date = date('Y-m-d', strtotime("+$day days"));
            
            // Skip if today and current time is past 2 PM (no same-day delivery after 2 PM)
            if ($day === 0 && date('H') >= 14) {
                continue;
            }
            
            $timeSlots = [
                ['08:00:00', '10:00:00'],
                ['10:00:00', '12:00:00'],
                ['12:00:00', '14:00:00'],
                ['14:00:00', '16:00:00'],
                ['16:00:00', '18:00:00']
            ];
            
            foreach ($timeSlots as $slot) {
                // For same-day delivery, only allow slots at least 2 hours from now
                if ($day === 0) {
                    $slotStartTime = strtotime("$date {$slot[0]}");
                    $twoHoursFromNow = time() + (2 * 3600);
                    if ($slotStartTime <= $twoHoursFromNow) {
                        continue;
                    }
                }
                
                $checkStmt = $pdo->prepare("SELECT id FROM delivery_slots WHERE farmer_id = ? AND date = ? AND start_time = ?");
                $checkStmt->execute([$farmer['id'], $date, $slot[0]]);
                
                if (!$checkStmt->fetch()) {
                    $insertStmt = $pdo->prepare("INSERT INTO delivery_slots (farmer_id, date, start_time, end_time, max_orders) VALUES (?, ?, ?, ?, 5)");
                    $insertStmt->execute([$farmer['id'], $date, $slot[0], $slot[1]]);
                }
            }
        }
    }

    // Initialize rating summaries for existing farmers
    $stmt = $pdo->prepare("
        INSERT INTO farmer_rating_summary (farmer_id, total_reviews, average_rating) 
        SELECT f.id, f.total_reviews, f.rating 
        FROM farmers f 
        LEFT JOIN farmer_rating_summary frs ON f.id = frs.farmer_id 
        WHERE frs.farmer_id IS NULL
    ");
    $stmt->execute();

    echo "✅ Database schema enhanced successfully!\n";
    echo "✅ Delivery slots table created\n";
    echo "✅ Enhanced reviews system added\n";
    echo "✅ Farmer rating summary table created\n";
    echo "✅ Notifications system added\n";
    echo "✅ Initial delivery slots generated\n";

} catch(PDOException $e) {
    die("Error upgrading database: " . $e->getMessage());
}
?>

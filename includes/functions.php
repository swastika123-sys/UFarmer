<?php
// Use system temp directory for sessions instead of local tmp to avoid permission issues
ini_set('session.save_path', sys_get_temp_dir());

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/debug.php';

// Log HTTP requests
if (!defined('DISABLE_HTTP_LOGGING')) {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    DebugLogger::httpRequest($method, $uri, http_response_code() ?: 200);
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function login($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_type'] = $user['user_type'];
        return true;
    }
    
    return false;
}

function register($name, $email, $password, $userType = 'customer') {
    global $pdo;
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false;
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $hashedPassword, $userType]);
}

function logout() {
    session_destroy();
    header('Location: ' . SITE_URL);
    exit();
}

// Farmer functions
function getFarmerByUserId($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM farmers WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createFarmerProfile($userId, $farmName, $description, $location, $phone) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Create farmer profile
        $stmt = $pdo->prepare("INSERT INTO farmers (user_id, farm_name, description, location, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $farmName, $description, $location, $phone]);
        
        // Add initial wallet balance of $500 for new farmers
        $stmt2 = $pdo->prepare("UPDATE users SET wallet_balance = 500.00 WHERE id = ?");
        $stmt2->execute([$userId]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollback();
        return false;
    }
}

function getAllFarmers($limit = null, $orderBy = 'created_at DESC') {
    global $pdo;
    $sql = "SELECT f.*, u.name as owner_name, u.email 
            FROM farmers f 
            JOIN users u ON f.user_id = u.id 
            ORDER BY " . $orderBy;
    
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFarmerProducts($farmerId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE farmer_id = ? AND is_active = 1");
    $stmt->execute([$farmerId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Product functions
function getAllProducts($limit = null) {
    global $pdo;
    $sql = "SELECT p.*, f.farm_name, u.name as farmer_name 
            FROM products p 
            JOIN farmers f ON p.farmer_id = f.id 
            JOIN users u ON f.user_id = u.id 
            WHERE p.is_active = 1 
            ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, f.farm_name, u.name as farmer_name 
                           FROM products p 
                           JOIN farmers f ON p.farmer_id = f.id 
                           JOIN users u ON f.user_id = u.id 
                           WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// File upload functions
function uploadFile($file, $directory, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp']) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        error_log("Upload failed: No file provided");
        return false;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("Upload failed: Error code " . $file['error']);
        return false;
    }
    
    $uploadDir = UPLOAD_PATH . $directory . '/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            error_log("Upload failed: Could not create directory " . $uploadDir);
            return false;
        }
    }
    
    // Verify directory is writable
    if (!is_writable($uploadDir)) {
        error_log("Upload failed: Directory not writable " . $uploadDir);
        return false;
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        error_log("Upload failed: Invalid file type " . $fileExtension);
        return false;
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        error_log("Upload failed: File too large " . $file['size']);
        return false;
    }
    
    $fileName = uniqid() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Ensure proper permissions
        chmod($filePath, 0644);
        error_log("Upload successful: " . $filePath);
        return $directory . '/' . $fileName;
    }
    
    error_log("Upload failed: Could not move file to " . $filePath);
    return false;
}

// Utility functions
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function generateStars($rating, $maxStars = 5) {
    $stars = '';
    for ($i = 1; $i <= $maxStars; $i++) {
        if ($i <= $rating) {
            $stars .= '<span class="star filled">★</span>';
        } else {
            $stars .= '<span class="star">☆</span>';
        }
    }
    return $stars;
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2629746) return floor($time/86400) . ' days ago';
    if ($time < 31556952) return floor($time/2629746) . ' months ago';
    return floor($time/31556952) . ' years ago';
}

function formatPrice($price) {
    return '₹' . number_format($price * 83, 0);
}

// Error handling
function handleError($message) {
    error_log($message);
    if (defined('DEBUG') && DEBUG) {
        die($message);
    } else {
        die('An error occurred. Please try again later.');
    }
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getCSRFInput() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// Wallet functions
function getUserWalletBalance($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END) as balance 
        FROM wallet_transactions 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (float)$result['balance'] : 0.00;
}

function addWalletTransaction($userId, $type, $amount, $description, $referenceType = 'purchase', $referenceId = null) {
    global $pdo;
    try {
        $pdo->beginTransaction();
        
        // Add transaction record
        $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description, reference_type, reference_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $type, $amount, $description, $referenceType, $referenceId]);
        
        // Update user wallet balance
        if ($type === 'credit') {
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
        }
        $stmt->execute([$amount, $userId]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollback();
        return false;
    }
}

function processWalletPayment($buyerId, $sellerId, $amount, $description, $referenceId = null) {
    global $pdo;
    
    // Check if buyer has sufficient balance
    $buyerBalance = getUserWalletBalance($buyerId);
    if ($buyerBalance < $amount) {
        return ['success' => false, 'message' => 'Insufficient wallet balance'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Debit from buyer
        $debitSuccess = addWalletTransaction($buyerId, 'debit', $amount, $description, 'purchase', $referenceId);
        
        // Credit to seller
        $creditSuccess = addWalletTransaction($sellerId, 'credit', $amount, "Sale: " . $description, 'sale', $referenceId);
        
        if ($debitSuccess && $creditSuccess) {
            $pdo->commit();
            return ['success' => true, 'message' => 'Payment processed successfully'];
        } else {
            $pdo->rollback();
            return ['success' => false, 'message' => 'Payment processing failed'];
        }
    } catch (Exception $e) {
        $pdo->rollback();
        return ['success' => false, 'message' => 'Payment processing error: ' . $e->getMessage()];
    }
}

function getWalletTransactions($userId, $limit = 10) {
    global $pdo;
    $limit = (int)$limit; // Ensure it's an integer
    $stmt = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT " . $limit);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Product discount functions
function getDiscountedPrice($originalPrice, $discountPercentage) {
    if ($discountPercentage > 0) {
        return $originalPrice - ($originalPrice * $discountPercentage / 100);
    }
    return $originalPrice;
}

function updateProductDiscount($productId, $discountPercentage) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        $discountedPrice = getDiscountedPrice($product['price'], $discountPercentage);
        $stmt = $pdo->prepare("UPDATE products SET discount_percentage = ?, discounted_price = ? WHERE id = ?");
        return $stmt->execute([$discountPercentage, $discountedPrice, $productId]);
    }
    return false;
}

// Enhanced Rating System Functions
function calculateFarmerRating($farmerId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as average_rating,
            AVG(product_quality_rating) as avg_product_quality,
            AVG(delivery_rating) as avg_delivery,
            AVG(service_rating) as avg_service,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM reviews 
        WHERE farmer_id = ?
    ");
    $stmt->execute([$farmerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total_reviews'] > 0) {
        // Update farmer rating summary
        $updateStmt = $pdo->prepare("
            INSERT INTO farmer_rating_summary 
            (farmer_id, total_reviews, average_rating, average_product_quality, average_delivery, average_service, 
             five_star_count, four_star_count, three_star_count, two_star_count, one_star_count) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            total_reviews = VALUES(total_reviews),
            average_rating = VALUES(average_rating),
            average_product_quality = VALUES(average_product_quality),
            average_delivery = VALUES(average_delivery),
            average_service = VALUES(average_service),
            five_star_count = VALUES(five_star_count),
            four_star_count = VALUES(four_star_count),
            three_star_count = VALUES(three_star_count),
            two_star_count = VALUES(two_star_count),
            one_star_count = VALUES(one_star_count)
        ");
        $updateStmt->execute([
            $farmerId,
            $result['total_reviews'],
            $result['average_rating'],
            $result['avg_product_quality'],
            $result['avg_delivery'],
            $result['avg_service'],
            $result['five_star'],
            $result['four_star'],
            $result['three_star'],
            $result['two_star'],
            $result['one_star']
        ]);
        
        // Update farmers table with new rating
        $farmerUpdateStmt = $pdo->prepare("UPDATE farmers SET rating = ?, total_reviews = ? WHERE id = ?");
        $farmerUpdateStmt->execute([$result['average_rating'], $result['total_reviews'], $farmerId]);
    }
    
    return $result;
}

function canUserReviewOrder($userId, $orderId) {
    global $pdo;
    
    // Check if order exists, belongs to user, is delivered, and hasn't been reviewed
    $stmt = $pdo->prepare("
        SELECT o.*, r.id as review_id 
        FROM orders o 
        LEFT JOIN reviews r ON o.id = r.order_id AND r.customer_id = ?
        WHERE o.id = ? AND o.customer_id = ? AND o.status = 'delivered'
    ");
    $stmt->execute([$userId, $orderId, $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result && !$result['review_id'];
}

function submitReview($customerId, $farmerId, $orderId, $rating, $comment, $productQuality = null, $delivery = null, $service = null, $wouldRecommend = true) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Insert review
        $stmt = $pdo->prepare("
            INSERT INTO reviews 
            (customer_id, farmer_id, order_id, rating, comment, product_quality_rating, delivery_rating, service_rating, would_recommend, verified_purchase) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$customerId, $farmerId, $orderId, $rating, $comment, $productQuality, $delivery, $service, $wouldRecommend]);
        
        // Recalculate farmer rating
        calculateFarmerRating($farmerId);
        
        // Create notification for farmer
        createNotification($farmerId, 'rating_request', 'New Review Received', 
            "You received a new {$rating}-star review from a customer.", $orderId);
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Review submission error: " . $e->getMessage());
        return false;
    }
}

function getFarmerReviews($farmerId, $limit = 10, $offset = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as customer_name, o.created_at as order_date
        FROM reviews r 
        JOIN users u ON r.customer_id = u.id 
        LEFT JOIN orders o ON r.order_id = o.id
        WHERE r.farmer_id = ?
        ORDER BY r.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$farmerId, $limit, $offset]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Delivery Slot Management Functions
function getAvailableDeliverySlots($farmerId, $date = null) {
    global $pdo;
    
    if (!$date) {
        $date = date('Y-m-d');
    }
    
    $stmt = $pdo->prepare("
        SELECT ds.*, 
               (ds.max_orders - ds.current_orders) as available_slots,
               CASE 
                   WHEN ds.date = CURDATE() AND ds.start_time <= ADDTIME(CURTIME(), '02:00:00') THEN FALSE
                   ELSE ds.is_available 
               END as can_book
        FROM delivery_slots ds 
        WHERE ds.farmer_id = ? 
        AND ds.date >= ?
        AND ds.date <= DATE_ADD(?, INTERVAL 7 DAY)
        AND ds.is_available = TRUE
        ORDER BY ds.date, ds.start_time
    ");
    $stmt->execute([$farmerId, $date, $date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function bookDeliverySlot($slotId, $orderId) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Check if slot is still available
        $stmt = $pdo->prepare("SELECT * FROM delivery_slots WHERE id = ? AND current_orders < max_orders FOR UPDATE");
        $stmt->execute([$slotId]);
        $slot = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$slot) {
            throw new Exception("Delivery slot not available");
        }
        
        // Update slot bookings
        $updateStmt = $pdo->prepare("UPDATE delivery_slots SET current_orders = current_orders + 1 WHERE id = ?");
        $updateStmt->execute([$slotId]);
        
        // Update order with delivery slot (simplified for current schema)
        $orderStmt = $pdo->prepare("
            UPDATE orders 
            SET delivery_date = ?
            WHERE id = ?
        ");
        $orderStmt->execute([
            $slot['date'],
            $orderId
        ]);
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Delivery slot booking error: " . $e->getMessage());
        return false;
    }
}

function createNotification($userId, $type, $title, $message, $orderId = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, related_order_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$userId, $type, $title, $message, $orderId]);
}

function getUserNotifications($userId, $limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markNotificationRead($notificationId, $userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
    return $stmt->execute([$notificationId, $userId]);
}

// Delivery Distance Calculation
function calculateDeliveryDistance($farmerLat, $farmerLng, $customerLat, $customerLng) {
    $earthRadius = 6371; // km
    
    $dLat = deg2rad($customerLat - $farmerLat);
    $dLng = deg2rad($customerLng - $farmerLng);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($farmerLat)) * cos(deg2rad($customerLat)) *
         sin($dLng/2) * sin($dLng/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

function isWithinDeliveryRadius($farmerId, $customerLat, $customerLng) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT latitude, longitude, delivery_radius_km 
        FROM farmers 
        WHERE id = ? AND offers_delivery = TRUE
    ");
    $stmt->execute([$farmerId]);
    $farmer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$farmer || !$farmer['latitude'] || !$farmer['longitude']) {
        return false;
    }
    
    $distance = calculateDeliveryDistance(
        $farmer['latitude'], 
        $farmer['longitude'], 
        $customerLat, 
        $customerLng
    );
    
    return $distance <= $farmer['delivery_radius_km'];
}

// Enhanced star display function
function generateStarsWithBreakdown($rating, $ratingBreakdown = null, $maxStars = 5) {
    $stars = generateStars($rating, $maxStars);
    
    if ($ratingBreakdown) {
        $total = array_sum($ratingBreakdown);
        if ($total > 0) {
            $breakdown = '<div class="rating-breakdown">';
            for ($i = 5; $i >= 1; $i--) {
                $count = $ratingBreakdown[$i . '_star'] ?? 0;
                $percentage = ($count / $total) * 100;
                $breakdown .= "
                    <div class='breakdown-row'>
                        <span class='star-label'>{$i} ★</span>
                        <div class='progress-bar-container'>
                            <div class='progress-bar' style='width: {$percentage}%'></div>
                        </div>
                        <span class='count'>({$count})</span>
                    </div>
                ";
            }
            $breakdown .= '</div>';
            return $stars . $breakdown;
        }
    }
    
    return $stars;
}
?>

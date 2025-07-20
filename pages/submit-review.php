<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || $_SESSION['user_type'] !== 'customer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$orderId = (int)$_POST['order_id'];
$farmerId = (int)$_POST['farmer_id'];
$rating = (int)$_POST['rating'];
$review = trim($_POST['review'] ?? '');
$customerId = getCurrentUser()['id'];

// Validation
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating value']);
    exit;
}

try {
    // If order_id is 0, this is a general farmer review (not tied to a specific order)
    if ($orderId === 0) {
        // Allow general farmer reviews without order verification
        $verifiedPurchase = 0;
    } else {
        // Verify order belongs to customer and is delivered
        $stmt = $pdo->prepare("
            SELECT id FROM orders 
            WHERE id = ? AND customer_id = ? AND farmer_id = ? AND status = 'delivered'
        ");
        $stmt->execute([$orderId, $customerId, $farmerId]);
        
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Order not found or not eligible for review']);
            exit;
        }
        
        // Check if already reviewed this specific order
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE customer_id = ? AND order_id = ?");
        $stmt->execute([$customerId, $orderId]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You have already reviewed this order']);
            exit;
        }
        
        $verifiedPurchase = 1;
    }
    
    // For general reviews (orderId = 0), check if user has already reviewed this farmer generally
    if ($orderId === 0) {
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE customer_id = ? AND farmer_id = ? AND order_id = 0");
        $stmt->execute([$customerId, $farmerId]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You have already reviewed this farmer']);
            exit;
        }
    }
    
    // Insert review
    $stmt = $pdo->prepare("
        INSERT INTO reviews (customer_id, farmer_id, order_id, rating, comment, verified_purchase, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$customerId, $farmerId, $orderId, $rating, $review, $verifiedPurchase]);
    
    // Update farmer's rating summary if function exists
    if (function_exists('calculateFarmerRating')) {
        calculateFarmerRating($farmerId);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for your review! Your feedback helps other customers and supports local farmers.'
    ]);
    
} catch (Exception $e) {
    error_log("Review submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while submitting your review']);
}
?>

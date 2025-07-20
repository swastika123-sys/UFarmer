<?php
require_once '../../includes/functions.php';

// Ensure user is logged in and is a farmer
if (!isLoggedIn() || $_SESSION['user_type'] !== 'farmer') {
    header('Location: ' . SITE_URL);
    exit();
}

$farmer = getFarmerByUserId($_SESSION['user_id']);

if (!$farmer) {
    header('Location: setup.php');
    exit();
}

// Get product ID
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if (!$product_id) {
    $_SESSION['error_message'] = 'Invalid product ID.';
    header('Location: dashboard.php');
    exit();
}

// Verify the product belongs to this farmer
global $pdo;
$stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ? AND farmer_id = ?");
$stmt->execute([$product_id, $farmer['id']]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $_SESSION['error_message'] = 'Product not found or you do not have permission to delete it.';
    header('Location: dashboard.php');
    exit();
}

try {
    // Delete the product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND farmer_id = ?");
    $stmt->execute([$product_id, $farmer['id']]);
    
    $_SESSION['success_message'] = 'Product "' . $product['name'] . '" has been deleted successfully.';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Failed to delete product. Please try again.';
    error_log("Delete product error: " . $e->getMessage());
}

header('Location: dashboard.php');
exit();
?>

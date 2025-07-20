<?php
header('Content-Type: application/json');
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$farmerId = $_GET['farmer_id'] ?? null;
$date = $_GET['date'] ?? date('Y-m-d');

if (!$farmerId) {
    http_response_code(400);
    echo json_encode(['error' => 'Farmer ID required']);
    exit;
}

// Check if farmer offers delivery
$stmt = $pdo->prepare("
    SELECT offers_delivery, same_day_delivery, delivery_fee, min_order_delivery, delivery_radius_km 
    FROM farmers 
    WHERE id = ?
");
$stmt->execute([$farmerId]);
$farmer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$farmer || !$farmer['offers_delivery']) {
    http_response_code(404);
    echo json_encode(['error' => 'Delivery not available for this farmer']);
    exit;
}

// Get available delivery slots
$slots = getAvailableDeliverySlots($farmerId, $date);

// Filter out slots that can't be booked
$availableSlots = array_filter($slots, function($slot) {
    return $slot['can_book'] && $slot['available_slots'] > 0;
});

// Group slots by date
$slotsByDate = [];
foreach ($availableSlots as $slot) {
    $slotsByDate[$slot['date']][] = $slot;
}

echo json_encode([
    'success' => true,
    'farmer' => $farmer,
    'slots' => $slotsByDate,
    'same_day_available' => $farmer['same_day_delivery'] && !empty($slotsByDate[date('Y-m-d')]),
    'delivery_fee' => $farmer['delivery_fee'],
    'min_order' => $farmer['min_order_delivery'],
    'delivery_radius' => $farmer['delivery_radius_km']
]);
?>

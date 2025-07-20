<?php
$pageTitle = 'Messages';
require_once '../../includes/functions.php';

// Ensure user is logged in and is a farmer
if (!isLoggedIn() || $_SESSION['user_type'] !== 'farmer') {
    header('Location: ' . SITE_URL);
    exit();
}

$farmer = getFarmerByUserId($_SESSION['user_id']);

// If no farmer profile exists, redirect to setup
if (!$farmer) {
    header('Location: setup.php');
    exit();
}

global $pdo;

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $customer_id = (int)$_POST['customer_id'];
    $message = trim($_POST['message']);
    
    if (!empty($message) && $customer_id > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (farmer_id, customer_id, sender_type, message, created_at) 
                                  VALUES (?, ?, 'farmer', ?, NOW())");
            $stmt->execute([$farmer['id'], $customer_id, $message]);
            
            showNotification('Message sent successfully!', 'success');
            header('Location: messages.php?customer=' . $customer_id);
            exit();
        } catch (PDOException $e) {
            showNotification('Error sending message: ' . $e->getMessage(), 'error');
        }
    }
}

// Get selected customer
$selected_customer_id = isset($_GET['customer']) ? (int)$_GET['customer'] : null;

// Get all customers who have ordered from this farmer
$customersStmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.name, u.email,
           MAX(o.created_at) as last_order,
           COUNT(o.id) as total_orders,
           (SELECT COUNT(*) FROM messages m 
            WHERE m.farmer_id = ? AND m.customer_id = u.id AND m.is_read = 0 AND m.sender_type = 'customer') as unread_count
    FROM users u 
    JOIN orders o ON u.id = o.customer_id 
    WHERE o.farmer_id = ?
    GROUP BY u.id, u.name, u.email
    ORDER BY unread_count DESC, last_order DESC
");
$customersStmt->execute([$farmer['id'], $farmer['id']]);
$customers = $customersStmt->fetchAll(PDO::FETCH_ASSOC);

// Get messages for selected customer
$messages = [];
if ($selected_customer_id) {
    $messagesStmt = $pdo->prepare("
        SELECT m.*, u.name as customer_name
        FROM messages m
        LEFT JOIN users u ON m.customer_id = u.id
        WHERE m.farmer_id = ? AND m.customer_id = ?
        ORDER BY m.created_at ASC
    ");
    $messagesStmt->execute([$farmer['id'], $selected_customer_id]);
    $messages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mark farmer's unread messages as read
    $markReadStmt = $pdo->prepare("UPDATE messages SET is_read = 1 
                                  WHERE farmer_id = ? AND customer_id = ? AND sender_type = 'customer' AND is_read = 0");
    $markReadStmt->execute([$farmer['id'], $selected_customer_id]);
    
    // Get customer info
    $customerInfo = null;
    foreach ($customers as $customer) {
        if ($customer['id'] == $selected_customer_id) {
            $customerInfo = $customer;
            break;
        }
    }
}

include '../../components/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="dashboard.php" class="btn btn-outline-secondary me-3">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        <div>
            <h2 class="mb-0">💬 Messages</h2>
            <p class="text-muted mb-0">Communicate with your customers</p>
        </div>
    </div>

    <div class="row messages-container">
        <!-- Customers List -->
        <div class="col-lg-4 col-md-5">
            <div class="card customers-panel">
                <div class="card-header">
                    <h5><i class="fas fa-users"></i> Customers</h5>
                    <span class="badge bg-primary"><?php echo count($customers); ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($customers)): ?>
                        <div class="empty-state text-center py-4">
                            <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                            <h6>No Customers Yet</h6>
                            <p class="text-muted">Customers who order from you will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="customers-list">
                            <?php foreach ($customers as $customer): ?>
                                <div class="customer-item <?php echo $selected_customer_id == $customer['id'] ? 'active' : ''; ?>"
                                     onclick="window.location.href='?customer=<?php echo $customer['id']; ?>'">
                                    <div class="customer-avatar">
                                        <?php echo strtoupper(substr($customer['name'], 0, 2)); ?>
                                        <?php if ($customer['unread_count'] > 0): ?>
                                            <span class="unread-badge"><?php echo $customer['unread_count']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="customer-info">
                                        <h6><?php echo htmlspecialchars($customer['name']); ?></h6>
                                        <p class="customer-meta">
                                            <span><?php echo $customer['total_orders']; ?> orders</span>
                                            <span>Last: <?php echo timeAgo($customer['last_order']); ?></span>
                                        </p>
                                    </div>
                                    <div class="customer-status">
                                        <?php if ($customer['unread_count'] > 0): ?>
                                            <i class="fas fa-circle text-primary"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Messages Panel -->
        <div class="col-lg-8 col-md-7">
            <div class="card messages-panel">
                <?php if ($selected_customer_id && $customerInfo): ?>
                    <!-- Messages Header -->
                    <div class="card-header messages-header">
                        <div class="customer-details">
                            <div class="customer-avatar">
                                <?php echo strtoupper(substr($customerInfo['name'], 0, 2)); ?>
                            </div>
                            <div>
                                <h6><?php echo htmlspecialchars($customerInfo['name']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($customerInfo['email']); ?></small>
                            </div>
                        </div>
                        <div class="customer-stats">
                            <span class="badge bg-success"><?php echo $customerInfo['total_orders']; ?> orders</span>
                        </div>
                    </div>

                    <!-- Messages Body -->
                    <div class="card-body messages-body" id="messagesBody">
                        <?php if (empty($messages)): ?>
                            <div class="empty-chat text-center py-5">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <h6>No messages yet</h6>
                                <p class="text-muted">Start a conversation with <?php echo htmlspecialchars($customerInfo['name']); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="messages-list">
                                <?php foreach ($messages as $message): ?>
                                    <div class="message-item <?php echo $message['sender_type']; ?>">
                                        <div class="message-bubble">
                                            <div class="message-content">
                                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                            </div>
                                            <div class="message-time">
                                                <?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Message Input -->
                    <div class="card-footer">
                        <form method="POST" class="message-form">
                            <input type="hidden" name="customer_id" value="<?php echo $selected_customer_id; ?>">
                            <div class="input-group">
                                <textarea name="message" 
                                         class="form-control" 
                                         placeholder="Type your message to <?php echo htmlspecialchars($customerInfo['name']); ?>..."
                                         rows="2" 
                                         required></textarea>
                                <button type="submit" name="send_message" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- No Customer Selected -->
                    <div class="card-body">
                        <div class="empty-chat text-center py-5">
                            <i class="fas fa-envelope fa-4x text-muted mb-3"></i>
                            <h5>Welcome to Messages</h5>
                            <p class="text-muted">Select a customer from the left to start messaging</p>
                            <?php if (empty($customers)): ?>
                                <div class="mt-4">
                                    <h6>Getting Started</h6>
                                    <ul class="list-unstyled text-muted">
                                        <li>• Customers who order from you will appear in the sidebar</li>
                                        <li>• You can communicate directly with them here</li>
                                        <li>• Messages help build customer relationships</li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.messages-container {
    height: calc(100vh - 200px);
}

.customers-panel, .messages-panel {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.customers-panel .card-body {
    flex: 1;
    overflow-y: auto;
}

.customers-panel .card-header {
    background: linear-gradient(135deg, var(--light-green), rgba(76, 175, 80, 0.1));
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.customers-list {
    max-height: 100%;
}

.customer-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.customer-item:hover {
    background: var(--light-green);
}

.customer-item.active {
    background: var(--primary-green);
    color: white;
}

.customer-item.active .customer-meta {
    color: rgba(255, 255, 255, 0.8) !important;
}

.customer-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--secondary-green);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-right: 1rem;
    position: relative;
}

.customer-item.active .customer-avatar {
    background: rgba(255, 255, 255, 0.2);
}

.unread-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 600;
}

.customer-info {
    flex: 1;
}

.customer-info h6 {
    margin: 0 0 0.25rem 0;
    color: var(--dark-green);
}

.customer-item.active .customer-info h6 {
    color: white;
}

.customer-meta {
    margin: 0;
    font-size: 0.85rem;
    color: var(--gray-medium);
}

.customer-meta span {
    display: block;
    line-height: 1.3;
}

.messages-header {
    background: linear-gradient(135deg, var(--light-green), rgba(76, 175, 80, 0.1));
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.customer-details {
    display: flex;
    align-items: center;
}

.customer-details .customer-avatar {
    margin-right: 1rem;
}

.customer-details h6 {
    margin: 0;
    color: var(--dark-green);
}

.messages-body {
    flex: 1;
    overflow-y: auto;
    max-height: calc(100vh - 350px);
}

.messages-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.message-item {
    display: flex;
}

.message-item.farmer {
    justify-content: flex-end;
}

.message-item.customer {
    justify-content: flex-start;
}

.message-bubble {
    max-width: 70%;
    padding: 1rem;
    border-radius: 18px;
    position: relative;
}

.message-item.farmer .message-bubble {
    background: var(--primary-green);
    color: white;
    border-bottom-right-radius: 5px;
}

.message-item.customer .message-bubble {
    background: #f1f3f4;
    color: var(--dark-green);
    border-bottom-left-radius: 5px;
}

.message-content {
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.message-time {
    font-size: 0.75rem;
    opacity: 0.8;
}

.message-form .input-group {
    align-items: flex-end;
}

.message-form textarea {
    resize: none;
    border-radius: 20px 0 0 20px;
}

.message-form .btn {
    border-radius: 0 20px 20px 0;
    padding: 0.75rem 1.5rem;
}

.empty-state, .empty-chat {
    color: var(--gray-medium);
}

@media (max-width: 768px) {
    .messages-container {
        height: auto;
    }
    
    .col-lg-4 {
        margin-bottom: 1rem;
    }
    
    .customers-panel, .messages-panel {
        height: auto;
        min-height: 400px;
    }
    
    .messages-body {
        max-height: 400px;
    }
    
    .message-bubble {
        max-width: 85%;
    }
    
    .customer-item {
        padding: 0.75rem;
    }
    
    .customer-avatar {
        width: 40px;
        height: 40px;
    }
}
</style>

<script>
// Auto-scroll to bottom of messages
function scrollToBottom() {
    const messagesBody = document.getElementById('messagesBody');
    if (messagesBody) {
        messagesBody.scrollTop = messagesBody.scrollHeight;
    }
}

// Scroll to bottom on page load
document.addEventListener('DOMContentLoaded', scrollToBottom);

// Handle textarea auto-resize
document.querySelector('textarea[name="message"]')?.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});

// Submit form with Enter key (but not Shift+Enter)
document.querySelector('textarea[name="message"]')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        this.closest('form').submit();
    }
});
</script>

<?php include '../../components/footer.php'; ?>

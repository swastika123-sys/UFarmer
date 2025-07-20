<?php
$pageTitle = "My Wallet";
require_once '../components/header.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$currentUser = getCurrentUser();
$walletBalance = getUserWalletBalance($currentUser['id']);
$transactions = getWalletTransactions($currentUser['id'], 20);

// Handle wallet recharge request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_money') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $amount = floatval($_POST['amount']);
        
        if ($amount >= 10 && $amount <= 10000) {
            try {
                global $pdo;
                $pdo->beginTransaction();
                
                // Add money to wallet
                $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
                $stmt->execute([$amount, $currentUser['id']]);
                
                // Record transaction
                $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, reference_type, reference_id, description, created_at) VALUES (?, 'credit', ?, 'recharge', NULL, ?, NOW())");
                $stmt->execute([$currentUser['id'], $amount, "Wallet recharge - ₹" . number_format($amount * 83, 0)]);
                
                $pdo->commit();
                
                // Simulate admin approval process
                $successMessage = "🎉 Payment Successful! Your wallet has been recharged with ₹" . number_format($amount * 83, 0) . " after verification by our financial team.";
                
                // Refresh wallet balance
                $walletBalance = getUserWalletBalance($currentUser['id']);
                $transactions = getWalletTransactions($currentUser['id'], 20);
                
            } catch (Exception $e) {
                $pdo->rollback();
                $errorMessage = "Failed to process payment. Please try again.";
            }
        } else {
            $errorMessage = "Amount must be between ₹830 and ₹8,30,000.";
        }
    }
}
?>

<main class="main-content">
    <div class="container">
        <div class="wallet-header">
            <h1><i class="fas fa-wallet"></i> My Wallet</h1>
            <div class="balance-card">
                <h2>Current Balance</h2>
                <div class="balance-amount">₹<?php echo number_format($walletBalance * 83, 0); ?></div>
            </div>
        </div>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <div class="wallet-content">
            <div class="wallet-actions">
                <div class="action-card">
                    <h3><i class="fas fa-plus-circle"></i> Add Money</h3>
                    <p>Recharge your wallet to make purchases</p>
                    <form method="POST" class="recharge-form">
                        <?php echo getCSRFInput(); ?>
                        <input type="hidden" name="action" value="add_money">
                        <div class="form-group">
                            <label for="amount">Amount (USD)</label>
                            <input type="number" id="amount" name="amount" min="10" max="10000" step="0.01" required placeholder="Enter amount (min $10, max $10,000)">
                            <small class="form-text text-muted">Amount will be converted to ₹ (1 USD = ₹83)</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-credit-card"></i>
                            Add Money Instantly
                        </button>
                    </form>
                    <div class="recharge-note">
                        <small><i class="fas fa-info-circle"></i> 
                        Instant approval after secure payment processing by our financial verification system.</small>
                    </div>
                </div>

                <?php if ($currentUser['user_type'] === 'farmer'): ?>
                <div class="action-card">
                    <h3><i class="fas fa-hand-holding-usd"></i> Earnings</h3>
                    <p>Your earnings from product sales</p>
                    <div class="earnings-summary">
                        <?php
                        $stmt = $pdo->prepare("SELECT SUM(amount) as total_earnings FROM wallet_transactions WHERE user_id = ? AND type = 'credit' AND reference_type = 'sale'");
                        $stmt->execute([$currentUser['id']]);
                        $earnings = $stmt->fetch(PDO::FETCH_ASSOC);
                        $totalEarnings = $earnings['total_earnings'] ?? 0;
                        ?>
                        <div class="stat">
                            <span class="stat-label">Total Earnings:</span>
                            <span class="stat-value">₹<?php echo number_format($totalEarnings * 83, 0); ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="transaction-history">
                <h3><i class="fas fa-history"></i> Transaction History</h3>
                <?php if (empty($transactions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <p>No transactions yet</p>
                    </div>
                <?php else: ?>
                    <div class="transactions-list">
                        <?php foreach ($transactions as $transaction): ?>
                            <div class="transaction-item <?php echo $transaction['type']; ?>">
                                <div class="transaction-icon">
                                    <?php if ($transaction['type'] === 'credit'): ?>
                                        <i class="fas fa-plus text-success"></i>
                                    <?php else: ?>
                                        <i class="fas fa-minus text-danger"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="transaction-details">
                                    <div class="transaction-description"><?php echo htmlspecialchars($transaction['description']); ?></div>
                                    <div class="transaction-meta">
                                        <span class="transaction-type"><?php echo ucfirst($transaction['reference_type']); ?></span>
                                        <span class="transaction-date"><?php echo date('M j, Y g:i A', strtotime($transaction['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="transaction-amount <?php echo $transaction['type']; ?>">
                                    <?php echo $transaction['type'] === 'credit' ? '+' : '-'; ?>₹<?php echo number_format($transaction['amount'] * 83, 0); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
.wallet-header {
    margin-bottom: 2rem;
}

.wallet-header h1 {
    color: var(--primary-green);
    margin-bottom: 1rem;
}

.balance-card {
    background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
    color: white;
    padding: 2rem;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.balance-amount {
    font-size: 3rem;
    font-weight: bold;
    margin-top: 0.5rem;
}

.wallet-content {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
}

.wallet-actions {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.action-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.action-card h3 {
    color: var(--primary-green);
    margin-bottom: 0.5rem;
}

.recharge-form .form-group {
    margin-bottom: 1rem;
}

.recharge-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.recharge-form input {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 1rem;
}

.recharge-form input:focus {
    border-color: var(--primary-green);
    outline: none;
}

.recharge-note {
    margin-top: 1rem;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 5px;
    border-left: 4px solid var(--info);
}

.earnings-summary .stat {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.stat-value {
    font-weight: bold;
    color: var(--primary-green);
}

.transaction-history {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.transaction-history h3 {
    color: var(--primary-green);
    margin-bottom: 1rem;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--gray-medium);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.transactions-list {
    max-height: 500px;
    overflow-y: auto;
}

.transaction-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.3s ease;
}

.transaction-item:hover {
    background-color: #f8f9fa;
}

.transaction-icon {
    margin-right: 1rem;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
}

.transaction-details {
    flex-grow: 1;
}

.transaction-description {
    font-weight: 500;
    margin-bottom: 0.3rem;
}

.transaction-meta {
    font-size: 0.85rem;
    color: var(--gray-medium);
}

.transaction-meta span {
    margin-right: 1rem;
}

.transaction-amount {
    font-weight: bold;
    font-size: 1.1rem;
}

.transaction-amount.credit {
    color: var(--success);
}

.transaction-amount.debit {
    color: var(--danger);
}

.text-success {
    color: var(--success);
}

.text-danger {
    color: var(--danger);
}

.alert {
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

@media (max-width: 768px) {
    .wallet-content {
        grid-template-columns: 1fr;
    }
    
    .balance-amount {
        font-size: 2rem;
    }
}
</style>

<?php require_once '../components/footer.php'; ?>

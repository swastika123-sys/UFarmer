<?php
session_start();

// Simulate logged in farmer for testing navbar
$_SESSION['user_id'] = 2;
$_SESSION['user_name'] = 'Maria Rodriguez';
$_SESSION['user_type'] = 'farmer';

$pageTitle = "Navbar Test - Farmer";
require_once '../components/header.php';
?>

<div style="padding: 2rem; text-align: center;">
    <h1>Farmer Navbar Layout Test</h1>
    <p>This page simulates a logged in farmer to test navbar layout.</p>
    <p>Check the navbar above - it should show:</p>
    <ul style="list-style: none; padding: 0;">
        <li>✓ User greeting (Hello, Maria Rodriguez!)</li>
        <li>✓ Wallet balance</li>
        <li>✓ My Farm link in navigation</li>
        <li>✓ Wallet button</li>
        <li>✓ Logout button</li>
        <li>✗ No Cart button (farmers don't shop)</li>
    </ul>
    
    <div style="margin-top: 2rem;">
        <a href="navbar-test.php" class="btn btn-secondary">Test Customer Navbar</a>
        <a href="../pages/farmer/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
    </div>
</div>

<?php require_once '../components/footer.php'; ?>

<?php
session_start();

// Simulate logged in user for testing navbar
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'John Doe';
$_SESSION['user_type'] = 'customer';

$pageTitle = "Navbar Test - Logged In";
require_once '../components/header.php';
?>

<div style="padding: 2rem; text-align: center;">
    <h1>Navbar Layout Test</h1>
    <p>This page simulates a logged in customer to test navbar layout.</p>
    <p>Check the navbar above - it should show:</p>
    <ul style="list-style: none; padding: 0;">
        <li>✓ User greeting (Hello, John Doe!)</li>
        <li>✓ Wallet balance</li>
        <li>✓ Cart button</li>
        <li>✓ Wallet button</li>
        <li>✓ Logout button</li>
    </ul>
    
    <h3>Test Different Screen Sizes:</h3>
    <p>Resize your browser window to test responsive behavior.</p>
    
    <div style="margin-top: 2rem;">
        <a href="../pages/auth/logout.php" class="btn btn-secondary">Logout</a>
        <a href="../index.php" class="btn btn-primary">Go to Homepage</a>
    </div>
</div>

<?php require_once '../components/footer.php'; ?>

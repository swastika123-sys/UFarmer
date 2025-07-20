<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/functions.php';

echo "<h2>Login Test</h2>";

if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    echo "Attempting login for: $email<br>";
    
    if (login($email, $password)) {
        echo "✅ Login successful!<br>";
        echo "User ID: " . $_SESSION['user_id'] . "<br>";
        echo "User Type: " . $_SESSION['user_type'] . "<br>";
        
        $user = getCurrentUser();
        if ($user) {
            echo "User Name: " . $user['name'] . "<br>";
            echo "Wallet Balance: $" . number_format(getUserWalletBalance($user['id']), 2) . "<br>";
        }
    } else {
        echo "❌ Login failed<br>";
    }
}
?>

<form method="POST">
    <h3>Test Login</h3>
    <p>
        <label>Email:</label><br>
        <input type="email" name="email" value="demo@example.com" required>
    </p>
    <p>
        <label>Password:</label><br>
        <input type="password" name="password" value="password" required>
    </p>
    <p>
        <button type="submit">Login</button>
    </p>
</form>

<h4>Available Test Accounts:</h4>
<ul>
    <li>demo@example.com / password (Customer)</li>
    <li>john@greenvalley.com / password (Farmer)</li>
    <li>maria@sunshineacres.com / password (Farmer)</li>
</ul>

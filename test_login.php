<?php
include_once 'api/conn.php';

echo "<h2>Test Login</h2>";

$email = 'admin@solidarite.ma';
$password = 'password123';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p style='color: green;'>✓ User found in database</p>";
        echo "<p><strong>Email:</strong> {$user['email']}</p>";
        echo "<p><strong>Role:</strong> {$user['role']}</p>";
        echo "<p><strong>Password hash:</strong> " . substr($user['password'], 0, 30) . "...</p>";
        
        echo "<hr>";
        
        // Test password
        if (password_verify($password, $user['password'])) {
            echo "<p style='color: green; font-size: 18px;'>✓✓✓ PASSWORD CORRECT! ✓✓✓</p>";
            echo "<p>You can login with:</p>";
            echo "<ul>";
            echo "<li><strong>Email:</strong> admin@solidarite.ma</li>";
            echo "<li><strong>Password:</strong> password123</li>";
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>✗ Password incorrect</p>";
            echo "<p>The password in database doesn't match 'password123'</p>";
            echo "<p>Run this SQL to fix:</p>";
            echo "<pre style='background: #f0f0f0; padding: 10px;'>";
            echo "UPDATE users SET password = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin@solidarite.ma';";
            echo "</pre>";
        }
    } else {
        echo "<p style='color: red;'>✗ User not found</p>";
        echo "<p>Email 'admin@solidarite.ma' doesn't exist in database</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

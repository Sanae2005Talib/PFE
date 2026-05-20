<?php
include_once 'api/conn.php';

echo "<h2>Reset Admin Password</h2>";

try {
    // New password: admin123
    $new_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Update admin password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@solidarite.ma'");
    $stmt->execute([$new_password]);
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green; font-size: 18px;'>✓ Password updated successfully!</p>";
        echo "<hr>";
        echo "<h3>Login Credentials:</h3>";
        echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 10px; display: inline-block;'>";
        echo "<p><strong>Email:</strong> admin@solidarite.ma</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "</div>";
        echo "<hr>";
        echo "<p>You can now login with these credentials in:</p>";
        echo "<ul>";
        echo "<li>Web: <a href='login.php'>http://localhost/solidarite_connect/login.php</a></li>";
        echo "<li>Flutter App: Use the same credentials</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>Email not found in database!</p>";
        echo "<p>Available admin emails:</p>";
        $stmt = $pdo->query("SELECT email FROM users WHERE role = 'admin'");
        $admins = $stmt->fetchAll();
        echo "<ul>";
        foreach ($admins as $admin) {
            echo "<li>{$admin['email']}</li>";
        }
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<?php
include_once 'api/conn.php';

echo "<h2>Creating Test Accounts...</h2>";

try {
    // Password: password123
    $password = password_hash('password123', PASSWORD_DEFAULT);
    
    // 1. Create Citizen
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'citizen@test.com'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Test Citizen', 'citizen@test.com', $password, 'citizen']);
        echo "<p style='color: green;'>✓ Citizen account created: citizen@test.com / password123</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Citizen account already exists: citizen@test.com / password123</p>";
    }
    
    // 2. Create Association
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'association@test.com'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Manager Association', 'association@test.com', $password, 'association']);
        $user_id = $pdo->lastInsertId();
        
        // Create association details
        $stmt = $pdo->prepare("INSERT INTO associations (user_id, association_name, phone, address, region_id, is_validated) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, 'Association Test', '+212 600000000', 'Casablanca, Morocco', 1, 1]);
        
        echo "<p style='color: green;'>✓ Association account created: association@test.com / password123</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Association account already exists: association@test.com / password123</p>";
    }
    
    // 3. Create Admin
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'admin@test.com'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Admin Test', 'admin@test.com', $password, 'admin']);
        echo "<p style='color: green;'>✓ Admin account created: admin@test.com / password123</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Admin account already exists: admin@test.com / password123</p>";
    }
    
    echo "<hr>";
    echo "<h3>Test Accounts Summary:</h3>";
    echo "<ul>";
    echo "<li><strong>Citizen:</strong> citizen@test.com / password123</li>";
    echo "<li><strong>Association:</strong> association@test.com / password123</li>";
    echo "<li><strong>Admin:</strong> admin@test.com / password123</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>All Users in Database:</h3>";
    $stmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

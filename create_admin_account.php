<?php
// Script pour créer un compte admin garanti
require_once 'api/conn.php';

echo "<h2>🔧 Création compte Admin</h2>";

// Email et password pour le compte admin
$email = 'admin@test.com';
$password = 'password123';
$name = 'Admin Test';
$role = 'admin';

// Hash du password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Vérifier si le compte existe déjà
$check_query = "SELECT * FROM users WHERE email = '$email'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    echo "<p style='color: orange;'>⚠️ Le compte existe déjà. Mise à jour du mot de passe...</p>";
    
    // Update password
    $update_query = "UPDATE users SET password = '$password_hash', name = '$name', role = '$role' WHERE email = '$email'";
    
    if (mysqli_query($conn, $update_query)) {
        echo "<div style='background-color: #d4edda; padding: 20px; border-radius: 5px; border-left: 4px solid #28a745;'>";
        echo "<h3>✅ Compte mis à jour avec succès!</h3>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Password:</strong> $password</p>";
        echo "<p><strong>Nom:</strong> $name</p>";
        echo "<p><strong>Rôle:</strong> $role</p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Erreur lors de la mise à jour: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ Création d'un nouveau compte...</p>";
    
    // Insert new account
    $insert_query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password_hash', '$role')";
    
    if (mysqli_query($conn, $insert_query)) {
        echo "<div style='background-color: #d4edda; padding: 20px; border-radius: 5px; border-left: 4px solid #28a745;'>";
        echo "<h3>✅ Compte créé avec succès!</h3>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Password:</strong> $password</p>";
        echo "<p><strong>Nom:</strong> $name</p>";
        echo "<p><strong>Rôle:</strong> $role</p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Erreur lors de la création: " . mysqli_error($conn) . "</p>";
    }
}

echo "<br><hr><br>";

// Afficher tous les comptes admin
echo "<h3>📋 Tous les comptes Admin:</h3>";
$admin_query = "SELECT id, name, email, role, created_at FROM users WHERE role = 'admin'";
$admin_result = mysqli_query($conn, $admin_query);

if (mysqli_num_rows($admin_result) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #10B981; color: white;'>";
    echo "<th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Date création</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($admin_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td><strong>" . $row['email'] . "</strong></td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Aucun compte admin trouvé!</p>";
}

echo "<br><hr><br>";

// Test du password hash
echo "<h3>🔐 Test Password Hash:</h3>";
$test_password = 'password123';
$test_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

if (password_verify($test_password, $test_hash)) {
    echo "<p style='color: green;'>✅ Le hash standard fonctionne pour 'password123'</p>";
} else {
    echo "<p style='color: red;'>❌ Le hash standard ne fonctionne pas</p>";
}

// Test avec le nouveau hash
$new_hash = password_hash($test_password, PASSWORD_DEFAULT);
if (password_verify($test_password, $new_hash)) {
    echo "<p style='color: green;'>✅ Le nouveau hash fonctionne pour 'password123'</p>";
    echo "<p><strong>Nouveau hash:</strong> <code>$new_hash</code></p>";
} else {
    echo "<p style='color: red;'>❌ Le nouveau hash ne fonctionne pas</p>";
}

mysqli_close($conn);
?>

<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background-color: #f5f5f5;
    }
    h2, h3 {
        color: #10B981;
    }
    code {
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }
</style>

<br><br>
<div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
    <h3>📝 Instructions:</h3>
    <ol>
        <li>Utilisez ces identifiants dans l'app Flutter:</li>
        <ul>
            <li><strong>Email:</strong> admin@test.com</li>
            <li><strong>Password:</strong> password123</li>
        </ul>
        <li>Testez le login avec: <a href="test_login_api.php" target="_blank">test_login_api.php</a></li>
    </ol>
</div>

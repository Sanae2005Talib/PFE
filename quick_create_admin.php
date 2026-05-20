<?php
// Script simple pour créer compte admin
$host = 'localhost';
$dbname = 'solidarite_connect';
$username = 'root';
$password = '';

// Connexion
$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Erreur connexion: " . mysqli_connect_error());
}

echo "<h2>🔧 Création Compte Admin</h2>";

// Supprimer ancien compte
$delete = "DELETE FROM users WHERE email = 'admin@test.com'";
mysqli_query($conn, $delete);

// Créer nouveau compte
$insert = "INSERT INTO users (name, email, password, role) VALUES (
    'Admin Test',
    'admin@test.com',
    '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
)";

if (mysqli_query($conn, $insert)) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>✅ Compte créé avec succès!</h3>";
    echo "<p><strong>Email:</strong> admin@test.com</p>";
    echo "<p><strong>Password:</strong> password123</p>";
    echo "</div>";
    
    // Vérifier
    $check = "SELECT id, name, email, role FROM users WHERE email = 'admin@test.com'";
    $result = mysqli_query($conn, $check);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px;'>";
        echo "<h4>📋 Compte vérifié:</h4>";
        echo "<p>ID: " . $row['id'] . "</p>";
        echo "<p>Nom: " . $row['name'] . "</p>";
        echo "<p>Email: " . $row['email'] . "</p>";
        echo "<p>Rôle: " . $row['role'] . "</p>";
        echo "</div>";
    }
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24;'>❌ Erreur</h3>";
    echo "<p>" . mysqli_error($conn) . "</p>";
    echo "</div>";
}

mysqli_close($conn);
?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    background: #f5f5f5;
}
</style>

<br><br>
<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>
    <h3>📝 Prochaine étape:</h3>
    <p>Testez maintenant avec: <a href="test_login_api.php">test_login_api.php</a></p>
    <p>Ou directement dans l'app Flutter avec:</p>
    <ul>
        <li><strong>Email:</strong> admin@test.com</li>
        <li><strong>Password:</strong> password123</li>
    </ul>
</div>

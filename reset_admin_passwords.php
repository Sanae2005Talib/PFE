<?php
// Script pour reset passwords de TOUS les admins
$host = 'localhost';
$dbname = 'solidarite_connect';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Erreur connexion: " . mysqli_connect_error());
}

echo "<h2>🔐 Reset Passwords Admin</h2>";

// Password hash pour "password123"
$password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

// Liste des admins à reset
$admins = [
    'admin@solidarite.ma',
    'admin@solidarite.com',
    'admin@test.com'
];

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
echo "<h3>📋 Reset en cours...</h3>";
echo "</div>";

foreach ($admins as $email) {
    $update = "UPDATE users SET password = '$password_hash' WHERE email = '$email'";
    
    if (mysqli_query($conn, $update)) {
        $affected = mysqli_affected_rows($conn);
        
        if ($affected > 0) {
            echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;'>";
            echo "✅ <strong>$email</strong> - Password mis à jour → <code>password123</code>";
            echo "</div>";
        } else {
            echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
            echo "⚠️ <strong>$email</strong> - Compte non trouvé ou déjà à jour";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
        echo "❌ <strong>$email</strong> - Erreur: " . mysqli_error($conn);
        echo "</div>";
    }
}

echo "<br><hr><br>";

// Vérifier les comptes
echo "<h3>📋 Vérification des comptes admin:</h3>";
$check = "SELECT id, name, email, role FROM users WHERE role = 'admin'";
$result = mysqli_query($conn, $check);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; background: white;'>";
echo "<tr style='background-color: #10B981; color: white;'>";
echo "<th>ID</th><th>Nom</th><th>Email</th><th>Password</th><th>Status</th>";
echo "</tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td><strong>" . $row['email'] . "</strong></td>";
    echo "<td><code>password123</code></td>";
    echo "<td><span style='background: #28a745; color: white; padding: 5px 10px; border-radius: 5px;'>✅ Prêt</span></td>";
    echo "</tr>";
}

echo "</table>";

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
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}
</style>

<br><br>
<div style='background: #d4edda; padding: 20px; border-radius: 5px; border-left: 4px solid #28a745;'>
    <h3>✅ Passwords réinitialisés!</h3>
    <p>Vous pouvez maintenant utiliser ces comptes:</p>
    <ul>
        <li><strong>admin@solidarite.ma</strong> / password123</li>
        <li><strong>admin@solidarite.com</strong> / password123</li>
        <li><strong>admin@test.com</strong> / password123</li>
    </ul>
</div>

<br>
<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
    <h3>🧪 Testez maintenant:</h3>
    <ol>
        <li><a href="test_login_api.php" target="_blank">Test Login API</a> - Testez l'API directement</li>
        <li>Ou lancez l'app Flutter et utilisez un des emails ci-dessus</li>
    </ol>
</div>

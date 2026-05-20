<?php
// Script FINAL pour fixer le password
$host = 'localhost';
$dbname = 'solidarite_connect';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Erreur connexion: " . mysqli_connect_error());
}

echo "<h2>🔧 Fix Password MAINTENANT</h2>";

// Créer un NOUVEAU hash pour password123
$new_password = 'password123';
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
echo "<h3>🔐 Nouveau hash créé:</h3>";
echo "<p><strong>Password:</strong> $new_password</p>";
echo "<p><strong>Hash:</strong> <code style='font-size: 11px;'>$new_hash</code></p>";
echo "</div>";

// Tester le nouveau hash
if (password_verify($new_password, $new_hash)) {
    echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;'>";
    echo "✅ Test hash: FONCTIONNE!";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
    echo "❌ Test hash: NE FONCTIONNE PAS!";
    echo "</div>";
}

// Update TOUS les admins avec le nouveau hash
$admins = ['admin@solidarite.ma', 'admin@solidarite.com', 'admin@test.com'];

echo "<br><h3>📝 Mise à jour des comptes admin:</h3>";

foreach ($admins as $email) {
    $update = "UPDATE users SET password = '$new_hash' WHERE email = '$email'";
    
    if (mysqli_query($conn, $update)) {
        $affected = mysqli_affected_rows($conn);
        
        if ($affected > 0) {
            echo "<div style='background: #d4edda; padding: 10px; margin: 5px 0; border-left: 4px solid #28a745;'>";
            echo "✅ <strong>$email</strong> - Password mis à jour!";
            echo "</div>";
        } else {
            echo "<div style='background: #fff3cd; padding: 10px; margin: 5px 0; border-left: 4px solid #ffc107;'>";
            echo "⚠️ <strong>$email</strong> - Pas de changement (compte existe pas ou déjà à jour)";
            echo "</div>";
        }
    }
}

echo "<br><hr><br>";

// Vérifier IMMÉDIATEMENT avec le nouveau hash
echo "<h3>🧪 Test IMMÉDIAT du login:</h3>";

$test_email = 'admin@test.com';
$test_password = 'password123';

$query = "SELECT * FROM users WHERE email = '$test_email'";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>User:</strong> " . $row['name'] . "</p>";
    echo "<p><strong>Email:</strong> " . $row['email'] . "</p>";
    echo "<p><strong>Hash actuel:</strong> <code style='font-size: 10px;'>" . substr($row['password'], 0, 50) . "...</code></p>";
    echo "</div>";
    
    // Test password
    if (password_verify($test_password, $row['password'])) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 10px 0; border: 3px solid #28a745;'>";
        echo "<h3 style='color: #155724; margin: 0;'>✅ ✅ ✅ PASSWORD FONCTIONNE! ✅ ✅ ✅</h3>";
        echo "<p style='margin: 10px 0 0 0;'>Le login devrait maintenant fonctionner avec:</p>";
        echo "<ul style='margin: 10px 0;'>";
        echo "<li><strong>Email:</strong> $test_email</li>";
        echo "<li><strong>Password:</strong> $test_password</li>";
        echo "</ul>";
        echo "</div>";
        
        // Simuler réponse API
        $api_response = [
            "success" => true,
            "message" => "Connexion réussie",
            "user" => [
                "id" => (int)$row['id'],
                "name" => $row['name'],
                "email" => $row['email'],
                "role" => $row['role']
            ],
            "token" => base64_encode($row['id'] . ':' . time())
        ];
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>📋 Réponse API attendue:</h4>";
        echo "<pre>" . json_encode($api_response, JSON_PRETTY_PRINT) . "</pre>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; margin: 10px 0; border: 3px solid #dc3545;'>";
        echo "<h3 style='color: #721c24; margin: 0;'>❌ PASSWORD NE FONCTIONNE TOUJOURS PAS!</h3>";
        echo "<p>Il y a un problème avec password_verify() sur votre serveur.</p>";
        echo "</div>";
    }
}

mysqli_close($conn);
?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    background-color: #f5f5f5;
}
h2, h3, h4 {
    color: #10B981;
}
code, pre {
    background: #f8f9fa;
    padding: 5px;
    border-radius: 3px;
    font-family: monospace;
}
pre {
    padding: 15px;
    overflow-x: auto;
}
</style>

<br><br>
<div style='background: #fff3cd; padding: 20px; border-radius: 5px; border-left: 4px solid #ffc107;'>
    <h3>🎯 Prochaines étapes:</h3>
    <ol>
        <li>Si vous voyez "✅ PASSWORD FONCTIONNE!" ci-dessus</li>
        <li>Testez maintenant avec: <a href="test_login_api.php" target="_blank" style="background: #0EA5E9; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;">Test Login API</a></li>
        <li>Puis dans l'app Flutter avec: <strong>admin@test.com</strong> / <strong>password123</strong></li>
    </ol>
</div>

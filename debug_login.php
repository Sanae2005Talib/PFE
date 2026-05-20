<?php
// Script pour debug le login step by step
$host = 'localhost';
$dbname = 'solidarite_connect';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Erreur connexion: " . mysqli_connect_error());
}

echo "<h2>🔍 Debug Login API</h2>";

// Test avec admin@test.com
$test_email = 'admin@test.com';
$test_password = 'password123';

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
echo "<h3>📋 Test avec:</h3>";
echo "<p><strong>Email:</strong> $test_email</p>";
echo "<p><strong>Password:</strong> $test_password</p>";
echo "</div>";

// Step 1: Chercher le user
echo "<h3>Step 1: Chercher le user dans la database</h3>";
$query = "SELECT * FROM users WHERE email = '$test_email'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ User trouvé!<br>";
    echo "<strong>ID:</strong> " . $user['id'] . "<br>";
    echo "<strong>Nom:</strong> " . $user['name'] . "<br>";
    echo "<strong>Email:</strong> " . $user['email'] . "<br>";
    echo "<strong>Rôle:</strong> " . $user['role'] . "<br>";
    echo "<strong>Password Hash:</strong> <code style='font-size: 11px;'>" . $user['password'] . "</code>";
    echo "</div>";
    
    // Step 2: Vérifier le password
    echo "<h3>Step 2: Vérifier le password</h3>";
    
    $password_hash = $user['password'];
    $is_valid = password_verify($test_password, $password_hash);
    
    if ($is_valid) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ <strong>Password CORRECT!</strong><br>";
        echo "password_verify('$test_password', hash) = TRUE";
        echo "</div>";
        
        // Step 3: Simuler la réponse API
        echo "<h3>Step 3: Réponse API simulée</h3>";
        $response = [
            "success" => true,
            "message" => "Connexion réussie",
            "user" => [
                "id" => (int)$user['id'],
                "name" => $user['name'],
                "email" => $user['email'],
                "role" => $user['role']
            ],
            "token" => base64_encode($user['id'] . ':' . time())
        ];
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ <strong>Réponse API:</strong><br>";
        echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ <strong>Password INCORRECT!</strong><br>";
        echo "password_verify('$test_password', hash) = FALSE<br><br>";
        echo "<strong>Problème:</strong> Le hash dans la database ne correspond pas au password 'password123'";
        echo "</div>";
        
        // Tester avec le hash standard
        echo "<h3>🔧 Test avec hash standard</h3>";
        $standard_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $is_standard_valid = password_verify($test_password, $standard_hash);
        
        echo "<p>Hash standard: <code style='font-size: 11px;'>$standard_hash</code></p>";
        echo "<p>Test: password_verify('$test_password', standard_hash) = " . ($is_standard_valid ? "TRUE ✅" : "FALSE ❌") . "</p>";
        
        if ($is_standard_valid) {
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "⚠️ <strong>Solution:</strong> Le hash standard fonctionne!<br>";
            echo "Il faut mettre à jour le hash dans la database.<br><br>";
            echo "<a href='reset_admin_passwords.php' style='background: #10B981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Réinitialiser les passwords</a>";
            echo "</div>";
        }
    }
    
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ User NON trouvé!<br>";
    echo "Aucun utilisateur avec l'email: $test_email";
    echo "</div>";
}

// Step 4: Tester l'API réelle
echo "<br><hr><br>";
echo "<h3>Step 4: Tester l'API réelle</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<p>Maintenant testez avec l'API réelle:</p>";
echo "<a href='test_login_api.php' target='_blank' style='background: #0EA5E9; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Test Login API</a>";
echo "</div>";

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

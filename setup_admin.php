<?php
include_once 'api/conn.php';

$email = 'admin@solidarite.com';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$name = 'System Admin';
$role = 'admin';

try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "L'admin avec l'email $email existe déjà.<br>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password, $role]);
        echo "Admin créé avec succès !<br>";
        echo "Email: $email<br>";
        echo "Password: $password<br>";
    }
    echo "<br><a href='login.php'>Aller à la page de connexion</a>";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

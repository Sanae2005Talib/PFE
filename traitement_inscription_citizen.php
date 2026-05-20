<?php
include_once 'api/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        header("Location: inscription_citizen.php?error=champs_vides");
        exit();
    }

    // Vérifier que les mots de passe correspondent
    if ($password !== $password_confirm) {
        header("Location: inscription_citizen.php?error=password_mismatch");
        exit();
    }

    // Vérifier si l'email existe déjà
    $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt_check->execute([$email]);
    if ($stmt_check->fetch()) {
        header("Location: inscription_citizen.php?error=email_exists");
        exit();
    }

    // Hash du mot de passe
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Insérer le nouvel utilisateur avec role = 'citizen'
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'citizen')");
        $stmt->execute([$name, $email, $password_hash]);

        // Rediriger vers la page de connexion avec message de succès
        header("Location: login.php?success=registered");
        exit();

    } catch (Exception $e) {
        header("Location: inscription_citizen.php?error=inscription_echouee");
        exit();
    }
} else {
    header("Location: inscription_citizen.php");
    exit();
}
?>

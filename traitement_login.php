<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include_once 'api/conn.php';

// Kan-chekkou wach l-data jaya format JSON (dial Flutter)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ila kant l-data men Flutter, n-akhdouha men $input, sinon men $_POST
    $email = isset($input['email']) ? $input['email'] : $_POST['email'];
    $password = isset($input['password']) ? $input['password'] : $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Hna t-akked men password_verify (Khass password ikoun hashed f la base)
    // Ila knti derti ghir ktaba 3adia f la base, dir: if ($user && $password === $user['password'])
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // ILA KANT REQUÊTE JSON (FLUTTER)
        if (isset($input['email'])) {
            echo json_encode([
                "status" => "success",
                "message" => "Connecté",
                "role" => $user['role'],
                "user_id" => $user['id']
            ]);
            exit();
        }

        // ILA KANT REQUÊTE FORMULAIRE (WEB PHP)
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
            exit();
        } elseif ($user['role'] === 'citizen') {
            header("Location: donateur_dashboard.php");
            exit();
        } else {
            header("Location: association_dashboard.php");
            exit();
        }
    } else {
        if (isset($input['email'])) {
            echo json_encode(["status" => "error", "message" => "Email ou password ghalat"]);
            exit();
        }
        header("Location: login.php?error=1");
        exit();
    }
}
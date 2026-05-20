<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../conn.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->name) || !isset($data->email) || !isset($data->password)) {
    echo json_encode([
        "success" => false,
        "message" => "Tous les champs sont requis"
    ]);
    exit();
}

try {
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data->email]);
    if ($stmt->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "Cet email est déjà utilisé"
        ]);
        exit();
    }

    // Insert user
    $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'citizen')");
    $stmt->execute([$data->name, $data->email, $hashed_password]);

    echo json_encode([
        "success" => true,
        "message" => "Inscription réussie"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur: " . $e->getMessage()
    ]);
}
?>

<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../conn.php';

$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (!isset($data->user_name) || !isset($data->email) || !isset($data->password) ||
    !isset($data->assoc_name) || !isset($data->phone) || !isset($data->region_id) || !isset($data->address)) {
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

    $pdo->beginTransaction();

    // Insert user
    $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'association')");
    $stmt->execute([$data->user_name, $data->email, $hashed_password]);
    $user_id = $pdo->lastInsertId();

    // Insert association
    $stmt = $pdo->prepare("
        INSERT INTO associations (user_id, association_name, phone, address, region_id, is_validated) 
        VALUES (?, ?, ?, ?, ?, 0)
    ");
    $stmt->execute([
        $user_id,
        $data->assoc_name,
        $data->phone,
        $data->address,
        $data->region_id
    ]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Inscription réussie. Votre compte sera validé par un administrateur."
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        "success" => false,
        "message" => "Erreur: " . $e->getMessage()
    ]);
}
?>

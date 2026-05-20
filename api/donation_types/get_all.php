<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../conn.php';

try {
    $stmt = $pdo->query("SELECT * FROM donation_types ORDER BY name ASC");
    $types = $stmt->fetchAll();

    echo json_encode([
        "success" => true,
        "count" => count($types),
        "data" => $types
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur: " . $e->getMessage()
    ]);
}
?>

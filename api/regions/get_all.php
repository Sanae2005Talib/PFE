<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../conn.php';

try {
    $stmt = $pdo->query("SELECT * FROM regions ORDER BY name ASC");
    $regions = $stmt->fetchAll();

    echo json_encode([
        "success" => true,
        "count" => count($regions),
        "data" => $regions
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur: " . $e->getMessage()
    ]);
}
?>

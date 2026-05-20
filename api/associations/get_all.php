<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../conn.php';

try {
    $where = " WHERE a.is_validated = 1";
    $params = [];

    if (isset($_GET['region_id']) && !empty($_GET['region_id'])) {
        $where .= " AND a.region_id = :region_id";
        $params[':region_id'] = $_GET['region_id'];
    }

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $where .= " AND a.association_name LIKE :search";
        $params[':search'] = '%' . $_GET['search'] . '%';
    }

    $sql = "SELECT 
                a.id,
                a.association_name,
                a.logo,
                a.description,
                a.phone,
                a.address,
                r.name as region_name,
                r.id as region_id,
                (SELECT COUNT(*) FROM needs WHERE association_id = a.id AND status != 'satisfied') as active_needs_count
            FROM associations a
            LEFT JOIN regions r ON a.region_id = r.id
            $where
            ORDER BY a.association_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $associations = $stmt->fetchAll();

    echo json_encode([
        "success" => true,
        "count" => count($associations),
        "data" => $associations
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur: " . $e->getMessage()
    ]);
}
?>

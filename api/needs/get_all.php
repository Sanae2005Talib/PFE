<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../conn.php';

try {
    // Build query with filters
    $where = " WHERE n.status != 'satisfied' AND a.is_validated = 1";
    $params = [];

    if (isset($_GET['region_id']) && !empty($_GET['region_id'])) {
        $where .= " AND a.region_id = :region_id";
        $params[':region_id'] = $_GET['region_id'];
    }

    if (isset($_GET['type_id']) && !empty($_GET['type_id'])) {
        $where .= " AND n.donation_type_id = :type_id";
        $params[':type_id'] = $_GET['type_id'];
    }

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $where .= " AND (n.title LIKE :search OR n.description LIKE :search)";
        $params[':search'] = '%' . $_GET['search'] . '%';
    }

    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $where .= " AND n.status = :status";
        $params[':status'] = $_GET['status'];
    }

    $sql = "SELECT 
                n.id,
                n.title,
                n.description,
                n.status,
                n.quantity,
                n.location,
                n.deadline,
                a.id as association_id,
                a.association_name,
                a.phone,
                a.logo,
                r.name as region_name,
                t.name as type_name,
                t.id as type_id
            FROM needs n 
            JOIN associations a ON n.association_id = a.id
            JOIN regions r ON a.region_id = r.id 
            JOIN donation_types t ON n.donation_type_id = t.id
            $where 
            ORDER BY (n.status = 'urgent') DESC, n.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $needs = $stmt->fetchAll();

    echo json_encode([
        "success" => true,
        "count" => count($needs),
        "data" => $needs
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur: " . $e->getMessage()
    ]);
}
?>

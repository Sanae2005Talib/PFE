<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../conn.php';

if (!isset($_GET['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "ID association requis"
    ]);
    exit();
}

try {
    // Get association details
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            r.name as region_name,
            u.email,
            u.name as user_name
        FROM associations a
        LEFT JOIN regions r ON a.region_id = r.id
        LEFT JOIN users u ON a.user_id = u.id
        WHERE a.id = ? AND a.is_validated = 1
    ");
    $stmt->execute([$_GET['id']]);
    $association = $stmt->fetch();

    if (!$association) {
        echo json_encode([
            "success" => false,
            "message" => "Association non trouvée"
        ]);
        exit();
    }

    // Get association needs
    $stmt_needs = $pdo->prepare("
        SELECT 
            n.*,
            t.name as type_name
        FROM needs n
        JOIN donation_types t ON n.donation_type_id = t.id
        WHERE n.association_id = ?
        ORDER BY (n.status = 'urgent') DESC, n.id DESC
    ");
    $stmt_needs->execute([$_GET['id']]);
    $needs = $stmt_needs->fetchAll();

    // Get stats
    $stats = [
        'total_needs' => count($needs),
        'urgent_needs' => count(array_filter($needs, fn($n) => $n['status'] === 'urgent')),
        'satisfied_needs' => count(array_filter($needs, fn($n) => $n['status'] === 'satisfied'))
    ];

    echo json_encode([
        "success" => true,
        "data" => [
            "association" => $association,
            "needs" => $needs,
            "stats" => $stats
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur: " . $e->getMessage()
    ]);
}
?>

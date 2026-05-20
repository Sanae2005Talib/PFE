<?php
header('Content-Type: application/json');
include_once 'conn.php';

if (!isset($_GET['association_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID association manquant']);
    exit();
}

$association_id = $_GET['association_id'];

try {
    // Récupérer les infos de l'association
    $stmt = $pdo->prepare("
        SELECT a.*, r.name as region_name 
        FROM associations a 
        LEFT JOIN regions r ON a.region_id = r.id 
        WHERE a.id = ? AND a.is_validated = 1
    ");
    $stmt->execute([$association_id]);
    $association = $stmt->fetch();

    if (!$association) {
        echo json_encode(['success' => false, 'message' => 'Association non trouvée ou non validée']);
        exit();
    }

    // Récupérer les besoins actifs de l'association
    $stmt_needs = $pdo->prepare("
        SELECT n.*, t.name as type_name 
        FROM needs n 
        JOIN donation_types t ON n.donation_type_id = t.id 
        WHERE n.association_id = ? AND n.status != 'satisfied'
        ORDER BY 
            CASE WHEN n.status = 'urgent' THEN 1 ELSE 2 END,
            n.id DESC
    ");
    $stmt_needs->execute([$association_id]);
    $needs = $stmt_needs->fetchAll();

    echo json_encode([
        'success' => true,
        'association' => $association,
        'needs' => $needs
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>

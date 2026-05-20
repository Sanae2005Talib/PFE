<?php
header('Content-Type: application/json');
include_once 'conn.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit();
}

$id = $_GET['id'];

try {
    // Récupérer les infos de l'association
    $stmt = $pdo->prepare("
        SELECT a.*, u.email, u.name as user_name 
        FROM associations a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.id = ?
    ");
    $stmt->execute([$id]);
    $association = $stmt->fetch();

    if (!$association) {
        echo json_encode(['success' => false, 'message' => 'Association non trouvée']);
        exit();
    }

    // Récupérer les besoins de l'association
    $stmt_needs = $pdo->prepare("
        SELECT n.*, t.name as type_name 
        FROM needs n 
        JOIN donation_types t ON n.donation_type_id = t.id 
        WHERE n.association_id = ? 
        ORDER BY n.created_at DESC
    ");
    $stmt_needs->execute([$id]);
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

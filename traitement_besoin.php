<?php
session_start();
include_once 'api/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Récupérer l'association_id
    $stmt_assoc = $pdo->prepare("SELECT id FROM associations WHERE user_id = ?");
    $stmt_assoc->execute([$_SESSION['user_id']]);
    $assoc = $stmt_assoc->fetch();

    if (!$assoc) { 
        die("Erreur: Association non trouvée."); 
    }

    $association_id = $assoc['id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $donation_type_id = $_POST['donation_type_id'];
    $status = $_POST['status'];
    $quantity = trim($_POST['quantity']);
    $location = trim($_POST['location']);
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

    // 2. Validation
    if (empty($title) || empty($description) || empty($quantity) || empty($location)) {
        header("Location: ajouter_besoin.php?error=champs_vides");
        exit();
    }

    // 3. Insérer dans la table NEEDS
    $sql = "INSERT INTO needs (association_id, donation_type_id, title, description, status, quantity, location, deadline) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([
        $association_id, 
        $donation_type_id, 
        $title, 
        $description, 
        $status,
        $quantity,
        $location,
        $deadline
    ])) {
        header("Location: association_dashboard.php?success=besoin_ajoute");
    } else {
        header("Location: ajouter_besoin.php?error=ajout_echoue");
    }
    exit();
}
?>

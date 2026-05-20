<?php
session_start();
include_once 'api/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $donation_type_id = $_POST['donation_type_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $quantity = trim($_POST['quantity'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

    // Validation
    if (!$id || empty($title) || empty($quantity) || empty($location)) {
        header("Location: modifier_besoin.php?id=$id&error=champs_vides");
        exit();
    }

    // Mise à jour SQL
    $sql = "UPDATE needs SET title = ?, description = ?, donation_type_id = ?, status = ?, quantity = ?, location = ?, deadline = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$title, $description, $donation_type_id, $status, $quantity, $location, $deadline, $id])) {
        header("Location: association_dashboard.php?success=besoin_modifie");
        exit();
    } else {
        header("Location: modifier_besoin.php?id=$id&error=modification_echouee");
        exit();
    }
} else {
    header("Location: association_dashboard.php");
    exit();
}
?>
